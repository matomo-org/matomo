<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

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
class Piwik_DataAccess_ArchiveSelector
{
    const NB_VISITS_RECORD_LOOKED_UP = "nb_visits";

    const NB_VISITS_CONVERTED_RECORD_LOOKED_UP = "nb_visits_converted";

    static public function getArchiveIdAndVisits(Piwik_Site $site, Piwik_Period $period, Piwik_Segment $segment, $minDatetimeArchiveProcessedUTC, $requestedPlugin)
    {
        $dateStart = $period->getDateStart();
        $bindSQL = array($site->getId(),
                         $dateStart->toString('Y-m-d'),
                         $period->getDateEnd()->toString('Y-m-d'),
                         $period->getId(),
        );

        $timeStampWhere = '';
        if ($minDatetimeArchiveProcessedUTC) {
            $timeStampWhere = " AND ts_archived >= ? ";
            $bindSQL[] = Piwik_Date::factory($minDatetimeArchiveProcessedUTC)->getDatetime();
        }

        $pluginOrVisitsSummary = array("VisitsSummary", $requestedPlugin);
        $pluginOrVisitsSummary = array_unique($pluginOrVisitsSummary);
        $sqlWhereArchiveName = self::getNameCondition($pluginOrVisitsSummary, $segment);

        $sqlQuery = "	SELECT idarchive, value, name, date1 as startDate
						FROM " . Piwik_DataAccess_ArchiveTableCreator::getNumericTable($dateStart) . "``
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ( ($sqlWhereArchiveName)
								  OR name = '" . self::NB_VISITS_RECORD_LOOKED_UP . "'
								  OR name = '" . self::NB_VISITS_CONVERTED_RECORD_LOOKED_UP . "')
							$timeStampWhere
						ORDER BY idarchive DESC";
        $results = Piwik_FetchAll($sqlQuery, $bindSQL);
        if (empty($results)) {
            return false;
        }

        $idArchive = self::getMostRecentIdArchiveFromResults($segment, $requestedPlugin, $results);
        $idArchiveVisitsSummary = self::getMostRecentIdArchiveFromResults($segment, "VisitsSummary", $results);

        list($visits, $visitsConverted) = self::getVisitsMetricsFromResults($idArchive, $idArchiveVisitsSummary, $results);

        if ($visits === false
            && $idArchive === false
        ) {
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

    protected static function getMostRecentIdArchiveFromResults(Piwik_Segment $segment, $requestedPlugin, $results)
    {
        $idArchive = false;
        $namesRequestedPlugin = Piwik_ArchiveProcessor_Rules::getDoneFlags(array($requestedPlugin), $segment);
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
     * @param Piwik_Segment $segment
     * @param array $plugins List of plugin names for which data is being requested.
     * @return array Archive IDs are grouped by archive name and period range, ie,
     *               array(
     *                   'VisitsSummary.done' => array(
     *                       '2010-01-01' => array(1,2,3)
     *                   )
     *               )
     */
    static public function getArchiveIds($siteIds, $periods, $segment, $plugins)
    {
        $getArchiveIdsSql = "SELECT idsite, name, date1, date2, MAX(idarchive) as idarchive
                               FROM %s
                              WHERE period = ?
                                AND %s
                                AND " . self::getNameCondition($plugins, $segment) . "
                                AND idsite IN (" . implode(',', $siteIds) . ")
                           GROUP BY idsite, date1, date2";

        $monthToPeriods = array();
        foreach ($periods as $period) {
            /** @var Piwik_Period $period */
            $table = Piwik_DataAccess_ArchiveTableCreator::getNumericTable($period->getDateStart());
            $monthToPeriods[$table][] = $period;
        }

        // for every month within the archive query, select from numeric table
        $result = array();
        foreach ($monthToPeriods as $table => $periods) {
            $firstPeriod = reset($periods);

            // if looking for a range archive. NOTE: we assume there's only one period if its a range.
            $bind = array($firstPeriod->getId());
            if ($firstPeriod instanceof Piwik_Period_Range) {
                $dateCondition = "date1 = ? AND date2 = ?";
                $bind[] = $firstPeriod->getDateStart()->toString('Y-m-d');
                $bind[] = $firstPeriod->getDateEnd()->toString('Y-m-d');
            } else { // if looking for a normal period
                $dateStrs = array();
                foreach ($periods as $period) {
                    $dateStrs[] = $period->getDateStart()->toString('Y-m-d');
                }

                $dateCondition = "date1 IN ('" . implode("','", $dateStrs) . "')";
            }

            $sql = sprintf($getArchiveIdsSql, $table, $dateCondition);

            // get the archive IDs
            foreach (Piwik_FetchAll($sql, $bind) as $row) {
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
    static public function getArchiveData($archiveIds, $recordNames, $archiveDataType, $loadAllSubtables)
    {
        // create the SQL to select archive data
        $inNames = Piwik_Common::getSqlStringFieldsArray($recordNames);
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
            $date = Piwik_Date::factory(substr($period, 0, 10));
            if ($archiveDataType == 'numeric') {
                $table = Piwik_DataAccess_ArchiveTableCreator::getNumericTable($date);
            } else {
                $table = Piwik_DataAccess_ArchiveTableCreator::getBlobTable($date);
            }
            $sql = sprintf($getValuesSql, $table, implode(',', $ids));
            $dataRows = Piwik_FetchAll($sql, $bind);
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
     * @param Piwik_Segment $segment
     * @return string
     */
    static private function getNameCondition(array $plugins, $segment)
    {
        // the flags used to tell how the archiving process for a specific archive was completed,
        // if it was completed
        $doneFlags = Piwik_ArchiveProcessor_Rules::getDoneFlags($plugins, $segment);

        $allDoneFlags = "'" . implode("','", $doneFlags) . "'";

        // create the SQL to find archives that are DONE
        return "(name IN ($allDoneFlags)) AND " .
            " (value = '" . Piwik_ArchiveProcessor::DONE_OK . "' OR " .
            " value = '" . Piwik_ArchiveProcessor::DONE_OK_TEMPORARY . "')";
    }

    static public function purgeOutdatedArchives(Piwik_Date $dateStart)
    {

        $purgeArchivesOlderThan = Piwik_ArchiveProcessor_Rules::shouldPurgeOutdatedArchives($dateStart);
        if (!$purgeArchivesOlderThan) {
            return;
        }

        $idArchivesToDelete = self::getTemporaryArchiveIdsOlderThan($dateStart, $purgeArchivesOlderThan);
        if (!empty($idArchivesToDelete)) {
            self::deleteArchiveIds($dateStart, $idArchivesToDelete);
        }
        self::deleteArchivesWithPeriodRange($dateStart);

        Piwik::log("Purging temporary archives: done [ purged archives older than $purgeArchivesOlderThan in "
            . $dateStart->toString("Y-m") ." ] [Deleted IDs: " . implode (',', $idArchivesToDelete) . "]");
    }

    /*
     * Deleting "Custom Date Range" reports after 1 day, since they can be re-processed and would take up un-necessary space
     */
    protected static function deleteArchivesWithPeriodRange(Piwik_Date $date)
    {
        $query = "DELETE FROM %s WHERE period = ? AND ts_archived < ?";

        $yesterday = Piwik_Date::factory('yesterday')->getDateTime();
        $bind = array(Piwik::$idPeriods['range'], $yesterday);
        $numericTable = Piwik_DataAccess_ArchiveTableCreator::getNumericTable($date);
        Piwik_Query(sprintf($query, $numericTable), $bind);
        Piwik::log("Purging Custom Range archives: done [ purged archives older than $yesterday from $numericTable / blob ]");
        try {
            Piwik_Query(sprintf($query, Piwik_DataAccess_ArchiveTableCreator::getBlobTable($date)), $bind);
        } catch (Exception $e) {
            // Individual blob tables could be missing
        }
    }

    protected static function deleteArchiveIds(Piwik_Date $date, $idArchivesToDelete)
    {
        $query = "DELETE FROM %s WHERE idarchive IN (" . implode(',', $idArchivesToDelete) . ")";

        Piwik_Query(sprintf($query, Piwik_DataAccess_ArchiveTableCreator::getNumericTable($date)));
        try {
            Piwik_Query(sprintf($query, Piwik_DataAccess_ArchiveTableCreator::getBlobTable($date)));
        } catch (Exception $e) {
            // Individual blob tables could be missing
        }
    }

    protected static function getTemporaryArchiveIdsOlderThan(Piwik_Date $date, $purgeArchivesOlderThan)
    {
        $query = "SELECT idarchive
                FROM ". Piwik_DataAccess_ArchiveTableCreator::getNumericTable($date) ."
                WHERE name LIKE 'done%'
                    AND ((  value = " . Piwik_ArchiveProcessor::DONE_OK_TEMPORARY . "
                            AND ts_archived < ?)
                         OR value = " . Piwik_ArchiveProcessor::DONE_ERROR . ")";

        $result = Piwik_FetchAll($query, array($purgeArchivesOlderThan));
        $idArchivesToDelete = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $idArchivesToDelete[] = $row['idarchive'];
            }
        }
        return $idArchivesToDelete;
    }
}
