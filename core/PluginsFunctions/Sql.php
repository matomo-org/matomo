<?php

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

