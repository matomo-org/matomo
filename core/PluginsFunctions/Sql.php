<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
		return self::getDb()->exec($sql);
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
