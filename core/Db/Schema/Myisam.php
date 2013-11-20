<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Db\Schema;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db\SchemaInterface;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * MySQL schema
 *
 * @package Piwik
 * @subpackage Piwik_Db
 */
class Myisam implements SchemaInterface
{
    /**
     * Is this MySQL storage engine available?
     *
     * @param string $engineName
     * @return bool  True if available and enabled; false otherwise
     */
    static private function hasStorageEngine($engineName)
    {
        $db = Db::get();
        $allEngines = $db->fetchAssoc('SHOW ENGINES');
        if (array_key_exists($engineName, $allEngines)) {
            $support = $allEngines[$engineName]['Support'];
            return $support == 'DEFAULT' || $support == 'YES';
        }
        return false;
    }

    /**
     * Is this schema available?
     *
     * @return bool  True if schema is available; false otherwise
     */
    static public function isAvailable()
    {
        return self::hasStorageEngine('MyISAM');
    }

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql()
    {
        $config = Config::getInstance();
        $prefixTables = $config->database['tables_prefix'];
        $tables = array(
            'user'                  => "CREATE TABLE {$prefixTables}user (
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

            'access'                => "CREATE TABLE {$prefixTables}access (
						  login VARCHAR(100) NOT NULL,
						  idsite INTEGER UNSIGNED NOT NULL,
						  access VARCHAR(10) NULL,
						  PRIMARY KEY(login, idsite)
						)  DEFAULT CHARSET=utf8
			",

            'site'                  => "CREATE TABLE {$prefixTables}site (
						  idsite INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						  name VARCHAR(90) NOT NULL,
						  main_url VARCHAR(255) NOT NULL,
  						  ts_created TIMESTAMP NULL,
  						  ecommerce TINYINT DEFAULT 0,
  						  sitesearch TINYINT DEFAULT 1,
  						  sitesearch_keyword_parameters TEXT NOT NULL,
  						  sitesearch_category_parameters TEXT NOT NULL,
  						  timezone VARCHAR( 50 ) NOT NULL,
  						  currency CHAR( 3 ) NOT NULL,
  						  excluded_ips TEXT NOT NULL,
  						  excluded_parameters TEXT NOT NULL,
  						  excluded_user_agents TEXT NOT NULL,
  						  `group` VARCHAR(250) NOT NULL,
  						  `type` VARCHAR(255) NOT NULL,
  						  keep_url_fragment TINYINT NOT NULL DEFAULT 0,
						  PRIMARY KEY(idsite)
						)  DEFAULT CHARSET=utf8
			",

