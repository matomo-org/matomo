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
 * Data Access object used to query archives, create new archives, and insert data for them.
 */
class Piwik_DataAccess_Archiver
{
    public static function deletePreviousArchiveStatus($numericTable, $requestedPlugin, $segment, $period, $idArchive)
    {
        $done = Piwik_ArchiveProcessor_Rules::getDoneStringFlagFor($segment, $period->getLabel(), $requestedPlugin);
        Piwik_Query("DELETE FROM " . $numericTable . "
					WHERE idarchive = ? AND (name = '" . $done . "' OR name LIKE '" . self::PREFIX_SQL_LOCK . "%')",
            array($idArchive)
        );
        return $done;
    }

    /**
     * Generate advisory lock name
     *
     * @param int $idsite
     * @param Piwik_Period $period
     * @param Piwik_Segment $segment
     * @return string
     */
    static protected function getArchiveProcessorLockName($idsite, $period, Piwik_Segment $segment)
    {
        $config = Piwik_Config::getInstance();

        $lockName = 'piwik.'
            . $config->database['dbname'] . '.'
            . $config->database['tables_prefix'] . '/'
            . $idsite . '/'
            . (!$segment->isEmpty() ? $segment->getHash() . '/' : '')
            . $period->getId() . '/'
            . $period->getDateStart()->toString('Y-m-d') . ','
            . $period->getDateEnd()->toString('Y-m-d');
        return $lockName . '/' . md5($lockName . Piwik_Common::getSalt());
    }

    /**
     * Get an advisory lock
     *
     * @param int $idsite
     * @param Piwik_Period $period
     * @param Piwik_Segment $segment
     * @return bool  True if lock acquired; false otherwise
     */
    static public function getArchiveProcessorLock($idsite, $period, $segment)
    {
        $lockName = self::getArchiveProcessorLockName($idsite, $period, $segment);
        return Piwik_GetDbLock($lockName, $maxRetries = 30);
    }

    /**
     * Release an advisory lock
     *
     * @param int $idsite
     * @param Piwik_Period $period
     * @param Piwik_Segment $segment
     * @return bool True if lock released; false otherwise
     */
    static public function releaseArchiveProcessorLock($idsite, $period, $segment)
    {
        $lockName = self::getArchiveProcessorLockName($idsite, $period, $segment);
        return Piwik_ReleaseDbLock($lockName);
    }

    /**
     * A row is created to lock an idarchive for the current archive being processed
     * @var string
     */
    const PREFIX_SQL_LOCK = "locked_";


    public static function allocateNewArchiveId($table, $idSite)
    {
        $dbLockName = "allocateNewArchiveId.$table";

        $db = Zend_Registry::get('db');
        $locked = self::PREFIX_SQL_LOCK . Piwik_Common::generateUniqId();
        $date = date("Y-m-d H:i:s");

        if (Piwik_GetDbLock($dbLockName, $maxRetries = 30) === false) {
            throw new Exception("allocateNewArchiveId: Cannot get named lock for table $table.");
        }
        $db->exec("INSERT INTO $table "
            . " SELECT ifnull(max(idarchive),0)+1,
								'" . $locked . "',
								" . (int)$idSite . ",
								'" . $date . "',
								'" . $date . "',
								0,
								'" . $date . "',
								0 "
            . " FROM $table as tb1");
        Piwik_ReleaseDbLock($dbLockName);
        $id = $db->fetchOne("SELECT idarchive FROM $table WHERE name = ? LIMIT 1", $locked);
        return $id;
    }


