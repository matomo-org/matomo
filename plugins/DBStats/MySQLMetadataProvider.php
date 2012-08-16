<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: MySQLMetadataProvider.php $
 * 
 * @category Piwik_Plugins
 * @package Piwik_DBStats
 */

/**
 * Utility class that provides general information about databases, including the size of
 * the entire database, the size and row count of each table and the size and row count
 * of each metric/report type currently stored.
 * 
 * This class will cache the table information it retrieves from the database. In order to
 * issue a new query instead of using this cache, you must create a new instance of this type.
 */
class Piwik_DBStats_MySQLMetadataProvider
{
	/**
	 * Cached MySQL table statuses. So we won't needlessly re-issue SHOW TABLE STATUS queries.
	 */
	private $tableStatuses = null;
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// empty
	}
	
	/**
	 * Gets general database info that is not specific to any table.
	 * 
	 * @return array See http://dev.mysql.com/doc/refman/5.1/en/show-status.html .
	 */
	public function getDBStatus()
	{
		if (function_exists('mysql_connect'))
		{
			$configDb = Piwik_Config::getInstance()->database;
			$link   = mysql_connect($configDb['host'], $configDb['username'], $configDb['password']);
			$status = mysql_stat($link);
			mysql_close($link);
			$status = explode("  ", $status);
		}
		else
		{
			$fullStatus = Piwik_FetchAssoc('SHOW STATUS');
			if (empty($fullStatus))
			{
				throw new Exception('Error, SHOW STATUS failed');
			}

			$status = array(
				'Uptime' => $fullStatus['Uptime']['Value'],
				'Threads' => $fullStatus['Threads_running']['Value'],
				'Questions' => $fullStatus['Questions']['Value'],
				'Slow queries' => $fullStatus['Slow_queries']['Value'],
				'Flush tables' => $fullStatus['Flush_commands']['Value'],
				'Open tables' => $fullStatus['Open_tables']['Value'],
				'Opens' => 'unavailable', // not available via SHOW STATUS
				'Queries per second avg' => 'unavailable' // not available via SHOW STATUS
			);
		}

		return $status;
	}

	/**
	 * Gets the MySQL table status of the requested Piwik table.
	 * 
	 * @param string $table The name of the table. Should not be prefixed (ie, 'log_visit' is
	 *                      correct, 'piwik_log_visit' is not).
	 * @return array See http://dev.mysql.com/doc/refman/5.1/en/show-table-status.html .
	 */
	public function getTableStatus( $table )
	{
		$prefixed = Piwik_Common::prefixTable($table);
		
		// if we've already gotten every table status, don't issue an uneeded query
		if (!is_null($this->tableStatuses) && isset($this->tableStatuses[$prefixed]))
		{
			return $this->tableStatuses[$prefixed];
		}
		else
		{
			return Piwik_FetchRow("SHOW TABLE STATUS LIKE ?", array($prefixed));
		}
	}
	
	/**
	 * Gets the result of a SHOW TABLE STATUS query for every Piwik table in the DB.
	 * Non-piwik tables are ignored.
	 * 
	 * @param string $matchingRegex Regex used to filter out tables whose name doesn't
	 *                              match it.
	 * @return array The table information. See http://dev.mysql.com/doc/refman/5.5/en/show-table-status.html
	 *               for specifics.
	 */
	public function getAllTablesStatus( $matchingRegex = null )
	{
		if (is_null($this->tableStatuses))
		{
			$tablesPiwik = Piwik::getTablesInstalled();
		
			$this->tableStatuses = array();
			foreach(Piwik_FetchAll("SHOW TABLE STATUS") as $t)
			{
				if (in_array($t['Name'], $tablesPiwik))
				{
					$this->tableStatuses[$t['Name']] = $t;
				}
			}
		}
		
		if (is_null($matchingRegex))
		{
			return $this->tableStatuses;
		}
		
		$result = array();
		foreach ($this->tableStatuses as $status)
		{
			if (preg_match($matchingRegex, $status['Name']))
			{
				$result[] = $status;
			}
		}
		return $result;
	}
	
	/**
	 * Returns table statuses for every log table.
	 * 
	 * @return array An array of status arrays. See http://dev.mysql.com/doc/refman/5.5/en/show-table-status.html.
	 */
	public function getAllLogTableStatus()
	{
		$regex = "/^".Piwik_Common::prefixTable('log_')."(?!profiling)/";
		return $this->getAllTablesStatus($regex);
	}
	
	/**
	 * Returns table statuses for every numeric archive table.
	 * 
	 * @return array An array of status arrays. See http://dev.mysql.com/doc/refman/5.5/en/show-table-status.html.
	 */
	public function getAllNumericArchiveStatus()
	{
		$regex = "/^".Piwik_Common::prefixTable('archive_numeric')."_/";
		return $this->getAllTablesStatus($regex);
	}
	
	/**
	 * Returns table statuses for every blob archive table.
	 * 
	 * @return array An array of status arrays. See http://dev.mysql.com/doc/refman/5.5/en/show-table-status.html.
	 */
	public function getAllBlobArchiveStatus()
	{
		$regex = "/^".Piwik_Common::prefixTable('archive_blob')."_/";
		return $this->getAllTablesStatus($regex);
	}
	
	/**
	 * Retruns table statuses for every admin table.
	 * 
	 * @return array An array of status arrays. See http://dev.mysql.com/doc/refman/5.5/en/show-table-status.html.
	 */
	public function getAllAdminTableStatus()
	{
		$regex = "/^".Piwik_Common::prefixTable('')."(?!archive_|(?:log_(?!profiling)))/";
		return $this->getAllTablesStatus($regex);
	}
	
	/**
	 * Returns a DataTable that lists the number of rows and the estimated amount of space
	 * each blob archive type takes up in the database.
	 * 
	 * Blob types are differentiated by name.
	 * 
	 * @param bool $forceCache false to use the cached result, true to run the queries again and
	 *                         cache the result.
	 * @return Piwik_DataTable
	 */
	public function getRowCountsAndSizeByBlobName( $forceCache = false )
	{
		$extraSelects = array("SUM(OCTET_LENGTH(value)) AS 'blob_size'", "SUM(LENGTH(name)) AS 'name_size'");
		$extraCols = array('blob_size', 'name_size');
		return $this->getRowCountsByArchiveName(
			$this->getAllBlobArchiveStatus(), 'getEstimatedBlobArchiveRowSize', $forceCache, $extraSelects,
			$extraCols);
	}
	
	/**
	 * Returns a DataTable that lists the number of rows and the estimated amount of space
	 * each metric archive type takes up in the database.
	 * 
	 * Metric types are differentiated by name.
	 * 
	 * @param bool $forceCache false to use the cached result, true to run the queries again and
	 *                         cache the result.
	 * @return Piwik_DataTable
	 */
	public function getRowCountsAndSizeByMetricName( $forceCache = false )
	{
		return $this->getRowCountsByArchiveName(
			$this->getAllNumericArchiveStatus(), 'getEstimatedRowsSize', $forceCache);
	}
	
	/**
	 * Utility function. Gets row count of a set of tables grouped by the 'name' column.
	 * This is the implementation of the getRowCountsAndSizeBy... functions.
	 */
	private function getRowCountsByArchiveName( $statuses, $getRowSizeMethod, $forceCache = false,
												$otherSelects = array(), $otherDataTableColumns = array() )
	{
		$extraCols = '';
		if (!empty($otherSelects))
		{
			$extraCols = ', '.implode(', ', $otherSelects);
		}

		$cols = array_merge(array('row_count'), $otherDataTableColumns);
		
		$dataTable = new Piwik_DataTable();
		foreach ($statuses as $status)
		{
			$dataTableOptionName = $this->getCachedOptionName($status['Name'], 'byArchiveName');
			
			// if option exists && !$forceCache, use the cached data, otherwise create the
			$cachedData = Piwik_GetOption($dataTableOptionName);
			if ($cachedData !== false && !$forceCache)
			{
				$table = new Piwik_DataTable();
				$table->addRowsFromSerializedArray($cachedData);
			}
			else
			{
				// otherwise, create data table & cache it
				$sql = "SELECT name as 'label', COUNT(*) as 'row_count'$extraCols FROM {$status['Name']} GROUP BY name";
				
				$table = new Piwik_DataTable();
				$table->addRowsFromSimpleArray(Piwik_FetchAll($sql));
				
				$reduceArchiveRowName = array($this, 'reduceArchiveRowName');
				$table->filter('GroupBy', array('label', $reduceArchiveRowName));
				
				$serializedTables = $table->getSerialized();
				$serializedTable = reset($serializedTables);
				Piwik_SetOption($dataTableOptionName, $serializedTable);
			}
			
			// add estimated_size column
			$getEstimatedSize = array($this, $getRowSizeMethod);
			$table->filter('ColumnCallbackAddColumn',
				array($cols, 'estimated_size', $getEstimatedSize, array($status)));
			
			$dataTable->addDataTable($table);
			destroy($table);
		}
		return $dataTable;
	}
	
	/**
	 * Gets the estimated database size a count of rows takes in a table.
	 */
	public function getEstimatedRowsSize( $row_count, $status )
	{
		if($status['Rows'] == 0)
		{
			return 0;
		}
		$avgRowSize = ($status['Data_length'] + $status['Index_length']) / $status['Rows'];
		return $avgRowSize * $row_count;
	}
	
	/**
	 * Gets the estimated database size a count of rows in a blob_archive table. Depends on
	 * the data table row to contain the size of all blobs & name strings in the row set it
	 * represents.
	 */
	public function getEstimatedBlobArchiveRowSize( $row_count, $blob_size, $name_size, $status )
	{
		// calculate the size of each fixed size column in a blob archive table
		static $fixedSizeColumnLength = null;
		if (is_null($fixedSizeColumnLength))
		{
			$fixedSizeColumnLength = 0;
			foreach (Piwik_FetchAll("SHOW COLUMNS FROM ".$status['Name']) as $column)
			{
				$columnType = $column['Type'];
				
				if (($paren = strpos($columnType, '(')) !== false)
				{
					$columnType = substr($columnType, 0, $paren);
				}
				
				$fixedSizeColumnLength += $this->sizeOfMySQLColumn($columnType);
			}
		}
		// calculate the average row size
		if($status['Rows'] == 0) {
			$avgRowSize = 0;
		} else {
			$avgRowSize = $status['Index_length'] / $status['Rows'] + $fixedSizeColumnLength;
		}
		
		// calculate the row set's size
		return $avgRowSize * $row_count + $blob_size + $name_size;
	}
	
	/** Returns the size in bytes of a fixed size MySQL data type. Returns 0 for unsupported data type. */
	private function sizeOfMySQLColumn( $columnType )
	{
		switch (strtolower($columnType))
		{
			case "tinyint":
			case "year":
				return 1;
			case "smallint":
				return 2;
			case "mediumint":
			case "date":
			case "time":
				return 3;
			case "int":
			case "float": // assumes precision isn't used
			case "timestamp":
				return 4;
			case "bigint":
			case "double":
			case "real":
			case "datetime":
				return 8;
			default:
				return 0;
		}
	}
	
	/**
	 * Gets the option name used to cache the result of an intensive query.
	 */
	private function getCachedOptionName( $tableName, $suffix )
	{
		return 'dbstats_cached_'.$tableName.'_'.$suffix;
	}
	
	/**
	 * Reduces the given metric name. Used to simplify certain reports.
	 * 
	 * Some metrics, like goal metrics, can have different string names. For goal metrics,
	 * there's one name per goal ID. Grouping metrics and reports like these together
	 * simplifies the tables that display them.
	 * 
	 * This function makes goal names, 'done...' names and names of the format .*_[0-9]+
	 * equivalent.
	 */
	public function reduceArchiveRowName( $name )
	{
		// all 'done...' fields are considered the same
		if (strpos($name, 'done') === 0)
		{
			return 'done';
		}
		
		// check for goal id, if present (Goals_... reports should not be reduced here, just Goal_... ones)
		if (preg_match("/^Goal_(?:-?[0-9]+_)?(.*)/", $name, $matches))
		{
			$name = "Goal_*_".$matches[1];
		}
		
		// remove subtable id suffix, if present
		if (preg_match("/^(.*)_[0-9]+$/", $name, $matches))
		{
			$name = $matches[1]."_*";
		}
		
		return $name;
	}
}

