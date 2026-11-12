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
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Period;
use Piwik\Period\Range;
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
 * Records are built as a partial archive of the page's records only, for all visits and pre-processed segments.
 */
final class StoredRecordsLoader
{
    private const LOCK_NAMESPACE = 'Transitions.buildPreprocessedDay.';

    private const LOCK_TTL_SECONDS = 120;

    private const LOCK_POLL_MILLISECONDS = 100;

    /**
     * @var array{listed: bool, refreshAfter: int}|null
     */
    private ?array $pageSettings = null;

    public function __construct(
        private readonly StoredRecordsReader $reader,
        private readonly Service $service,
        private readonly LockBackend $lockBackend,
        private readonly LoggerInterface $logger,
        private readonly int $maxLockWaitMilliseconds = 10000
    ) {
    }

    /**
     * @param list<string> $tableParts the lists and the referrers to read, see {@link Records}
     * @return StoredRecords|null null unless stored records cover the whole period
     */
    public function load(int $idSite, Period $period, Segment $segment, int $idAction, array $tableParts): ?StoredRecords
    {
        $this->pageSettings = null;
        $periods = $period instanceof Range ? array_values($period->getSubperiods()) : [$period];
        $site = new Site($idSite);

        $records = $this->reader->readSum($idSite, $periods, $segment, $idAction, $tableParts);
        if ($records !== null && !$this->hasOutdatedPeriod($site, $periods, $records->getTsArchivedByPeriod())) {
            return $records;
        }

        $isWithoutCron = Rules::isBrowserTriggerEnabled();
        // removed pages keep their stored records, which are never rebuilt
        if (($records === null && !$isWithoutCron) || !$this->isListed($idSite, $idAction)) {
            return $records;
        }

        $canBuild = $this->canBuildForSegment($site, $segment);
        if ($records !== null && !$canBuild) {
            return $records;
        }

        $mayRefresh = $canBuild && $this->getRefreshAfter($idSite) > 0;
        $mayBuildDay = $isWithoutCron && $canBuild && !$period instanceof Range && $period->getLabel() === 'day';

        $resolved = $this->resolve($site, $periods, $segment, $idAction, $mayRefresh, $mayBuildDay, $isWithoutCron);
        if ($resolved === null) {
            return $records;
        }

        return $this->reader->readSum($idSite, $resolved, $segment, $idAction, $tableParts) ?? $records;
    }

    /**
     * Returns periods whose stored records cover the given ones, as fresh as allowed, building day records on the way.
     * The subperiods of all periods of one level are looked at together.
     *
     * @param list<Period> $periods
     * @return list<Period>|null null if they can't be covered
     */
    private function resolve(Site $site, array $periods, Segment $segment, int $idAction, bool $mayRefresh, bool $mayBuildDay, bool $mayDecompose): ?array
    {
        $coverage = $this->reader->getCoverage((int) $site->getId(), $periods, $segment, $idAction);

        $resolved = [];
        $outdated = [];
        $uncovered = [];
        foreach ($periods as $period) {
            $isDay = $period->getLabel() === 'day';

            if (array_key_exists($period->getRangeString(), $coverage)) {
                $tsArchived = $coverage[$period->getRangeString()];
                if ($mayRefresh && $tsArchived !== null && $this->isOutdated($site, $period, $tsArchived)) {
                    if ($isDay) {
                        $this->buildDay($site, $period, $segment, $idAction, true);
                    } else {
                        $outdated[] = $period;
                        continue;
                    }
                }

                $resolved[] = $period;
            } elseif ($isDay && $mayBuildDay && $this->buildDay($site, $period, $segment, $idAction, false)) {
                $resolved[] = $period;
            } elseif (!$isDay && $mayDecompose) {
                $uncovered[] = $period;
            } else {
                return null;
            }
        }

        if (!empty($uncovered)) {
            $subperiods = $this->resolve($site, $this->getSubperiods($uncovered), $segment, $idAction, $mayRefresh, false, true);
            if ($subperiods === null) {
                return null;
            }
            $resolved = array_merge($resolved, $subperiods);
        }

        if (!empty($outdated)) {
            // summed from their subperiods as far as they are stored, else used as they are
            $subperiods = $this->resolve($site, $this->getSubperiods($outdated), $segment, $idAction, true, false, $mayDecompose);
            $resolved = array_merge($resolved, $subperiods ?? $outdated);
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
        $timezone = $site->getTimezone();
        $today = Date::factory('now', $timezone)->toString();
        $yesterday = Date::factory($today)->subDay(1)->toString();
        if ($period->getDateStart()->toString() > $today || $period->getDateEnd()->toString() < $yesterday) {
            return false;
        }

        $archivedAt = Date::factory($tsArchived)->getTimestamp();
        $periodEnd = $period->getDateEnd()->setTimezone($timezone)->getEndOfDay()->getTimestamp();
        $refreshAfter = $this->getRefreshAfter((int) $site->getId());

        return $refreshAfter > 0
            && $archivedAt < $periodEnd
            && Date::now()->getTimestamp() - $archivedAt > $refreshAfter;
    }

    /**
     * Builds the page's day records unless another request does or did meanwhile. A request waits for another one
     * building the same records, and uses the stored records if that takes too long.
     *
     * @param bool $isRefresh whether existing records are rebuilt, else missing ones are built
     * @return bool whether the records exist now
     */
    private function buildDay(Site $site, Period $day, Segment $segment, int $idAction, bool $isRefresh): bool
    {
        $idSite = (int) $site->getId();
        $today = Date::factory('now', $site->getTimezone())->toString();
        if (!$this->isListed($idSite, $idAction) || $day->getDateStart()->toString() > $today) {
            return false;
        }

        $lock = new Lock($this->lockBackend, self::LOCK_NAMESPACE);
        $lockId = $idSite . '.' . $idAction . '.' . $day->getDateStart()->toString() . '.' . $segment->getHash();
        if (!$this->acquireLock($lock, $lockId)) {
            return $isRefresh;
        }

        try {
            // another request may have built them while this one waited
            $coverage = $this->reader->getCoverage($idSite, [$day], $segment, $idAction);
            $tsArchived = $coverage[$day->getRangeString()] ?? null;
            if ($tsArchived !== null && (!$isRefresh || !$this->isOutdated($site, $day, $tsArchived))) {
                return true;
            }

            $this->archiveDay($site, $day, $segment, $idAction);
        } catch (\Exception $e) {
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
    private function archiveDay(Site $site, Period $day, Segment $segment, int $idAction): void
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
        $params->setArchiveOnlyReport(Records::getRebuildReportName($idAction));

        $archiver = new PluginsArchiver($params);
        $metrics = $archiver->callAggregateCoreMetrics();
        $archiver->callAggregateAllPlugins($metrics['nb_visits'], $metrics['nb_visits_converted']);
        $archiver->finalizeArchive();
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
     * Records are built for all visits and the segments that are pre-processed.
     */
    private function canBuildForSegment(Site $site, Segment $segment): bool
    {
        return $segment->isEmpty() || Rules::isSegmentPreProcessed([(int) $site->getId()], $segment);
    }

    private function isListed(int $idSite, int $idAction): bool
    {
        return $this->getPageSettings($idSite, $idAction)['listed'];
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
    private function getPageSettings(int $idSite, int $idAction): array
    {
        if ($this->pageSettings === null) {
            $this->pageSettings = [
                'listed' => in_array($idAction, $this->service->getStoredIdActionsForSite($idSite), true),
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
