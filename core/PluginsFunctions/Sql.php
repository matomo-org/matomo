<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package PluginsFunctions
 */

/**
 * SQL wrapper
 *
 * @package PluginsFunctions
 */
class Piwik_Sql
{
	static private function getDb()
	{
		$db = null;
		if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
		{
			$db = Piwik_Tracker::getDatabase();
		}
		if($db === null)
		{
			$db = Zend_Registry::get('db');
		}
		return $db;
	}

	static public function exec($sql)
	{
		$profiler = Zend_Registry::get('db')->getProfiler();
		$q = $profiler->queryStart($sql, Zend_Db_Profiler::INSERT);
		$return = self::getDb()->exec($sql);
		$profiler->queryEnd($q);
		return $return;
	}

	static public function query($sql, $parameters = array())
	{
		return self::getDb()->query($sql, $parameters);
	}

	static public function fetchAll($sql, $parameters = array())
	{
		return self::getDb()->fetchAll($sql, $parameters);
	}

	static public function fetchRow($sql, $parameters = array())
	{
		return self::getDb()->fetchRow($sql, $parameters);
	}

	static public function fetchOne($sql, $parameters = array())
	{
		return self::getDb()->fetchOne($sql, $parameters);
	}
	
	static public function deleteAllRows( $table, $where, $maxRowsPerQuery = 100000, $parameters = array() )
	{
		$sql = "DELETE FROM $table $where LIMIT ".(int)$maxRowsPerQuery;
		
		// delete rows w/ a limit
		$totalRowsDeleted = 0;
		do
		{
			$rowsDeleted = self::query($sql, $parameters)->rowCount();
			
			$totalRowsDeleted += $rowsDeleted;
		} while ($rowsDeleted >= $maxRowsPerQuery);
		
		return $totalRowsDeleted;
	}

	static public function optimizeTables( $tables )
	{
		if (!is_array($tables))
		{
			$tables = array($tables);
		}
		
		return self::query("OPTIMIZE TABLE ".implode(',', $tables));
	}
	
	static public function dropTables( $tables )
	{
		if (!is_array($tables))
		{
			$tables = array($tables);
		}
		
		return self::query("DROP TABLE ".implode(',', $tables));
	}
	
	static public function lockTables( $tablesToRead, $tablesToWrite )
	{
		if (!is_array($tablesToRead))
		{
			$tablesToRead = array($tablesToRead);
		}
		if (!is_array($tablesToWrite))
		{
			$tablesToWrite = array($tablesToWrite);
		}
		
		$lockExprs = array();
		foreach ($tablesToRead as $table)
		{
			$lockExprs[] = $table." READ";
		}
		foreach ($tablesToWrite as $table)
		{
			$lockExprs[] = $table." WRITE";
		}
		
		return self::query("LOCK TABLES ".implode(', ', $lockExprs));
	}
	
	static public function unlockAllTables()
	{
		return self::query("UNLOCK TABLES");
	}
}

/**
 * Executes an unprepared SQL query on the DB.  Recommended for DDL statements, e.g., CREATE/DROP/ALTER.
 * The return result is DBMS-specific. For MySQLI, it returns the number of rows affected.  For PDO, it returns the Zend_Db_Statement object
 * If you want to fetch data from the DB you should use the function Piwik_FetchAll()
 *
 * @param string $sqlQuery
 * @param array Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return integer|Zend_Db_Statement
 */
function Piwik_Exec($sqlQuery)
{
	return Piwik_Sql::exec($sqlQuery);
}

/**
 * Executes a SQL query on the DB and returns the Zend_Db_Statement object
 * If you want to fetch data from the DB you should use the function Piwik_FetchAll()
 * 
 * See also http://framework.zend.com/manual/en/zend.db.statement.html
 *
 * @param string $sqlQuery
 * @param array Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return Zend_Db_Statement
 */
function Piwik_Query($sqlQuery, $parameters = array())
{
	return Piwik_Sql::query($sqlQuery, $parameters);
}

/**
 * Executes the SQL Query and fetches all the rows from the database query
 *
 * @param string $sqlQuery
 * @param array $parameters Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array (one row in the array per row fetched in the DB)
 */
function Piwik_FetchAll( $sqlQuery, $parameters = array())
{
	return Piwik_Sql::fetchAll($sqlQuery, $parameters);
}

/**
 * Fetches first row of result from the database query
 *
 * @param string $sqlQuery
 * @param array $parameters Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array
 */
function Piwik_FetchRow($sqlQuery, $parameters = array())
{
	return Piwik_Sql::fetchRow($sqlQuery, $parameters);
}

/**
 * Fetches first column of first row of result from the database query
 *
 * @param string $sqlQuery
 * @param array $parameters Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return string
 */
function Piwik_FetchOne( $sqlQuery, $parameters = array())
{
	return Piwik_Sql::fetchOne($sqlQuery, $parameters);
}

/**
 * Deletes all desired rows in a table, while using a limit. This function will execute a
 * DELETE query until there are no more rows to delete.
 * 
 * @param string $table The name of the table to delete from. Must be prefixed.
 * @param string $where The where clause of the query. Must include the WHERE keyword.
 * @param int $maxRowsPerQuery The maximum number of rows to delete per DELETE query.
 * @param array $parameters Parameters to bind in the query.
 * @return int The total number of rows deleted.
 */
function Piwik_DeleteAllRows( $table, $where, $maxRowsPerQuery, $parameters = array() )
{
	return Piwik_Sql::deleteAllRows($table, $where, $maxRowsPerQuery, $parameters);
}

/**
 * Runs an OPTIMIZE TABLE query on the supplied table or tables. The table names must be prefixed.
 * 
 * @param string|array $tables The name of the table to optimize or an array of tables to optimize.
 * @return Zend_Db_Statement
 */
function Piwik_OptimizeTables( $tables )
{
	return Piwik_Sql::optimizeTables($tables);
}

/**
 * Drops the supplied table or tables. The table names must be prefixed.
 * 
 * @param string|array $tables The name of the table to drop or an array of table names to drop.
 * @return Zend_Db_Statement
 */
function Piwik_DropTables( $tables )
{
	return Piwik_Sql::dropTables($tables);
}

/**
 * Locks the supplied table or tables. The table names must be prefixed.
 * 
 * @param string|array $tablesToRead The table or tables to obtain 'read' locks on.
 * @param string|array $tablesToWrite The table or tables to obtain 'write' locks on.
 * @return Zend_Db_Statement
 */
function Piwik_LockTables( $tablesToRead, $tablesToWrite )
{
	return Piwik_Sql::lockTables($tablesToRead, $tablesToWrite);
}

/**
 * Releases all table locks.
 * 
 * @return Zend_Db_Statement
 */
function Piwik_UnlockAllTables()
{
	return Piwik_Sql::unlockAllTables();
}

