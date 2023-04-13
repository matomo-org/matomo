<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataAccess;

use Exception;
use Piwik\Archive;
use Piwik\Archive\Chunk;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Segment;
use Psr\Log\LoggerInterface;

/**
 * Data Access object used to query archives
 *
 * A record in the Database for a given report is defined by
 * - idarchive     = unique ID that is associated to all the data of this archive (idsite+period+date)
 * - idsite        = the ID of the website
 * - date1         = starting day of the period
 * - date2         = ending day of the period
 * - period        = integer that defines the period (day/week/etc.). @see period::getId()
 * - ts_archived   = timestamp when the archive was processed (UTC)
 * - name          = the name of the report (ex: uniq_visitors or search_keywords_by_search_engines)
 * - value         = the actual data (a numeric value, or a blob of compressed serialized data)
 *
 */
class ArchiveSelector
{
    const NB_VISITS_RECORD_LOOKED_UP = "nb_visits";

    const NB_VISITS_CONVERTED_RECORD_LOOKED_UP = "nb_visits_converted";

    private static function getModel()
    {
        return new Model();
    }

    /**
     * @param ArchiveProcessor\Parameters $params
     * @param bool $minDatetimeArchiveProcessedUTC deprecated. Will be removed in Matomo 4.
     * @return array An array with four values:
     *               - the latest archive ID or false if none
     *               - the latest visits value for the latest archive, regardless of whether the archive is invalidated or not
     *               - the latest visits converted value for the latest archive, regardless of whether the archive is invalidated or not
     *               - whether there is an archive that exists or not. if this is true and the latest archive is false, it means
     *                 the archive found was not usable (for example, it was invalidated and we are not looking for invalidated archives)
     *               - the ts_archived for the latest usable archive
     * @throws Exception
     */
    public static function getArchiveIdAndVisits(ArchiveProcessor\Parameters $params, $minDatetimeArchiveProcessedUTC = false, $includeInvalidated = null)
    {
        $idSite       = $params->getSite()->getId();
        $period       = $params->getPeriod()->getId();
        $dateStart    = $params->getPeriod()->getDateStart();
        $dateStartIso = $dateStart->toString('Y-m-d');
        $dateEndIso   = $params->getPeriod()->getDateEnd()->toString('Y-m-d');

        $numericTable = ArchiveTableCreator::getNumericTable($dateStart);

        $requestedPlugin = $params->getRequestedPlugin();
        $segment         = $params->getSegment();
        $plugins = array("VisitsSummary", $requestedPlugin);
        $plugins = array_filter($plugins);

        $doneFlags      = Rules::getDoneFlags($plugins, $segment);

        $requestedPluginDoneFlags = empty($requestedPlugin) ? [] : Rules::getDoneFlags([$requestedPlugin], $segment);
        $allPluginsDoneFlag = Rules::getDoneFlagArchiveContainsAllPlugins($segment);

        $doneFlagValues = Rules::getSelectableDoneFlagValues($includeInvalidated === null ? true : $includeInvalidated, $params, $includeInvalidated === null);

        $results = self::getModel()->getArchiveIdAndVisits($numericTable, $idSite, $period, $dateStartIso, $dateEndIso, null, $doneFlags);
        if (empty($results)) { // no archive found
            return [false, false, false, false, false, false];
        }

        $result = self::findArchiveDataWithLatestTsArchived($results, $requestedPluginDoneFlags, $allPluginsDoneFlag);

        $tsArchived = isset($result['ts_archived']) ? $result['ts_archived'] : false;
        $visits = isset($result['nb_visits']) ? $result['nb_visits'] : false;
        $visitsConverted = isset($result['nb_visits_converted']) ? $result['nb_visits_converted'] : false;
        $value = isset($result['value']) ? $result['value'] : false;

        $result['idarchive'] = empty($result['idarchive']) ? [] : [$result['idarchive']];
        if (isset($result['partial'])) {
            $result['idarchive'] = array_merge($result['idarchive'], $result['partial']);
        }

        if (empty($result['idarchive'])
            || (isset($result['value'])
                && !in_array($result['value'], $doneFlagValues))
        ) { // the archive cannot be considered valid for this request (has wrong done flag value)
            return [false, $visits, $visitsConverted, true, $tsArchived, $value];
        }

        if (!empty($minDatetimeArchiveProcessedUTC) && !is_object($minDatetimeArchiveProcessedUTC)) {
            $minDatetimeArchiveProcessedUTC = Date::factory($minDatetimeArchiveProcessedUTC);
        }

        // the archive is too old
        if ($minDatetimeArchiveProcessedUTC
            && !empty($result['idarchive'])
            && Date::factory($tsArchived)->isEarlier($minDatetimeArchiveProcessedUTC)
        ) {
            return [false, $visits, $visitsConverted, true, $tsArchived, $value];
        }

        $idArchives = !empty($result['idarchive']) ? $result['idarchive'] : false;

        return [$idArchives, $visits, $visitsConverted, true, $tsArchived, $value];
    }

