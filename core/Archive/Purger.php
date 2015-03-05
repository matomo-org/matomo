<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Archive;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;

/**
 *
 * This class purges two types of archives:
 *
 * (1) Deletes invalidated archives (from Invalidator)
 *
 * (2) Deletes outdated archives (the temporary or errored archives)
 *
 */
class Purger
{
    /**
     * @var Model
     */
    private $model;

    /**
     * TODO
     *
     * @var Date
     */
    private $purgeCustomRangesOlderThan;

    /**
     * TODO
     *
     * @var Date
     */
    private $yesterday;

    /**
     * TODO
     *
     * @var $today
     */
    private $today;

    /**
     * TODO
     *
     * @var int
     */
    private $now;

    public function __construct(Model $model = null, Date $purgeCustomRangesOlderThan = null)
    {
        $this->model = $model ?: new Model();

        $this->purgeCustomRangesOlderThan = $purgeCustomRangesOlderThan ?: self::getDefaultCustomRangeToPurgeAgeThreshold();

        $this->yesterday = Date::factory('yesterday');
        $this->today = Date::factory('today');
        $this->now = time();
    }

    /**
     * TODO
     */
    public function purgeInvalidatedArchives()
    {
        $archivesToPurge = new ArchivesToPurgeDistributedList();

        $yearMonths = $archivesToPurge->getAll();
        foreach ($yearMonths as $yearMonth) {
            try {
                $date = Date::factory(str_replace('_', '-', $yearMonth) . '-01');
            } catch (\Exception $ex) {
                continue; // invalid year month in distributed list
            }

            $this->purgeInvalidatedArchivesFrom($date);

            $archivesToPurge->remove($yearMonth);
        }
    }

    /**
     * TODO
     */
    public function purgeInvalidatedArchivesFrom(Date $date)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($date);

        // we don't want to do an INNER JOIN on every row in a archive table that can potentially have tens to hundreds of thousands of rows,
        // so we first look for sites w/ invalidated archives, and use this as a constraint in getInvalidatedArchiveIdsSafeToDelete() below.
        // the constraint will hit an INDEX and speed up the inner join that happens in getInvalidatedArchiveIdsSafeToDelete().
        $idSites = $this->model->getSitesWithInvalidatedArchive($numericTable);
        if (empty($idSites)) {
            return;
        }

        $archiveIds = $this->model->getInvalidatedArchiveIdsSafeToDelete($numericTable, $idSites);
        if (empty($archiveIds)) {
            return;
        }

        $this->deleteArchiveIds($date, $archiveIds);
    }

    /**
     * Removes the outdated archives for the given month.
     * (meaning they are marked with a done flag of ArchiveWriter::DONE_OK_TEMPORARY or ArchiveWriter::DONE_ERROR)
     *
     * @param Date $dateStart Only the month will be used
     */
    public function purgeOutdatedArchives(Date $dateStart)
    {
        $purgeArchivesOlderThan = $this->getOldestTemporaryArchiveToKeepThreshold();

        $idArchivesToDelete = $this->getOutdatedArchiveIds($dateStart, $purgeArchivesOlderThan);
        if (!empty($idArchivesToDelete)) {
            $this->deleteArchiveIds($dateStart, $idArchivesToDelete);
        }

        $this->deleteArchivesWithPeriodRange($dateStart); // TODO: this is unrelated to outdated archive purging, should be in its own method

        Log::debug("Purging temporary archives: done [ purged archives older than %s in %s ] [Deleted IDs: %s]",
                   $purgeArchivesOlderThan,
                   $dateStart->toString("Y-m"),
                   implode(',', $idArchivesToDelete));
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
     */
    protected  function deleteArchivesWithPeriodRange(Date $date)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        $blobTable    = ArchiveTableCreator::getBlobTable($date);

        $this->model->deleteArchivesWithPeriod($numericTable, $blobTable, Piwik::$idPeriods['range'], $this->purgeCustomRangesOlderThan);

        Log::debug("Purging Custom Range archives: done [ purged archives older than %s from %s / blob ]",
            $this->purgeCustomRangesOlderThan, $numericTable);
    }

    /**
     * Deletes by batches Archive IDs in the specified month,
     *
     * @param Date $date
     * @param $idArchivesToDelete
     */
    protected function deleteArchiveIds(Date $date, $idArchivesToDelete)
    {
        $batches      = array_chunk($idArchivesToDelete, 1000);
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        $blobTable    = ArchiveTableCreator::getBlobTable($date);

        foreach ($batches as $idsToDelete) {
            $this->model->deleteArchiveIds($numericTable, $blobTable, $idsToDelete);
        }
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