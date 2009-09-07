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
 * PDO MySQL wrapper
 *
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Db_PdoMySql extends Piwik_Tracker_Db
{
	private $connection = null;
	private $dsn;
	private $username;
	private $password;
	
	/**
	 * Builds the DB object
	 */
	public function __construct( $dbInfo, $driverName = 'mysql') 
	{
		if(isset($dbInfo['unix_socket']) && $dbInfo['unix_socket'][0] == '/')
		{
			$this->dsn = $driverName.":dbname=${dbInfo['dbname']};unix_socket=${dbInfo['unix_socket']}";
		}
		else if ($dbInfo['port'][0] == '/')
		{
			$this->dsn = $driverName.":dbname=${dbInfo['dbname']};unix_socket=${dbInfo['port']}";
		}
		else
		{
			$this->dsn = $driverName.":dbname=${dbInfo['dbname']};host=${dbInfo['host']};port=${dbInfo['port']}";
		}
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
		
		$this->connection = new PDO($this->dsn, $this->username, $this->password);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// we may want to setAttribute(PDO::ATTR_TIMEOUT ) to a few seconds (default is 60) in case the DB is locked
		// the piwik.php would stay waiting for the database... bad!
		// we delete the password from this object "just in case" it could be printed 
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
			$sth = $this->query( $query, $parameters );
			if($sth === false)
			{
				return false;
			}
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
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
			$sth = $this->query( $query, $parameters );
			if($sth === false)
			{
				return false;
			}
			return $sth->fetch(PDO::FETCH_ASSOC);			
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}

	/**
	 * Executes a query, using optional bound parameters.
	 * 
	 * @param string Query 
	 * @param array|string Parameters to bind array('idsite'=> 1)
	 * 
	 * @return PDOStatement or false if failed
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
			$sth = $this->connection->prepare($query);
			$sth->execute( $parameters );
			
			if(self::$profiling)
			{
				$this->recordQueryProfile($query, $timer);
			}
			return $sth;
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage() . "
								In query: $query
								Parameters: ".var_export($parameters, true));
		}
	}
	
	/**
	 * Returns the last inserted ID in the DB
	 * Wrapper of PDO::lastInsertId()
	 * 
	 * @return int
	 */
	public function lastInsertId()
	{
		return $this->connection->lastInsertId();
	}
}