    /**
     * Queries and returns archive IDs for a set of sites, periods, and a segment.
     *
     * @param int[] $siteIds
     * @param Period[] $periods
     * @param Segment $segment
     * @param string[] $plugins List of plugin names for which data is being requested.
     * @param bool $includeInvalidated true to include archives that are DONE_INVALIDATED, false if only DONE_OK.
     * @param bool $_skipSetGroupConcatMaxLen for tests
     * @return array Archive IDs are grouped by archive name and period range, ie,
     *               array(
     *                   'VisitsSummary.done' => array(
     *                       '2010-01-01' => array(1,2,3)
     *                   )
     *               )
     * @throws
     */
    public static function getArchiveIds($siteIds, $periods, $segment, $plugins, $includeInvalidated = true, $_skipSetGroupConcatMaxLen = false)
    {
        $logger = StaticContainer::get(LoggerInterface::class);
        if (!$_skipSetGroupConcatMaxLen) {
            try {
                Db::get()->query('SET SESSION group_concat_max_len=' . (128 * 1024));
            } catch (\Exception $ex) {
                $logger->info("Could not set group_concat_max_len MySQL session variable.");
            }
        }

        if (empty($siteIds)) {
            throw new \Exception("Website IDs could not be read from the request, ie. idSite=");
        }

        foreach ($siteIds as $index => $siteId) {
            $siteIds[$index] = (int) $siteId;
        }

        $getArchiveIdsSql = "SELECT idsite, date1, date2,
                                    GROUP_CONCAT(CONCAT(idarchive,'|',`name`,'|',`value`) ORDER BY idarchive DESC SEPARATOR ',') AS archives
                               FROM %s
                              WHERE idsite IN (" . implode(',', $siteIds) . ")
                                AND " . self::getNameCondition($plugins, $segment, $includeInvalidated) . "
                                AND ts_archived IS NOT NULL
                                AND %s
                           GROUP BY idsite, date1, date2";

        $monthToPeriods = array();
        foreach ($periods as $period) {
            /** @var Period $period */
            if ($period->getDateStart()->isLater(Date::now()->addDay(2))) {
                continue; // avoid creating any archive tables in the future
            }
            $table = ArchiveTableCreator::getNumericTable($period->getDateStart());
            $monthToPeriods[$table][] = $period;
        }

        $db = Db::get();

        // for every month within the archive query, select from numeric table
        $result = array();
        foreach ($monthToPeriods as $table => $periods) {
            $firstPeriod = reset($periods);

            $bind = array();

            if ($firstPeriod instanceof Range) {
                $dateCondition = "date1 = ? AND date2 = ?";
                $bind[] = $firstPeriod->getDateStart()->toString('Y-m-d');
                $bind[] = $firstPeriod->getDateEnd()->toString('Y-m-d');
            } else {
                // we assume there is no range date in $periods
                $dateCondition = '(';

                foreach ($periods as $period) {
                    if (strlen($dateCondition) > 1) {
                        $dateCondition .= ' OR ';
                    }

                    $dateCondition .= "(period = ? AND date1 = ? AND date2 = ?)";
                    $bind[] = $period->getId();
                    $bind[] = $period->getDateStart()->toString('Y-m-d');
                    $bind[] = $period->getDateEnd()->toString('Y-m-d');
                }

                $dateCondition .= ')';
            }

            $sql = sprintf($getArchiveIdsSql, $table, $dateCondition);
            $archiveIds = $db->fetchAll($sql, $bind);

            // get the archive IDs. we keep all archives until the first all plugins archive.
            // everything older than that one is discarded.
            foreach ($archiveIds as $row) {
                $dateStr = $row['date1'] . ',' . $row['date2'];

                $archives = $row['archives'];
                $pairs = explode(',', $archives);
                foreach ($pairs as $pair) {
                    $parts = explode('|', $pair);
                    if (count($parts) != 3) { // GROUP_CONCAT got cut off, have to ignore the rest
                        // note: in this edge case, we end up not selecting the all plugins archive because it will be older than the partials.
                        // not ideal, but it avoids an exception.
                        $logger->info("GROUP_CONCAT got cut off in ArchiveSelector." . __FUNCTION__ . ' for idsite = ' . $row['idsite'] . ', period = ' . $dateStr);
                        continue;
                    }

                    list($idarchive, $doneFlag, $value) = $parts;

                    $result[$doneFlag][$dateStr][] = $idarchive;
                    if (strpos($doneFlag, '.') === false // all plugins archive
                        // sanity check: DONE_PARTIAL shouldn't be used w/ done archives, but in case we see one,
                        // don't treat it like an all plugins archive
                        && $value != ArchiveWriter::DONE_PARTIAL
                    ) {
                        break; // found the all plugins archive, don't need to look in older archives since we have everything here
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Queries and returns archive data using a set of archive IDs.
     *
     * @param array $archiveIds The IDs of the archives to get data from.
     * @param array $recordNames The names of the data to retrieve (ie, nb_visits, nb_actions, etc.).
     *                           Note: You CANNOT pass multiple recordnames if $loadAllSubtables=true.
     * @param string $archiveDataType The archive data type (either, 'blob' or 'numeric').
     * @param int|null|string $idSubtable  null if the root blob should be loaded, an integer if a subtable should be
     *                                     loaded and 'all' if all subtables should be loaded.
     * @return array
     *@throws Exception
     */
    public static function getArchiveData($archiveIds, $recordNames, $archiveDataType, $idSubtable)
    {
        $chunk = new Chunk();
        $db = Db::get();

        $loadAllSubtables = $idSubtable === Archive::ID_SUBTABLE_LOAD_ALL_SUBTABLES;
        [$getValuesSql, $bind] = self::getSqlTemplateToFetchArchiveData($recordNames, $idSubtable);

        $archiveIdsPerMonth = self::getArchiveIdsByYearMonth($archiveIds);

        // get data from every table we're querying
        $rows = array();
        foreach ($archiveIdsPerMonth as $yearMonth => $ids) {
            if (empty($ids)) {
                throw new Exception("Unexpected: id archive not found for period '$yearMonth' '");
            }

            // $yearMonth = "2022-11",
            $date = Date::factory($yearMonth . '-01');

            $isNumeric = $archiveDataType === 'numeric';
            if ($isNumeric) {
                $table = ArchiveTableCreator::getNumericTable($date);
            } else {
                $table = ArchiveTableCreator::getBlobTable($date);
            }

            $ids      = array_map('intval', $ids);
            $sql      = sprintf($getValuesSql, $table, implode(',', $ids));
            $dataRows = $db->fetchAll($sql, $bind);

            foreach ($dataRows as $row) {
                if ($isNumeric) {
                    $rows[] = $row;
                } else {
                    $row['value'] = self::uncompress($row['value']);

                    if ($chunk->isRecordNameAChunk($row['name'])) {
                        self::moveChunkRowToRows($rows, $row, $chunk, $loadAllSubtables, $idSubtable);
                    } else {
                        $rows[] = $row;
                    }
                }
            }
        }

        return $rows;
    }

    private static function moveChunkRowToRows(&$rows, $row, Chunk $chunk, $loadAllSubtables, $idSubtable)
    {
        // $blobs = array([subtableID] = [blob of subtableId])
        $blobs = Common::safe_unserialize($row['value']);

        if (!is_array($blobs)) {
            return;
        }

        // $rawName = eg 'PluginName_ArchiveName'
        $rawName = $chunk->getRecordNameWithoutChunkAppendix($row['name']);

        if ($loadAllSubtables) {
            foreach ($blobs as $subtableId => $blob) {
                $row['value'] = $blob;
                $row['name']  = self::appendIdSubtable($rawName, $subtableId);
                $rows[] = $row;
            }
        } elseif (array_key_exists($idSubtable, $blobs)) {
            $row['value'] = $blobs[$idSubtable];
            $row['name'] = self::appendIdSubtable($rawName, $idSubtable);
            $rows[] = $row;
        }
    }

    public static function appendIdSubtable($recordName, $id)
    {
        return $recordName . "_" . $id;
    }

    public static function uncompress($data)
    {
        return @gzuncompress($data);
    }

    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     *
     * @param array $plugins
     * @param Segment $segment
     * @param bool $includeInvalidated
     * @return string
     */
    private static function getNameCondition(array $plugins, Segment $segment, $includeInvalidated = true)
    {
        // the flags used to tell how the archiving process for a specific archive was completed,
        // if it was completed
        $doneFlags    = Rules::getDoneFlags($plugins, $segment);
        $allDoneFlags = "'" . implode("','", $doneFlags) . "'";

        $possibleValues = Rules::getSelectableDoneFlagValues($includeInvalidated, null, $checkAuthorizedToArchive = false);

        // create the SQL to find archives that are DONE
        return "((name IN ($allDoneFlags)) AND (value IN (" . implode(',', $possibleValues) . ")))";
    }

    /**
     * This method takes the output of Model::getArchiveIdAndVisits() and selects data from the
     * latest archives.
     *
     * This includes:
     * - the idarchive with the latest ts_archived ($results will be ordered by ts_archived desc)
     * - the visits/converted visits of the latest archive, which includes archives for VisitsSummary alone
     *   ($requestedPluginDoneFlags will have the done flag for the overall archive plus a done flag for
     *   VisitsSummary by itself)
     * - the ts_archived for the latest idarchive
     * - the doneFlag value for the latest archive
     *
     * @param $results
     * @param $doneFlags
     * @return array
     */
    private static function findArchiveDataWithLatestTsArchived($results, $requestedPluginDoneFlags, $allPluginsDoneFlag)
    {
        $doneFlags = array_merge($requestedPluginDoneFlags, [$allPluginsDoneFlag]);

        // find latest idarchive for each done flag
        $idArchives = [];
        $tsArchiveds = [];
        foreach ($results as $row) {
            $doneFlag = $row['name'];
            if (!isset($idArchives[$doneFlag])) {
                $idArchives[$doneFlag] = $row['idarchive'];
                $tsArchiveds[$doneFlag] = $row['ts_archived'];
            }
        }

        $archiveData = [
            self::NB_VISITS_RECORD_LOOKED_UP => false,
            self::NB_VISITS_CONVERTED_RECORD_LOOKED_UP => false,
        ];

        foreach ($results as $result) {
            if (in_array($result['name'], $doneFlags)
                && in_array($result['idarchive'], $idArchives)
                && $result['value'] != ArchiveWriter::DONE_PARTIAL
            ) {
                $archiveData = $result;
                if (empty($archiveData[self::NB_VISITS_RECORD_LOOKED_UP])) {
                    $archiveData[self::NB_VISITS_RECORD_LOOKED_UP] = 0;
                }
                if (empty($archiveData[self::NB_VISITS_CONVERTED_RECORD_LOOKED_UP])) {
                    $archiveData[self::NB_VISITS_CONVERTED_RECORD_LOOKED_UP] = 0;
                }
                break;
            }
        }

        foreach ([self::NB_VISITS_RECORD_LOOKED_UP, self::NB_VISITS_CONVERTED_RECORD_LOOKED_UP] as $metric) {
            foreach ($results as $result) {
                if (!in_array($result['idarchive'], $idArchives)) {
                    continue;
                }

                if (empty($archiveData[$metric])) {
                    if (!empty($result[$metric]) || $result[$metric] === 0 || $result[$metric] === '0') {
                        $archiveData[$metric] = $result[$metric];
                    }
                }
            }
        }

        // add partial archives
        $mainTsArchived = isset($tsArchiveds[$allPluginsDoneFlag]) ? $tsArchiveds[$allPluginsDoneFlag] : null;
        foreach ($results as $row) {
            if (!isset($idArchives[$row['name']])) {
                continue;
            }

            $thisTsArchived = Date::factory($row['ts_archived']);
            if ($row['value'] == ArchiveWriter::DONE_PARTIAL
                && (empty($mainTsArchived) || !Date::factory($mainTsArchived)->isLater($thisTsArchived))
            ) {
                $archiveData['partial'][] = $row['idarchive'];

                if (empty($archiveData['ts_archived'])) {
                    $archiveData['ts_archived'] = $row['ts_archived'];
                }
            }
        }

        return $archiveData;
    }

    public static function querySingleBlob(array $archiveIds, string $recordName)
    {
        $chunk = new Chunk();

        [$getValuesSql, $bind] = self::getSqlTemplateToFetchArchiveData(
            [$recordName], Archive::ID_SUBTABLE_LOAD_ALL_SUBTABLES, true);

        $archiveIdsPerMonth = self::getArchiveIdsByYearMonth($archiveIds);

        $periodsSeen = [];

        // $yearMonth = "2022-11",
        foreach ($archiveIdsPerMonth as $yearMonth => $ids) {
            $date = Date::factory($yearMonth . '-01');

            $table = ArchiveTableCreator::getBlobTable($date);

            $ids      = array_map('intval', $ids);
            $sql      = sprintf($getValuesSql, $table, implode(',', $ids));

            $cursor = Db::get()->query($sql, $bind);
            while ($row = $cursor->fetch()) {
                $period = $row['date1'] . ',' . $row['date2'];
                $recordName = $row['name'];

                // FIXME: This hack works around a strange bug that occurs when getting
                //         archive IDs through ArchiveProcessing instances. When a table
                //         does not already exist, for some reason the archive ID for
                //         today (or from two days ago) will be added to the Archive
                //         instances list. The Archive instance will then select data
                //         for periods outside of the requested set.
                //         working around the bug here, but ideally, we need to figure
                //         out why incorrect idarchives are being selected.
                if (empty($archiveIds[$period])) {
                    continue;
                }

                // only use the first period/blob name combination seen (since we order by ts_archived descending)
                if (!empty($periodsSeen[$period][$recordName])) {
                    continue;
                }

                $periodsSeen[$period][$recordName] = true;

                $row['value'] = ArchiveSelector::uncompress($row['value']);
                if ($chunk->isRecordNameAChunk($row['name'])) {
                    // $blobs = array([subtableID] = [blob of subtableId])
                    $blobs = Common::safe_unserialize($row['value']);
                    if (!is_array($blobs)) {
                        yield $row;
                    }

                    ksort($blobs);

                    // $rawName = eg 'PluginName_ArchiveName'
                    $rawName = $chunk->getRecordNameWithoutChunkAppendix($row['name']);
                    foreach ($blobs as $subtableId => $blob) {
                        yield array_merge($row, [
                            'value' => $blob,
                            'name' => ArchiveSelector::appendIdSubtable($rawName, $subtableId),
                        ]);
                    }
                } else {
                    yield $row;
                }
            }
        }
    }

    /**
     * Returns SQL to fetch data from an archive table. The SQL has two %s placeholders, one for the
     * archive table name and another for the comma separated list of archive IDs to look for.
     *
     * @param array $recordNames The list of records to look for.
     * @param string|int $idSubtable The idSubtable to look for or 'all' to load all of them.
     * @param boolean $orderBySubtableId If true, orders the result set by start date ascending, subtable ID
     *                                   ascending and ts_archived descending. Only applied if loading all
     *                                   subtables for a single record.
     *
     *                                   This parameter is used when aggregating blob data for a single record
     *                                   without loading entire datatable trees in memory.
     * @return array The sql and bind values.
     */
    private static function getSqlTemplateToFetchArchiveData(array $recordNames, $idSubtable, $orderBySubtableId = false)
    {
        $chunk = new Chunk();

        $orderBy = 'ORDER BY ts_archived ASC';

        // create the SQL to select archive data
        $loadAllSubtables = $idSubtable === Archive::ID_SUBTABLE_LOAD_ALL_SUBTABLES;
        if ($loadAllSubtables) {
            $name = reset($recordNames);

            // select blobs w/ name like "$name_[0-9]+" w/o using RLIKE
            $nameEnd = strlen($name) + 1;
            $nameEndAppendix = $nameEnd + 1;
            $appendix = $chunk->getAppendix();
            $lenAppendix = strlen($appendix);

            $checkForChunkBlob  = "SUBSTRING(name, $nameEnd, $lenAppendix) = '$appendix'";
            $checkForSubtableId = "(SUBSTRING(name, $nameEndAppendix, 1) >= '0'
                                    AND SUBSTRING(name, $nameEndAppendix, 1) <= '9')";

            $whereNameIs = "(name = ? OR (name LIKE ? AND ( $checkForChunkBlob OR $checkForSubtableId ) ))";
            $bind = array($name, $name . '%');

            if ($orderBySubtableId && count($recordNames) == 1) {
                $idSubtableAsInt = self::getExtractIdSubtableFromBlobNameSql($chunk, $name);

                $orderBy = "ORDER BY date1 ASC, " . // ordering by date just so column order in tests will be predictable
                    " $idSubtableAsInt ASC,
                  ts_archived DESC"; // ascending order so we use the latest data found
            }
        } else {
            if ($idSubtable === null) {
                // select root table or specific record names
                $bind = array_values($recordNames);
            } else {
                // select a subtable id
                $bind = array();
                foreach ($recordNames as $recordName) {
                    // to be backwards compatible we need to look for the exact idSubtable blob and for the chunk
                    // that stores the subtables (a chunk stores many blobs in one blob)
                    $bind[] = $chunk->getRecordNameForTableId($recordName, $idSubtable);
                    $bind[] = self::appendIdSubtable($recordName, $idSubtable);
                }
            }

            $inNames     = Common::getSqlStringFieldsArray($bind);
            $whereNameIs = "name IN ($inNames)";
        }

        $getValuesSql = "SELECT value, name, idsite, date1, date2, ts_archived
                                FROM %s
                                WHERE idarchive IN (%s)
                                  AND " . $whereNameIs . "
                             $orderBy"; // ascending order so we use the latest data found

        return [$getValuesSql, $bind];
    }

    private static function getArchiveIdsByYearMonth(array $archiveIds)
    {
        // We want to fetch as many archives at once as possible instead of fetching each period individually
        // eg instead of issueing one query per day we'll merge all the IDs of a given month into one query
        // we group by YYYY-MM as we have one archive table per month
        $archiveIdsPerMonth = [];
        foreach ($archiveIds as $period => $ids) {
            $yearMonth = substr($period, 0, 7); // eg 2022-11
            if (empty($archiveIdsPerMonth[$yearMonth])) {
                $archiveIdsPerMonth[$yearMonth] = [];
            }
            $archiveIdsPerMonth[$yearMonth] = array_merge($archiveIdsPerMonth[$yearMonth], $ids);
        }
        return $archiveIdsPerMonth;
    }

    // public for tests
    public static function getExtractIdSubtableFromBlobNameSql(Chunk $chunk, $name)
    {
        // select blobs w/ name like "$name_[0-9]+" w/o using RLIKE
        $nameEnd = strlen($name) + 1;
        $nameEndAfterUnderscore = $nameEnd + 1;
        $appendix = $chunk->getAppendix();
        $lenAppendix = strlen($appendix);
        $chunkEnd = $nameEnd + $lenAppendix;

        $checkForChunkBlob  = "SUBSTRING(name, $nameEnd, $lenAppendix) = '$appendix'";

        $extractSuffix = "SUBSTRING(name, IF($checkForChunkBlob, $chunkEnd, $nameEndAfterUnderscore))";
        $locateSecondUnderscore = "IF((@secondunderscore := LOCATE('_', $extractSuffix) - 1) < 0, LENGTH(name), @secondunderscore)";
        $extractIdSubtableStart = "IF( (@idsubtable := SUBSTRING($extractSuffix, 1, $locateSecondUnderscore)) = '', -1, @idsubtable )";
        $idSubtableAsInt = "CAST($extractIdSubtableStart AS SIGNED)";

        return $idSubtableAsInt;
    }
}
