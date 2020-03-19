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
use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\ArchivingStatus;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
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

    /**
     * @var ArchivingStatus
     */
    private $archivingStatus; // TODO: use DI correctly

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
        $this->archivingStatus = StaticContainer::get(ArchivingStatus::class);
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

        // select all usable archives
        $sql = "SELECT idsite, date1, date2, period, name,
                       GROUP_CONCAT(idarchive, '.', value ORDER BY ts_archived DESC) as archives
                  FROM `$archiveTable`
                 WHERE name LIKE 'done%'
                   AND value NOT IN (" . ArchiveWriter::DONE_ERROR . ")
                   AND idsite IN (" . implode(',', $idSites) . ")
                 GROUP BY idsite, date1, date2, period, name";

        $archiveIds = array();

        $rows = Db::fetchAll($sql);
        foreach ($rows as $row) {
            $duplicateArchives = explode(',', $row['archives']);
            $countOfArchives = count($duplicateArchives);

            $firstArchive = array_shift($duplicateArchives);
            list($firstArchiveId, $firstArchiveValue) = explode('.', $firstArchive);

            // if there is more than one archive, the older invalidated ones can be deleted
            if ($countOfArchives > 1) {
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

    public function updateArchiveAsInvalidated($archiveTable, $idSites, $allPeriodsToInvalidate, Segment $segment = null)
    {
        // select all idarchive/name pairs we want to invalidate
        $sql = "SELECT idarchive, idsite, period, date1, date2, `name`
                  FROM `$archiveTable`
                 WHERE idsite IN (" . implode(',', $idSites) . ")";

        if (!empty($allPeriodsToInvalidate)) {
            $sql .= " AND (";

            $isFirst = true;
            /** @var Period $period */
            foreach ($allPeriodsToInvalidate as $period) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $sql .= " OR ";
                }

                if ($period->getLabel() == 'range') { // for ranges, we delete all ranges that contain the given date(s)
                    $sql .= "(period = " . (int)$period->getId()
                        . " AND date2 >= '" . $period->getDateStart()->getDatetime()
                        . "' AND date1 <= '" . $period->getDateEnd()->getDatetime() . "')";
                } else {
                    $sql .= "(period = " . (int)$period->getId()
                        . " AND date1 = '" . $period->getDateStart()->getDatetime() . "'"
                        . " AND date2 = '" . $period->getDateEnd()->getDatetime() . "')";
                }
            }
            $sql .= ")";
        }

        if ($segment) {
            $nameCondition = "name LIKE '" . Rules::getDoneFlagArchiveContainsAllPlugins($segment) . "%'";
        } else {
            $nameCondition = "name LIKE 'done%'";
        }

        $sql .= " AND $nameCondition";

        $archivesToInvalidate = Db::fetchAll($sql);
        $idArchives = array_column($archivesToInvalidate, 'idarchive');

        // update each archive as invalidated
        if (!empty($idArchives)) {
            $sql = "UPDATE `$archiveTable` SET `value` = " . ArchiveWriter::DONE_INVALIDATED . " WHERE idarchive IN ("
                . implode(',', $idArchives) . ") AND $nameCondition";

            Db::query($sql);
        }

        // for every archive we need to invalidate, if one does not already exist, create a dummy archive so CronArchive
        // will pick it up
        // TODO: explain this later
        $allArchivesFoundIndexed = [];
        foreach ($archivesToInvalidate as $row) {
            $allArchivesFoundIndexed[$row['idsite']][$row['period']][$row['date1']][$row['date2']] = $row['idarchive'];
        }

        foreach ($idSites as $idSite) {
            foreach ($allPeriodsToInvalidate as $period) {
                $startDate = $period->getDateStart()->getDatetime();
                $endDate = $period->getDateEnd()->getDatetime();
                if (!empty($allArchivesFoundIndexed[$idSite][$period->getId()][$startDate][$endDate])
                    || $period->getLabel() == 'range'
                ) {
                    continue;
                }

                $this->createDummyArchive($idSite, $period, $segment);
            }
        }

        return count($idArchives);

        // TODO: in archive.php, maybe only invalidate the first N elements in the list? need to check performance.
        // TODO: check about race conditions here between the select and update
    }

    /**
     * @param string $archiveTable Prefixed table name
     * @param int[] $idSites
     * @param string[][] $datesByPeriodType
     * @param Segment $segment
     * @return \Zend_Db_Statement
     * @throws Exception
     */
    public function updateRangeArchiveAsInvalidated($archiveTable, $idSites, $allPeriodsToInvalidate, Segment $segment = null)
    {
        $bind = array();

        $periodConditions = array();
        if (!empty($allPeriodsToInvalidate)) {
            foreach ($allPeriodsToInvalidate as $period) {
                $dateConditions = array();

                /** @var Period $period */
                $dateConditions[] = "(date1 <= ? AND ? <= date2)";
                $bind[] = $period->getDateStart();
                $bind[] = $period->getDateEnd();

                $dateConditionsSql = implode(" OR ", $dateConditions);
                $periodConditions[] = "(period = 5 AND ($dateConditionsSql))";
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
                         AND ts_archived IS NOT NULL
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
        $rows = Db::fetchAll("SELECT DISTINCT idsite FROM `$numericTable` WHERE name LIKE 'done%' AND value IN (" . ArchiveWriter::DONE_INVALIDATED . ', ' . ArchiveWriter::DONE_IN_PROGRESS);

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

    /**
     * TODO: docs + test
     */
    public function startArchive($idSite, $date1, $date2, $period, $doneFlag)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory($date1));

        // find latest idarchive
        $latestArchive = Db::fetchOne("SELECT MAX(ts_archived), idarchive, `value`
            FROM `$table`
            WHERE idsite = ? AND date1 = ? AND date2 = ? AND period = ? AND `name` = ?",
            [$idSite, $date1, $date2, $period, $doneFlag]);

        if (empty($latestArchive)) { // should never happen
            return;
        }

        // if the archive is done or being processed, we don't need to do anything so we abort
        if (!empty($latestArchive)
            && ($latestArchive['value'] == ArchiveWriter::DONE_OK
                || $latestArchive['value'] == ArchiveWriter::DONE_IN_PROGRESS)
        ) {
            return null;
        }

        // TODO: what do we do when there's no archive? uhhhhh oh.
        //       archive invalidation must be insert or update? not sure. we could try to switch to using a lock as well?
        //       easiest to insert if row does not exist.

        // set archive value to DONE_IN_PROGRESS IF NOT SET ALREADY
        $statement = Db::query("UPDATE `$table` SET `value` = ? WHERE idarchive = ? AND `name` = ? AND value = ?", [
            ArchiveWriter::DONE_IN_PROGRESS,
            $latestArchive['idarchive'],
            $doneFlag,
            ArchiveWriter::DONE_INVALIDATED,
        ]);

        if ($statement->rowCount() > 0) { // if we updated, then we've marked the archive as started
            return $latestArchive['idarchive'];
        }

        // if we didn't get anything, some process either got there first, OR
        // the archive was started previously and failed in a way that kept it's done value
        // set to DONE_IN_PROGRESS. try to acquire the lock and if acquired, archiving isn' in process
        // so we can claim it.
        $lock = $this->archivingStatus->acquireArchiveInProgressLock($idSite, $date1, $date2, $period, $doneFlag);
        if (!$lock->isLocked()) {
            return null; // we couldn't claim the lock, archive is in progress
        }

        Db::query("UPDATE `$table` SET `value` = ? WHERE idarchive = ? AND `name` = ?", [
            ArchiveWriter::DONE_IN_PROGRESS, $latestArchive['idarchive'], $doneFlag]);

        return $latestArchive['idarchive'];
    }

    /**
     * TODO: docs  + test
     *
     * @param string[] $tables
     * @param int $count
     */
    public function getNextInvalidatedArchive($table, $period = null, $idSites = null)
    {
        $sql = "SELECT idsite, date1, date2, period, `name`
                  FROM `$table`
                 WHERE `name` LIKE 'done%' AND `value` = ?";
        $bind[] = ArchiveWriter::DONE_INVALIDATED;

        if (!empty($period)) {
            $sql .= " AND period = ?";
            $bind[] = $period;
        }

        if (!empty($idSites)) {
            $idSites = array_map('intval', $idSites);
            $sql .= " AND idsite IN (" . implode(',', $idSites) . ")";
        }

        $sql .= "ORDER BY idsite ASC, period ASC LIMIT 1";

        return Db::fetchRow($sql, $bind);
    }

    public function getTablesWithInvalidatedArchives()
    {
        $tables = [];

        $numericTables = ArchiveTableCreator::getTablesArchivesInstalled('numeric', $forceReload = true);
        print_r($numericTables);
        foreach ($numericTables as $table) {
            print "  $table\n";
            // we look for both invalidated and in progress archives, since it's possible an in progress archive failed and was never set to invalidated
            $sql = "SELECT idarchive FROM `$table` WHERE name LIKE 'done%' AND `value` IN (" . ArchiveWriter::DONE_INVALIDATED . ', ' . ArchiveWriter::DONE_IN_PROGRESS . ") LIMIT 1";
            $idArchive = Db::fetchOne($sql);

            if (!empty($idArchive)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    private function createDummyArchive($idSite, Period $period, Segment $segment = null)
    {
        $archiveTable = ArchiveTableCreator::getNumericTable($period->getDateStart());
        $idArchive = $this->allocateNewArchiveId($archiveTable);
        $sql = "INSERT INTO `$archiveTable` (idarchive, `name`, idsite, date1, date2, period, ts_archived, `value`)
            VALUES (?, ?, ?, ?, ?, ?, NULL, ?)";

        $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($segment ?: new Segment('', []));
        Db::query($sql, [
            $idArchive,
            $doneFlag,
            $idSite,
            $period->getDateStart()->getDatetime(),
            $period->getDateEnd()->getDatetime(),
            $period->getId(), ArchiveWriter::DONE_INVALIDATED,
        ]);
    }
}
