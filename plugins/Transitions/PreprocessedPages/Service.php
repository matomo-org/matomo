<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Date;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\TableLogAction;

/**
 * Manages the pages of a website whose Transitions report is pre-processed during archiving.
 *
 * @phpstan-type PreprocessedPageEntry array{idpage: int, url: string, created_date: string, created_by: string|null}
 */
class Service
{
    public const DEFAULT_MAX_PAGES = 20;

    /**
     * Longest URL accepted. Like the tracker, normalization then truncates it to `[Tracker] page_maximum_length`.
     */
    public const MAX_URL_LENGTH = 4096;

    public const DEFAULT_REFRESH_AFTER = 900;

    public const LOCK_NAMESPACE = 'Transitions.addPreprocessedPage';

    private const NON_USER_LOGINS = ['anonymous', 'super user was set'];

    private Dao $dao;

    private ArchiveInvalidator $invalidator;

    private LockBackend $lockBackend;

    public function __construct(Dao $dao, ArchiveInvalidator $invalidator, LockBackend $lockBackend)
    {
        $this->dao = $dao;
        $this->invalidator = $invalidator;
        $this->lockBackend = $lockBackend;
    }

    /**
     * @return list<PreprocessedPageEntry> newest first
     */
    public function getPages(int $idSite): array
    {
        return array_map([$this, 'toEntry'], $this->dao->getPagesForSite($idSite));
    }

    /**
     * @return PreprocessedPageEntry
     */
    public function addPage(int $idSite, string $pageUrl): array
    {
        $url = $this->normalizeUrl($idSite, $pageUrl);
        $urlHash = self::getUrlHash($url);

        $login = Piwik::getCurrentUserLogin();
        $createdBy = in_array($login, self::NON_USER_LOGINS, true) ? null : $login;

        // concurrent adds must not both pass the limit check
        $lock = new Lock($this->lockBackend, self::LOCK_NAMESPACE);
        $this->acquireLock($lock, $idSite);

        try {
            if ($this->dao->getPageByUrlHash($idSite, $urlHash) !== null) {
                throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageAlreadySelected'));
            }

            $maxPages = $this->getMaxPages($idSite);
            if ($this->dao->getNumPagesForSite($idSite) >= $maxPages) {
                throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPagesLimitReached', [(string) $maxPages]));
            }

            if (!$this->dao->addPage($idSite, $url, $urlHash, Date::now()->getDatetime(), $createdBy)) {
                throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageAlreadySelected'));
            }

            // the website may have been deleted since it was checked, after its pages were cleaned up
            if (!$this->dao->siteExists($idSite)) {
                $this->dao->deletePagesForSite($idSite);
                throw new UnexpectedWebsiteFoundException('The requested website id = ' . $idSite . ' couldn\'t be found');
            }
        } finally {
            $lock->unlock();
        }

        $page = $this->dao->getPageByUrlHash($idSite, $urlHash);
        if ($page === null) {
            throw new \Exception('The added page could not be read back.');
        }

        // a URL not tracked yet is rebuilt once archiving finds it
        $idAction = $this->getIdActionForUrl($url);
        if ($idAction !== null) {
            $this->storeIdAction($idSite, $page['idpage'], $idAction, $page['idaction']);
        }

        return $this->toEntry($page);
    }

    /**
     * A page that does not exist and one of another website are rejected alike.
     */
    public function removePage(int $idSite, int $idPage): void
    {
        $page = $this->dao->getPage($idSite, $idPage);
        if ($page === null) {
            throw new ThingNotFoundException(Piwik::translate('Transitions_PreprocessedPageNotSelected'));
        }

        $this->dao->removePage($idSite, $idPage);

        $idAction = $page['idaction'] ?? $this->getIdActionForUrl($page['url']);
        if ($idAction !== null) {
            $this->invalidator->removeInvalidationsSafely([$idSite], 'Transitions', Records::getRebuildReportName($idAction));
        }
    }

    /**
     * Returns the idaction of each listed page that has been tracked, resolved now in one query. A page found under a
     * new idaction, such as one tracked for the first time since it was added, gets its data rebuilt like a new page.
     *
     * @return array<int, int> idaction by idpage
     */
    public function getIdActionsForSite(int $idSite): array
    {
        $pages = $this->dao->getPagesForSite($idSite);
        $resolvedIdActions = $this->getIdActionsForUrls(array_column($pages, 'url'));

        $idActions = [];
        foreach ($pages as $page) {
            $idAction = $resolvedIdActions[$page['url']] ?? null;
            if ($idAction === null) {
                continue;
            }

            if ($idAction !== $page['idaction']) {
                $this->storeIdAction($idSite, $page['idpage'], $idAction, $page['idaction']);
            }
            $idActions[$page['idpage']] = $idAction;
        }

        return $idActions;
    }

    /**
     * Returns the idaction last found for each listed page, without looking them up.
     *
     * @return array<int, int> idaction by idpage
     */
    public function getStoredIdActionsForSite(int $idSite): array
    {
        $idActions = [];
        foreach ($this->dao->getPagesForSite($idSite) as $page) {
            if ($page['idaction'] !== null) {
                $idActions[$page['idpage']] = $page['idaction'];
            }
        }

        return $idActions;
    }

