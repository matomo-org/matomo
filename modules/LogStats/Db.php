<?php

/**
 * Simple database PDO wrapper.
 * We can't afford to have a dependency with the Zend_Db module in the LogStats module.
 * 
 * @package Piwik_LogStats
 */

class Piwik_LogStats_Db 
{
	private $connection;
	private $username;
	private $password;
	
	static private $profiling = false;

	protected $queriesProfiling;
	
	/**
	 * Builds the DB object
	 */
	public function __construct( $host, $username, $password, $dbname) 
	{
		$this->dsn = "mysql:dbname=$dbname;host=$host";
		$this->username = $username;
		$this->password = $password;
	}

	
	/**
	 * Returns true if the profiler is enabled
	 * Only used by the unit test that tests that the profiler is off on a  production server
	 * 
	 * @return bool 
	 */
	static public function isProfilingEnabled()
	{
		return self::$profiling;
	}
	
	/**
	 * Enables the profiling. 
	 * For each query, saves in the DB the time spent on this query. 
	 * Very useful to see the slow query under heavy load.
	 * You can then use Piwik::printLogStatsSQLProfiling(); 
	 * to display the SQLProfiling report and see which queries take time, etc.
	 */
	static public function enableProfiling()
	{
		self::$profiling = true;
	}
	/** 
	 * Disables the profiling logging.
	 */
	static public function disableProfiling()
	{
		self::$profiling = false;
	}
	
	/**
	 * Connects to the DB
	 */
	public function connect() 
	{
		try {
			$pdoConnect = new PDO($this->dsn, $this->username, $this->password);
			$pdoConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection = $pdoConnect;
		} catch (PDOException $e) {
			throw new Exception("Error connecting database: ".$e->getMessage());
		}
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
	 * Returns an array containing all the rows of a query result.
	 * @see also query()
	 */
	public function fetchAll( $query, $parameters )
	{
		try {
			$sth = $this->query( $query, $parameters );
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	/**
	 * Returns the first row of a query result.
	 * @see query()
	 */
	public function fetch( $query, $parameters )
	{
		try {
			$sth = $this->query( $query, $parameters );
			return $sth->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	/**
	 * Executes a query with bind parameters
	 * 
	 * @param string Query 
	 * @param array Parameters to bind
	 */
	public function query($query, $parameters = array()) 
	{
		try {
			
			if(self::$profiling)
			{
				require_once "Timer.php";
				$t = new Piwik_Timer;
			}
			
			$sth = $this->connection->prepare($query);
			$sth->execute( $parameters );
			
			if(self::$profiling)
			{
				if(!isset($this->queriesProfiling[$query])) $this->queriesProfiling[$query] = array('sum_time_ms' => 0, 'count' => 0);
				$time = $t->getTimeMs(2);
				$time += $this->queriesProfiling[$query]['sum_time_ms'];
				$count = $this->queriesProfiling[$query]['count'] + 1;
				$this->queriesProfiling[$query]	= array('sum_time_ms' => $time, 'count' => $count);
			}
			
			return $sth;
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	/**
	 * Returns the last inserted ID in the DB
	 * Wrapper of PDO::lastInsertId()
	 * @return int
	 */
	public function lastInsertId()
	{
		return  $this->connection->lastInsertId();
	}
	
	/**
	 * When destroyed, log the SQL profiling information
	 */
	public function __destruct()
	{
		if(self::$profiling)
		{
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
}


