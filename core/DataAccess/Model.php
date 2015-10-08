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
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Sequence;
use Psr\Log\LoggerInterface;

/**
 * Cleans up outdated archives
 *
 * @package Piwik\DataAccess
 */
class Model
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
    }

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
        try {
            Db::get()->query('SET SESSION group_concat_max_len=' . (128 * 1024));
        } catch (\Exception $ex) {
            $this->logger->info("Could not set group_concat_max_len MySQL session variable.");
        }

        $idSites = array_map(function ($v) { return (int)$v; }, $idSites);

        $sql = "SELECT idsite, date1, date2, period, name,
                       GROUP_CONCAT(idarchive, '.', value ORDER BY ts_archived DESC) as archives
                  FROM `$archiveTable`
                 WHERE name LIKE 'done%'
                   AND value IN (" . ArchiveWriter::DONE_INVALIDATED . ','
                                   . ArchiveWriter::DONE_OK . ','
                                   . ArchiveWriter::DONE_OK_TEMPORARY . ")
                   AND idsite IN (" . implode(',', $idSites) . ")
                 GROUP BY idsite, date1, date2, period, name";

        $archiveIds = array();

        $rows = Db::fetchAll($sql);
        foreach ($rows as $row) {
            $duplicateArchives = explode(',', $row['archives']);

            $firstArchive = array_shift($duplicateArchives);
            list($firstArchiveId, $firstArchiveValue) = explode('.', $firstArchive);

            // if the first archive (ie, the newest) is an 'ok' or 'ok temporary' archive, then
            // all invalidated archives after it can be deleted
            if ($firstArchiveValue == ArchiveWriter::DONE_OK
                || $firstArchiveValue == ArchiveWriter::DONE_OK_TEMPORARY
            ) {
                foreach ($duplicateArchives as $pair) {
                    if (strpos($pair, '.') === false) {
                        $this->logger->info("GROUP_CONCAT cut off the query result, you may have to purge archives again.");
                        break;
                    }

                    list($idarchive, $value) = explode('.', $pair);
                    if ($value == ArchiveWriter::DONE_INVALIDATED) {
                        $archiveIds[] = $idarchive;
                    }
                }
            }
        }

        return $archiveIds;
    }

    /**
     * @param string $archiveTable Prefixed table name
     * @param int[] $idSites
     * @param string[][] $datesByPeriodType
     * @param Segment $segment
     * @return \Zend_Db_Statement
     * @throws Exception
     */
    public function updateArchiveAsInvalidated($archiveTable, $idSites, $datesByPeriodType, Segment $segment = null)
    {
        $idSites = array_map('intval', $idSites);

        $bind = array();

        $periodConditions = array();
        foreach ($datesByPeriodType as $periodType => $dates) {
            $dateConditions = array();

            foreach ($dates as $date) {
                $dateConditions[] = "(date1 <= ? AND ? <= date2)";
                $bind[] = $date;
                $bind[] = $date;
            }

            $dateConditionsSql = implode(" OR ", $dateConditions);
            if (empty($periodType)
                || $periodType == Period\Day::PERIOD_ID
            ) {
                // invalidate all periods if no period supplied or period is day
                $periodConditions[] = "($dateConditionsSql)";
            } else if ($periodType == Period\Range::PERIOD_ID) {
                $periodConditions[] = "(period = " . Period\Range::PERIOD_ID . " AND ($dateConditionsSql))";
            } else {
                // for non-day periods, invalidate greater periods, but not range periods
                $periodConditions[] = "(period >= " . (int)$periodType . " AND period < " . Period\Range::PERIOD_ID . " AND ($dateConditionsSql))";
            }
        }

        if ($segment) {
            $nameCondition = "name LIKE '" . Rules::getDoneFlagArchiveContainsAllPlugins($segment) . "%'";
        } else {
            $nameCondition = "name LIKE 'done%'";
        }

        $sql = "UPDATE $archiveTable SET value = " . ArchiveWriter::DONE_INVALIDATED
             . " WHERE $nameCondition
                   AND idsite IN (" . implode(", ", $idSites) . ")
                   AND (" . implode(" OR ", $periodConditions) . ")";

        return Db::query($sql, $bind);
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

        $queryObj = Db::query(sprintf($query, $numericTable), $bind);
        $deletedRows = $queryObj->rowCount();

        try {
            $queryObj = Db::query(sprintf($query, $blobTable), $bind);
            $deletedRows += $queryObj->rowCount();
        } catch (Exception $e) {
            // Individual blob tables could be missing
            $this->logger->debug("Unable to delete archives by period from {blobTable}.", array(
                'blobTable' => $blobTable,
                'exception' => $e,
            ));
        }

        return $deletedRows;
    }

    public function deleteArchiveIds($numericTable, $blobTable, $idsToDelete)
    {
        $idsToDelete = array_values($idsToDelete);
        $query = "DELETE FROM %s WHERE idarchive IN (" . Common::getSqlStringFieldsArray($idsToDelete) . ")";

        $queryObj = Db::query(sprintf($query, $numericTable), $idsToDelete);
        $deletedRows = $queryObj->rowCount();

        try {
            $queryObj = Db::query(sprintf($query, $blobTable), $idsToDelete);
            $deletedRows += $queryObj->rowCount();
        } catch (Exception $e) {
            // Individual blob tables could be missing
            $this->logger->debug("Unable to delete archive IDs from {blobTable}.", array(
                'blobTable' => $blobTable,
                'exception' => $e,
            ));
        }

        return $deletedRows;
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

        try {
            $idarchive = $sequence->getNextId();
        } catch (Exception $e) {
            // edge case: sequence was not found, create it now
            $sequence->create();

            $idarchive = $sequence->getNextId();
        }

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
     * Returns the site IDs for invalidated archives in an archive table.
     *
     * @param string $numericTable The numeric table to search through.
     * @return int[]
     */
    public function getSitesWithInvalidatedArchive($numericTable)
    {
        $rows = Db::fetchAll("SELECT DISTINCT idsite FROM `$numericTable` WHERE name LIKE 'done%' AND value = " . ArchiveWriter::DONE_INVALIDATED);

        $result = array();
        foreach ($rows as $row) {
            $result[] = $row['idsite'];
        }
        return $result;
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
