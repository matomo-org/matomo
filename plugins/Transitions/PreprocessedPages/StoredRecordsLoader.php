<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config\GeneralConfig;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Plugins\Transitions\RecordBuilders\PageTransitions;
use Piwik\Segment;
use Piwik\Site;

/**
 * Loads the stored records of a pre-processed page for a request, refreshing or building day records where allowed:
 *
 * - A selected page's records of today, or of yesterday until it is archived again, that were built before the day
 *   ended and are older than `preprocessed_refresh_after` are rebuilt for that page only. A longer period holding such
 *   a day is summed from its subperiods instead of its own archive.
 * - Without cron archiving, a requested single day without records is built for a selected page, and a longer period
 *   without records is summed from the stored records of its subperiods, down to days. Nothing else is built.
 *
 * Records are built as a partial archive of the page's records only, for all visits and pre-processed segments. At
 * most {@link MAX_SUMMED_PERIODS} periods are summed for a request.
 */
final class StoredRecordsLoader
{
    /**
     * The most periods summed for a request, so a request covering more is answered from the logs.
     */
    public const MAX_SUMMED_PERIODS = 400;

    private const LOCK_NAMESPACE = 'Transitions.buildPreprocessedDay.';

    private const LOCK_TTL_SECONDS = 120;

    private const LOCK_POLL_MILLISECONDS = 100;

    /**
     * A build that failed, or didn't finish, isn't tried again for at least this long.
     */
    private const MIN_RETRY_AFTER_SECONDS = 300;

    private const ATTEMPT_CACHE_ID_PREFIX = 'Transitions_buildPreprocessedDay_';

    /**
     * @var array{listed: bool, refreshAfter: int}|null
     */
    private ?array $pageSettings = null;

    public function __construct(
        private readonly StoredRecordsReader $reader,
        private readonly Service $service,
        private readonly LockBackend $lockBackend,
        private readonly LoggerInterface $logger,
        private readonly int $maxLockWaitMilliseconds = 10000,
        private readonly int $maxSummedPeriods = self::MAX_SUMMED_PERIODS
    ) {
    }

    /**
     * @param list<string> $tableParts the lists and the referrers to read, see {@link Records}
     * @param bool $isPeriodAllowed whether `max_period_allowed` allows the period; if not, only stored data that covers
     *                              it directly or is refreshed is used, so the logs refuse the rest early
     * @return StoredRecords|null null unless stored records cover the whole period
     */
    public function load(int $idSite, Period $period, Segment $segment, int $idPage, array $tableParts, bool $isPeriodAllowed = true): ?StoredRecords
    {
        $this->pageSettings = null;
        $periods = $period instanceof Range ? array_values($period->getSubperiods()) : [$period];
        if (count($periods) > $this->maxSummedPeriods) {
            return null;
        }
        $site = new Site($idSite);

        $records = $this->reader->readSum($idSite, $periods, $segment, $idPage, $tableParts);
        if ($records !== null && !$this->hasOutdatedPeriod($site, $periods, $records->getTsArchivedByPeriod())) {
            return $records;
        }

        $isWithoutCron = ArchivingMode::isWithoutCron();
        if ($records === null && (!$isWithoutCron || !$isPeriodAllowed)) {
            return null;
        }

        // removed pages keep their stored records, which are never rebuilt
        if (!$this->isListed($idSite, $idPage)) {
            return $records;
        }

        $canBuild = $this->canBuildForSegment($site, $segment);
        if ($records !== null && !$canBuild) {
            return $records;
        }

        $mayRefresh = $canBuild && $this->getRefreshAfter($idSite) > 0;
        $mayBuildDay = $isWithoutCron && $canBuild && !$period instanceof Range && $period->getLabel() === 'day';

        $resolved = $this->resolve($site, $periods, $segment, $idPage, $mayRefresh, $mayBuildDay, $isWithoutCron, false);
        if ($resolved === null) {
            return $records;
        }

        return $this->reader->readSum($idSite, $resolved, $segment, $idPage, $tableParts) ?? $records;
    }

