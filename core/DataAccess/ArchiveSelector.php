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
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Segment;

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

    public static function getArchiveIdAndVisits(ArchiveProcessor\Parameters $params, $minDatetimeArchiveProcessedUTC)
    {
        $idSite       = $params->getSite()->getId();
        $period       = $params->getPeriod()->getId();
        $dateStart    = $params->getPeriod()->getDateStart();
        $dateStartIso = $dateStart->toString('Y-m-d');
        $dateEndIso   = $params->getPeriod()->getDateEnd()->toString('Y-m-d');

        $numericTable = ArchiveTableCreator::getNumericTable($dateStart);

        $minDatetimeIsoArchiveProcessedUTC = null;
        if ($minDatetimeArchiveProcessedUTC) {
            $minDatetimeIsoArchiveProcessedUTC = Date::factory($minDatetimeArchiveProcessedUTC)->getDatetime();
        }

        $requestedPlugin = $params->getRequestedPlugin();
        $segment         = $params->getSegment();
        $isSkipAggregationOfSubTables = $params->isSkipAggregationOfSubTables();
        $plugins = array("VisitsSummary", $requestedPlugin);

        $doneFlags      = self::getDoneFlags($plugins, $segment, $isSkipAggregationOfSubTables);
        $possibleValues = self::getPossibleValues();

        $results = self::getModel()->getArchiveIdAndVisits($numericTable, $idSite, $period, $dateStartIso, $dateEndIso, $minDatetimeIsoArchiveProcessedUTC, $doneFlags, $possibleValues);

        if (empty($results)) {
            return false;
        }

        $idArchive = self::getMostRecentIdArchiveFromResults($segment, $requestedPlugin, $isSkipAggregationOfSubTables, $results);
        $idArchiveVisitsSummary = self::getMostRecentIdArchiveFromResults($segment, "VisitsSummary", $isSkipAggregationOfSubTables, $results);

        list($visits, $visitsConverted) = self::getVisitsMetricsFromResults($idArchive, $idArchiveVisitsSummary, $results);

        if (false === $visits && false === $idArchive) {

            return false;
        }

        return array($idArchive, $visits, $visitsConverted);
    }

    protected static function getVisitsMetricsFromResults($idArchive, $idArchiveVisitsSummary, $results)
    {
        $visits = $visitsConverted = false;
        $archiveWithVisitsMetricsWasFound = ($idArchiveVisitsSummary !== false);

        if ($archiveWithVisitsMetricsWasFound) {
            $visits = $visitsConverted = 0;
        }

        foreach ($results as $result) {
            if (in_array($result['idarchive'], array($idArchive, $idArchiveVisitsSummary))) {
                $value = (int)$result['value'];
                if (empty($visits)
                    && $result['name'] == self::NB_VISITS_RECORD_LOOKED_UP
                ) {
                    $visits = $value;
                }
                if (empty($visitsConverted)
                    && $result['name'] == self::NB_VISITS_CONVERTED_RECORD_LOOKED_UP
                ) {
                    $visitsConverted = $value;
                }
            }
        }

        return array($visits, $visitsConverted);
    }

    protected static function getMostRecentIdArchiveFromResults(Segment $segment, $requestedPlugin, $isSkipAggregationOfSubTables, $results)
    {
        $idArchive = false;
        $namesRequestedPlugin = Rules::getDoneFlags(array($requestedPlugin), $segment, $isSkipAggregationOfSubTables);

        foreach ($results as $result) {
            if ($idArchive === false
                && in_array($result['name'], $namesRequestedPlugin)
            ) {
                $idArchive = $result['idarchive'];
                break;
            }
        }

        return $idArchive;
    }

    /**
     * Queries and returns archive IDs for a set of sites, periods, and a segment.
     *
     * @param array $siteIds
     * @param array $periods
     * @param Segment $segment
     * @param array $plugins List of plugin names for which data is being requested.
     * @param bool $isSkipAggregationOfSubTables Whether we are selecting an archive that may be partial (no sub-tables)
     * @return array Archive IDs are grouped by archive name and period range, ie,
     *               array(
     *                   'VisitsSummary.done' => array(
     *                       '2010-01-01' => array(1,2,3)
     *                   )
     *               )
     * @throws
     */
    public static function getArchiveIds($siteIds, $periods, $segment, $plugins, $isSkipAggregationOfSubTables = false)
    {
        if (empty($siteIds)) {
            throw new \Exception("Website IDs could not be read from the request, ie. idSite=");
        }

        $getArchiveIdsSql = "SELECT idsite, name, date1, date2, MAX(idarchive) as idarchive
                               FROM %s
                              WHERE %s
                                AND " . self::getNameCondition($plugins, $segment, $isSkipAggregationOfSubTables) . "
                                AND idsite IN (" . implode(',', $siteIds) . ")
                           GROUP BY idsite, date1, date2";

        $monthToPeriods = array();
        foreach ($periods as $period) {
            /** @var Period $period */
            $table = ArchiveTableCreator::getNumericTable($period->getDateStart());
            $monthToPeriods[$table][] = $period;
        }

        // for every month within the archive query, select from numeric table
        $result = array();
        foreach ($monthToPeriods as $table => $periods) {
            $firstPeriod = reset($periods);

            $bind = array();

            if ($firstPeriod instanceof Range) {
                $dateCondition = "period = ? AND date1 = ? AND date2 = ?";
                $bind[] = $firstPeriod->getId();
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

            $archiveIds = Db::fetchAll($sql, $bind);

            // get the archive IDs
            foreach ($archiveIds as $row) {
                $archiveName = $row['name'];

                //FIXMEA duplicate with Archive.php
                $dateStr = $row['date1'] . "," . $row['date2'];

                $result[$archiveName][$dateStr][] = $row['idarchive'];
            }
        }

        return $result;
    }

    /**
     * Queries and returns archive data using a set of archive IDs.
     *
     * @param array $archiveIds The IDs of the archives to get data from.
     * @param array $recordNames The names of the data to retrieve (ie, nb_visits, nb_actions, etc.)
     * @param string $archiveDataType The archive data type (either, 'blob' or 'numeric').
     * @param bool $loadAllSubtables Whether to pre-load all subtables
     * @throws Exception
     * @return array
     */
    public static function getArchiveData($archiveIds, $recordNames, $archiveDataType, $loadAllSubtables)
    {
        // create the SQL to select archive data
        $inNames = Common::getSqlStringFieldsArray($recordNames);
        if ($loadAllSubtables) {
            $name = reset($recordNames);

            // select blobs w/ name like "$name_[0-9]+" w/o using RLIKE
            $nameEnd = strlen($name) + 2;
            $whereNameIs = "(name = ?
                            OR (name LIKE ?
                                 AND SUBSTRING(name, $nameEnd, 1) >= '0'
                                 AND SUBSTRING(name, $nameEnd, 1) <= '9') )";
            $bind = array($name, $name . '%');
        } else {
            $whereNameIs = "name IN ($inNames)";
            $bind = array_values($recordNames);
        }

        $getValuesSql = "SELECT value, name, idsite, date1, date2, ts_archived
                                FROM %s
                                WHERE idarchive IN (%s)
                                  AND " . $whereNameIs;

        // get data from every table we're querying
        $rows = array();
        foreach ($archiveIds as $period => $ids) {

            if (empty($ids)) {
                throw new Exception("Unexpected: id archive not found for period '$period' '");
            }

            // $period = "2009-01-04,2009-01-04",
            $date = Date::factory(substr($period, 0, 10));

            if ($archiveDataType == 'numeric') {
                $table = ArchiveTableCreator::getNumericTable($date);
            } else {
                $table = ArchiveTableCreator::getBlobTable($date);
            }

            $sql      = sprintf($getValuesSql, $table, implode(',', $ids));
            $dataRows = Db::fetchAll($sql, $bind);

            foreach ($dataRows as $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     *
     * @param array $plugins
     * @param Segment $segment
     * @param bool $isSkipAggregationOfSubTables
     * @return string
     */
    private static function getNameCondition(array $plugins, Segment $segment, $isSkipAggregationOfSubTables)
    {
        // the flags used to tell how the archiving process for a specific archive was completed,
        // if it was completed
        $doneFlags    = self::getDoneFlags($plugins, $segment, $isSkipAggregationOfSubTables);
        $allDoneFlags = "'" . implode("','", $doneFlags) . "'";

        $possibleValues = self::getPossibleValues();

        // create the SQL to find archives that are DONE
        return "((name IN ($allDoneFlags)) AND (value IN (" . implode(',', $possibleValues) . ")))";
    }

    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     *
     * @param array $plugins
     * @param Segment $segment
     * @param bool $isSkipAggregationOfSubTables
     * @return string
     */
    private static function getDoneFlags(array $plugins, Segment $segment, $isSkipAggregationOfSubTables)
    {
        // the flags used to tell how the archiving process for a specific archive was completed,
        // if it was completed
        $doneFlags = Rules::getDoneFlags($plugins, $segment, $isSkipAggregationOfSubTables);

        return $doneFlags;
    }

    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     *
     * @return string
     */
    private static function getPossibleValues()
    {
        $possibleValues = array(ArchiveWriter::DONE_OK, ArchiveWriter::DONE_OK_TEMPORARY);

        if (!Rules::isRequestAuthorizedToArchive()) {
            //If request is not authorized to archive then fetch also invalidated archives
            $possibleValues[] = ArchiveWriter::DONE_INVALIDATED;
        }

        return $possibleValues;
    }

}
