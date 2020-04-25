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
    private $archivingStatus;

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

        $idSites = array_map('intval', $idSites);

        // select all usable archives
        $sql = "SELECT idsite, date1, date2, period, name,
                       GROUP_CONCAT(idarchive, '.', value ORDER BY ts_archived DESC) as archives
                  FROM `$archiveTable`
                 WHERE name LIKE 'done%'
                   AND value NOT IN (" . ArchiveWriter::DONE_ERROR . ")
                   AND idsite IN (" . implode(',', $idSites) . ")
                   AND ts_archived IS NOT NULL
                 GROUP BY idsite, date1, date2, period, name";

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
                    $archiveIds[] = $idarchive; // does not matter what the value is, the latest is usable so older archives can be purged
                }
            }
        }

        return $archiveIds;
    }

    public function getPlaceholderArchiveIds($archiveTable, array $idSites)
    {
        $idSites = array_map('intval', $idSites);

        $sql = "SELECT DISTINCT idarchive FROM `$archiveTable` WHERE ts_archived IS NULL AND idsite IN (" . implode(',', $idSites) . ")";
        $result = Db::fetchAll($sql);
        $result = array_column($result, 'idarchive');
        return $result;
    }

    public function updateArchiveAsInvalidated($archiveTable, $idSites, $allPeriodsToInvalidate, Segment $segment = null, $forceInvalidateNonexistantRanges = false)
    {
        // select all idarchive/name pairs we want to invalidate
        $sql = "SELECT idarchive, idsite, period, date1, date2, `name`, `value`
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
            $idArchives = array_map('intval', $idArchives);

            $sql = "UPDATE `$archiveTable` SET `value` = " . ArchiveWriter::DONE_INVALIDATED . " WHERE idarchive IN ("
                . implode(',', $idArchives) . ") AND $nameCondition AND value <> " . ArchiveWriter::DONE_IN_PROGRESS;

            Db::query($sql);
        }

        // these should not be included in the number of invalidated archives, so we count and subtract them
        // (we do want them to be in $allArchivesFoundIndexed, so dummy archives won't be created for them)
        $countOfInProgress = 0;

        // for every archive we need to invalidate, if one does not already exist, create a dummy archive so CronArchive
        // will pick it up
        //
        // we do this by first indexing every archive we found (including DONE_IN_PROGRESS ones for whom we shouldn't
        // create new rows). then below, if one of the period/site/segment combinations isn't in this array, we insert
        // a row
        $allArchivesFoundIndexed = [];
        foreach ($archivesToInvalidate as $row) {
            if ($row['value'] == ArchiveWriter::DONE_IN_PROGRESS) {
                ++$countOfInProgress;
            }

            $allArchivesFoundIndexed[$row['idsite']][$row['period']][$row['date1']][$row['date2']] = $row['idarchive'];
        }

        $now = Date::getNowTimestamp();

        $dummyArchives = [];
        foreach ($idSites as $idSite) {
            foreach ($allPeriodsToInvalidate as $period) {
                $idArchive = $this->allocateNewArchiveId($archiveTable);
                $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($segment ?: new Segment('', []));;

                $dummyArchives[] = [
                    'idarchive' => $idArchive,
                    'name' => $doneFlag,
                    'idsite' => $idSite,
                    'date1' => $period->getDateStart()->getDatetime(),
                    'date2' => $period->getDateEnd()->getDatetime(),
                    'period' => $period->getId(),
                    'ts_invalidated' => $now,
                ];
            }
        }

        $fields = ['idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_invalidated'];
        Db\BatchInsert::tableInsertBatch('archive_invalidations', $fields, $dummyArchives);

        return count($idArchives);
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

    public function getInvalidatedArchiveIdsAsOldOrOlderThan($archive)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory($archive['date1']));
        $sql = "SELECT idarchive FROM `$table` WHERE idsite = ? AND period = ? AND date1 = ? AND date2 = ? AND `name` = ? AND `value` IN ("
            . ArchiveWriter::DONE_INVALIDATED . ', ' . ArchiveWriter::DONE_IN_PROGRESS . ") AND idarchive <= ?";
        $bind = [
            $archive['idsite'],
            $archive['period'],
            $archive['date1'],
            $archive['date2'],
            $archive['name'],
            $archive['idarchive'],
        ];

        $result = Db::fetchAll($sql, $bind);
        $result = array_column($result, 'idarchive');

        return $result;
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
        $rows = Db::fetchAll("SELECT DISTINCT idsite FROM `$numericTable` WHERE `name` LIKE 'done%' AND value IN ("
            . ArchiveWriter::DONE_INVALIDATED . ', ' . ArchiveWriter::DONE_IN_PROGRESS . ")");

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
     * Marks an archive as in progress if it has not been already. This method must be thread
     * safe.
     */
    public function startArchive($idSite, $date1, $date2, $period, $doneFlag)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::factory($date1));

        // find latest idarchive
        $sql = "SELECT ts_archived, idarchive, `value`
            FROM `$table`
            WHERE idsite = ? AND date1 = ? AND date2 = ? AND period = ? AND `name` = ?
            ORDER BY ts_archived DESC
            LIMIT 1";
        $bind = [$idSite, $date1, $date2, $period, $doneFlag];
        $latestArchive = Db::fetchRow($sql, $bind);

        if (empty($latestArchive)) { // should never happen
            return null;
        }

        // if the archive is done or being processed, we don't need to do anything so we abort
        if (!empty($latestArchive)
            && ($latestArchive['value'] == ArchiveWriter::DONE_OK
                || $latestArchive['value'] == ArchiveWriter::DONE_IN_PROGRESS)
        ) {
            return null;
        }

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
     * Gets the next invalidated archive that should be archived in a table.
     *
     * @param string[] $tables
     * @param int $count
     */
    public function getNextInvalidatedArchive($table, $idSite, $period = null, $idSites = null, $idArchivesToExclude = null)
    {
        $sql = "SELECT idarchive, idsite, date1, date2, period, `name`
                  FROM `$table`
                 WHERE `name` LIKE 'done%' AND `value` = ? AND idsite = ?";
        $bind = [
            ArchiveWriter::DONE_INVALIDATED,
            $idSite,
        ];

        if (!empty($period)) {
            $sql .= " AND period = ?";
            $bind[] = $period;
        }

        if (!empty($idSites)) {
            $idSites = array_map('intval', $idSites);
            $sql .= " AND idsite IN (" . implode(',', $idSites) . ")";
        }

        if (!empty($idArchivesToExclude)) {
            $idArchivesToExclude = array_map('intval', $idArchivesToExclude);
            $sql .= " AND idarchive NOT IN (" . implode(',', $idArchivesToExclude) . ')';
        }

        $sql .= " ORDER BY idsite ASC, period ASC, date1 ASC, idarchive DESC LIMIT 1";

        return Db::fetchRow($sql, $bind);
    }

    public function getTablesWithInvalidatedArchives()
    {
        $tables = [];

        $numericTables = ArchiveTableCreator::getTablesArchivesInstalled('numeric', $forceReload = true);
        rsort($numericTables); // sort by date desc so we report tables in the order we want to archive them in

        foreach ($numericTables as $table) {
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

    /**
     * Returns true if there is an archive that exists that can be used when aggregating an archive for $period.
     *
     * @param $idSite
     * @param Period $period
     * @return bool
     * @throws Exception
     */
    public function hasChildArchivesInPeriod($idSite, Period $period)
    {
        $date = $period->getDateStart();
        while ($date->isEarlier($period->getDateEnd()->addPeriod(1, 'month'))) {
            $archiveTable = ArchiveTableCreator::getNumericTable($date);

            $sql = "SELECT idarchive
                  FROM `$archiveTable`
                 WHERE idsite = ? AND date1 >= ? AND date2 <= ? AND period < ? AND `name` LIKE 'done%' AND `value` = " . ArchiveWriter::DONE_OK . "
                 LIMIT 1";
            $bind = [$idSite, $period->getDateStart()->getDatetime(), $period->getDateEnd()->getDatetime(), $period->getId()];

            $result = (bool) Db::fetchOne($sql, $bind);
            if ($result) {
                return true;
            }

            $date = $date->addPeriod(1, 'month'); // move to next archive table
        }
        return false;
    }
}
