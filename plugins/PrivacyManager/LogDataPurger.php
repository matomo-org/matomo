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
	const TEMP_TABLE_NAME = 'tmp_log_actions_to_keep';
	
	/**
	 * The max set of rows each table scan select should query at one time.
	 */
	public static $selectSegmentSize = 100000;
	
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
	 * @param int $maxRowsToDeletePerQuery The maximum number of rows to delete in one query. Used to
	 *                                     make sure log tables aren't locked for too long.
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
	 * - log_action
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
			// deleting from log_action must be handled differently, so we do it later
			if ($logTable != Piwik_Common::prefixTable('log_action'))
			{
				Piwik_DeleteAllRows($logTable, $where, $this->maxRowsToDeletePerQuery, array($maxIdVisit));
			}
		}
		
		// delete unused actions from the log_action table (but only if we can lock tables)
		if (Piwik::isLockPrivilegeGranted())
		{
			$this->purgeUnusedLogActions();
		}
		else
		{
			$logMessage = get_class($this).": LOCK TABLES privilege not granted; skipping unused actions purge";
			Piwik::log($logMessage);
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
				// getting an estimate for log_action is not supported since it can take too long
				if ($table != Piwik_Common::prefixTable('log_action'))
				{
					$rowCount = $this->getLogTableDeleteCount($table, $maxIdVisit);
					if ($rowCount > 0)
					{
						$result[$table] = $rowCount;
					}
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Safely delete all unused log_action rows.
	 */
	private function purgeUnusedLogActions()
	{
		$this->createTempTable();
		
		// get current max ID in log tables w/ idaction references.
		$maxIds = $this->getMaxIdsInLogTables();
		
		// do large insert (inserting everything before maxIds) w/o locking tables...
		$this->insertActionsToKeep($maxIds, $deleteOlderThanMax = true);
		
		// ... then do small insert w/ locked tables to minimize the amount of time tables are locked.
		$this->lockLogTables();
		$this->insertActionsToKeep($maxIds, $deleteOlderThanMax = false);
		
		// delete before unlocking tables so there's no chance a new log row that references an
		// unused action will be inserted.
		$this->deleteUnusedActions();
		$this->unlockLogTables();
	}
	
	/**
	 * get highest idVisit to delete rows from
	 * @return string
	 */
	private function getDeleteIdVisitOffset()
	{
		$logVisit = Piwik_Common::prefixTable("log_visit");
		
		// get max idvisit
		$maxIdVisit = Piwik_FetchOne("SELECT MAX(idvisit) FROM $logVisit");
		if (empty($maxIdVisit))
		{
			return false;
		}
		
		// select highest idvisit to delete from
		$dateStart = Piwik_Date::factory("today")->subDay($this->deleteLogsOlderThan);
		$sql = "SELECT idvisit
		          FROM $logVisit
		         WHERE '".$dateStart->toString('Y-m-d H:i:s')."' > visit_last_action_time
		           AND idvisit <= ?
		           AND idvisit > ?
		      ORDER BY idvisit DESC
		         LIMIT 1";
		
		return Piwik_SegmentedFetchFirst($sql, $maxIdVisit, 0, -self::$selectSegmentSize);
	}

	private function getLogTableDeleteCount( $table, $maxIdVisit )
	{
		$sql = "SELECT COUNT(*) FROM $table WHERE idvisit <= ?";
		return (int)Piwik_FetchOne($sql, array($maxIdVisit));
	}
	
	private function createTempTable()
	{
		$sql = "CREATE TEMPORARY TABLE ".Piwik_Common::prefixTable(self::TEMP_TABLE_NAME)." (
					idaction INT(11),
					PRIMARY KEY (idaction)
				)";
		Piwik_Query($sql);
	}
	
	private function getMaxIdsInLogTables()
	{
		$tables = array('log_conversion', 'log_link_visit_action', 'log_visit', 'log_conversion_item');
		$idColumns = $this->getTableIdColumns();
		
		$result = array();
		foreach ($tables as $table)
		{
			$idCol = $idColumns[$table];
			$result[$table] = Piwik_FetchOne("SELECT MAX($idCol) FROM ".Piwik_Common::prefixTable($table));
		}
		
		return $result;
	}
	
	private function insertActionsToKeep( $maxIds, $olderThan = true )
	{
		$tempTableName = Piwik_Common::prefixTable(self::TEMP_TABLE_NAME);
		
		$idColumns = $this->getTableIdColumns();
		foreach ($this->getIdActionColumns() as $table => $columns)
		{
			$idCol = $idColumns[$table];
			
			foreach ($columns as $col)
			{
				$select = "SELECT $col FROM ".Piwik_Common::prefixTable($table)." WHERE $idCol >= ? AND $idCol < ?";
				$sql = "INSERT IGNORE INTO $tempTableName $select";
				
				if ($olderThan)
				{
					$start = 0;
					$finish = $maxIds[$table];
				}
				else
				{
					$start = $maxIds[$table];
					$finish = Piwik_FetchOne("SELECT MAX($idCol) FROM ".Piwik_Common::prefixTable($table));
				}
				
				Piwik_SegmentedQuery($sql, $start, $finish, self::$selectSegmentSize);
			}
		}
		
		// allow code to be executed after data is inserted. for concurrency testing purposes.
		if ($olderThan)
		{
			Piwik_PostEvent("LogDataPurger.actionsToKeepInserted.olderThan");
		}
		else
		{
			Piwik_PostEvent("LogDataPurger.actionsToKeepInserted.newerThan");
		}
	}
	
	private function lockLogTables()
	{
		Piwik_LockTables(
			$readLocks = Piwik_Common::prefixTables('log_conversion',
													'log_link_visit_action',
													'log_visit',
													'log_conversion_item'),
			$writeLocks = Piwik_Common::prefixTables('log_action')
		);
	}
	
	private function unlockLogTables()
	{
		Piwik_UnlockAllTables();
	}
	
	private function deleteUnusedActions()
	{
		list($logActionTable, $tempTableName) = Piwik_Common::prefixTables("log_action", self::TEMP_TABLE_NAME);
		
		$deleteSql = "DELETE LOW_PRIORITY QUICK IGNORE $logActionTable
						FROM $logActionTable
				   LEFT JOIN $tempTableName tmp ON tmp.idaction = $logActionTable.idaction
					   WHERE tmp.idaction IS NULL";
		
		Piwik_Query($deleteSql);
	}
	
	private function getIdActionColumns()
	{
		return array(
			'log_link_visit_action' => array( 'idaction_url',
											  'idaction_url_ref',
											  'idaction_name',
											  'idaction_name_ref' ),
											  
			'log_conversion' => array( 'idaction_url' ),
			
			'log_visit' => array( 'visit_exit_idaction_url',
								  'visit_exit_idaction_name',
								  'visit_entry_idaction_url',
								  'visit_entry_idaction_name' ),
								  
			'log_conversion_item' => array( 'idaction_sku',
											'idaction_name',
											'idaction_category',
											'idaction_category2',
											'idaction_category3',
											'idaction_category4',
											'idaction_category5' )
		);
	}
	
	private function getTableIdColumns()
	{
		return array(
			'log_link_visit_action' => 'idlink_va',
			'log_conversion' => 'idvisit',
			'log_visit' => 'idvisit',
			'log_conversion_item' => 'idvisit'
		);
	}
	
	// let's hardcode, since these are not dynamically created tables
	public static function getDeleteTableLogTables()
	{
		$result = Piwik_Common::prefixTables('log_conversion',
											 'log_link_visit_action',
											 'log_visit',
											 'log_conversion_item');
		if (Piwik::isLockPrivilegeGranted())
		{
			$result[] = Piwik_Common::prefixTable('log_action');
		}
		return $result;
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
	 * @param array $settings Array of settings
	 * @param bool $useRealTable
	 * @return Piwik_PrivacyManager_LogDataPurger
	 */
	public static function make( $settings, $useRealTable = false )
	{
		return new Piwik_PrivacyManager_LogDataPurger(
			$settings['delete_logs_older_than'],
			$settings['delete_logs_max_rows_per_query']
		);
	}
}

