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
use Piwik\Sequence;

/**
 * Cleans up outdated archives
 *
 * @package Piwik\DataAccess
 */
class Model
{

    /**
     * Returns the archives IDs that have already been invalidated and have been since re-processed.
     *
     * These archives { archive name (includes segment hash) , idsite, date, period } will be deleted.
     *
     * @param string $archiveTable
     * @param array $idSites
     * @return array
     * @throws Exception
     */
    public function getInvalidatedArchiveIdsSafeToDelete($archiveTable, array $idSites)
    {
        // prevent error 'The SELECT would examine more than MAX_JOIN_SIZE rows'
        Db::get()->query('SET SQL_BIG_SELECTS=1');

        $query = 'SELECT t1.idarchive FROM `' . $archiveTable . '` t1
                  INNER JOIN `' . $archiveTable . '` t2
                      ON t1.name    = t2.name
                      AND t1.idsite = t2.idsite
                      AND t1.date1  = t2.date1
                      AND t1.date2  = t2.date2
                      AND t1.period = t2.period
                  WHERE t1.value = ' . ArchiveWriter::DONE_INVALIDATED . '
                  AND t1.idsite IN (' . implode(",", $idSites) . ')
                  AND t2.value IN(' . ArchiveWriter::DONE_OK . ', ' . ArchiveWriter::DONE_OK_TEMPORARY . ')
                  AND t1.ts_archived < t2.ts_archived
                  AND t1.name LIKE \'done%\'
        ';

        $result = Db::fetchAll($query);

        $archiveIds = array_map(
            function ($elm) {
                return $elm['idarchive'];
            },
            $result
        );
        return $archiveIds;
    }

    /**
     * @param $archiveTable
     * @param $idSites
     * @param $periodId
     * @param $datesToDelete
     * @throws Exception
     */
    public function updateArchiveAsInvalidated($archiveTable, $idSites, $periodId, $datesToDelete)
    {
        $sql = $bind = array();
        $datesToDelete = array_unique($datesToDelete);
        foreach ($datesToDelete as $dateToDelete) {
            $sql[] = '(date1 <= ? AND ? <= date2 AND name LIKE \'done%\')';
            $bind[] = $dateToDelete;
            $bind[] = $dateToDelete;
        }
        $sql = implode(" OR ", $sql);

        $sqlPeriod = "";
        if ($periodId) {
            $sqlPeriod = " AND period = ? ";
            $bind[] = $periodId;
        }

        $query = "UPDATE $archiveTable " .
            " SET value = " . ArchiveWriter::DONE_INVALIDATED .
            " WHERE ( $sql ) " .
            " AND idsite IN (" . implode(",", $idSites) . ")" .
            $sqlPeriod;
        Db::query($query, $bind);
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

    public function deleteArchivesWithPeriod($numericTable, $blobTable, $period, $date)
    {
        $query = "DELETE FROM %s WHERE period = ? AND ts_archived < ?";
        $bind  = array($period, $date);

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

    public function getArchiveIdAndVisits($numericTable, $idSite, $period, $dateStartIso, $dateEndIso, $minDatetimeIsoArchiveProcessedUTC, $doneFlags, $doneFlagValues)
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

        $sqlWhereArchiveName = self::getNameCondition($doneFlags, $doneFlagValues);

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

        try {
            if (ArchiveTableCreator::NUMERIC_TABLE === ArchiveTableCreator::getTypeFromTableName($tableName)) {
                $sequence = new Sequence($tableName);
                $sequence->create();
            }
        } catch (Exception $e) {

        }
    }

    public function allocateNewArchiveId($numericTable)
    {
        $sequence  = new Sequence($numericTable);
        $idarchive = $sequence->getNextId();

        return $idarchive;
    }

    public function deletePreviousArchiveStatus($numericTable, $archiveId, $doneFlag)
    {
        $dbLockName = "deletePreviousArchiveStatus.$numericTable.$archiveId";

        // without advisory lock here, the DELETE would acquire Exclusive Lock
        $this->acquireArchiveTableLock($dbLockName);

        Db::query("DELETE FROM $numericTable WHERE idarchive = ? AND (name = '" . $doneFlag . "')",
            array($archiveId)
        );

        $this->releaseArchiveTableLock($dbLockName);
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

    protected function acquireArchiveTableLock($dbLockName)
    {
        if (Db::getDbLock($dbLockName, $maxRetries = 30) === false) {
            throw new Exception("Cannot get named lock $dbLockName.");
        }
    }

    protected function releaseArchiveTableLock($dbLockName)
    {
        Db::releaseDbLock($dbLockName);
    }

}
