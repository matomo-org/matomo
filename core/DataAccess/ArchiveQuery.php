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
 * Data Access object used to query archive data.
 */
class Piwik_DataAccess_ArchiveQuery
{
    /**
     * Queries and returns archive IDs for a set of sites, periods, and a segment.
     * 
     * @param array $siteIds
     * @param array $periods
     * @param Piwik_Segment|null $segment
     * @param array $plugins List of plugin names for which data is being requested.
     * @return array Archive IDs are grouped by archive name and period range, ie,
     *               array(
     *                   'VisitsSummary.done' => array(
     *                       '2010-01-01' => array(1,2,3)
     *                   )
     *               )
     */
    public function getArchiveIds($siteIds, $periods, $segment, $plugins)
    {
        $periodType = reset($periods)->getLabel();
        
        $getArchiveIdsSql = "SELECT idsite, name, date1, date2, MAX(idarchive) as idarchive
                               FROM %s
                              WHERE period = ?
                                AND %s
                                AND ".$this->getNameCondition($plugins, $segment, $periodType)."
                                AND idsite IN (".implode(',', $siteIds).")
                           GROUP BY idsite, date1, date2";
        
        // for every month within the archive query, select from numeric table
        $result = array();
        foreach ($this->getPeriodsByTableMonth($periods) as $tableMonth => $periods) {
            $firstPeriod = reset($periods);
            $table = Piwik_Common::prefixTable("archive_numeric_$tableMonth");
//            echo $periods;
            
            Piwik_TablePartitioning_Monthly::createArchiveTablesIfAbsent($firstPeriod);
            
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
                
                $dateCondition = "date1 IN ('".implode("','", $dateStrs)."')";
            }
            
            $sql = sprintf($getArchiveIdsSql, $table, $dateCondition);
            
            // get the archive IDs
            foreach (Piwik_FetchAll($sql, $bind) as $row) {
                $archiveName = $row['name'];

                //FIXMEA duplicate with Archive.php
                $dateStr = $row['date1'].",".$row['date2'];
                
                $result[$archiveName][$dateStr][] = $row['idarchive'];
            }
        }
        
        return $result;
    }
    
    /**
     * Queries and returns archive data using a set of archive IDs.
     * 
     * @param array $archiveIds The IDs of the archives to get data from.
     * @param array $archiveNames The names of the data to retrieve (ie, nb_visits,
     *                            nb_actions, etc.)
     * @param string $archiveDataType The archive data type (either, 'blob' or 'numeric').
     * @param string|null $idSubtable The subtable to retrieve ('all' for all subtables).
     */
    public function getArchiveData($archiveIds, $archiveNames, $archiveDataType, $idSubtable)
    {
        $archiveTableType = 'archive_'.$archiveDataType;
        
        // create the SQL to select archive data
        $inNames = Piwik_Common::getSqlStringFieldsArray($archiveNames);
        if ($idSubtable == 'all') {
            $name = reset($archiveNames);
            
            // select blobs w/ name like "$name_[0-9]+" w/o using RLIKE
            $nameEnd = strlen($name) + 2;
            $getValuesSql = "SELECT value, name, idsite, date1, date2, ts_archived
                                FROM %s
                                WHERE idarchive IN (%s)
                                  AND (name = ? OR
                                            (name LIKE ? AND SUBSTRING(name, $nameEnd, 1) >= '0'
                                                         AND SUBSTRING(name, $nameEnd, 1) <= '9') )";
            $bind = array($name, $name.'%');
        } else {
            $getValuesSql = "SELECT name, value, idsite, date1, date2, ts_archived
                               FROM %s
                              WHERE idarchive IN (%s)
                                AND name IN ($inNames)";
            $bind = array_values($archiveNames);
        }
        
        // get data from every table we're querying
        $rows = array();
        foreach ($archiveIds as $period => $ids) {
            $tableMonth = $this->getTableMonthFromDateRange($period);
            
            $table = Piwik_Common::prefixTable($archiveTableType."_".$tableMonth);
            $sql = sprintf($getValuesSql, $table, implode(',', $ids));
            
            foreach (Piwik_FetchAll($sql, $bind) as $row) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * Returns the SQL condition used to find successfully completed archives that
     * this instance is querying for.
     * 
     * @param array $plugins @see getArchiveData
     * @param Piwik_Segment $segment
     * @param string $periodType
     * @return string
     */
    private function getNameCondition($plugins, $segment, $periodType)
    {
        // the flags used to tell how the archiving process for a specific archive was completed,
        // if it was completed
        $doneFlags = array();
        foreach ($plugins as $plugin) {
            $done = Piwik_ArchiveProcessing::getDoneStringFlagFor($segment, $periodType, $plugin);
            $donePlugins = Piwik_ArchiveProcessing::getDoneStringFlagFor($segment, $periodType, $plugin, true);
            
            $doneFlags[$done] = $done;
            $doneFlags[$donePlugins] = $donePlugins;
        }

        $allDoneFlags = "'".implode("','", $doneFlags)."'";
        
        // create the SQL to find archives that are DONE
        return "(name IN ($allDoneFlags)) AND
                (value = '".Piwik_ArchiveProcessing::DONE_OK."' OR
                 value = '".Piwik_ArchiveProcessing::DONE_OK_TEMPORARY."')";
    }
    
    /**
     * Returns the periods of the archives this instance is querying for grouped by
     * by year & month.
     * 
     * @return array The result will be an array of Piwik_Period instances, where each
     *               instance is associated w/ a string describing the year and month,
     *               eg, 2012_01. The format is the same format used in archive database
     *               table names.
     */
    private function getPeriodsByTableMonth($periods)
    {
        $result = array();
        foreach ($periods as $period) {
            $tableMonth = $period->getDateStart()->toString('Y_m');
            $result[$tableMonth][] = $period;
        }
        return $result;
    }
    
    /**
     * Returns the table & month that an archive for a specific date range is stored
     * in.
     * 
     * @param string $dateRange eg, "2012-01-01,2012-01-02"
     * @return string eg, "2012_01"
     */
    private function getTableMonthFromDateRange($dateRange)
    {
        return str_replace('-', '_', substr($dateRange, 0, 7));
    }
}
