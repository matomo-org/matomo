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
	
	public function __construct( $host, $username, $password, $dbname) 
	{
		$this->dsn = "mysql:dbname=$dbname;host=$host";
		$this->username = $username;
		$this->password = $password;
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
		$prefix = Piwik_LogStats_Config::getInstance()->database['tables_prefix'];
		
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
			$sth = $this->connection->prepare($query);
			$sth->execute( $parameters );
			return $sth;
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	public function lastInsertId()
	{
		return  $this->connection->lastInsertId();
	}
}
?>