    /**
     * Looks the URL up the way the Transitions report does, so protocol and `www.` variants match.
     */
    public function getIdActionForUrl(string $url): ?int
    {
        return $this->getIdActionsForUrls([$url])[$url] ?? null;
    }

    /**
     * Looks the URLs up like `TableLogAction::getIdActionFromSegment()` does for `pageUrl==`: by the URL without protocol
     * and `www.`, decoded, then as given.
     *
     * @param list<string> $urls
     * @return array<string, int> idaction by URL, for the URLs that have been tracked
     */
    private function getIdActionsForUrls(array $urls): array
    {
        $namesByUrl = [];
        foreach ($urls as $url) {
            $name = TableLogAction::removeProtocolIfSegmentStoredWithoutIt($url, Action::TYPE_PAGE_URL, 'pageUrl');
            $namesByUrl[$url] = array_values(array_unique([Common::unsanitizeInputValue($name), $name]));
        }

        $idActionsByName = $this->dao->getPageUrlIdActions(array_merge([], ...array_values($namesByUrl)));

        $idActions = [];
        foreach ($namesByUrl as $url => $names) {
            foreach ($names as $name) {
                if (isset($idActionsByName[$name])) {
                    $idActions[$url] = $idActionsByName[$name];
                    break;
                }
            }
        }

        return $idActions;
    }

    /**
     * Rebuilds the page's data within the re-archiving window when its idaction is new, instead of a rebuild still
     * pending for the previous one.
     */
    private function storeIdAction(int $idSite, int $idPage, int $idAction, ?int $previousIdAction): void
    {
        if (!$this->dao->setIdAction($idPage, $idAction)) {
            return;
        }

        if ($previousIdAction !== null && $previousIdAction !== $idAction) {
            $this->invalidator->removeInvalidationsSafely([$idSite], 'Transitions', Records::getRebuildReportName($previousIdAction));
        }
        $this->invalidator->scheduleReArchiving([$idSite], 'Transitions', Records::getRebuildReportName($idAction));
    }

    /**
     * `[Transitions_<idSite>] max_preprocessed_pages` overrides `[Transitions] max_preprocessed_pages`.
     */
    public function getMaxPages(int $idSite): int
    {
        return $this->getSiteConfigValue($idSite, 'max_preprocessed_pages', self::DEFAULT_MAX_PAGES);
    }

    /**
     * Seconds after which stored data built before its period ended is rebuilt in a request, 0 for never:
     * `[Transitions_<idSite>] preprocessed_refresh_after` overrides `[Transitions] preprocessed_refresh_after`.
     */
    public function getRefreshAfter(int $idSite): int
    {
        return $this->getSiteConfigValue($idSite, 'preprocessed_refresh_after', self::DEFAULT_REFRESH_AFTER);
    }

    /**
     * An integer >= 0 from the website's section, else from `[Transitions]`, else the default.
     */
    private function getSiteConfigValue(int $idSite, string $name, int $default): int
    {
        $config = Config::getInstance();
        $siteSection = 'Transitions_' . $idSite;

        foreach ([$siteSection => $config->$siteSection, 'Transitions' => $config->Transitions] as $sectionName => $section) {
            if (!is_array($section) || !array_key_exists($name, $section)) {
                continue;
            }

            $value = $section[$name];
            if (is_int($value) && $value >= 0) {
                return $value;
            }

            if (is_string($value) && ctype_digit($value)) {
                return (int) $value;
            }

            throw new \Exception('Invalid value for [' . $sectionName . '] ' . $name . ': expected an integer >= 0.');
        }

        return $default;
    }

    /**
     * Normalizes the URL the way the tracker stores it: excluded query parameters are removed, the host is lowercased
     * and the fragment is dropped unless the website keeps it.
     */
    public function normalizeUrl(int $idSite, string $pageUrl): string
    {
        $pageUrl = trim($pageUrl);

        // API parameters arrive HTML-encoded, which would count `&` as five characters
        if (mb_strlen(Common::unsanitizeInputValue($pageUrl)) > self::MAX_URL_LENGTH) {
            throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageUrlTooLong', [(string) self::MAX_URL_LENGTH]));
        }

        $url = preg_match('~^https?://~i', $pageUrl) ? PageUrl::excludeQueryParametersFromUrl($pageUrl, $idSite) : false;

        if (!is_string($url) || empty(parse_url($url, PHP_URL_HOST))) {
            throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageInvalidUrl'));
        }

        return $url;
    }

    /**
     * Waits up to 5 seconds, as `Lock::execute()` does.
     */
    private function acquireLock(Lock $lock, int $idSite): void
    {
        for ($attempt = 0; !$lock->acquireLock((string) $idSite); $attempt++) {
            if ($attempt >= 50) {
                throw new PreprocessedPagesBusyException();
            }

            usleep(100 * 1000);
        }
    }

    /**
     * Protocol and `www.` variants of a URL get the same hash, as they are the same page in `log_action`.
     */
    public static function getUrlHash(string $normalizedUrl): string
    {
        return sha1(PageUrl::normalizeUrl($normalizedUrl)['url']);
    }

    /**
     * @param array{idpage: int, url: string, created_date: string, created_by: string|null} $page
     * @return PreprocessedPageEntry
     */
    private function toEntry(array $page): array
    {
        return [
            'idpage' => $page['idpage'],
            'url' => $page['url'],
            'created_date' => $page['created_date'],
            'created_by' => $page['created_by'],
        ];
    }
}
