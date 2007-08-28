<?php

/**
 * Simple database PDO wrapper
 * 
 */
class Piwik_LogStats_Db 
{
	private $connection;
	private $username;
	private $password;
	//TODO test that in production is false
	static private $profiling = false;
	protected $queriesProfiling;
	
	public function __construct( $host, $username, $password, $dbname) 
	{
		$this->dsn = "mysql:dbname=$dbname;host=$host";
		$this->username = $username;
		$this->password = $password;
	}

	static public function enableProfiling()
	{
		self::$profiling = true;
	}
	static public function disableProfiling()
	{
		self::$profiling = false;
	}
	
	
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

	public function prefixTable( $suffix )
	{
		static $prefix;
		if (!isset($prefix)) {
			$prefix = Piwik_LogStats_Config::getInstance()->database['tables_prefix'];
		}		
		return $prefix . $suffix;
	}
	
	public function fetchAll( $query, $parameters )
	{
		try {
			$sth = $this->query( $query, $parameters );
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	public function fetch( $query, $parameters )
	{
		try {
			$sth = $this->query( $query, $parameters );
			return $sth->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
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
	
	public function lastInsertId()
	{
		return  $this->connection->lastInsertId();
	}
	
	public function __destruct()
	{
		if(self::$profiling)
		{
			self::$profiling= false;
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
			self::$profiling= true;
		}
	}
}


