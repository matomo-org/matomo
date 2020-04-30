<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
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
    public function getInvalidatedArchiveIdsSafeToDelete($archiveTable)
    {
        try {
            Db::get()->query('SET SESSION group_concat_max_len=' . (128 * 1024));
        } catch (\Exception $ex) {
            $this->logger->info("Could not set group_concat_max_len MySQL session variable.");
        }

        $sql = "SELECT idsite, date1, date2, period, name,
                       GROUP_CONCAT(idarchive, '.', value ORDER BY ts_archived DESC) as archives
                  FROM `$archiveTable`
                 WHERE name LIKE 'done%'
                   AND `value` NOT IN (" . ArchiveWriter::DONE_ERROR . ")
              GROUP BY idsite, date1, date2, period, name HAVING count(*) > 1";

        $archiveIds = array();

        $rows = Db::fetchAll($sql);
        foreach ($rows as $row) {
            $duplicateArchives = explode(',', $row['archives']);
            $countOfArchives = count($duplicateArchives);

            // if there is more than one archive, the older invalidated ones can be deleted
            if ($countOfArchives > 1) {
                array_shift($duplicateArchives); // we don't want to delete the latest archive if it is usable

                foreach ($duplicateArchives as $pair) {
                    if (strpos($pair, '.') === false) {
                        $this->logger->info("GROUP_CONCAT cut off the query result, you may have to purge archives again.");
                        break;
                    }

                    list($idarchive, $value) = explode('.', $pair);
                    $archiveIds[] = $idarchive;
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

            if ($periodType == Period\Range::PERIOD_ID) {
                foreach ($dates as $date) {
                    // Ranges in the DB match if their date2 is after the start of the search range and date1 is before the end
                    // e.g. search range is 2019-01-01 to 2019-01-31
                    // date2 >= startdate -> Ranges with date2 < 2019-01-01 (ended before 1 January) and are excluded
                    // date1 <= endate -> Ranges with date1 > 2019-01-31 (started after 31 January) and are excluded
                    $dateConditions[] = "(date2 >= ? AND date1 <= ?)";
                    $bind = array_merge($bind, explode(',', $date));
                }
            } else {
                foreach ($dates as $date) {
                    $dateConditions[] = "(date1 <= ? AND ? <= date2)";
                    $bind[] = $date;
                    $bind[] = $date;
                }
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

        $idsToDelete = array_map('intval', $idsToDelete);
        $query = "DELETE FROM %s WHERE idarchive IN (" . implode(',', $idsToDelete) . ")";

        $queryObj = Db::query(sprintf($query, $numericTable), array());
        $deletedRows = $queryObj->rowCount();

        try {
            $queryObj = Db::query(sprintf($query, $blobTable), array());
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

    public function getArchiveIdAndVisits($numericTable, $idSite, $period, $dateStartIso, $dateEndIso, $minDatetimeIsoArchiveProcessedUTC,
                                          $doneFlags, $doneFlagValues = null)
    {
        $bindSQL = array($idSite,
            $dateStartIso,
            $dateEndIso,
            $period,
        );

        $sqlWhereArchiveName = self::getNameCondition($doneFlags, $doneFlagValues);

        $timeStampWhere = '';
        if ($minDatetimeIsoArchiveProcessedUTC) {
            $timeStampWhere = " AND ts_archived >= ? ";
            $bindSQL[]      = $minDatetimeIsoArchiveProcessedUTC;
        }

        // NOTE: we can't predict how many segments there will be so there could be lots of nb_visits/nb_visits_converted rows... have to select everything.
        $sqlQuery = "SELECT idarchive, value, name, ts_archived, date1 as startDate FROM $numericTable
                     WHERE idsite = ?
                         AND date1 = ?
                         AND date2 = ?
                         AND period = ?
                         AND ( ($sqlWhereArchiveName)
                               OR name = '" . ArchiveSelector::NB_VISITS_RECORD_LOOKED_UP . "'
                               OR name = '" . ArchiveSelector::NB_VISITS_CONVERTED_RECORD_LOOKED_UP . "')
                         $timeStampWhere
                     ORDER BY ts_archived DESC, idarchive DESC";
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

    public function updateArchiveStatus($numericTable, $archiveId, $doneFlag, $value)
    {
        Db::query("UPDATE $numericTable SET `value` = ? WHERE idarchive = ? and `name` = ?",
            array($value, $archiveId, $doneFlag)
        );
    }

    public function insertRecord($tableName, $fields, $record, $name, $value)
    {
        // duplicate idarchives are Ignored, see https://github.com/piwik/piwik/issues/987
        $query = "INSERT IGNORE INTO " . $tableName . " (" . implode(", ", $fields) . ")
                  VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE " . end($fields) . " = ?";

        $bindSql   = $record;
        $bindSql[] = $name;
        $bindSql[] = $value;
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
     * Get a list of IDs of archives that don't have any matching rows in the site table. Excludes temporary archives
     * that may still be in use, as specified by the $oldestToKeep passed in.
     * @param string $archiveTableName
     * @param string $oldestToKeep Datetime string
     * @return array of IDs
     */
    public function getArchiveIdsForDeletedSites($archiveTableName)
    {
        $sql = "SELECT DISTINCT idsite FROM " . $archiveTableName;
        $rows = Db::getReader()->fetchAll($sql, array());

        if (empty($rows)) {
            return array(); // nothing to delete
        }

        $idSitesUsed = array_column($rows, 'idsite');

        $model = new \Piwik\Plugins\SitesManager\Model();
        $idSitesExisting = $model->getSitesId();

        $deletedSites = array_diff($idSitesUsed, $idSitesExisting);

        if (empty($deletedSites)) {
            return array();
        }
        $deletedSites = array_values($deletedSites);
        $deletedSites = array_map('intval', $deletedSites);

        $sql = "SELECT DISTINCT idarchive FROM " . $archiveTableName . " WHERE idsite IN (".implode(',',$deletedSites).")";

        $rows = Db::getReader()->fetchAll($sql, array());

        return array_column($rows, 'idarchive');
    }

    /**
     * Get a list of IDs of archives with segments that no longer exist in the DB. Excludes temporary archives that 
     * may still be in use, as specified by the $oldestToKeep passed in.
     * @param string $archiveTableName
     * @param array $segments  List of segments to match against
     * @param string $oldestToKeep Datetime string
     * @return array With keys idarchive, name, idsite
     */
    public function getArchiveIdsForSegments($archiveTableName, array $segments, $oldestToKeep)
    {
        $segmentClauses = [];
        foreach ($segments as $segment) {
            if (!empty($segment['definition'])) {
                $segmentClauses[] = $this->getDeletedSegmentWhereClause($segment);
            }
        }

        if (empty($segmentClauses)) {
            return array();
        }

        $segmentClauses = implode(' OR ', $segmentClauses);

        $sql = 'SELECT idarchive FROM ' . $archiveTableName
            . ' WHERE ts_archived < ?'
            . ' AND (' . $segmentClauses . ')';

        $rows = Db::fetchAll($sql, array($oldestToKeep));

        return array_column($rows, 'idarchive');
    }

    private function getDeletedSegmentWhereClause(array $segment)
    {
        $idSite = (int)$segment['enable_only_idsite'];
        $segmentHash = Segment::getSegmentHash($segment['definition']);
        // Valid segment hashes are md5 strings - just confirm that it is so it's safe for SQL injection
        if (!ctype_xdigit($segmentHash)) {
            throw new Exception($segment . ' expected to be an md5 hash');
        }

        $nameClause = 'name LIKE "done' . $segmentHash . '%"';
        $idSiteClause = '';
        if ($idSite > 0) {
            $idSiteClause = ' AND idsite = ' . $idSite;
        } elseif (! empty($segment['idsites_to_preserve'])) {
            // A segment for all sites was deleted, but there are segments for a single site with the same definition
            $idSitesToPreserve = array_map('intval', $segment['idsites_to_preserve']);
            $idSiteClause = ' AND idsite NOT IN (' . implode(',', $idSitesToPreserve) . ')';
        }

        return "($nameClause $idSiteClause)";
    }

    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     */
    private static function getNameCondition($doneFlags, $possibleValues)
    {
        $allDoneFlags = "'" . implode("','", $doneFlags) . "'";

        // create the SQL to find archives that are DONE
        $result = "((name IN ($allDoneFlags))";

        if (!empty($possibleValues)) {
            $result .= " AND (value IN (" . implode(',', $possibleValues) . ")))";
        }
        $result .= ')';

        return $result;
    }

}
