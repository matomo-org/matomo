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
class Piwik_Tracker_Db_Mysqli extends Piwik_Tracker_Db
{
	protected $connection = null;
	private $host;
	private $port;
	private $socket;
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
			$this->host = null;
			$this->port = null;
			$this->socket = $dbInfo['unix_socket'];
		}
		else if ($dbInfo['port'][0] == '/')
		{
			$this->host = null;
			$this->port = null;
			$this->socket = $dbInfo['port'];
		}
		else
		{
			$this->host = $dbInfo['host'];
			$this->port = $dbInfo['port'];
			$this->socket = null;
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
		
		$this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->dbname, $this->port, $this->socket);
		if(!$this->connection || mysqli_connect_errno())
		{
			throw new Exception("Connect failed: " . mysqli_connect_error());
		}

		if(!mysqli_set_charset($this->connection, 'utf8'))
		{
			throw new Exception("Set Charset failed: " . mysqli_error($this->connection));
		}

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
		mysqli_close($this->connection);
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
			if(self::$profiling)
			{
				$timer = $this->initProfiler();
			}

			$query = $this->prepare( $query, $parameters );
			$rs = mysqli_query($this->connection, $query);
			while($row = mysqli_fetch_array($rs, MYSQL_ASSOC)) 
			{
				$rows[] = $row;
			}
			mysqli_free_result($rs);

			if(self::$profiling)
			{
				$this->recordQueryProfile($query, $timer);
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
			if(self::$profiling)
			{
				$timer = $this->initProfiler();
			}

			$query = $this->prepare( $query, $parameters );
			$rs = mysqli_query($this->connection, $query);
			if($rs === false)
			{
				return false;
			}
			$row = mysqli_fetch_array($rs, MYSQL_ASSOC);
			mysqli_free_result($rs);

			if(self::$profiling)
			{
				$this->recordQueryProfile($query, $timer);
			}
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
			$result = mysqli_query($this->connection, $query);
			if(!is_bool($result))
			{
				mysqli_free_result($result);
			}
			
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
		return mysqli_insert_id($this->connection);
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
		$query = call_user_func_array('sprintf', $parameters);
		return $query;
	}

	/**
	 * Test error number
	 *
	 * @param Exception $e
	 * @param string $errno
	 * @return bool
	 */
	public function isErrNo($e, $errno)
	{
		return mysqli_errno($this->_connection) == $errno;
	}

	/**
	 * Return number of affected rows in last query
	 *
	 * @param mixed $queryResult Result from query()
	 * @return int
	 */
	public function rowCount($queryResult)
	{
		return mysqli_affected_rows($this->connection);
	}
}
