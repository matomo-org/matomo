<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataAccess;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log;
use Piwik\Piwik;

/**
 *
 * This class purges two types of archives:
 *
 * (1) Deletes invalidated archives (from ArchiveInvalidator)
 *
 * (2) Deletes outdated archives (the temporary or errored archives)
 *
 *
 * @package Piwik\DataAccess
 */
class ArchivePurger
{
    public static function purgeInvalidatedArchives()
    {
        $store = new InvalidatedReports();
        $idSitesByYearMonth = $store->getSitesByYearMonthArchiveToPurge();
        foreach ($idSitesByYearMonth as $yearMonth => $idSites) {
            if(empty($idSites)) {
                continue;
            }

            $date = Date::factory(str_replace('_', '-', $yearMonth) . '-01');
            $numericTable = ArchiveTableCreator::getNumericTable($date);

            $archiveIds = self::getModel()->getInvalidatedArchiveIdsSafeToDelete($numericTable, $idSites);

            if (count($archiveIds) == 0) {
                continue;
            }
            self::deleteArchiveIds($date, $archiveIds);

            $store->markSiteIdsHaveBeenPurged($idSites, $yearMonth);
        }
    }

    /**
     * Removes the outdated archives for the given month.
     * (meaning they are marked with a done flag of ArchiveWriter::DONE_OK_TEMPORARY or ArchiveWriter::DONE_ERROR)
     *
     * @param Date $dateStart Only the month will be used
     */
    public static function purgeOutdatedArchives(Date $dateStart)
    {
        $purgeArchivesOlderThan = Rules::shouldPurgeOutdatedArchives($dateStart);

        if (!$purgeArchivesOlderThan) {
            return;
        }

        $idArchivesToDelete = self::getOutdatedArchiveIds($dateStart, $purgeArchivesOlderThan);

        if (!empty($idArchivesToDelete)) {
            self::deleteArchiveIds($dateStart, $idArchivesToDelete);
        }

        self::deleteArchivesWithPeriodRange($dateStart);

        Log::debug("Purging temporary archives: done [ purged archives older than %s in %s ] [Deleted IDs: %s]",
                   $purgeArchivesOlderThan,
                   $dateStart->toString("Y-m"),
                   implode(',', $idArchivesToDelete));
    }

    protected static function getOutdatedArchiveIds(Date $date, $purgeArchivesOlderThan)
    {
        $archiveTable = ArchiveTableCreator::getNumericTable($date);

        $result = self::getModel()->getTemporaryArchivesOlderThan($archiveTable, $purgeArchivesOlderThan);

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
    protected static function deleteArchivesWithPeriodRange(Date $date)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        $blobTable    = ArchiveTableCreator::getBlobTable($date);
        $yesterday    = Date::factory('yesterday')->getDateTime();

        self::getModel()->deleteArchivesWithPeriod($numericTable, $blobTable, Piwik::$idPeriods['range'], $yesterday);

        Log::debug("Purging Custom Range archives: done [ purged archives older than %s from %s / blob ]",
            $yesterday, $numericTable);
    }

    /**
     * Deletes by batches Archive IDs in the specified month,
     *
     * @param Date $date
     * @param $idArchivesToDelete
     */
    protected static function deleteArchiveIds(Date $date, $idArchivesToDelete)
    {
        $batches      = array_chunk($idArchivesToDelete, 1000);
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        $blobTable    = ArchiveTableCreator::getBlobTable($date);

        foreach ($batches as $idsToDelete) {
            self::getModel()->deleteArchiveIds($numericTable, $blobTable, $idsToDelete);
        }
    }

    private static function getModel()
    {
        return new Model();
    }

}