            'site_url'              => "CREATE TABLE {$prefixTables}site_url (
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  url VARCHAR(255) NOT NULL,
							  PRIMARY KEY(idsite, url)
						)  DEFAULT CHARSET=utf8
			",

            'goal'                  => "	CREATE TABLE `{$prefixTables}goal` (
							  `idsite` int(11) NOT NULL,
							  `idgoal` int(11) NOT NULL,
							  `name` varchar(50) NOT NULL,
							  `match_attribute` varchar(20) NOT NULL,
							  `pattern` varchar(255) NOT NULL,
							  `pattern_type` varchar(10) NOT NULL,
							  `case_sensitive` tinyint(4) NOT NULL,
							  `allow_multiple` tinyint(4) NOT NULL,
							  `revenue` float NOT NULL,
							  `deleted` tinyint(4) NOT NULL default '0',
							  PRIMARY KEY  (`idsite`,`idgoal`)
							)  DEFAULT CHARSET=utf8
			",

            'logger_message'        => "CREATE TABLE {$prefixTables}logger_message (
									  idlogger_message INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                                      tag VARCHAR(50) NULL,
									  timestamp TIMESTAMP NULL,
                                      level VARCHAR(16) NULL,
									  message TEXT NULL,
									  PRIMARY KEY(idlogger_message)
									)  DEFAULT CHARSET=utf8
			",


            'log_action'            => "CREATE TABLE {$prefixTables}log_action (
									  idaction INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
									  name TEXT,
									  hash INTEGER(10) UNSIGNED NOT NULL,
  									  type TINYINT UNSIGNED NULL,
  									  url_prefix TINYINT(2) NULL,
									  PRIMARY KEY(idaction),
									  INDEX index_type_hash (type, hash)
						)  DEFAULT CHARSET=utf8
			",

            'log_visit'             => "CREATE TABLE {$prefixTables}log_visit (
							  idvisit INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
							  idsite INTEGER(10) UNSIGNED NOT NULL,
							  idvisitor BINARY(8) NOT NULL,
							  visitor_localtime TIME NOT NULL,
							  visitor_returning TINYINT(1) NOT NULL,
							  visitor_count_visits SMALLINT(5) UNSIGNED NOT NULL,
							  visitor_days_since_last SMALLINT(5) UNSIGNED NOT NULL,
							  visitor_days_since_order SMALLINT(5) UNSIGNED NOT NULL,
							  visitor_days_since_first SMALLINT(5) UNSIGNED NOT NULL,
							  visit_first_action_time DATETIME NOT NULL,
							  visit_last_action_time DATETIME NOT NULL,
							  visit_exit_idaction_url INTEGER(11) UNSIGNED NULL DEFAULT 0,
							  visit_exit_idaction_name INTEGER(11) UNSIGNED NOT NULL,
							  visit_entry_idaction_url INTEGER(11) UNSIGNED NOT NULL,
							  visit_entry_idaction_name INTEGER(11) UNSIGNED NOT NULL,
							  visit_total_actions SMALLINT(5) UNSIGNED NOT NULL,
							  visit_total_searches SMALLINT(5) UNSIGNED NOT NULL,
							  visit_total_events SMALLINT(5) UNSIGNED NOT NULL,
							  visit_total_time SMALLINT(5) UNSIGNED NOT NULL,
							  visit_goal_converted TINYINT(1) NOT NULL,
							  visit_goal_buyer TINYINT(1) NOT NULL,
							  referer_type TINYINT(1) UNSIGNED NULL,
							  referer_name VARCHAR(70) NULL,
							  referer_url TEXT NOT NULL,
							  referer_keyword VARCHAR(255) NULL,
							  config_id BINARY(8) NOT NULL,
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
							  location_ip VARBINARY(16) NOT NULL,
							  location_browser_lang VARCHAR(20) NOT NULL,
							  location_country CHAR(3) NOT NULL,
							  location_region char(2) DEFAULT NULL,
							  location_city varchar(255) DEFAULT NULL,
							  location_latitude float(10, 6) DEFAULT NULL,
							  location_longitude float(10, 6) DEFAULT NULL,
							  custom_var_k1 VARCHAR(200) DEFAULT NULL,
							  custom_var_v1 VARCHAR(200) DEFAULT NULL,
							  custom_var_k2 VARCHAR(200) DEFAULT NULL,
							  custom_var_v2 VARCHAR(200) DEFAULT NULL,
							  custom_var_k3 VARCHAR(200) DEFAULT NULL,
							  custom_var_v3 VARCHAR(200) DEFAULT NULL,
							  custom_var_k4 VARCHAR(200) DEFAULT NULL,
							  custom_var_v4 VARCHAR(200) DEFAULT NULL,
							  custom_var_k5 VARCHAR(200) DEFAULT NULL,
							  custom_var_v5 VARCHAR(200) DEFAULT NULL,
							  PRIMARY KEY(idvisit),
							  INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time),
							  INDEX index_idsite_datetime (idsite, visit_last_action_time),
							  INDEX index_idsite_idvisitor (idsite, idvisitor)
							)  DEFAULT CHARSET=utf8
			",

            'log_conversion_item'   => "CREATE TABLE `{$prefixTables}log_conversion_item` (
												  idsite int(10) UNSIGNED NOT NULL,
										  		  idvisitor BINARY(8) NOT NULL,
										          server_time DATETIME NOT NULL,
												  idvisit INTEGER(10) UNSIGNED NOT NULL,
												  idorder varchar(100) NOT NULL,

												  idaction_sku INTEGER(10) UNSIGNED NOT NULL,
												  idaction_name INTEGER(10) UNSIGNED NOT NULL,
												  idaction_category INTEGER(10) UNSIGNED NOT NULL,
												  idaction_category2 INTEGER(10) UNSIGNED NOT NULL,
												  idaction_category3 INTEGER(10) UNSIGNED NOT NULL,
												  idaction_category4 INTEGER(10) UNSIGNED NOT NULL,
												  idaction_category5 INTEGER(10) UNSIGNED NOT NULL,
												  price FLOAT NOT NULL,
												  quantity INTEGER(10) UNSIGNED NOT NULL,
												  deleted TINYINT(1) UNSIGNED NOT NULL,

												  PRIMARY KEY(idvisit, idorder, idaction_sku),
										          INDEX index_idsite_servertime ( idsite, server_time )
												)  DEFAULT CHARSET=utf8
			",

            'log_conversion'        => "CREATE TABLE `{$prefixTables}log_conversion` (
									  idvisit int(10) unsigned NOT NULL,
									  idsite int(10) unsigned NOT NULL,
									  idvisitor BINARY(8) NOT NULL,
									  server_time datetime NOT NULL,
									  idaction_url int(11) default NULL,
									  idlink_va int(11) default NULL,
									  referer_visit_server_date date default NULL,
									  referer_type int(10) unsigned default NULL,
									  referer_name varchar(70) default NULL,
									  referer_keyword varchar(255) default NULL,
									  visitor_returning tinyint(1) NOT NULL,
        							  visitor_count_visits SMALLINT(5) UNSIGNED NOT NULL,
        							  visitor_days_since_first SMALLINT(5) UNSIGNED NOT NULL,
							  		  visitor_days_since_order SMALLINT(5) UNSIGNED NOT NULL,
									  location_country char(3) NOT NULL,
									  location_region char(2) DEFAULT NULL,
									  location_city varchar(255) DEFAULT NULL,
									  location_latitude float(10, 6) DEFAULT NULL,
									  location_longitude float(10, 6) DEFAULT NULL,
									  url text NOT NULL,
									  idgoal int(10) NOT NULL,
									  buster int unsigned NOT NULL,

									  idorder varchar(100) default NULL,
									  items SMALLINT UNSIGNED DEFAULT NULL,
									  revenue float default NULL,
									  revenue_subtotal float default NULL,
									  revenue_tax float default NULL,
									  revenue_shipping float default NULL,
									  revenue_discount float default NULL,

									  custom_var_k1 VARCHAR(200) DEFAULT NULL,
        							  custom_var_v1 VARCHAR(200) DEFAULT NULL,
        							  custom_var_k2 VARCHAR(200) DEFAULT NULL,
        							  custom_var_v2 VARCHAR(200) DEFAULT NULL,
        							  custom_var_k3 VARCHAR(200) DEFAULT NULL,
        							  custom_var_v3 VARCHAR(200) DEFAULT NULL,
        							  custom_var_k4 VARCHAR(200) DEFAULT NULL,
        							  custom_var_v4 VARCHAR(200) DEFAULT NULL,
        							  custom_var_k5 VARCHAR(200) DEFAULT NULL,
        							  custom_var_v5 VARCHAR(200) DEFAULT NULL,
									  PRIMARY KEY (idvisit, idgoal, buster),
									  UNIQUE KEY unique_idsite_idorder (idsite, idorder),
									  INDEX index_idsite_datetime ( idsite, server_time )
									) DEFAULT CHARSET=utf8
			",

            'log_link_visit_action' => "CREATE TABLE {$prefixTables}log_link_visit_action (
											  idlink_va INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
									          idsite int(10) UNSIGNED NOT NULL,
									  		  idvisitor BINARY(8) NOT NULL,
									          server_time DATETIME NOT NULL,
											  idvisit INTEGER(10) UNSIGNED NOT NULL,
											  idaction_url INTEGER(10) UNSIGNED DEFAULT NULL,
											  idaction_url_ref INTEGER(10) UNSIGNED NULL DEFAULT 0,
											  idaction_name INTEGER(10) UNSIGNED,
											  idaction_name_ref INTEGER(10) UNSIGNED NOT NULL,
											  idaction_event_category INTEGER(10) UNSIGNED DEFAULT NULL,
											  idaction_event_action INTEGER(10) UNSIGNED DEFAULT NULL,
											  time_spent_ref_action INTEGER(10) UNSIGNED NOT NULL,
											  custom_var_k1 VARCHAR(200) DEFAULT NULL,
											  custom_var_v1 VARCHAR(200) DEFAULT NULL,
											  custom_var_k2 VARCHAR(200) DEFAULT NULL,
											  custom_var_v2 VARCHAR(200) DEFAULT NULL,
											  custom_var_k3 VARCHAR(200) DEFAULT NULL,
											  custom_var_v3 VARCHAR(200) DEFAULT NULL,
											  custom_var_k4 VARCHAR(200) DEFAULT NULL,
											  custom_var_v4 VARCHAR(200) DEFAULT NULL,
											  custom_var_k5 VARCHAR(200) DEFAULT NULL,
											  custom_var_v5 VARCHAR(200) DEFAULT NULL,
											  custom_float FLOAT NULL DEFAULT NULL,
											  PRIMARY KEY(idlink_va),
											  INDEX index_idvisit(idvisit),
									          INDEX index_idsite_servertime ( idsite, server_time )
											)  DEFAULT CHARSET=utf8
			",

            'log_profiling'         => "CREATE TABLE {$prefixTables}log_profiling (
								  query TEXT NOT NULL,
								  count INTEGER UNSIGNED NULL,
								  sum_time_ms FLOAT NULL,
								  UNIQUE KEY query(query(100))
								)  DEFAULT CHARSET=utf8
			",

            'option'                => "CREATE TABLE `{$prefixTables}option` (
								option_name VARCHAR( 255 ) NOT NULL,
								option_value LONGTEXT NOT NULL,
								autoload TINYINT NOT NULL DEFAULT '1',
								PRIMARY KEY ( option_name ),
								INDEX autoload( autoload )
								)  DEFAULT CHARSET=utf8
			",

            'session'               => "CREATE TABLE {$prefixTables}session (
								id CHAR(32) NOT NULL,
								modified INTEGER,
								lifetime INTEGER,
								data TEXT,
								PRIMARY KEY ( id )
								)  DEFAULT CHARSET=utf8
			",

            'archive_numeric'       => "CREATE TABLE {$prefixTables}archive_numeric (
									  idarchive INTEGER UNSIGNED NOT NULL,
									  name VARCHAR(255) NOT NULL,
									  idsite INTEGER UNSIGNED NULL,
									  date1 DATE NULL,
								  	  date2 DATE NULL,
									  period TINYINT UNSIGNED NULL,
								  	  ts_archived DATETIME NULL,
								  	  value DOUBLE NULL,
									  PRIMARY KEY(idarchive, name),
									  INDEX index_idsite_dates_period(idsite, date1, date2, period, ts_archived),
									  INDEX index_period_archived(period, ts_archived)
									)  DEFAULT CHARSET=utf8
			",

            'archive_blob'          => "CREATE TABLE {$prefixTables}archive_blob (
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
     * Get the SQL to create a specific Piwik table
     *
     * @param string $tableName
     * @throws Exception
     * @return string  SQL
     */
    public function getTableCreateSql($tableName)
    {
        $tables = DbHelper::getTablesCreateSql();

        if (!isset($tables[$tableName])) {
            throw new Exception("The table '$tableName' SQL creation code couldn't be found.");
        }

        return $tables[$tableName];
    }

    /**
     * Names of all the prefixed tables in piwik
     * Doesn't use the DB
     *
     * @return array  Table names
     */
    public function getTablesNames()
    {
        $aTables = array_keys($this->getTablesCreateSql());
        $config = Config::getInstance();
        $prefixTables = $config->database['tables_prefix'];
        $return = array();
        foreach ($aTables as $table) {
            $return[] = $prefixTables . $table;
        }
        return $return;
    }

    private $tablesInstalled = null;

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload Invalidate cache
     * @return array  installed Tables
     */
    public function getTablesInstalled($forceReload = true)
    {
        if (is_null($this->tablesInstalled)
            || $forceReload === true
        ) {
            $db = Db::get();
            $config = Config::getInstance();
            $prefixTables = $config->database['tables_prefix'];

            // '_' matches any character; force it to be literal
            $prefixTables = str_replace('_', '\_', $prefixTables);

            $allTables = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "%'");

            // all the tables to be installed
            $allMyTables = $this->getTablesNames();

            // we get the intersection between all the tables in the DB and the tables to be installed
            $tablesInstalled = array_intersect($allMyTables, $allTables);

            // at this point we have the static list of core tables, but let's add the monthly archive tables
            $allArchiveNumeric = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_numeric%'");
            $allArchiveBlob = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_blob%'");

            $allTablesReallyInstalled = array_merge($tablesInstalled, $allArchiveNumeric, $allArchiveBlob);

            $this->tablesInstalled = $allTablesReallyInstalled;
        }
        return $this->tablesInstalled;
    }

    /**
     * Checks whether any table exists
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables()
    {
        return count($this->getTablesInstalled()) != 0;
    }

    /**
     * Create database
     *
     * @param string $dbName Name of the database to create
     */
    public function createDatabase($dbName = null)
    {
        if (is_null($dbName)) {
            $dbName = Config::getInstance()->database['dbname'];
        }
        Db::exec("CREATE DATABASE IF NOT EXISTS " . $dbName . " DEFAULT CHARACTER SET utf8");
    }

    /**
     * Drop database
     */
    public function dropDatabase()
    {
        $dbName = Config::getInstance()->database['dbname'];
        Db::exec("DROP DATABASE IF EXISTS " . $dbName);
    }

    /**
     * Create all tables
     */
    public function createTables()
    {
        $db = Db::get();
        $config = Config::getInstance();
        $prefixTables = $config->database['tables_prefix'];

        $tablesAlreadyInstalled = $this->getTablesInstalled();
        $tablesToCreate = $this->getTablesCreateSql();
        unset($tablesToCreate['archive_blob']);
        unset($tablesToCreate['archive_numeric']);

        foreach ($tablesToCreate as $tableName => $tableSql) {
            $tableName = $prefixTables . $tableName;
            if (!in_array($tableName, $tablesAlreadyInstalled)) {
                $db->query($tableSql);
            }
        }
    }

    /**
     * Creates an entry in the User table for the "anonymous" user.
     */
    public function createAnonymousUser()
    {
        // The anonymous user is the user that is assigned by default
        // note that the token_auth value is anonymous, which is assigned by default as well in the Login plugin
        $db = Db::get();
        $db->query("INSERT INTO " . Common::prefixTable("user") . "
					VALUES ( 'anonymous', '', 'anonymous', 'anonymous@example.org', 'anonymous', '" . Date::factory('now')->getDatetime() . "' );");
    }

    /**
     * Truncate all tables
     */
    public function truncateAllTables()
    {
        $tablesAlreadyInstalled = $this->getTablesInstalled($forceReload = true);
        foreach ($tablesAlreadyInstalled as $table) {
            Db::query("TRUNCATE `$table`");
        }
    }

    /**
     * Drop specific tables
     *
     * @param array $doNotDelete Names of tables to not delete
     */
    public function dropTables($doNotDelete = array())
    {
        $tablesAlreadyInstalled = $this->getTablesInstalled();
        $db = Db::get();

        $doNotDeletePattern = '/(' . implode('|', $doNotDelete) . ')/';

        foreach ($tablesAlreadyInstalled as $tableName) {
            if (count($doNotDelete) == 0
                || (!in_array($tableName, $doNotDelete)
                    && !preg_match($doNotDeletePattern, $tableName)
                )
            ) {
                $db->query("DROP TABLE `$tableName`");
            }
        }
    }
}
