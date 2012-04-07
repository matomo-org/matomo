<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: $
 *
 * @category Piwik_Plugins
 * @package Piwik_PrivacyManager
 */

/**
 * Purges the log_visit, log_conversion and related tables of old visit data.
 */
class Piwik_PrivacyManager_LogDataPurger
{
	/**
	 * The number of days after which log entries are considered old.
	 */
	private $deleteLogsOlderThan;
	
	/**
	 * The number of rows to delete per DELETE query.
	 */
	private $maxRowsToDeletePerQuery;
	
	/**
	 * Constructor.
	 * 
	 * @param int $deleteLogsOlderThan The number of days after which log entires are considered old.
	 *                                 Visits and related data whose age is greater than this number
	 *                                 will be purged.
	 */
	public function __construct( $deleteLogsOlderThan, $maxRowsToDeletePerQuery )
	{
		$this->deleteLogsOlderThan = $deleteLogsOlderThan;
		$this->maxRowsToDeletePerQuery = $maxRowsToDeletePerQuery;
	}
	
	/**
	 * Purges old data from the following tables:
	 * - log_visit
	 * - log_link_visit_action
	 * - log_conversion
	 * - log_conversion_item
	 */
	public function purgeData()
	{
		$maxIdVisit = $this->getDeleteIdVisitOffset();
		
		// break if no ID was found (nothing to delete for given period)
		if (empty($maxIdVisit))
		{
			return;
		}
		
		$logTables = self::getDeleteTableLogTables();
		
		// delete data from log tables
		$where = "WHERE idvisit <= ?";
		foreach ($logTables as $logTable)
		{
			Piwik_DeleteAllRows($logTable, $where, $this->maxRowsToDeletePerQuery, array($maxIdVisit));
		}
		
		// optimize table overhead after deletion
		Piwik_OptimizeTables($logTables);
	}
	
	/**
	 * Returns an array describing what data would be purged if purging were invoked.
	 * 
	 * This function returns an array that maps table names with the number of rows
	 * that will be deleted.
	 * 
	 * @return array
	 */
	public function getPurgeEstimate()
	{
		$result = array();
		
		// deal w/ log tables that will be purged
		$maxIdVisit = $this->getDeleteIdVisitOffset();
		if (!empty($maxIdVisit))
		{
			foreach ($this->getDeleteTableLogTables() as $table)
			{
				$rowCount = $this->getLogTableDeleteCount($table, $maxIdVisit);
				if ($rowCount > 0)
				{
					$result[$table] = $rowCount;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * get highest idVisit to delete rows from
	 */
	private function getDeleteIdVisitOffset()
	{
		$dateStart = Piwik_Date::factory("today")->subDay($this->deleteLogsOlderThan);
		
		$sql = "SELECT idvisit
		          FROM ".Piwik_Common::prefixTable("log_visit")."
		         WHERE '".$dateStart->toString('Y-m-d H:i:s')."' > visit_last_action_time AND idvisit > 0
		      ORDER BY idvisit DESC
		         LIMIT 1";

		return Piwik_FetchOne($sql);
	}

    private function getLogTableDeleteCount( $table, $maxIdVisit )
    {
		$sql = "SELECT COUNT(*) FROM $table WHERE idvisit <= ?";
		return (int)Piwik_FetchOne($sql, array($maxIdVisit));
    }
	
    // let's hardcode, since these are no dynamically created tables
    // exclude piwik_log_action since it is a lookup table
    public static function getDeleteTableLogTables()
    {
        return array(Piwik_Common::prefixTable("log_conversion"),
                     Piwik_Common::prefixTable("log_link_visit_action"),
                     Piwik_Common::prefixTable("log_visit"),
                     Piwik_Common::prefixTable("log_conversion_item"));
    }
    
    /**
     * Utility function. Creates a new instance of LogDataPurger with the supplied array
     * of settings.
     * 
     * $settings must contain values for the following keys:
     * - 'delete_logs_older_than': The number of days after which log entries are considered
     *                             old.
     * - 'delete_logs_max_rows_per_query': Max number of rows to DELETE in one query.
     * 
     * @param $settings Array of settings
     */
    public static function make( $settings )
    {
    	return new Piwik_PrivacyManager_LogDataPurger(
    		$settings['delete_logs_older_than'],
    		$settings['delete_logs_max_rows_per_query']
		);
    }
}

