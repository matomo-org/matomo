<?php

class Piwik
{
	const CLASSES_PREFIX = "Piwik_";
	
	static public function log($message, $priority = Zend_Log::NOTICE)
	{
		Zend_Registry::get('logger')->log($message . PHP_EOL, $priority);
	}
	
	static public function getTablesCreateSql()
	{
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tables = array(
			'user' => "CREATE TABLE {$prefixTables}user (
						  login VARCHAR(20) NOT NULL,
						  password CHAR(32) NOT NULL,
						  alias VARCHAR(45) NOT NULL,
						  email VARCHAR(100) NOT NULL,
						  token_auth CHAR(32) NOT NULL,
						  date_registered TIMESTAMP NOT NULL,
						  PRIMARY KEY(login)
						)
			",
			
			'role' => "CREATE TABLE {$prefixTables}role (
						  login VARCHAR(20) NOT NULL,
						  idsite INTEGER UNSIGNED NOT NULL,
						  role VARCHAR(10) NULL,
						  PRIMARY KEY(login, idsite)
						)
			",
			
			'site' => "CREATE TABLE {$prefixTables}site (
						  idsite INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						  name VARCHAR(90) NOT NULL,
						  main_url VARCHAR(255) NOT NULL,
						  PRIMARY KEY(idsite)
						)
			",
			
			'site_url' => "CREATE TABLE {$prefixTables}site_url (
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  url VARCHAR(255) NOT NULL,
							  PRIMARY KEY(idsite, url)
						)
			",
			
			);
		return $tables;
	}
	
	static public function prefixClass( $class )
	{
		if(substr_count($class, Piwik::CLASSES_PREFIX) > 0)
		{
			return $class;
		}
		return Piwik::CLASSES_PREFIX.$class;
	}
	
	static public function prefixTable( $table )
	{
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		return $prefixTables . $table;
	}
	
	static public function getTablesNames()
	{
		$aTables = array_keys(self::getTablesCreateSql());
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$return = array();
		foreach($aTables as $table)
		{
			$return[] = $prefixTables.$table;
		}
		return $return;
	}
	
	static public function getTablesInstalled()
	{
		$allMyTables = self::getTablesNames();

		$db = Zend_Registry::get('db');
		$allTables = $db->fetchCol('SHOW TABLES');
		
		$intersect = array_intersect($allTables, $allMyTables);
		
		return $intersect;		
	}
	
	static public function createDatabase()
	{
		$db = Zend_Registry::get('db');
		$dbName = Zend_Registry::get('config')->database->dbname;
		$db->query("CREATE DATABASE IF NOT EXISTS ".$dbName);
	}
	
	static public function dropDatabase()
	{
		$db = Zend_Registry::get('db');
		$dbName = Zend_Registry::get('config')->database->dbname;
		$db->query("DROP DATABASE IF EXISTS ".$dbName);
	}
	
	
	static public function createDatabaseObject()
	{
		$config = Zend_Registry::get('config');
		$dbOptions = $config->database->toArray();
		$db = Zend_Db::factory($config->database->adapter, $dbOptions);
		Zend_Db_Table::setDefaultAdapter($db);
		Zend_Registry::set('db', $db);
	}

	static public function createLogObject()
	{
		$log = new Piwik_Log;
	}
	static public function createConfigObject()
	{
		$config = new Piwik_Config;
		
		assert(count($config) != 0);
	}

	static public function createTables()
	{
		$db = Zend_Registry::get('db');
		
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		
		//Piwik::log("Creating ". implode(", ", self::getTablesNames()));
		
		$tablesToCreate = self::getTablesCreateSql();
		
		$tablesAlreadyInstalled = self::getTablesInstalled();
		
		foreach($tablesToCreate as $tableName => $tableSql)
		{
			$tableName = $prefixTables . $tableName;

			// if the table doesn't exist already
			if(!in_array($tableName, $tablesAlreadyInstalled))
			{
				$db->query( $tableSql );
			}
		}
	}
	
	static public function uninstall()
	{
		// delete tables
				//create tables
		$db = Zend_Registry::get('db');
		
		Piwik::log("Droping ". implode(", ", self::getTablesNames()));
		
		$db->query( "DROP TABLE IF EXISTS ". implode(", ", self::getTablesNames()) );
	}
}
?>
