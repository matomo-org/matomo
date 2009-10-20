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
class Piwik_Tracker_Db_Pdo_Mysql extends Piwik_Tracker_Db
{
	protected $connection = null;
	private $dsn;
	private $username;
	private $password;
	private $charset;
	
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
		$this->charset = isset($dbInfo['charset']) ? $dbInfo['charset'] : null;
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

		$this->connection = new PDO($this->dsn, $this->username, $this->password, $config = array());
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// we may want to setAttribute(PDO::ATTR_TIMEOUT ) to a few seconds (default is 60) in case the DB is locked
		// the piwik.php would stay waiting for the database... bad!
		// we delete the password from this object "just in case" it could be printed 
		$this->password = '';
		
		/*
		 * Lazy initialization via MYSQL_ATTR_INIT_COMMAND depends
		 * on mysqlnd support, PHP version, and OS.
		 * see ZF-7428 and http://bugs.php.net/bug.php?id=47224
		 */
		if(!empty($this->charset))
		{
			$sql = "SET NAMES '" . $this->charset . "'";
			$this->connection->exec($sql);
		}

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

	/**
	 * Test error number
	 *
	 * @param Exception $e
	 * @param string $errno
	 * @return bool
	 */
	public function isErrNo($e, $errno)
	{
		if(preg_match('/([0-9]{4})/', $e->getMessage(), $match))
		{
			return $match[1] == $errno;
		}
		return false;
	}

	/**
	 * Return number of affected rows in last query
	 *
	 * @param mixed $queryResult Result from query()
	 * @return int
	 */
	public function rowCount($queryResult)
	{
		return $queryResult->rowCount();
	}
}
