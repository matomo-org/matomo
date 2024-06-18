<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Exception;
use Piwik\Archive\ArchiveInvalidator;
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
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Log\LoggerInterface;

/**
 * Cleans up outdated archives
 */
class Model
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: StaticContainer::get(LoggerInterface::class);
    }

    /**
     * Returns the archives IDs that have already been invalidated and have been since re-processed.
     *
     * These archives { archive name (includes segment hash) , idsite, date, period } will be deleted.
     *
     * @param string $archiveTable
     * @param array $idSites
     * @param bool $setGroupContentMaxLen for tests only
     * @return array
     * @throws Exception
     */
    public function getInvalidatedArchiveIdsSafeToDelete($archiveTable, $setGroupContentMaxLen = true)
    {
        if ($setGroupContentMaxLen) {
            try {
                Db::get()->query('SET SESSION group_concat_max_len=' . (128 * 1024));
            } catch (\Exception $ex) {
                $this->logger->info("Could not set group_concat_max_len MySQL session variable.");
            }
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

            // do not consider purging partial archives, if they are the latest archive,
            // and we don't want to delete the latest archive if it is usable
            while (!empty($duplicateArchives)) {
                $pair = $duplicateArchives[0];
                if ($this->isCutOffGroupConcatResult($pair)) { // can occur if the GROUP_CONCAT value is cut off
                    break;
                }

                [$idarchive, $value] = explode('.', $pair);

                array_shift($duplicateArchives);

                if ($value != ArchiveWriter::DONE_PARTIAL) {
                    break;
                }
            }

            // if there is more than one archive, the older invalidated ones can be deleted
            if (!empty($duplicateArchives)) {
                foreach ($duplicateArchives as $pair) {
                    if ($this->isCutOffGroupConcatResult($pair)) {
                        $this->logger->info("GROUP_CONCAT cut off the query result, you may have to purge archives again.");
                        break;
                    }

                    [$idarchive, $value] = explode('.', $pair);
                    $archiveIds[] = $idarchive; // does not matter what the value is, the latest is usable so older archives can be purged
                }
            }
        }

        return $archiveIds;
    }

    public function updateArchiveAsInvalidated(
        $archiveTable,
        $idSites,
        $allPeriodsToInvalidate,
        Segment $segment = null,
        bool $forceInvalidateNonexistentRanges = false,
        ?string $name = null,
        bool $doNotCreateInvalidations = false
    ) {
        if (empty($idSites)) {
            return 0;
        }

        // select all idarchive/name pairs we want to invalidate
        $sql = "SELECT idarchive, idsite, period, date1, date2, `name`, `value`
                  FROM `$archiveTable`
                 WHERE idsite IN (" . implode(',', $idSites) . ") AND value <> " . ArchiveWriter::DONE_PARTIAL;

        $periodCondition = '';
        if (!empty($allPeriodsToInvalidate)) {
            $periodCondition .= " AND (";

            $isFirst = true;
            /** @var Period $period */
            foreach ($allPeriodsToInvalidate as $period) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $periodCondition .= " OR ";
                }

                if ($period->getLabel() == 'range') { // for ranges, we delete all ranges that contain the given date(s)
                    $periodCondition .= "(period = " . (int)$period->getId()
                        . " AND date2 >= '" . $period->getDateStart()->getDatetime()
                        . "' AND date1 <= '" . $period->getDateEnd()->getDatetime() . "')";
                } else {
                    $periodCondition .= "(period = " . (int)$period->getId()
                        . " AND date1 = '" . $period->getDateStart()->getDatetime() . "'"
                        . " AND date2 = '" . $period->getDateEnd()->getDatetime() . "')";
                }
            }
            $periodCondition .= ")";
        }
        $sql .= $periodCondition;

        if (!empty($name)) {
            if (strpos($name, '.') !== false) {
                [$plugin, $name] = explode('.', $name, 2);
            } else {
                $plugin = $name;
                $name = null;
            }
        }

        if (empty($plugin)) {
            $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($segment ?: new Segment('', []));
        } else {
            $doneFlag = Rules::getDoneFlagArchiveContainsOnePlugin($segment ?: new Segment('', []), $plugin);
        }

        $nameCondition = "name LIKE '$doneFlag%'";

        $sql .= " AND $nameCondition";

        $idArchives = [];
        $archivesToInvalidate = [];

        // update each archive as invalidated (but only for full archives or plugin archives, not for partial archives.
        // DONE_INVALIDATED also implies that an archive is whole and not partial, and we want to avoid that.)
        if (empty($name)) {
            $archivesToInvalidate = Db::fetchAll($sql);
            $idArchives = array_column($archivesToInvalidate, 'idarchive');

            if (!empty($idArchives)) {
                $idArchives = array_map('intval', $idArchives);

                $sql = "UPDATE `$archiveTable` SET `value` = " . ArchiveWriter::DONE_INVALIDATED . " WHERE idarchive IN ("
                    . implode(',', $idArchives) . ") AND $nameCondition";

                Db::query($sql);
            }
        }

        if (true === $doNotCreateInvalidations) {
            return count($idArchives);
        }

        // we add every archive we need to invalidate + the archives that do not already exist to archive_invalidations.
        // except for archives that are DONE_IN_PROGRESS.
        $archivesToCreateInvalidationRowsFor = [];
        foreach ($archivesToInvalidate as $row) {
            $archivesToCreateInvalidationRowsFor[$row['idsite']][$row['period']][$row['date1']][$row['date2']][$row['name']] = $row['idarchive'];
        }

        $now = Date::now()->getDatetime();

        $existingInvalidations = $this->getExistingInvalidations($idSites, $periodCondition, $nameCondition);

        $hashesOfAllSegmentsToArchiveInCoreArchive = Rules::getSegmentsToProcess($idSites);
        $hashesOfAllSegmentsToArchiveInCoreArchive = array_map(function ($definition) {
            return Segment::getSegmentHash($definition);
        }, $hashesOfAllSegmentsToArchiveInCoreArchive);

        $dummyArchives = [];
        foreach ($idSites as $idSite) {
            try {
                $siteCreationTime = Site::getCreationDateFor($idSite);
            } catch (\Exception $ex) {
                continue;
            }

            $siteCreationTime = Date::factory($siteCreationTime);
            foreach ($allPeriodsToInvalidate as $period) {
                if (
                    $period->getLabel() == 'range'
                    && !$forceInvalidateNonexistentRanges
                ) {
                    continue; // range
                }

                if ($period->getDateEnd()->isEarlier($siteCreationTime)) {
                    continue; // don't add entries if it is before the time the site was created
                }

                $date1 = $period->getDateStart()->toString();
                $date2 = $period->getDateEnd()->toString();

                // we insert rows for the doneFlag we want to invalidate + any others we invalidated when doing the LIKE above.
                // if we invalidated something in the archive tables, we want to make sure it appears in the invalidation queue,
                // so we'll eventually reprocess it.
                $doneFlagsFound = $archivesToCreateInvalidationRowsFor[$idSite][$period->getId()][$date1][$date2] ?? [];
                $doneFlagsFound = array_keys($doneFlagsFound);
                $doneFlagsToCheck = array_merge([$doneFlag], $doneFlagsFound);
                $doneFlagsToCheck = array_unique($doneFlagsToCheck);

                foreach ($doneFlagsToCheck as $doneFlagToCheck) {
                    $key = $this->makeExistingInvalidationArrayKey($idSite, $date1, $date2, $period->getId(), $doneFlagToCheck, $name);
                    if (!empty($existingInvalidations[$key])) {
                        continue; // avoid adding duplicates where possible
                    }

                    $hash = $this->getHashFromDoneFlag($doneFlagToCheck);
                    if (
                        $doneFlagToCheck != $doneFlag
                        && (empty($hash)
                            || !in_array($hash, $hashesOfAllSegmentsToArchiveInCoreArchive)
                            || strpos($doneFlagToCheck, '.') !== false)
                    ) {
                        continue; // the done flag is for a segment that is not auto archive or a plugin specific archive, so we don't want to process it.
                    }

                    $idArchive = $archivesToCreateInvalidationRowsFor[$idSite][$period->getId()][$date1][$date2][$doneFlagToCheck] ?? null;

                    $dummyArchives[] = [
                        'idarchive' => $idArchive,
                        'name' => $doneFlagToCheck,
                        'report' => $name,
                        'idsite' => $idSite,
                        'date1' => $period->getDateStart()->getDatetime(),
                        'date2' => $period->getDateEnd()->getDatetime(),
                        'period' => $period->getId(),
                        'ts_invalidated' => $now,
                    ];
                }
            }
        }

        if (!empty($dummyArchives)) {
            $fields = ['idarchive', 'name', 'report', 'idsite', 'date1', 'date2', 'period', 'ts_invalidated'];
            Db\BatchInsert::tableInsertBatch(Common::prefixTable('archive_invalidations'), $fields, $dummyArchives);
        }

        return count($idArchives);
    }

    private function getExistingInvalidations($idSites, $periodCondition, $nameCondition)
    {
        $table = Common::prefixTable('archive_invalidations');

        $idSites = array_map('intval', $idSites);

        $sql = "SELECT idsite, date1, date2, period, name, report, COUNT(*) as `count` FROM `$table`
                 WHERE idsite IN (" . implode(',', $idSites) . ") AND status = " . ArchiveInvalidator::INVALIDATION_STATUS_QUEUED . "
                       $periodCondition AND $nameCondition
              GROUP BY idsite, date1, date2, period, name";
        $rows = Db::fetchAll($sql);

        $invalidations = [];
        foreach ($rows as $row) {
            $key = $this->makeExistingInvalidationArrayKey($row['idsite'], $row['date1'], $row['date2'], $row['period'], $row['name'], $row['report']);
            $invalidations[$key] = $row['count'];
        }
        return $invalidations;
    }

    private function makeExistingInvalidationArrayKey($idSite, $date1, $date2, $period, $name, $report)
    {
        return implode('.', [$idSite, $date1, $date2, $period, $name, $report]);
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
        if (empty($idSites)) {
            return;
        }

        $bind = array();

        $periodConditions = array();
        if (!empty($allPeriodsToInvalidate)) {
            foreach ($allPeriodsToInvalidate as $period) {
                $dateConditions = array();

                /** @var Period $period */
                $dateConditions[] = "(date1 <= ? AND ? <= date2)";
                $bind[] = $period->getDateStart()->getDatetime();
                $bind[] = $period->getDateEnd()->getDatetime();

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
        if (SettingsServer::isArchivePhpTriggered()) {
            StaticContainer::get(LoggerInterface::class)->info('deleteArchivesWithPeriod: ' . $numericTable . ' with period = ' . $period . ' and date = ' . $date);
        }

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

    public function deleteOlderArchives(Parameters $params, $name, $tsArchived, $idArchive)
    {
        $dateStart = $params->getPeriod()->getDateStart();
        $dateEnd = $params->getPeriod()->getDateEnd();

        $numericTable = ArchiveTableCreator::getNumericTable($dateStart);
        $blobTable = ArchiveTableCreator::getBlobTable($dateStart);

        $sql = "SELECT idarchive FROM `$numericTable` WHERE idsite = ? AND date1 = ? AND date2 = ? AND period = ? AND name = ? AND ts_archived <= ? AND idarchive < ?";

        $idArchives = Db::fetchAll($sql, [$params->getSite()->getId(), $dateStart->getDatetime(), $dateEnd->getDatetime(), $params->getPeriod()->getId(), $name, $tsArchived, $idArchive]);
        $idArchives = array_column($idArchives, 'idarchive');
        if (empty($idArchives)) {
            return;
        }

        if (SettingsServer::isArchivePhpTriggered()) {
            StaticContainer::get(LoggerInterface::class)->info('deleteOlderArchives with ' . $params . ', name = ' . $name . ', ts_archived < ' . $tsArchived . ', idarchive < ' . $idArchive);
        }

        $this->deleteArchiveIds($numericTable, $blobTable, $idArchives);
    }

    public function getArchiveIdAndVisits(
        $numericTable,
        $idSite,
        $period,
        $dateStartIso,
        $dateEndIso,
        $minDatetimeIsoArchiveProcessedUTC,
        $doneFlags,
        $doneFlagValues = null
    ) {
        $bindSQL = array($idSite,
            $dateStartIso,
            $dateEndIso,
            $period,
        );

        $sqlWhereArchiveName = self::getNameCondition($doneFlags, $doneFlagValues);

        $timeStampWhere = '';
        if ($minDatetimeIsoArchiveProcessedUTC) {
            $timeStampWhere = " AND arc1.ts_archived >= ? ";
            $bindSQL[]      = $minDatetimeIsoArchiveProcessedUTC;
        }

        // NOTE: we can't predict how many segments there will be so there could be lots of nb_visits/nb_visits_converted rows... have to select everything.
        $sqlQuery = "SELECT arc1.idarchive, arc1.value, arc1.name, arc1.ts_archived, arc1.date1 as startDate, arc2.value as " . ArchiveSelector::NB_VISITS_RECORD_LOOKED_UP . ", arc3.value as " . ArchiveSelector::NB_VISITS_CONVERTED_RECORD_LOOKED_UP . "
                     FROM $numericTable arc1
                     LEFT JOIN $numericTable arc2 on arc2.idarchive = arc1.idarchive and (arc2.name = '" . ArchiveSelector::NB_VISITS_RECORD_LOOKED_UP . "')
                     LEFT JOIN $numericTable arc3 on arc3.idarchive = arc1.idarchive and (arc3.name = '" . ArchiveSelector::NB_VISITS_CONVERTED_RECORD_LOOKED_UP . "')
                     WHERE arc1.idsite = ?
                         AND arc1.date1 = ?
                         AND arc1.date2 = ?
                         AND arc1.period = ?
                         AND ($sqlWhereArchiveName)
                         $timeStampWhere
                     ORDER BY arc1.ts_archived DESC, arc1.idarchive DESC";

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

    public function getInstalledArchiveTables()
    {
        $allArchiveNumeric = Db::get()->fetchCol("SHOW TABLES LIKE '" . Common::prefixTable('archive_numeric%') . "'");
        $allArchiveBlob    = Db::get()->fetchCol("SHOW TABLES LIKE '" . Common::prefixTable('archive_blob%') . "'");

        return array_merge($allArchiveBlob, $allArchiveNumeric);
    }

    public function allocateNewArchiveId($numericTable)
    {
        $sequence  = new Sequence($numericTable);

        try {
            $idarchive = $sequence->getNextId();
        } catch (Exception $e) {
            // edge case: sequence was not found, create it now
            try {
                $sequence->create();
            } catch (Exception $ex) {
                // Ignore duplicate entry error, as that means another request might have already created the sequence
                if (!Db::get()->isErrNo($ex, \Piwik\Updater\Migration\Db::ERROR_CODE_DUPLICATE_ENTRY)) {
                    throw $ex;
                }
            }

            $idarchive = $sequence->getNextId();
        }

        return $idarchive;
    }

    public function updateArchiveStatus($numericTable, $archiveId, $doneFlag, $value)
    {
        Db::query(
            "UPDATE $numericTable SET `value` = ? WHERE idarchive = ? and `name` = ?",
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
        $rows = Db::fetchAll("SELECT DISTINCT idsite FROM `$numericTable` WHERE `name` LIKE 'done%' AND `value` IN ("
            . ArchiveWriter::DONE_INVALIDATED . ")");

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

        $sql = "SELECT DISTINCT idarchive FROM " . $archiveTableName . " WHERE idsite IN (" . implode(',', $deletedSites) . ")";

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
        $segmentHash = $segment['hash'] ?? '';
        // Valid segment hashes are md5 strings - just confirm that it is so it's safe for SQL injection
        if (!ctype_xdigit($segmentHash)) {
            throw new Exception($segmentHash . ' expected to be an md5 hash');
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
        $result = "((arc1.name IN ($allDoneFlags))";

        if (!empty($possibleValues)) {
            $result .= " AND (arc1.value IN (" . implode(',', $possibleValues) . ")))";
        }
        $result .= ')';

        return $result;
    }

    /**
     * Marks an archive as in progress if it has not been already. This method must be thread
     * safe.
     */
    public function startArchive($invalidation)
    {
        $table = Common::prefixTable('archive_invalidations');

        // set archive value to in progress if not set already
        $statement = Db::query("UPDATE `$table` SET `status` = ?, ts_started = NOW() WHERE idinvalidation = ? AND status = ?", [
            ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
            $invalidation['idinvalidation'],
            ArchiveInvalidator::INVALIDATION_STATUS_QUEUED,
        ]);

        if ($statement->rowCount() > 0) { // if we updated, then we've marked the archive as started
            return true;
        }

        // archive was not originally started or was started within 24 hours, we assume it's ongoing and another process
        // (on this machine or another) is actively archiving it.
        if (
            empty($invalidation['ts_started'])
            || $invalidation['ts_started'] > Date::now()->subDay(1)->getTimestamp()
        ) {
            return false;
        }

        // archive was started over 24 hours ago, we assume it failed and take it over
        Db::query("UPDATE `$table` SET `status` = ?, ts_started = NOW() WHERE idinvalidation = ?", [
            ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
            $invalidation['idinvalidation'],
        ]);

        // remove similar invalidations w/ lesser idinvalidation values
        $bind = [
            $invalidation['idsite'],
            $invalidation['period'],
            $invalidation['date1'],
            $invalidation['date2'],
            $invalidation['name'],
            ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
        ];

        if (empty($invalidation['report'])) {
            $reportClause = "(report IS NULL OR report = '')";
        } else {
            $reportClause = "report = ?";
            $bind[] = $invalidation['report'];
        }

        $sql = "DELETE FROM " . Common::prefixTable('archive_invalidations') . " WHERE idinvalidation < ? AND idsite = ? AND "
            . "date1 = ? AND date2 = ? AND `period` = ? AND `name` = ? AND $reportClause";
        Db::query($sql, $bind);

        return true;
    }

    public function isSimilarArchiveInProgress($invalidation)
    {
        $table = Common::prefixTable('archive_invalidations');

        $bind = [
            $invalidation['idsite'],
            $invalidation['period'],
            $invalidation['date1'],
            $invalidation['date2'],
            $invalidation['name'],
            ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
        ];

        if (empty($invalidation['report'])) {
            $reportClause = "(report IS NULL OR report = '')";
        } else {
            $reportClause = "report = ?";
            $bind[] = $invalidation['report'];
        }

        $sql = "SELECT idinvalidation FROM `$table` WHERE idsite = ? AND `period` = ? AND date1 = ? AND date2 = ? AND `name` = ? AND `status` = ? AND ts_started IS NOT NULL AND $reportClause LIMIT 1";
        $result = Db::fetchOne($sql, $bind);

        return !empty($result);
    }

    /**
     * Gets the next invalidated archive that should be archived in a table.
     *
     * @param int $idSite
     * @param string $archivingStartTime
     * @param int[]|null $idInvalidationsToExclude
     * @param bool $useLimit Whether to limit the result set to one result or not. Used in tests only.
     */
    public function getNextInvalidatedArchive($idSite, $archivingStartTime, $idInvalidationsToExclude = null, $useLimit = true)
    {
        $table = Common::prefixTable('archive_invalidations');
        $sql = "SELECT *
                  FROM `$table`
                 WHERE idsite = ? AND status != ? AND ts_invalidated <= ?";
        $bind = [
            $idSite,
            ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
            $archivingStartTime,
        ];

        if (!empty($idInvalidationsToExclude)) {
            $idInvalidationsToExclude = array_map('intval', $idInvalidationsToExclude);
            $sql .= " AND idinvalidation NOT IN (" . implode(',', $idInvalidationsToExclude) . ')';
        }

        // NOTE: order here is very important to ensure we process lower period archives first, and general 'all' archives before
        // segment archives, and so we use the latest idinvalidation
        $sql .= " ORDER BY date1 DESC, period ASC, CHAR_LENGTH(name) ASC, idinvalidation DESC";

        if ($useLimit) {
            $sql .= " LIMIT 1";
            return Db::fetchRow($sql, $bind);
        } else {
            return Db::fetchAll($sql, $bind);
        }
    }

    public function deleteInvalidations($archiveInvalidations)
    {
        $ids = array_column($archiveInvalidations, 'idinvalidation');
        $ids = array_map('intval', $ids);

        $table = Common::prefixTable('archive_invalidations');
        $sql = "DELETE FROM `$table` WHERE idinvalidation IN (" . implode(', ', $ids) . ")";

        Db::query($sql);
    }

    public function removeInvalidationsLike($idSite, $start)
    {
        $idSitesClause = $this->getRemoveInvalidationsIdSitesClause($idSite);

        $table = Common::prefixTable('archive_invalidations');
        $sql = "DELETE FROM `$table` WHERE $idSitesClause `name` LIKE ?";

        Db::query($sql, ['done%.' . str_replace('_', "\\_", $start)]);
    }

    public function removeInvalidations($idSite, $plugin, $report)
    {
        $idSitesClause = $this->getRemoveInvalidationsIdSitesClause($idSite);

        $table = Common::prefixTable('archive_invalidations');
        $sql = "DELETE FROM `$table` WHERE $idSitesClause `name` LIKE ? AND report = ?";

        Db::query($sql, ['done%.' . str_replace('_', "\\_", $plugin), $report]);
    }

    public function isArchiveAlreadyInProgress($invalidatedArchive)
    {
        $table = Common::prefixTable('archive_invalidations');

        $bind = [
            $invalidatedArchive['idsite'],
            $invalidatedArchive['date1'],
            $invalidatedArchive['date2'],
            $invalidatedArchive['period'],
            $invalidatedArchive['name'],
        ];

        $reportClause = "(report = '' OR report IS NULL)";
        if (!empty($invalidatedArchive['report'])) {
            $reportClause = "report = ?";
            $bind[] = $invalidatedArchive['report'];
        }

        $sql = "SELECT MAX(idinvalidation) FROM `$table` WHERE idsite = ? AND date1 = ? AND date2 = ? AND `period` = ? AND `name` = ? AND status = 1 AND $reportClause";

        $inProgressInvalidation = Db::fetchOne($sql, $bind);
        return $inProgressInvalidation;
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

            // we look for any archive that can be used to compute this one. this includes invalidated archives, since it is possible
            // under certain circumstances for them to exist, when archiving a higher period that includes them. the main example being
            // the GoogleAnalyticsImporter which disallows the recomputation of invalidated archives for imported data, since that would
            // essentially get rid of the imported data.
            $usableDoneFlags = [ArchiveWriter::DONE_OK, ArchiveWriter::DONE_INVALIDATED, ArchiveWriter::DONE_PARTIAL, ArchiveWriter::DONE_OK_TEMPORARY];

            $sql = "SELECT idarchive
                  FROM `$archiveTable`
                 WHERE idsite = ? AND date1 >= ? AND date2 <= ? AND period < ? AND `name` LIKE 'done%' AND `value` IN (" . implode(', ', $usableDoneFlags) . ")
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

    /**
     * Returns true if any invalidations exists for the given
     * $idsite and $doneFlag (name column) for the $period.
     *
     * @param mixed $idSite
     * @param Period $period
     * @param mixed $doneFlag
     * @param mixed $report
     * @return bool
     * @throws Exception
     */
    public function hasInvalidationForPeriodAndName($idSite, Period $period, $doneFlag, $report = null)
    {
        $table = Common::prefixTable('archive_invalidations');

        if (empty($report)) {
            $sql = "SELECT idinvalidation FROM `$table` WHERE idsite = ? AND date1 = ? AND date2 = ? AND `period` = ? AND `name` = ?  AND `report` IS NULL LIMIT 1";
        } else {
            $sql = "SELECT idinvalidation FROM `$table` WHERE idsite = ? AND date1 = ? AND date2 = ? AND `period` = ? AND `name` = ? AND `report` = ? LIMIT 1";
        }

        $bind = [
            $idSite,
            $period->getDateStart()->toString(),
            $period->getDateEnd()->toString(),
            $period->getId(),
            $doneFlag
        ];

        if (!empty($report)) {
            $bind[] = $report;
        }

        $idInvalidation = Db::fetchOne($sql, $bind);

        if (empty($idInvalidation)) {
            return false;
        }

        return true;
    }

    public function deleteInvalidationsForSites(array $idSites)
    {
        $idSites = array_map('intval', $idSites);

        $table = Common::prefixTable('archive_invalidations');
        $sql = "DELETE FROM `$table` WHERE idsite IN (" . implode(',', $idSites) . ")";

        Db::query($sql);
    }

    public function deleteInvalidationsForDeletedSites()
    {
        $siteTable = Common::prefixTable('site');
        $table = Common::prefixTable('archive_invalidations');
        $sql = "DELETE a FROM `$table` a LEFT JOIN `$siteTable` s ON a.idsite = s.idsite WHERE s.idsite IS NULL";
        Db::query($sql);
    }

    private function getRemoveInvalidationsIdSitesClause($idSite)
    {
        if ($idSite === 'all') {
            return '';
        }

        $idSites = is_array($idSite) ? $idSite : [$idSite];
        $idSites = array_map('intval', $idSites);
        $idSitesStr = implode(',', $idSites);

        return "idsite IN ($idSitesStr) AND";
    }

    public function releaseInProgressInvalidation($idinvalidation)
    {
        $table = Common::prefixTable('archive_invalidations');
        $sql = "UPDATE $table SET status = " . ArchiveInvalidator::INVALIDATION_STATUS_QUEUED . ", ts_started = NULL WHERE idinvalidation = ?";
        Db::query($sql, [$idinvalidation]);
    }

    public function resetFailedArchivingJobs()
    {
        $table = Common::prefixTable('archive_invalidations');
        $sql = "UPDATE $table SET status = ? WHERE status = ? AND (ts_started IS NULL OR ts_started < ?)";

        $bind = [
            ArchiveInvalidator::INVALIDATION_STATUS_QUEUED,
            ArchiveInvalidator::INVALIDATION_STATUS_IN_PROGRESS,
            Date::now()->subDay(1)->getDatetime(),
        ];

        $query = Db::query($sql, $bind);
        return $query->rowCount();
    }

    public function getRecordsContainedInArchives(Date $archiveStartDate, array $idArchives, $requestedRecords): array
    {
        $idArchives = array_map('intval', $idArchives);
        $idArchives = implode(',', $idArchives);

        $requestedRecords = is_string($requestedRecords) ? [$requestedRecords] : $requestedRecords;
        $placeholders = Common::getSqlStringFieldsArray($requestedRecords);

        $countSql = "SELECT DISTINCT name FROM %s WHERE idarchive IN ($idArchives) AND name IN ($placeholders) LIMIT " . count($requestedRecords);

        $numericTable = ArchiveTableCreator::getNumericTable($archiveStartDate);
        $blobTable = ArchiveTableCreator::getBlobTable($archiveStartDate);

        // if the requested metrics look numeric, prioritize the numeric table, otherwise the blob table. this way, if all the metrics are
        // found in this table (which will be most of the time), we don't have to query the other table
        if ($this->doRequestedRecordsLookNumeric($requestedRecords)) {
            $tablesToSearch = [$numericTable, $blobTable];
        } else {
            $tablesToSearch = [$blobTable, $numericTable];
        }

        $existingRecords = [];
        foreach ($tablesToSearch as $tableName) {
            $sql = sprintf($countSql, $tableName);
            $rows = Db::fetchAll($sql, $requestedRecords);
            $existingRecords = array_merge($existingRecords, array_column($rows, 'name'));

            if (count($existingRecords) == count($requestedRecords)) {
                break;
            }
        }
        return $existingRecords;
    }

    private function isCutOffGroupConcatResult($pair)
    {
        $position = strpos($pair, '.');
        return $position === false || $position === strlen($pair) - 1;
    }

    private function getHashFromDoneFlag($doneFlag)
    {
        preg_match('/^done([a-zA-Z0-9]+)/', $doneFlag, $matches);
        return $matches[1] ?? '';
    }

    private function doRequestedRecordsLookNumeric(array $requestedRecords): bool
    {
        foreach ($requestedRecords as $record) {
            if (preg_match('/^nb_/', $record)) {
                return true;
            }
        }
        return false;
    }
}