    /**
     * Returns periods whose stored records cover the given ones, as fresh as allowed, building day records on the way.
     * The subperiods of all periods of one level are looked at together, the longest stored periods first.
     *
     * @param list<Period> $periods
     * @param bool $isWithinOutdatedPeriod whether the periods make up a covered period that is outdated: its past days
     *                                     without records had no visits, and today and yesterday may be built
     * @return list<Period>|null null if they can't be covered
     */
    private function resolve(
        Site $site,
        array $periods,
        Segment $segment,
        int $idPage,
        bool $mayRefresh,
        bool $mayBuildDay,
        bool $mayDecompose,
        bool $isWithinOutdatedPeriod
    ): ?array {
        if (count($periods) > $this->maxSummedPeriods) {
            return null;
        }

        $coverage = $this->reader->getCoverage((int) $site->getId(), $periods, $segment, $idPage);

        $resolved = [];
        $outdated = [];
        $uncovered = [];
        $uncoveredDays = [];
        foreach ($periods as $period) {
            $isDay = $period->getLabel() === 'day';

            if (array_key_exists($period->getRangeString(), $coverage)) {
                $tsArchived = $coverage[$period->getRangeString()];
                if ($mayRefresh && $tsArchived !== null && $this->isOutdated($site, $period, $tsArchived)) {
                    if ($isDay) {
                        $this->buildDay($site, $period, $segment, $idPage, true);
                    } else {
                        $outdated[] = $period;
                        continue;
                    }
                }

                $resolved[] = $period;
            } elseif ($isDay && $mayBuildDay) {
                if (!$this->buildDay($site, $period, $segment, $idPage, false)) {
                    return null;
                }
                $resolved[] = $period;
            } elseif ($isDay && $isWithinOutdatedPeriod) {
                $uncoveredDays[] = $period;
            } elseif (!$isDay && ($mayDecompose || $isWithinOutdatedPeriod)) {
                $uncovered[] = $period;
            } else {
                return null;
            }
        }

        if (!empty($uncoveredDays)) {
            $days = $this->resolveUncoveredDays($site, $uncoveredDays, $segment, $idPage, $mayRefresh);
            if ($days === null) {
                return null;
            }
            $resolved = array_merge($resolved, $days);
        }

        if (!empty($uncovered)) {
            $subperiods = $this->getSubperiods($uncovered);
            if (count($resolved) + count($subperiods) > $this->maxSummedPeriods) {
                return null;
            }

            $subperiods = $this->resolve($site, $subperiods, $segment, $idPage, $mayRefresh, false, $mayDecompose, $isWithinOutdatedPeriod);
            if ($subperiods === null) {
                return null;
            }
            $resolved = array_merge($resolved, $subperiods);
        }

        if (!empty($outdated)) {
            // summed from their subperiods as far as they are stored, else used as they are
            $subperiods = $this->getSubperiods($outdated);
            if (count($resolved) + count($subperiods) <= $this->maxSummedPeriods) {
                $subperiods = $this->resolve($site, $subperiods, $segment, $idPage, true, false, $mayDecompose, true);
            } else {
                $subperiods = null;
            }
            $resolved = array_merge($resolved, $subperiods ?? $outdated);
        }

        return $resolved;
    }

    /**
     * Days without records of an outdated period: today and yesterday are built, past days are left out when they had
     * no visits, which archiving skips.
     *
     * @param list<Period> $days
     * @return list<Period>|null the days to sum, or null if a day with visits has no records
     */
    private function resolveUncoveredDays(Site $site, array $days, Segment $segment, int $idPage, bool $mayRefresh): ?array
    {
        $visits = $this->reader->getVisits((int) $site->getId(), $days, $segment);

        $resolved = [];
        foreach ($days as $day) {
            if ($this->isTodayOrYesterday($site, $day)) {
                if (!$mayRefresh || !$this->buildDay($site, $day, $segment, $idPage, false)) {
                    return null;
                }
                $resolved[] = $day;
            } elseif (($visits[$day->getRangeString()] ?? 0) > 0) {
                return null;
            }
        }

        return $resolved;
    }