    /**
     * @param $numericTableName
     * @param $site
     * @param $period
     * @param $segment
     * @param $minDatetimeArchiveProcessedUTC
     * @param $requestedPlugin
     * @return array|bool
     */
    static public function getArchiveIdAndVisits($numericTableName, Piwik_Site $site, Piwik_Period $period, Piwik_Segment $segment, $minDatetimeArchiveProcessedUTC, $requestedPlugin)
    {
        $bindSQL = array($site->getId(),
                         $period->getDateStart()->toString('Y-m-d'),
                         $period->getDateEnd()->toString('Y-m-d'),
                         $period->getId(),
        );

        $timeStampWhere = '';
        if ($minDatetimeArchiveProcessedUTC) {
            $timeStampWhere = " AND ts_archived >= ? ";
            $bindSQL[] = Piwik_Date::factory($minDatetimeArchiveProcessedUTC)->getDatetime();
        }

        $done = Piwik_ArchiveProcessor_Rules::getDoneFlagArchiveContainsOnePlugin($segment, $requestedPlugin);
        $doneAllPluginsProcessed = Piwik_ArchiveProcessor_Rules::getDoneFlagArchiveContainsAllPlugins($segment);

        $doneFlagSelect = self::getNameCondition(array($requestedPlugin), $segment);
        $sqlQuery = "	SELECT idarchive, value, name, date1 as startDate
						FROM " . $numericTableName . "``
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ( $doneFlagSelect
								  OR name = 'nb_visits')
							$timeStampWhere
						ORDER BY idarchive DESC";
        $results = Piwik_FetchAll($sqlQuery, $bindSQL);
        if (empty($results)) {
            return false;
        }

        $idArchive = false;
        // we look for the more recent idarchive
        foreach ($results as $result) {
            if ( in_array($result['name'], array($done, $doneAllPluginsProcessed)) ) {
                $idArchive = $result['idarchive'];
                break;
            }
        }

        if(!$idArchive) {
            return false;
        }

        $visits = 0;
        foreach($results as $result) {
            if($result['idarchive'] == $idArchive
                && $result['name'] == 'nb_visits') {
                $visits = (int)$result['value'];
                break;
            }
        }
        return array($idArchive, $visits);
    }

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
    static public function getArchiveIds($siteIds, $periods, $segment, $plugins)
    {
        $getArchiveIdsSql = "SELECT idsite, name, date1, date2, MAX(idarchive) as idarchive
                               FROM %s
                              WHERE period = ?
                                AND %s
                                AND ".self::getNameCondition($plugins, $segment)."
                                AND idsite IN (".implode(',', $siteIds).")
                           GROUP BY idsite, date1, date2";
        
        // for every month within the archive query, select from numeric table
        $result = array();
        foreach (self::getPeriodsByTableMonth($periods) as $tableMonth => $periods) {
            $firstPeriod = reset($periods);
            $table = Piwik_Common::prefixTable("archive_numeric_$tableMonth");

            Piwik_TablePartitioning_Monthly::createArchiveTablesIfAbsent($firstPeriod->getDateStart());
            
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
    static public function getArchiveData($archiveIds, $archiveNames, $archiveDataType, $idSubtable)
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
            if(empty($ids)) {
                throw new Exception("Unexpected: id archive not found for period '$period' '");
            }
            // $period = "2009-01-04,2009-01-04",
            $date = substr($period, 0, 10);
            $tableMonth = str_replace('-', '_', substr($date, 0, 7) );

            Piwik_TablePartitioning_Monthly::createArchiveTablesIfAbsent(Piwik_Date::factory($date));

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
     * @return string
     */
    static private function getNameCondition(array $plugins, $segment)
    {
        // the flags used to tell how the archiving process for a specific archive was completed,
        // if it was completed
        $doneFlags = array();
        foreach ($plugins as $plugin) {
            $doneAllPlugins = Piwik_ArchiveProcessor_Rules::getDoneFlagArchiveContainsAllPlugins($segment);
            $doneOnePlugin = Piwik_ArchiveProcessor_Rules::getDoneFlagArchiveContainsOnePlugin($segment, $plugin);
            
            $doneFlags[$doneAllPlugins] = $doneAllPlugins;
            $doneFlags[$doneOnePlugin] = $doneOnePlugin;
        }

        $allDoneFlags = "'".implode("','", $doneFlags)."'";
        
        // create the SQL to find archives that are DONE
        return "(name IN ($allDoneFlags)) AND
                (value = '".Piwik_ArchiveProcessor::DONE_OK."' OR
                 value = '".Piwik_ArchiveProcessor::DONE_OK_TEMPORARY."')";
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
    static private function getPeriodsByTableMonth($periods)
    {
        $result = array();
        foreach ($periods as $period) {
            $tableMonth = $period->getDateStart()->toString('Y_m');
            $result[$tableMonth][] = $period;
        }
        return $result;
    }


    static public function purgeOutdatedArchives($numericTable, $blobTable, $purgeArchivesOlderThan)
    {
        $result = Piwik_FetchAll("
                SELECT idarchive
                FROM $numericTable
                WHERE name LIKE 'done%'
                    AND ((  value = " . Piwik_ArchiveProcessor::DONE_OK_TEMPORARY . "
                            AND ts_archived < ?)
                         OR value = " . Piwik_ArchiveProcessor::DONE_ERROR . ")",
            array($purgeArchivesOlderThan)
        );

        $idArchivesToDelete = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $idArchivesToDelete[] = $row['idarchive'];
            }
            $query = "DELETE
                            FROM %s
                            WHERE idarchive IN (" . implode(',', $idArchivesToDelete) . ")
                            ";

            Piwik_Query(sprintf($query, $numericTable));

            // Individual blob tables could be missing
            try {
                Piwik_Query(sprintf($query, $blobTable));
            } catch (Exception $e) {
            }
        }
        Piwik::log("Purging temporary archives: done [ purged archives older than $purgeArchivesOlderThan from $blobTable and $numericTable ] [Deleted IDs: " . implode(',', $idArchivesToDelete) . "]");

        // Deleting "Custom Date Range" reports after 1 day, since they can be re-processed
        // and would take up unecessary space
        $yesterday = Piwik_Date::factory('yesterday')->getDateTime();
        $query = "DELETE
                        FROM %s
                        WHERE period = ?
                            AND ts_archived < ?";
        $bind = array(Piwik::$idPeriods['range'], $yesterday);
        Piwik::log("Purging Custom Range archives: done [ purged archives older than $yesterday from $blobTable and $numericTable ]");

        Piwik_Query(sprintf($query, $numericTable), $bind);

        // Individual blob tables could be missing
        try {
            Piwik_Query(sprintf($query, $blobTable), $bind);
        } catch (Exception $e) {
        }
    }
}
