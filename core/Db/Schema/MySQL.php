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
 * MySQL schema
 * 
 * @package Piwik
 */
class Piwik_Db_Schema_MySQL extends Piwik_Db_Schema
{
	static public function getTableCreateSql( $tableName )
	{
		$tables = Piwik::getTablesCreateSql();
		
		if(!isset($tables[$tableName]))
		{
			throw new Exception("The table '$tableName' SQL creation code couldn't be found.");
		}
		
		return $tables[$tableName];
	}
	
	static public function getTablesCreateSql()
	{
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tables = array(
			'user' => "CREATE TABLE {$prefixTables}user (
						  login VARCHAR(100) NOT NULL,
						  password CHAR(32) NOT NULL,
						  alias VARCHAR(45) NOT NULL,
						  email VARCHAR(100) NOT NULL,
						  token_auth CHAR(32) NOT NULL,
						  date_registered TIMESTAMP NULL,
						  PRIMARY KEY(login),
						  UNIQUE KEY uniq_keytoken(token_auth)
						)  DEFAULT CHARSET=utf8 
			",
			
			'access' => "CREATE TABLE {$prefixTables}access (
						  login VARCHAR(100) NOT NULL,
						  idsite INTEGER UNSIGNED NOT NULL,
						  access VARCHAR(10) NULL,
						  PRIMARY KEY(login, idsite)
						)  DEFAULT CHARSET=utf8 
			",
			
