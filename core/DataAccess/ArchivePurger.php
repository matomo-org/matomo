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
 * Cleans up outdated archives
 *
 * @package Piwik\DataAccess
 */
class ArchivePurger
{
    public static function purgeInvalidatedArchives()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

        foreach ($archiveTables as $archiveTable) {
            /**
             * Select the archives that have already been invalidated and have been since re-processed.
             * It purges records for each distinct { archive name (includes segment hash) , idsite, date, period } tuple.
             */
            $query = '
                SELECT t1.idarchive FROM `' . $archiveTable . '` t1
                INNER JOIN `' . $archiveTable . '` t2
                    ON t1.name = t2.name AND t1.idsite=t2.idsite
                    AND t1.date1=t2.date1 AND t1.date2=t2.date2 AND t1.period=t2.period
                WHERE t1.value = ' . ArchiveWriter::DONE_INVALIDATED . '
                AND t2.value IN(' . ArchiveWriter::DONE_OK . ', ' . ArchiveWriter::DONE_OK_TEMPORARY . ')
                AND t1.ts_archived < t2.ts_archived AND t1.name LIKE \'done%\'';

            $result = Db::fetchAll($query);

            if (count($result) > 0) {
                $archiveIds = array_map(
                    function ($elm) {
                        return $elm['idarchive'];
                    },
                    $result
                );

                $date = ArchiveTableCreator::getDateFromTableName($archiveTable);
                $date = Date::factory(str_replace('_', '-', $date) . '-01');

                self::deleteArchiveIds($date, $archiveIds);
            }

        }
    }


    public static function purgeOutdatedArchives(Date $dateStart)
    {
        $purgeArchivesOlderThan = Rules::shouldPurgeOutdatedArchives($dateStart);
        if (!$purgeArchivesOlderThan) {
            return;
        }

        $idArchivesToDelete = self::getTemporaryArchiveIdsOlderThan($dateStart, $purgeArchivesOlderThan);
        if (!empty($idArchivesToDelete)) {
            self::deleteArchiveIds($dateStart, $idArchivesToDelete);
        }
        self::deleteArchivesWithPeriodRange($dateStart);

        Log::debug("Purging temporary archives: done [ purged archives older than %s in %s ] [Deleted IDs: %s]",
            $purgeArchivesOlderThan,
            $dateStart->toString("Y-m"),
            implode(',', $idArchivesToDelete));
    }

    protected static function getTemporaryArchiveIdsOlderThan(Date $date, $purgeArchivesOlderThan)
    {
        $query = "SELECT idarchive
                FROM " . ArchiveTableCreator::getNumericTable($date) . "
                WHERE name LIKE 'done%'
                    AND ((  value = " . ArchiveWriter::DONE_OK_TEMPORARY . "
                            AND ts_archived < ?)
                         OR value = " . ArchiveWriter::DONE_ERROR . ")";

        $result = Db::fetchAll($query, array($purgeArchivesOlderThan));
        $idArchivesToDelete = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $idArchivesToDelete[] = $row['idarchive'];
            }
        }

        return $idArchivesToDelete;
    }

    /*
     * Deleting "Custom Date Range" reports after 1 day, since they can be re-processed and would take up un-necessary space
     */
    protected static function deleteArchivesWithPeriodRange(Date $date)
    {
        $query = "DELETE FROM %s WHERE period = ? AND ts_archived < ?";

        $yesterday = Date::factory('yesterday')->getDateTime();
        $bind = array(Piwik::$idPeriods['range'], $yesterday);
        $numericTable = ArchiveTableCreator::getNumericTable($date);
        Db::query(sprintf($query, $numericTable), $bind);
        Log::debug("Purging Custom Range archives: done [ purged archives older than %s from %s / blob ]",
            $yesterday,
            $numericTable);
        try {
            Db::query(sprintf($query, ArchiveTableCreator::getBlobTable($date)), $bind);
        } catch (Exception $e) {
            // Individual blob tables could be missing
        }
    }

    protected static function deleteArchiveIds(Date $date, $idArchivesToDelete)
    {
        $batches = array_chunk($idArchivesToDelete, 1000);
        foreach ($batches as $idsToDelete) {
            $query = "DELETE FROM %s WHERE idarchive IN (" . implode(',', $idsToDelete) . ")";

            Db::query(sprintf($query, ArchiveTableCreator::getNumericTable($date)));
            try {
                Db::query(sprintf($query, ArchiveTableCreator::getBlobTable($date)));
            } catch (Exception $e) {
                // Individual blob tables could be missing
            }
        }

    }

}
