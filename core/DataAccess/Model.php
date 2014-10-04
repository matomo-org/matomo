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
use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * Cleans up outdated archives
 *
 * @package Piwik\DataAccess
 */
class Model
{
    const PREFIX_SQL_LOCK = "locked_";

    public function purgeInvalidatedArchiveTable($archiveTable)
    {
        /**
         * Select the archives that have already been invalidated and have been since re-processed.
         * It purges records for each distinct { archive name (includes segment hash) , idsite, date, period } tuple.
         */
        $query = 'SELECT t1.idarchive FROM `' . $archiveTable . '` t1
                  INNER JOIN `' . $archiveTable . '` t2
                      ON t1.name = t2.name AND t1.idsite=t2.idsite
                      AND t1.date1=t2.date1 AND t1.date2=t2.date2 AND t1.period=t2.period
                  WHERE t1.value = ' . ArchiveWriter::DONE_INVALIDATED . '
                  AND t2.value IN(' . ArchiveWriter::DONE_OK . ', ' . ArchiveWriter::DONE_OK_TEMPORARY . ')
                  AND t1.ts_archived < t2.ts_archived AND t1.name LIKE \'done%\'';

        $result = Db::fetchAll($query);

        return $result;
    }

    public function getTemporaryArchivesOlderThan($archiveTable, $purgeArchivesOlderThan)
    {
        $query = "SELECT idarchive FROM " . $archiveTable . "
                  WHERE name LIKE 'done%'
                    AND ((  value = " . ArchiveWriter::DONE_OK_TEMPORARY . "
                            AND ts_archived < ?)
                         OR value = " . ArchiveWriter::DONE_ERROR . ")";

        return Db::fetchAll($query, array($purgeArchivesOlderThan));
    }

    /*
     * Deleting "Custom Date Range" reports, since they can be re-processed and would take up un-necessary space
     */
    public function deleteArchivesWithPeriodRange($numericTable, $blobTable, $range, $date)
    {
        $query = "DELETE FROM %s WHERE period = ? AND ts_archived < ?";
        $bind  = array($range, $date);

        Db::query(sprintf($query, $numericTable), $bind);

        try {
            Db::query(sprintf($query, $blobTable), $bind);
        } catch (Exception $e) {
            // Individual blob tables could be missing
        }
    }

    public function deleteArchiveIds($numericTable, $blobTable, $idsToDelete)
    {
        $query = "DELETE FROM %s WHERE idarchive IN (" . implode(',', $idsToDelete) . ")";

        Db::query(sprintf($query, $numericTable));

        try {
            Db::query(sprintf($query, $blobTable));
        } catch (Exception $e) {
            // Individual blob tables could be missing
        }
    }

    public function getArchiveIdAndVisits($numericTable, $idSite, $period, $dateStartIso, $dateEndIso, $minDatetimeIsoArchiveProcessedUTC, $doneFlags, $possibleValues)
    {
        $bindSQL = array($idSite,
            $dateStartIso,
            $dateEndIso,
            $period,
        );

        $timeStampWhere = '';
        if ($minDatetimeIsoArchiveProcessedUTC) {
            $timeStampWhere = " AND ts_archived >= ? ";
            $bindSQL[]      = $minDatetimeIsoArchiveProcessedUTC;
        }

        $sqlWhereArchiveName = self::getNameCondition($doneFlags, $possibleValues);

        $sqlQuery = "SELECT idarchive, value, name, date1 as startDate FROM $numericTable
                     WHERE idsite = ?
                         AND date1 = ?
                         AND date2 = ?
                         AND period = ?
                         AND ( ($sqlWhereArchiveName)
                               OR name = '" . ArchiveSelector::NB_VISITS_RECORD_LOOKED_UP . "'
                               OR name = '" . ArchiveSelector::NB_VISITS_CONVERTED_RECORD_LOOKED_UP . "')
                         $timeStampWhere
                     ORDER BY idarchive DESC";
        $results = Db::fetchAll($sqlQuery, $bindSQL);

        return $results;
    }

    public function createArchiveTable($tableName, $tableNamePrefix)
    {
        $db  = Db::get();
        $sql = DbHelper::getTableCreateSql($tableNamePrefix);

        // replace table name template by real name
        $tableNamePrefix = Common::prefixTable($tableNamePrefix);
        $sql = str_replace($tableNamePrefix, $tableName, $sql);

        try {
            $db->query($sql);
        } catch (Exception $e) {
            // accept mysql error 1050: table already exists, throw otherwise
            if (!$db->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * Locks the archive table to generate a new archive ID.
     *
     * We lock to make sure that
     * if several archiving processes are running at the same time (for different websites and/or periods)
     * then they will each use a unique archive ID.
     *
     * @return int
     */
    public function insertNewArchiveId($numericTable, $idSite, $date)
    {
        $this->acquireArchiveTableLock($numericTable);

        $locked = self::PREFIX_SQL_LOCK . Common::generateUniqId();

        $insertSql = "INSERT INTO $numericTable "
            . " SELECT IFNULL( MAX(idarchive), 0 ) + 1,
                                '" . $locked . "',
                                " . (int)$idSite . ",
                                '" . $date . "',
                                '" . $date . "',
                                0,
                                '" . $date . "',
                                0 "
            . " FROM $numericTable as tb1";
        Db::get()->exec($insertSql);

        $this->releaseArchiveTableLock($numericTable);

        $selectIdSql = "SELECT idarchive FROM $numericTable WHERE name = ? LIMIT 1";
        $id = Db::get()->fetchOne($selectIdSql, $locked);
        return $id;
    }

    public function deletePreviousArchiveStatus($numericTable, $archiveId, $doneFlag)
    {
        // without advisory lock here, the DELETE would acquire Exclusive Lock
        $this->acquireArchiveTableLock($numericTable);

        Db::query("DELETE FROM $numericTable WHERE idarchive = ? AND (name = '" . $doneFlag
                . "' OR name LIKE '" . self::PREFIX_SQL_LOCK . "%')",
            array($archiveId)
        );

        $this->releaseArchiveTableLock($numericTable);
    }

    public function insertRecord($tableName, $fields, $record, $name, $value)
    {
        // duplicate idarchives are Ignored, see https://github.com/piwik/piwik/issues/987
        $query = "INSERT IGNORE INTO " . $tableName . " (" . implode(", ", $fields) . ")
                  VALUES (?,?,?,?,?,?,?,?)";

        $bindSql   = $record;
        $bindSql[] = $name;
        $bindSql[] = $value;

        Db::query($query, $bindSql);

        return true;
    }

    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     */
    private static function getNameCondition($doneFlags, $possibleValues)
    {
        $allDoneFlags = "'" . implode("','", $doneFlags) . "'";

        // create the SQL to find archives that are DONE
        return "((name IN ($allDoneFlags)) AND (value IN (" . implode(',', $possibleValues) . ")))";
    }

    protected function acquireArchiveTableLock($numericTable)
    {
        $dbLockName = $this->getArchiveLockName($numericTable);

        if (Db::getDbLock($dbLockName, $maxRetries = 30) === false) {
            throw new Exception("allocateNewArchiveId: Cannot get named lock $dbLockName.");
        }
    }

    protected function releaseArchiveTableLock($numericTable)
    {
        $dbLockName = $this->getArchiveLockName($numericTable);
        Db::releaseDbLock($dbLockName);
    }

    protected function getArchiveLockName($numericTable)
    {
        return "allocateNewArchiveId.$numericTable";
    }

}
