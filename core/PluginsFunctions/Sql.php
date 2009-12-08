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
 * @package PluginsFunctions
 */
class Piwik_Sql
{
}

function Piwik_Exec( $sqlQuery )
{
	return Zend_Registry::get('db')->exec( $sqlQuery );
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
function Piwik_Query( $sqlQuery, $parameters = array())
{
	return Zend_Registry::get('db')->query( $sqlQuery, $parameters);
}

/**
 * Executes the SQL Query and fetches all the rows from the database
 *
 * @param string $sqlQuery
 * @param array Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array (one row in the array per row fetched in the DB)
 */
function Piwik_FetchAll( $sqlQuery, $parameters = array())
{
	return Zend_Registry::get('db')->fetchAll( $sqlQuery, $parameters );
}

function Piwik_FetchOne( $sqlQuery, $parameters = array())
{
	return Zend_Registry::get('db')->fetchOne( $sqlQuery, $parameters );
}

function Piwik_Quote($value )
{
	return Zend_Registry::get('db')->quote($value);
}
