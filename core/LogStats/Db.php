<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Db.php 522 2008-06-11 00:31:03Z matt $
 * 
 * @package Piwik_LogStats
 */

/**
 * Simple database PDO wrapper.
 * We can't afford to have a dependency with the Zend_Db module in LogStats.
 * We wrote this simple class 
 * 
 * @package Piwik_LogStats
 */

class Piwik_LogStats_Db 
{
	private $connection = null;
	private $username;
	private $password;
	
	static private $profiling = false;

	protected $queriesProfiling = array();
	
	/**
	 * Builds the DB object
	 */
	public function __construct( $host, $username, $password, $dbname, $port, $driverName = 'mysql') 
	{
		$this->dsn = $driverName.":dbname=$dbname;host=$host;port=$port";
		$this->username = $username;
		$this->password = $password;
	}

	
	/**
	 * Returns true if the SQL profiler is enabled
	 * Only used by the unit test that tests that the profiler is off on a  production server
	 * 
	 * @return bool 
	 */
	static public function isProfilingEnabled()
	{
		return self::$profiling;
	}
	
	/**
	 * Enables the SQL profiling. 
	 * For each query, saves in the DB the time spent on this query. 
	 * Very useful to see the slow query under heavy load.
	 * You can then use Piwik::printSqlProfilingReportLogStats(); 
	 * to display the SQLProfiling report and see which queries take time, etc.
	 */
	static public function enableProfiling()
	{
		self::$profiling = true;
	}
	
	/** 
	 * Disables the SQL profiling logging.
	 */
	static public function disableProfiling()
	{
		self::$profiling = false;
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
	 * Disconnects from the Mysql server
	 *
	 * @return void
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
	 * Returns the table name prefixed by the table prefix.
	 * 
	 * @param string The table name to prefix, ie "log_visit"
	 * @return string The table name prefixed, ie "piwik-production_log_visit"
	 */
	public function prefixTable( $suffix )
	{
		static $prefix;
		if (!isset($prefix)) {
			$prefix = Piwik_LogStats_Config::getInstance()->database['tables_prefix'];
		}		
		return $prefix . $suffix;
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
	 * @param array Parameters to bind
	 * 
	 * @return PDOStatement or false if failed
	 * @throw Exception if an exception occured
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
			
			$sth = $this->connection->prepare($query);
			$sth->execute( $parameters );
			
			if(self::$profiling)
			{
				$this->recordQueryProfile($query, $timer);
			}
			return $sth;
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	protected function initProfiler()
	{
		require_once "Timer.php";
		return new Piwik_Timer;
	}
	
	protected function recordQueryProfile( $query, $timer )
	{
		if(!isset($this->queriesProfiling[$query])) $this->queriesProfiling[$query] = array('sum_time_ms' => 0, 'count' => 0);
		$time = $timer->getTimeMs(2);
		$time += $this->queriesProfiling[$query]['sum_time_ms'];
		$count = $this->queriesProfiling[$query]['count'] + 1;
		$this->queriesProfiling[$query]	= array('sum_time_ms' => $time, 'count' => $count);
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
	 * When destroyed, if SQL profiled enabled, logs the SQL profiling information
	 */
	public function recordProfiling()
	{
		if(is_null($this->connection)) 
		{
			return;
		}
	
		// turn off the profiler so we don't profile the following queries 
		self::$profiling = false;
		
		foreach($this->queriesProfiling as $query => $info)
		{
			$time = $info['sum_time_ms'];
			$count = $info['count'];

			$queryProfiling = "INSERT INTO ".$this->prefixTable('log_profiling')."
						(query,count,sum_time_ms) VALUES (?,$count,$time)
						ON DUPLICATE KEY 
							UPDATE count=count+$count,sum_time_ms=sum_time_ms+$time";
			$this->query($queryProfiling,array($query));
		}
		
		// turn back on profiling
		self::$profiling = true;
	}
}


