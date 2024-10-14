<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Archive;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Log\LoggerInterface;

/**
 * Service that purges temporary, error-ed, invalid and custom range archives from archive tables.
 *
 * Temporary archives are purged if they were archived before a specific time. The time is dependent
 * on whether browser triggered archiving is enabled or not.
 *
 * Error-ed archives are purged w/o constraint.
 *
 * Invalid archives are purged if a new, valid, archive exists w/ the same site, date, period combination.
 * Archives are marked as invalid via Piwik\Archive\ArchiveInvalidator.
 */
class ArchivePurger
{
    /**
     * @var Model
     */
    private $model;

    /**
     * Date threshold for purging custom range archives. Archives that are older than this date
     * are purged unconditionally from the requested archive table.
     *
     * @var Date
     */
    private $purgeCustomRangesOlderThan;

    /**
     * Date to use for 'yesterday'. Exists so tests can override this value.
     *
     * @var Date
     */
    private $yesterday;

    /**
     * Date to use for 'today'. Exists so tests can override this value.
     *
     * @var $today
     */
    private $today;

    /**
     * Date to use for 'now'. Exists so tests can override this value.
     *
     * @var int
     */
    private $now;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?Model $model = null, ?Date $purgeCustomRangesOlderThan = null, ?LoggerInterface $logger = null)
    {
        $this->model = $model ?: new Model();

        $this->purgeCustomRangesOlderThan = $purgeCustomRangesOlderThan ?: self::getDefaultCustomRangeToPurgeAgeThreshold();

        $this->yesterday = Date::factory('yesterday');
        $this->today = Date::factory('today');
        $this->now = time();
        $this->logger = $logger ?: StaticContainer::get(LoggerInterface::class);
    }

    /**
     * Purge all invalidate archives for whom there are newer, valid archives from the archive
     * table that stores data for `$date`.
     *
     * @param Date $date The date identifying the archive table.
     * @return int The total number of archive rows deleted (from both the blog & numeric tables).
     */
    public function purgeInvalidatedArchivesFrom(Date $date)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($date);

        $archiveIds = $this->model->getInvalidatedArchiveIdsSafeToDelete($numericTable);
        if (empty($archiveIds)) {
            $this->logger->debug("No invalidated archives found in {table} with newer, valid archives.", array('table' => $numericTable));
            return 0;
        }

        $this->logger->info("Found {countArchiveIds} invalidated archives safe to delete in {table}.", array(
            'table' => $numericTable, 'countArchiveIds' => count($archiveIds)
        ));

        $deletedRowCount = $this->deleteArchiveIds($date, $archiveIds);

        $this->logger->debug("Deleted {count} rows in {table} and its associated blob table.", array(
            'table' => $numericTable, 'count' => $deletedRowCount
        ));

        return $deletedRowCount;
    }

    /**
     * Removes the outdated archives for the given month.
     * (meaning they are marked with a done flag of ArchiveWriter::DONE_OK_TEMPORARY or ArchiveWriter::DONE_ERROR)
     *
     * @param Date $dateStart Only the month will be used
     * @return int Returns the total number of rows deleted.
     */
    public function purgeOutdatedArchives(Date $dateStart)
    {
        $purgeArchivesOlderThan = $this->getOldestTemporaryArchiveToKeepThreshold();
        $deletedRowCount = 0;

        $idArchivesToDelete = $this->getOutdatedArchiveIds($dateStart, $purgeArchivesOlderThan);
        if (!empty($idArchivesToDelete)) {
            $deletedRowCount = $this->deleteArchiveIds($dateStart, $idArchivesToDelete);

            $this->logger->info("Deleted {count} rows in archive tables (numeric + blob) for {date}.", array(
                'count' => $deletedRowCount,
                'date' => $dateStart
            ));
        } else {
            $this->logger->debug("No outdated archives found in archive numeric table for {date}.", array('date' => $dateStart));
        }

        $this->logger->debug("Purging temporary archives: done [ purged archives older than {date} in {yearMonth} ] [Deleted IDs count: {deletedIds}]", array(
            'date' => $purgeArchivesOlderThan,
            'yearMonth' => $dateStart->toString('Y-m'),
            'deletedIds' => count($idArchivesToDelete),
        ));

        return $deletedRowCount;
    }

    public function purgeDeletedSiteArchives(Date $dateStart)
    {
        $archiveTable = ArchiveTableCreator::getNumericTable($dateStart);
        $idArchivesToDelete = $this->model->getArchiveIdsForDeletedSites($archiveTable);

        return $this->purge($idArchivesToDelete, $dateStart, 'deleted sites');
    }

    /**
     * @param Date $dateStart
     * @param array $deletedSegments List of segments whose archives should be purged
     * @return int
     */
    public function purgeDeletedSegmentArchives(Date $dateStart, array $deletedSegments)
    {
        if (count($deletedSegments)) {
            $idArchivesToDelete = $this->getDeletedSegmentArchiveIds($dateStart, $deletedSegments);
            return $this->purge($idArchivesToDelete, $dateStart, 'deleted segments');
        }
    }

    /**
     * Purge all numeric and blob archives with the given IDs from the database.
     * @param array $idArchivesToDelete
     * @param Date $dateStart
     * @param string $reason
     * @return int
     */
    protected function purge(array $idArchivesToDelete, Date $dateStart, $reason)
    {
        $deletedRowCount = 0;
        if (!empty($idArchivesToDelete)) {
            $deletedRowCount = $this->deleteArchiveIds($dateStart, $idArchivesToDelete);

            $this->logger->info(
                "Deleted {count} rows in archive tables (numeric + blob) for {reason} for {date}.",
                array(
                    'count' => $deletedRowCount,
                    'date' => $dateStart,
                    'reason' => $reason
                )
            );

            $this->logger->debug("[Deleted IDs count: {deletedIds}]", array(
                'deletedIds' => count($idArchivesToDelete),
            ));
        } else {
            $this->logger->debug(
                "No archives for {reason} found in archive numeric table for {date}.",
                array('date' => $dateStart, 'reason' => $reason)
            );
        }

        return $deletedRowCount;
    }

    protected function getDeletedSegmentArchiveIds(Date $date, array $deletedSegments)
    {
        $archiveTable = ArchiveTableCreator::getNumericTable($date);
        return $this->model->getArchiveIdsForSegments(
            $archiveTable,
            $deletedSegments,
            $this->getOldestTemporaryArchiveToKeepThreshold()
        );
    }

    protected function getOutdatedArchiveIds(Date $date, $purgeArchivesOlderThan)
    {
        $archiveTable = ArchiveTableCreator::getNumericTable($date);

        $result = $this->model->getTemporaryArchivesOlderThan($archiveTable, $purgeArchivesOlderThan);

        $idArchivesToDelete = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $idArchivesToDelete[] = $row['idarchive'];
            }
        }

        return $idArchivesToDelete;
    }

    /**
     * Deleting "Custom Date Range" reports after 1 day, since they can be re-processed and would take up un-necessary space.
     *
     * @param $date Date
     * @return int The total number of rows deleted from both the numeric & blob table.
     */
    public function purgeArchivesWithPeriodRange(Date $date)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        $blobTable    = ArchiveTableCreator::getBlobTable($date);

        $deletedCount = $this->model->deleteArchivesWithPeriod(
            $numericTable,
            $blobTable,
            Piwik::$idPeriods['range'],
            $this->purgeCustomRangesOlderThan
        );

        $level = $deletedCount == 0 ? 'debug' : 'info';
        $this->logger->$level("Purged {count} range archive rows from {numericTable} & {blobTable}.", array(
            'count' => $deletedCount,
            'numericTable' => $numericTable,
            'blobTable' => $blobTable
        ));

        $this->logger->debug("  [ purged archives older than {threshold} ]", array('threshold' => $this->purgeCustomRangesOlderThan));

        return $deletedCount;
    }

    /**
     * Deletes by batches Archive IDs in the specified month,
     *
     * @param Date $date
     * @param $idArchivesToDelete
     * @return int Number of rows deleted from both numeric + blob table.
     */
    protected function deleteArchiveIds(Date $date, $idArchivesToDelete)
    {
        $batches      = array_chunk($idArchivesToDelete, 1000);
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        $blobTable    = ArchiveTableCreator::getBlobTable($date);

        $deletedCount = 0;
        foreach ($batches as $idsToDelete) {
            $deletedCount += $this->model->deleteArchiveIds($numericTable, $blobTable, $idsToDelete);
        }
        return $deletedCount;
    }

    /**
     * Returns a timestamp indicating outdated archives older than this timestamp (processed before) can be purged.
     *
     * @return int|bool  Outdated archives older than this timestamp should be purged
     */
    protected function getOldestTemporaryArchiveToKeepThreshold()
    {
        $temporaryArchivingTimeout = Rules::getTodayArchiveTimeToLive();
        if (Rules::isBrowserTriggerEnabled()) {
            // If Browser Archiving is enabled, it is likely there are many more temporary archives
            // We delete more often which is safe, since reports are re-processed on demand
            return Date::factory($this->now - 2 * $temporaryArchivingTimeout)->getDateTime();
        }

        // If cron core:archive command is building the reports, we should keep all temporary reports from today
        return $this->yesterday->getDateTime();
    }

    private static function getDefaultCustomRangeToPurgeAgeThreshold()
    {
        $daysRangesValid = Config::getInstance()->General['purge_date_range_archives_after_X_days'];
        return Date::factory('today')->subDay($daysRangesValid)->getDateTime();
    }

    /**
     * For tests.
     *
     * @param Date $yesterday
     */
    public function setYesterdayDate(Date $yesterday)
    {
        $this->yesterday = $yesterday;
    }

    /**
     * For tests.
     *
     * @param Date $today
     */
    public function setTodayDate(Date $today)
    {
        $this->today = $today;
    }

    /**
     * For tests.
     *
     * @param int $now
     */
    public function setNow($now)
    {
        $this->now = $now;
    }
}