			'site' => "CREATE TABLE {$prefixTables}site (
						  idsite INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						  name VARCHAR(90) NOT NULL,
						  main_url VARCHAR(255) NOT NULL,
  						  ts_created TIMESTAMP NULL,
  						  timezone VARCHAR( 50 ) NOT NULL,
  						  currency CHAR( 3 ) NOT NULL,
  						  excluded_ips TEXT NOT NULL,
  						  excluded_parameters VARCHAR ( 255 ) NOT NULL,
						  PRIMARY KEY(idsite)
						)  DEFAULT CHARSET=utf8 
			",
			
			'site_url' => "CREATE TABLE {$prefixTables}site_url (
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  url VARCHAR(255) NOT NULL,
							  PRIMARY KEY(idsite, url)
						)  DEFAULT CHARSET=utf8 
			",
			
			'goal' => "	CREATE TABLE `{$prefixTables}goal` (
							  `idsite` int(11) NOT NULL,
							  `idgoal` int(11) NOT NULL,
							  `name` varchar(50) NOT NULL,
							  `match_attribute` varchar(20) NOT NULL,
							  `pattern` varchar(255) NOT NULL,
							  `pattern_type` varchar(10) NOT NULL,
							  `case_sensitive` tinyint(4) NOT NULL,
							  `revenue` float NOT NULL,
							  `deleted` tinyint(4) NOT NULL default '0',
							  PRIMARY KEY  (`idsite`,`idgoal`)
							)  DEFAULT CHARSET=utf8 
			",
			
			'logger_message' => "CREATE TABLE {$prefixTables}logger_message (
									  idlogger_message INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  timestamp TIMESTAMP NULL,
									  message TEXT NULL,
									  PRIMARY KEY(idlogger_message)
									)  DEFAULT CHARSET=utf8 
			",
			
			'logger_api_call' => "CREATE TABLE {$prefixTables}logger_api_call (
									  idlogger_api_call INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  class_name VARCHAR(255) NULL,
									  method_name VARCHAR(255) NULL,
									  parameter_names_default_values TEXT NULL,
									  parameter_values TEXT NULL,
									  execution_time FLOAT NULL,
									  caller_ip INT UNSIGNED NULL,
									  timestamp TIMESTAMP NULL,
									  returned_value TEXT NULL,
									  PRIMARY KEY(idlogger_api_call)
									)  DEFAULT CHARSET=utf8 
			",
			
			'logger_error' => "CREATE TABLE {$prefixTables}logger_error (
									  idlogger_error INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  timestamp TIMESTAMP NULL,
									  message TEXT NULL,
									  errno INTEGER UNSIGNED NULL,
									  errline INTEGER UNSIGNED NULL,
									  errfile VARCHAR(255) NULL,
									  backtrace TEXT NULL,
									  PRIMARY KEY(idlogger_error)
									) DEFAULT CHARSET=utf8 
			",
			
			'logger_exception' => "CREATE TABLE {$prefixTables}logger_exception (
									  idlogger_exception INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
									  timestamp TIMESTAMP NULL,
									  message TEXT NULL,
									  errno INTEGER UNSIGNED NULL,
									  errline INTEGER UNSIGNED NULL,
									  errfile VARCHAR(255) NULL,
									  backtrace TEXT NULL,
									  PRIMARY KEY(idlogger_exception)
									)  DEFAULT CHARSET=utf8 
			",
			
			
			'log_action' => "CREATE TABLE {$prefixTables}log_action (
									  idaction INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
									  name TEXT,
									  hash INTEGER(10) UNSIGNED NOT NULL,
  									  type TINYINT UNSIGNED NULL,
									  PRIMARY KEY(idaction),
									  INDEX index_type_hash (type, hash)
						)  DEFAULT CHARSET=utf8 
			",
					
			'log_visit' => "CREATE TABLE {$prefixTables}log_visit (
							  idvisit INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  visitor_localtime TIME NOT NULL,
							  visitor_idcookie CHAR(32) NOT NULL,
							  visitor_returning TINYINT(1) NOT NULL,
							  visit_first_action_time DATETIME NOT NULL,
							  visit_last_action_time DATETIME NOT NULL,
							  visit_server_date DATE NOT NULL, 
							  visit_exit_idaction_url INTEGER(11) NOT NULL,
							  visit_entry_idaction_url INTEGER(11) NOT NULL,
							  visit_total_actions SMALLINT(5) UNSIGNED NOT NULL,
							  visit_total_time SMALLINT(5) UNSIGNED NOT NULL,
							  visit_goal_converted TINYINT(1) NOT NULL,
							  referer_type INTEGER UNSIGNED NULL,
							  referer_name VARCHAR(70) NULL,
							  referer_url TEXT NOT NULL,
							  referer_keyword VARCHAR(255) NULL,
							  config_md5config CHAR(32) NOT NULL,
							  config_os CHAR(3) NOT NULL,
							  config_browser_name VARCHAR(10) NOT NULL,
							  config_browser_version VARCHAR(20) NOT NULL,
							  config_resolution VARCHAR(9) NOT NULL,
							  config_pdf TINYINT(1) NOT NULL,
							  config_flash TINYINT(1) NOT NULL,
							  config_java TINYINT(1) NOT NULL,
							  config_director TINYINT(1) NOT NULL,
							  config_quicktime TINYINT(1) NOT NULL,
							  config_realplayer TINYINT(1) NOT NULL,
							  config_windowsmedia TINYINT(1) NOT NULL,
							  config_gears TINYINT(1) NOT NULL,
							  config_silverlight TINYINT(1) NOT NULL,
							  config_cookie TINYINT(1) NOT NULL,
							  location_ip INT UNSIGNED NOT NULL,
							  location_browser_lang VARCHAR(20) NOT NULL,
							  location_country CHAR(3) NOT NULL,
							  location_continent CHAR(3) NOT NULL,
							  PRIMARY KEY(idvisit),
							  INDEX index_idsite_idvisit (idsite, idvisit),
							  INDEX index_idsite_date_config (idsite, visit_server_date, config_md5config(8)),
							  INDEX index_idsite_datetime_config (idsite, visit_last_action_time, config_md5config(8))
							)  DEFAULT CHARSET=utf8 
			",		
			
			'log_conversion' => "CREATE TABLE `{$prefixTables}log_conversion` (
									  idvisit int(10) unsigned NOT NULL,
									  idsite int(10) unsigned NOT NULL,
									  visitor_idcookie char(32) NOT NULL,
									  server_time datetime NOT NULL,
									  idaction_url int(11) default NULL,
									  idlink_va int(11) default NULL,
									  referer_idvisit int(10) unsigned default NULL,
									  referer_visit_server_date date default NULL,
									  referer_type int(10) unsigned default NULL,
									  referer_name varchar(70) default NULL,
									  referer_keyword varchar(255) default NULL,
									  visitor_returning tinyint(1) NOT NULL,
									  location_country char(3) NOT NULL,
									  location_continent char(3) NOT NULL,
									  url text NOT NULL,
									  idgoal int(10) unsigned NOT NULL,
									  revenue float default NULL,
									  PRIMARY KEY  (idvisit, idgoal),
									  INDEX index_idsite_datetime ( idsite, server_time )
									) DEFAULT CHARSET=utf8 
			",
							
			'log_link_visit_action' => "CREATE TABLE {$prefixTables}log_link_visit_action (
											  idlink_va INTEGER(11) NOT NULL AUTO_INCREMENT,
											  idvisit INTEGER(10) UNSIGNED NOT NULL,
											  idaction_url INTEGER(10) UNSIGNED NOT NULL,
											  idaction_url_ref INTEGER(10) UNSIGNED NOT NULL,
											  idaction_name INTEGER(10) UNSIGNED,
											  time_spent_ref_action INTEGER(10) UNSIGNED NOT NULL,
											  PRIMARY KEY(idlink_va),
											  INDEX index_idvisit(idvisit)
											)  DEFAULT CHARSET=utf8 
			",
		
			'log_profiling' => "CREATE TABLE {$prefixTables}log_profiling (
								  query TEXT NOT NULL,
								  count INTEGER UNSIGNED NULL,
								  sum_time_ms FLOAT NULL,
								  UNIQUE KEY query(query(100))
								)  DEFAULT CHARSET=utf8 
			",
			
			'option' => "CREATE TABLE `{$prefixTables}option` (
								option_name VARCHAR( 64 ) NOT NULL,
								option_value LONGTEXT NOT NULL,
								autoload TINYINT NOT NULL DEFAULT '1',
								PRIMARY KEY ( option_name )
								)  DEFAULT CHARSET=utf8 
			",
								
			'archive_numeric'	=> "CREATE TABLE {$prefixTables}archive_numeric (
									  idarchive INTEGER UNSIGNED NOT NULL,
									  name VARCHAR(255) NOT NULL,
									  idsite INTEGER UNSIGNED NULL,
									  date1 DATE NULL,
								  	  date2 DATE NULL,
									  period TINYINT UNSIGNED NULL,
								  	  ts_archived DATETIME NULL,
								  	  value FLOAT NULL,
									  PRIMARY KEY(idarchive, name),
									  INDEX index_idsite_dates_period(idsite, date1, date2, period, ts_archived),
									  INDEX index_period_archived(period, ts_archived)
									)  DEFAULT CHARSET=utf8 
			",
			'archive_blob'	=> "CREATE TABLE {$prefixTables}archive_blob (
									  idarchive INTEGER UNSIGNED NOT NULL,
									  name VARCHAR(255) NOT NULL,
									  idsite INTEGER UNSIGNED NULL,
									  date1 DATE NULL,
									  date2 DATE NULL,
									  period TINYINT UNSIGNED NULL,
									  ts_archived DATETIME NULL,
									  value MEDIUMBLOB NULL,
									  PRIMARY KEY(idarchive, name),
									  INDEX index_period_archived(period, ts_archived)
									)  DEFAULT CHARSET=utf8 
			",
		);
		return $tables;
	}

	/**
	 * Names of all the prefixed tables in piwik
	 * Doesn't use the DB 
	 *
	 * @return array Table names
	 */
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
	
	static $tablesInstalled = null;

	/**
	 * Get list of tables installed
	 *
	 * @param bool $forceReload Invalidate cache
	 * @param string $idSite
	 * @return array Tables installed
	 */	
	static public function getTablesInstalled($forceReload = true,  $idSite = null)
	{
		if(is_null(self::$tablesInstalled)
			|| $forceReload === true)
		{
			$db = Zend_Registry::get('db');
			$config = Zend_Registry::get('config');
			$prefixTables = $config->database->tables_prefix;
			
			$allTables = $db->fetchCol("SHOW TABLES");
			
			// all the tables to be installed
			$allMyTables = self::getTablesNames();
			
			// we get the intersection between all the tables in the DB and the tables to be installed
			$tablesInstalled = array_intersect($allMyTables, $allTables);
			
			// at this point we have only the piwik tables which is good
			// but we still miss the piwik generated tables (using the class Piwik_TablePartitioning)
			$idSiteInSql = "no";
			if(!is_null($idSite))
			{
				$idSiteInSql = $idSite;
			}
			$allArchiveNumeric = $db->fetchCol("/* SHARDING_ID_SITE = ".$idSiteInSql." */ 
												SHOW TABLES LIKE '".$prefixTables."archive_numeric%'");
			$allArchiveBlob = $db->fetchCol("/* SHARDING_ID_SITE = ".$idSiteInSql." */ 
												SHOW TABLES LIKE '".$prefixTables."archive_blob%'");
					
			$allTablesReallyInstalled = array_merge($tablesInstalled, $allArchiveNumeric, $allArchiveBlob);
			
			self::$tablesInstalled = $allTablesReallyInstalled;
		}
		return 	self::$tablesInstalled;
	}

	/**
	 * Create database
	 */
	static public function createDatabase( $dbName = null )
	{
		if(is_null($dbName))
		{
			$dbName = Zend_Registry::get('config')->database->dbname;
		}
		Piwik_Exec("CREATE DATABASE IF NOT EXISTS ".$dbName);
	}

	/**
	 * Drop database
	 */
	static public function dropDatabase()
	{
		$dbName = Zend_Registry::get('config')->database->dbname;
		Piwik_Exec("DROP DATABASE IF EXISTS " . $dbName);

	}

	/**
	 * Drop specific tables
	 */	
	static public function dropTables( $doNotDelete = array() )
	{
		$tablesAlreadyInstalled = self::getTablesInstalled();
		$db = Zend_Registry::get('db');
		
		$doNotDeletePattern = '/('.implode('|',$doNotDelete).')/';
		
		foreach($tablesAlreadyInstalled as $tableName)
		{
			
			if( count($doNotDelete) == 0
				|| (!in_array($tableName,$doNotDelete)
					&& !preg_match($doNotDeletePattern,$tableName)
					)
				)
			{
				$db->query("DROP TABLE `$tableName`");
			}
		}			
	}
	
	/**
	 * Creates an entry in the User table for the "anonymous" user. 
	 */
	static public function createAnonymousUser()
	{
		// The anonymous user is the user that is assigned by default 
		// note that the token_auth value is anonymous, which is assigned by default as well in the Login plugin
		$db = Zend_Registry::get('db');
		$db->query("INSERT INTO ". Piwik_Common::prefixTable("user") . " 
					VALUES ( 'anonymous', '', 'anonymous', 'anonymous@example.org', 'anonymous', '".Piwik_Date::factory('now')->getDatetime()."' );" );
	}

	/**
	 * Create all tables
	 */	
	static public function createTables()
	{
		$db = Zend_Registry::get('db');
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;

		$tablesAlreadyInstalled = self::getTablesInstalled();
		$tablesToCreate = self::getTablesCreateSql();
		unset($tablesToCreate['archive_blob']);
		unset($tablesToCreate['archive_numeric']);

		foreach($tablesToCreate as $tableName => $tableSql)
		{
			$tableName = $prefixTables . $tableName;
			if(!in_array($tableName, $tablesAlreadyInstalled))
			{
				$db->query( $tableSql );
			}
		}
	}
}
