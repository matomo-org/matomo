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
use Piwik\Date;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\TableLogAction;
use Piwik\Tracker\TrackerConfig;
use Piwik\UrlHelper;

/**
 * Manages the pages of a website whose Transitions report is pre-processed during archiving.
 *
 * @phpstan-import-type PreprocessedPage from Dao
 * @phpstan-type PreprocessedPageEntry array{idpage: int, url: string, created_date: string, created_by: string|null, created_by_superuser: bool}
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
        $pages = $this->dao->getPagesForSite($idSite);
        $superUserLogins = $this->dao->getSuperUserLogins(array_values(array_filter(array_column($pages, 'created_by'), 'is_string')));

        $entries = [];
        foreach ($pages as $page) {
            $entries[] = $this->toEntry($page, $superUserLogins);
        }

        return $entries;
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
            $existing = $this->dao->getPageByUrlHash($idSite, $urlHash);
            if ($existing !== null && $existing['removed_date'] === null) {
                throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageAlreadySelected'));
            }

            $maxPages = $this->getMaxPages($idSite);
            if ($this->dao->getNumPagesForSite($idSite) >= $maxPages) {
                throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPagesLimitReached', [(string) $maxPages]));
            }

            $isAdded = $existing === null
                ? $this->dao->addPage($idSite, $url, $urlHash, Date::now()->getDatetime(), $createdBy)
                : $this->dao->restorePage($existing['idpage'], Date::now()->getDatetime(), $createdBy);
            if (!$isAdded) {
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

        // also for a URL not tracked yet, whose days then hold zero records, and for a removed page added again, which
        // archiving skipped meanwhile
        $this->invalidator->scheduleReArchiving([$idSite], 'Transitions', Records::getRebuildReportName($page['idpage']));

        return $this->toEntry($page, $this->dao->getSuperUserLogins($page['created_by'] === null ? [] : [$page['created_by']]));
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

        // the row is kept, so the page's stored records stay readable by URL
        $this->dao->removePage($idSite, $idPage, Date::now()->getDatetime());

        $this->invalidator->removeInvalidationsSafely([$idSite], 'Transitions', Records::getRebuildReportName($idPage));
    }

    /**
     * The idpages of the website's pages that aren't removed.
     *
     * @return list<int>
     */
    public function getPageIds(int $idSite): array
    {
        return array_column($this->dao->getPagesForSite($idSite), 'idpage');
    }

    /**
     * The idaction each page that isn't removed is tracked under now, resolved in one query; null for a URL that isn't
     * tracked, or whose `log_action` row was deleted with the unused actions.
     *
     * @param list<int>|null $idPages only these pages, else all
     * @return array<int, int|null> idaction by idpage
     */
    public function getIdActionsByPage(int $idSite, ?array $idPages = null): array
    {
        $pages = $this->dao->getPagesForSite($idSite);
        if ($idPages !== null) {
            $pages = array_values(array_filter($pages, fn(array $page): bool => in_array($page['idpage'], $idPages, true)));
        }

        $resolvedIdActions = $this->getIdActionsForUrls(array_column($pages, 'url'));

        $idActions = [];
        foreach ($pages as $page) {
            $idActions[$page['idpage']] = $resolvedIdActions[$page['url']] ?? null;
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
     * The page whose stored records answer a report of the page URL: the page the URL is the same as, also a removed
     * one. None for a URL that normalizing changes beyond its protocol, `www.` and the case of its host, for example
     * by removing query parameters or a fragment the website no longer keeps, or by truncating it to a lower
     * `page_maximum_length`: it may have been tracked as another page before. A URL without protocol is read as
     * `http://…`.
     *
     * @return PreprocessedPage|null
     */
    public function getPageForReport(int $idSite, string $pageUrl): ?array
    {
        // the report's page switcher sends the name stored in `log_action`, without protocol, which doesn't tell
        // pages apart
        $pageUrl = trim($pageUrl);
        if (!preg_match('~^https?://~i', $pageUrl)) {
            $pageUrl = 'http://' . $pageUrl;
        }

        try {
            $url = $this->normalizeUrl($idSite, $pageUrl);
        } catch (BadRequestException $e) {
            return null;
        }

        if ($this->isChangedByNormalizing($pageUrl, $url)) {
            return null;
        }

        return $this->dao->getPageByUrlHash($idSite, self::getUrlHash($url));
    }

    private function isChangedByNormalizing(string $pageUrl, string $normalizedUrl): bool
    {
        // as PageUrl::cleanupUrl(), without truncating
        $url = str_replace(["\n", "\r", "\0"], '', trim(Common::unsanitizeInputValue(trim($pageUrl))));
        if (strlen($url) > TrackerConfig::getIntegerConfigValue('page_maximum_length', PHP_INT_MAX)) {
            return true;
        }

        return self::getComparableUrl(PageUrl::convertMatrixUrl($url)) !== self::getComparableUrl($normalizedUrl);
    }

    /**
     * The URL without protocol and `www.`, with a lowercase host and without the HTML encoding the tracker gives query
     * values.
     */
    private static function getComparableUrl(string $url): string
    {
        $url = Common::unsanitizeInputValue($url);
        $parsedUrl = @parse_url($url);
        if (is_array($parsedUrl) && !empty($parsedUrl['host'])) {
            $parsedUrl['host'] = mb_strtolower($parsedUrl['host']);
            $url = (string) UrlHelper::getParseUrlReverse($parsedUrl);
        }

        return (string) PageUrl::normalizeUrl($url)['url'];
    }

    /**
     * @return array{selected: bool, idPage: int|null, used: int, limit: int, rearchiveMonths: int}
     */
    public function getPageStatus(int $idSite, string $pageUrl): array
    {
        $page = $this->findPage($idSite, $pageUrl);
        if ($page !== null && $page['removed_date'] !== null) {
            $page = null;
        }

        return [
            'selected' => $page !== null,
            'idPage' => $page['idpage'] ?? null,
            'used' => $this->dao->getNumPagesForSite($idSite),
            'limit' => $this->getMaxPages($idSite),
            'rearchiveMonths' => RebuildWindow::getMonthsProcessed(),
        ];
    }

    /**
     * Finds the page a URL matches as a duplicate when added, also a removed one; a URL that can't be added matches
     * none.
     *
     * @return PreprocessedPage|null
     */
    private function findPage(int $idSite, string $pageUrl): ?array
    {
        try {
            $urlHash = self::getUrlHash($this->normalizeUrl($idSite, $pageUrl));
        } catch (BadRequestException $e) {
            return null;
        }

        return $this->dao->getPageByUrlHash($idSite, $urlHash);
    }

    /**
     * `[Transitions_<idSite>] max_preprocessed_pages` overrides `[Transitions] max_preprocessed_pages`.
     */
    public function getMaxPages(int $idSite): int
    {
        return Records::getConfigValue($idSite, 'max_preprocessed_pages', self::DEFAULT_MAX_PAGES);
    }

    /**
     * Seconds after which stored data built before its period ended is rebuilt in a request, 0 for never:
     * `[Transitions_<idSite>] preprocessed_refresh_after` overrides `[Transitions] preprocessed_refresh_after`.
     */
    public function getRefreshAfter(int $idSite): int
    {
        return Records::getConfigValue($idSite, 'preprocessed_refresh_after', self::DEFAULT_REFRESH_AFTER);
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
     * @param list<string> $superUserLogins
     * @return PreprocessedPageEntry
     */
    private function toEntry(array $page, array $superUserLogins): array
    {
        $isCreatedBySuperUser = $page['created_by'] !== null && in_array($page['created_by'], $superUserLogins, true);

        return [
            'idpage' => $page['idpage'],
            'url' => $page['url'],
            'created_date' => $page['created_date'],
            // as UsersManager, only super users see the logins of super users
            'created_by' => $isCreatedBySuperUser && !Piwik::hasUserSuperUserAccess() ? null : $page['created_by'],
            'created_by_superuser' => $isCreatedBySuperUser,
        ];
    }
}
