<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * mysqli wrapper
 *
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Db_MySqli extends Piwik_Tracker_Db
{
	private $connection = null;
	private $host;
	private $dbname;
	private $username;
	private $password;
	
	/**
	 * Builds the DB object
	 */
	public function __construct( $dbInfo, $driverName = 'mysql') 
	{
		if(isset($dbInfo['unix_socket']) && $dbInfo['unix_socket'][0] == '/')
		{
			$this->host = ':' . $dbInfo['unix_socket'];
		}
		else if ($dbInfo['port'][0] == '/')
		{
			$this->host = ':' . $dbInfo['port'];
		}
		else
		{
			$this->host = $dbInfo['host'] . ':' . $dbInfo['port'];
		}
		$this->dbname = $dbInfo['dbname'];
		$this->username = $dbInfo['username'];
		$this->password = $dbInfo['password'];
	}

	public function __destruct() 
	{
		$this->connection = null;
	}

	/**
	 * Connects to the DB
	 * 
	 * @throws Exception if there was an error connecting the DB
	 */
	public function connect() 
	{
		if(self::$profiling)
		{
			$timer = $this->initProfiler();
		}
		
		$this->connection = mysql_connect($this->host, $this->username, $this->password);
		$result = mysql_select_db($this->dbname);

		$this->password = '';
		
		if(self::$profiling)
		{
			$this->recordQueryProfile('connect', $timer);
		}
	}
	
	/**
	 * Disconnects from the server
	 */
	public function disconnect()
	{
		if(self::$profiling)
		{
			$this->recordProfiling();
		}
		$this->connection = null;
	}
	
	/**
	 * Returns an array containing all the rows of a query result, using optional bound parameters.
	 * 
	 * @param string Query 
	 * @param array Parameters to bind
	 * @see also query()
	 * @throws Exception if an exception occured
	 */
	public function fetchAll( $query, $parameters = array() )
	{
		try {
			$query = $this->prepare( $query, $parameters );
			$rs = mysql_query($query);
			while($row = mysql_fetch_array($rs, MYSQL_ASSOC)) 
			{
				$rows[] = $row;
			}
			return $rows;
		} catch (Exception $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	/**
	 * Returns the first row of a query result, using optional bound parameters.
	 * 
	 * @param string Query 
	 * @param array Parameters to bind
	 * @see also query()
	 * 
	 * @throws Exception if an exception occured
	 */
	public function fetch( $query, $parameters = array() )
	{
		try {
			$query = $this->prepare( $query, $parameters );
			$rs = mysql_query($query);
			if($rs === false)
			{
				return false;
			}
			$row = mysql_fetch_array($rs, MYSQL_ASSOC);
			return $row;
		} catch (Exception $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	/**
	 * Executes a query, using optional bound parameters.
	 * 
	 * @param string Query 
	 * @param array|string Parameters to bind array('idsite'=> 1)
	 * 
	 * @return bool|resource false if failed
	 * @throws Exception if an exception occured
	 */
	public function query($query, $parameters = array()) 
	{
		if(is_null($this->connection))
		{
			return false;
		}
		try {
			if(self::$profiling)
			{
				$timer = $this->initProfiler();
			}
			
			if(!is_array($parameters))
			{
				$parameters = array( $parameters );
			}
			$query = $this->prepare( $query, $parameters );
			$result = mysql_query($query);
			
			if(self::$profiling)
			{
				$this->recordQueryProfile($query, $timer);
			}
			return $result;
		} catch (Exception $e) {
			throw new Exception("Error query: ".$e->getMessage() . "
								In query: $query
								Parameters: ".var_export($parameters, true));
		}
	}
	
	/**
	 * Returns the last inserted ID in the DB
	 * 
	 * @return int
	 */
	public function lastInsertId()
	{
		return mysql_insert_id();
	}

	/**
	 * Input is a prepared SQL statement and parameters
	 * Returns the SQL statement
	 *
	 * @param string $query
	 * @param array $parameters 
	 * @return string
	 */
	private function prepare($query, $parameters) {
		foreach($parameters as $i=>$p) 
		{
			$parameters[$i] = addslashes($p);
		}
		$query = str_replace('?', "'%s'", $query);
		array_unshift($parameters, $query);
		$query = call_user_func_array(sprintf, $parameters);
		return $query;
	}
}