    /**
     * @param list<Period> $periods
     * @param array<string, string> $tsArchivedByPeriod
     */
    private function hasOutdatedPeriod(Site $site, array $periods, array $tsArchivedByPeriod): bool
    {
        foreach ($periods as $period) {
            $tsArchived = $tsArchivedByPeriod[$period->getRangeString()] ?? null;
            if ($tsArchived !== null && $this->isOutdated($site, $period, $tsArchived)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Records built before their period ended are outdated once older than the refresh time, if the period holds today
     * or yesterday, the days that can be rebuilt in a request.
     */
    private function isOutdated(Site $site, Period $period, string $tsArchived): bool
    {
        if (!$this->holdsTodayOrYesterday($site, $period)) {
            return false;
        }

        $timezone = $site->getTimezone();
        $archivedAt = Date::factory($tsArchived)->getTimestamp();
        $periodEnd = $period->getDateEnd()->setTimezone($timezone)->getEndOfDay()->getTimestamp();
        $refreshAfter = $this->getRefreshAfter((int) $site->getId());

        return $refreshAfter > 0
            && $archivedAt < $periodEnd
            && Date::now()->getTimestamp() - $archivedAt > $refreshAfter;
    }

    private function holdsTodayOrYesterday(Site $site, Period $period): bool
    {
        $today = Date::factory('now', $site->getTimezone())->toString();
        $yesterday = Date::factory($today)->subDay(1)->toString();

        return $period->getDateStart()->toString() <= $today && $period->getDateEnd()->toString() >= $yesterday;
    }

    private function isTodayOrYesterday(Site $site, Period $day): bool
    {
        return $day->getLabel() === 'day' && $this->holdsTodayOrYesterday($site, $day);
    }

    /**
     * Builds the page's day records unless another request does or did meanwhile. A request waits for another one
     * building the same records, and uses the stored records if that takes too long.
     *
     * @param bool $isRefresh whether existing records are rebuilt, else missing ones are built
     * @return bool whether the records exist now
     */
    private function buildDay(Site $site, Period $day, Segment $segment, int $idPage, bool $isRefresh): bool
    {
        $idSite = (int) $site->getId();
        $today = Date::factory('now', $site->getTimezone())->toString();
        if (!$this->isListed($idSite, $idPage) || $day->getDateStart()->toString() > $today) {
            return false;
        }

        $lock = new Lock($this->lockBackend, self::LOCK_NAMESPACE);
        $lockId = $idSite . '.' . $idPage . '.' . $day->getDateStart()->toString() . '.' . $segment->getHash();
        if (!$this->acquireLock($lock, $lockId)) {
            return $isRefresh;
        }

        $cache = Cache::getLazyCache();
        $attemptId = self::ATTEMPT_CACHE_ID_PREFIX . md5($lockId);

        try {
            // another request may have built them while this one waited
            $coverage = $this->reader->getCoverage($idSite, [$day], $segment, $idPage);
            $tsArchived = $coverage[$day->getRangeString()] ?? null;
            if ($tsArchived !== null && (!$isRefresh || !$this->isOutdated($site, $day, $tsArchived))) {
                return true;
            }

            // a build that failed, or was cut off, is not tried again by every request
            if ($cache->contains($attemptId)) {
                return $isRefresh;
            }
            $cache->save($attemptId, 1, max(self::MIN_RETRY_AFTER_SECONDS, $this->getRefreshAfter($idSite)));

            $this->archiveDay($site, $day, $segment, $idPage);
            $cache->delete($attemptId);
        } catch (\Throwable $e) {
            // the stored records, if any, are served as they are
            $this->logger->warning('Could not build the pre-processed Transitions records of a page: {exception}', ['exception' => $e]);

            return $isRefresh;
        } finally {
            $lock->unlock();
        }

        return true;
    }

    /**
     * Writes a partial archive with the page's day records, built from the logs.
     */
    private function archiveDay(Site $site, Period $day, Segment $segment, int $idPage): void
    {
        $idSite = (int) $site->getId();
        $daySegment = new Segment(
            $segment->getOriginalString(),
            [$idSite],
            $day->getDateTimeStart()->setTimezone($site->getTimezone()),
            $day->getDateTimeEnd()->setTimezone($site->getTimezone())
        );

        $params = new Parameters($site, $day, $daySegment);
        $params->setRequestedPlugin('Transitions');
        $params->setArchiveOnlyReport(Records::getRebuildReportName($idPage));

        // the queries get the time limit of the report's log queries
        PageTransitions::executeWithTimeLimit(GeneralConfig::getFloatConfigValue('live_query_max_execution_time', -1.0), function () use ($params): void {
            $archiver = new PluginsArchiver($params);
            $metrics = $archiver->callAggregateCoreMetrics();
            // a day without visits gets records too, so it counts as covered
            $archiver->callAggregateAllPlugins($metrics['nb_visits'], $metrics['nb_visits_converted'], true);
            $archiver->finalizeArchive();
        });
    }

    private function acquireLock(Lock $lock, string $lockId): bool
    {
        $waited = 0;
        while (!$lock->acquireLock($lockId, self::LOCK_TTL_SECONDS)) {
            if ($waited >= $this->maxLockWaitMilliseconds) {
                return false;
            }

            usleep(self::LOCK_POLL_MILLISECONDS * 1000);
            $waited += self::LOCK_POLL_MILLISECONDS;
        }

        return true;
    }

    /**
     * Records are built for all visits and the segments that are pre-processed, unless segments aren't archived for
     * the plugin.
     */
    private function canBuildForSegment(Site $site, Segment $segment): bool
    {
        $idSite = (int) $site->getId();

        return $segment->isEmpty()
            || (Rules::isSegmentPreProcessed([$idSite], $segment) && !Rules::isSegmentPluginArchivingDisabled('Transitions', $idSite));
    }

    private function isListed(int $idSite, int $idPage): bool
    {
        return $this->getPageSettings($idSite, $idPage)['listed'];
    }

    private function getRefreshAfter(int $idSite): int
    {
        if ($this->pageSettings !== null) {
            return $this->pageSettings['refreshAfter'];
        }

        return $this->service->getRefreshAfter($idSite);
    }

    /**
     * @return array{listed: bool, refreshAfter: int}
     */
    private function getPageSettings(int $idSite, int $idPage): array
    {
        if ($this->pageSettings === null) {
            $this->pageSettings = [
                'listed' => in_array($idPage, $this->service->getPageIds($idSite), true),
                'refreshAfter' => $this->service->getRefreshAfter($idSite),
            ];
        }

        return $this->pageSettings;
    }

    /**
     * @param list<Period> $periods
     * @return list<Period>
     */
    private function getSubperiods(array $periods): array
    {
        $subperiods = [];
        foreach ($periods as $period) {
            foreach ($period->getSubperiods() as $subperiod) {
                $subperiods[] = $subperiod;
            }
        }

        return $subperiods;
    }
}
