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
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Date;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Segment\SegmentExpression;
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
     * Longest URL accepted, the length of `log_action`.`name`.
     */
    public const MAX_URL_LENGTH = 4096;

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
        $lock->execute((string) $idSite, function () use ($idSite, $url, $urlHash, $createdBy): void {
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
        });

        // a URL not tracked yet has no records to build
        $idAction = $this->getIdActionForUrl($url);
        if ($idAction !== null) {
            $this->invalidator->scheduleReArchiving([$idSite], 'Transitions', Records::getRebuildReportName($idAction));
        }

        $page = $this->dao->getPageByUrlHash($idSite, $urlHash);
        if ($page === null) {
            throw new \Exception('The added page could not be read back.');
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

        $idAction = $this->getIdActionForUrl($page['url']);
        if ($idAction !== null) {
            $this->invalidator->removeInvalidationsSafely([$idSite], 'Transitions', Records::getRebuildReportName($idAction));
        }
    }

    /**
     * Returns the idaction of each listed page that has been tracked, resolved now.
     *
     * @return array<int, int> idaction by idpage
     */
    public function getIdActionsForSite(int $idSite): array
    {
        $idActions = [];
        foreach ($this->dao->getPagesForSite($idSite) as $page) {
            $idAction = $this->getIdActionForUrl($page['url']);
            if ($idAction !== null) {
                $idActions[$page['idpage']] = $idAction;
            }
        }

        return $idActions;
    }

    /**
     * Looks the URL up the way the Transitions report does, so protocol and `www.` variants match.
     */
    public function getIdActionForUrl(string $url): ?int
    {
        $idAction = TableLogAction::getIdActionFromSegment($url, 'idaction_url', SegmentExpression::MATCH_EQUAL, 'pageUrl');

        return is_numeric($idAction) && $idAction > 0 ? (int) $idAction : null;
    }

    /**
     * `[Transitions_<idSite>] max_preprocessed_pages` overrides `[Transitions] max_preprocessed_pages`.
     */
    public function getMaxPages(int $idSite): int
    {
        $config = Config::getInstance();
        $siteSection = 'Transitions_' . $idSite;

        foreach ([$config->$siteSection, $config->Transitions] as $section) {
            if (is_array($section) && isset($section['max_preprocessed_pages']) && is_numeric($section['max_preprocessed_pages'])) {
                return max(0, (int) $section['max_preprocessed_pages']);
            }
        }

        return self::DEFAULT_MAX_PAGES;
    }

    /**
     * Normalizes the URL the way the tracker stores it: excluded query parameters are removed, the host is lowercased
     * and the fragment is dropped unless the website keeps it.
     */
    public function normalizeUrl(int $idSite, string $pageUrl): string
    {
        $pageUrl = trim($pageUrl);

        if (mb_strlen($pageUrl) > self::MAX_URL_LENGTH) {
            throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageUrlTooLong', [(string) self::MAX_URL_LENGTH]));
        }

        $url = preg_match('~^https?://~i', $pageUrl) ? PageUrl::excludeQueryParametersFromUrl($pageUrl, $idSite) : false;

        if (!is_string($url) || empty(parse_url($url, PHP_URL_HOST))) {
            throw new BadRequestException(Piwik::translate('Transitions_PreprocessedPageInvalidUrl'));
        }

        return $url;
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
