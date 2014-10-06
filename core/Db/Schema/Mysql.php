<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db\Schema;

use Exception;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db\SchemaInterface;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * MySQL schema
 */
class Mysql implements SchemaInterface
{
    private $tablesInstalled = null;

    /**
     * Is this MySQL storage engine available?
     *
     * @param string $engineName
     * @return bool  True if available and enabled; false otherwise
     */
    private static function hasStorageEngine($engineName)
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
    public static function isAvailable()
    {
        return self::hasStorageEngine('InnoDB');
    }

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql()
    {
        $engine       = $this->getTableEngine();
        $prefixTables = $this->getTablePrefix();

        $tables = array(
            'user'    => "CREATE TABLE {$prefixTables}user (
                          login VARCHAR(100) NOT NULL,
                          password CHAR(32) NOT NULL,
                          alias VARCHAR(45) NOT NULL,
                          email VARCHAR(100) NOT NULL,
                          token_auth CHAR(32) NOT NULL,
                          superuser_access TINYINT(2) unsigned NOT NULL DEFAULT '0',
                          date_registered TIMESTAMP NULL,
                            PRIMARY KEY(login),
                            UNIQUE KEY uniq_keytoken(token_auth)
                          ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'access'  => "CREATE TABLE {$prefixTables}access (
                          login VARCHAR(100) NOT NULL,
                          idsite INTEGER UNSIGNED NOT NULL,
                          access VARCHAR(10) NULL,
                            PRIMARY KEY(login, idsite)
                          ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'site'    => "CREATE TABLE {$prefixTables}site (
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
                            ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'site_url'    => "CREATE TABLE {$prefixTables}site_url (
                              idsite INTEGER(10) UNSIGNED NOT NULL,
                              url VARCHAR(255) NOT NULL,
                                PRIMARY KEY(idsite, url)
                              ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'goal'       => "CREATE TABLE `{$prefixTables}goal` (
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
                              ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'logger_message'      => "CREATE TABLE {$prefixTables}logger_message (
                                      idlogger_message INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                                      tag VARCHAR(50) NULL,
                                      timestamp TIMESTAMP NULL,
                                      level VARCHAR(16) NULL,
                                      message TEXT NULL,
                                        PRIMARY KEY(idlogger_message)
                                      ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'log_action'          => "CREATE TABLE {$prefixTables}log_action (
                                      idaction INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      name TEXT,
                                      hash INTEGER(10) UNSIGNED NOT NULL,
                                      type TINYINT UNSIGNED NULL,
                                      url_prefix TINYINT(2) NULL,
                                        PRIMARY KEY(idaction),
                                        INDEX index_type_hash (type, hash)
                                      ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'log_visit'   => "CREATE TABLE {$prefixTables}log_visit (
                              idvisit INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                              idsite INTEGER(10) UNSIGNED NOT NULL,
                              idvisitor BINARY(8) NOT NULL,
                              visit_last_action_time DATETIME NOT NULL,
                              config_id BINARY(8) NOT NULL,
                              location_ip VARBINARY(16) NOT NULL,
                                PRIMARY KEY(idvisit),
                                INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time),
                                INDEX index_idsite_datetime (idsite, visit_last_action_time),
                                INDEX index_idsite_idvisitor (idsite, idvisitor)
                              ) ENGINE=$engine DEFAULT CHARSET=utf8
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
                                        ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'log_conversion'      => "CREATE TABLE `{$prefixTables}log_conversion` (
                                      idvisit int(10) unsigned NOT NULL,
                                      idsite int(10) unsigned NOT NULL,
                                      idvisitor BINARY(8) NOT NULL,
                                      server_time datetime NOT NULL,
                                      idaction_url int(11) default NULL,
                                      idlink_va int(11) default NULL,
                                      idgoal int(10) NOT NULL,
                                      buster int unsigned NOT NULL,
                                      idorder varchar(100) default NULL,
                                      items SMALLINT UNSIGNED DEFAULT NULL,
                                      url text NOT NULL,
                                        PRIMARY KEY (idvisit, idgoal, buster),
                                        UNIQUE KEY unique_idsite_idorder (idsite, idorder),
                                        INDEX index_idsite_datetime ( idsite, server_time )
                                      ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'log_link_visit_action' => "CREATE TABLE {$prefixTables}log_link_visit_action (
                                        idlink_va INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        idsite int(10) UNSIGNED NOT NULL,
                                        idvisitor BINARY(8) NOT NULL,
                                        idvisit INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_url_ref INTEGER(10) UNSIGNED NULL DEFAULT 0,
                                        idaction_name_ref INTEGER(10) UNSIGNED NOT NULL,
                                        custom_float FLOAT NULL DEFAULT NULL,
                                          PRIMARY KEY(idlink_va),
                                          INDEX index_idvisit(idvisit)
                                        ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'log_profiling'   => "CREATE TABLE {$prefixTables}log_profiling (
                                  query TEXT NOT NULL,
                                  count INTEGER UNSIGNED NULL,
                                  sum_time_ms FLOAT NULL,
                                    UNIQUE KEY query(query(100))
                                  ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'option'        => "CREATE TABLE `{$prefixTables}option` (
                                option_name VARCHAR( 255 ) NOT NULL,
                                option_value LONGTEXT NOT NULL,
                                autoload TINYINT NOT NULL DEFAULT '1',
                                  PRIMARY KEY ( option_name ),
                                  INDEX autoload( autoload )
                                ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'session'       => "CREATE TABLE {$prefixTables}session (
                                id VARCHAR( 255 ) NOT NULL,
                                modified INTEGER,
                                lifetime INTEGER,
                                data TEXT,
                                  PRIMARY KEY ( id )
                                ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'archive_numeric'     => "CREATE TABLE {$prefixTables}archive_numeric (
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
                                      ) ENGINE=$engine DEFAULT CHARSET=utf8
            ",

            'archive_blob'        => "CREATE TABLE {$prefixTables}archive_blob (
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
                                      ) ENGINE=$engine DEFAULT CHARSET=utf8
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
        $aTables      = array_keys($this->getTablesCreateSql());
        $prefixTables = $this->getTablePrefix();

        $return = array();
        foreach ($aTables as $table) {
            $return[] = $prefixTables . $table;
        }

        return $return;
    }

    /**
     * Get list of installed columns in a table
     *
     * @param  string $tableName The name of a table.
     *
     * @return array  Installed columns indexed by the column name.
     */
    public function getTableColumns($tableName)
    {
        $db = $this->getDb();

        $allColumns = $db->fetchAll("SHOW COLUMNS FROM . $tableName");

        $fields = array();
        foreach ($allColumns as $column) {
            $fields[trim($column['Field'])] = $column;
        }

        return $fields;
    }

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

            $db = $this->getDb();
            $prefixTables = $this->getTablePrefixEscaped();

            $allTables = $this->getAllExistingTables($prefixTables);

            // all the tables to be installed
            $allMyTables = $this->getTablesNames();

            // we get the intersection between all the tables in the DB and the tables to be installed
            $tablesInstalled = array_intersect($allMyTables, $allTables);

            // at this point we have the static list of core tables, but let's add the monthly archive tables
            $allArchiveNumeric = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_numeric%'");
            $allArchiveBlob    = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_blob%'");

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
            $dbName = $this->getDbName();
        }

        Db::exec("CREATE DATABASE IF NOT EXISTS " . $dbName . " DEFAULT CHARACTER SET utf8");
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix The name of the table without any piwik prefix.
     * @param string $createDefinition  The table create definition, see the "MySQL CREATE TABLE" specification for
     *                                  more information.
     * @throws \Exception
     */
    public function createTable($nameWithoutPrefix, $createDefinition)
    {
        $statement = sprintf("CREATE TABLE `%s` ( %s ) ENGINE=%s DEFAULT CHARSET=utf8 ;",
                             Common::prefixTable($nameWithoutPrefix),
                             $createDefinition,
                             $this->getTableEngine());

        try {
            Db::exec($statement);
        } catch (Exception $e) {
            // mysql code error 1050:table already exists
            // see bug #153 https://github.com/piwik/piwik/issues/153
            if (!$this->getDb()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * Drop database
     */
    public function dropDatabase($dbName = null)
    {
        $dbName = $dbName ?: $this->getDbName();
        Db::exec("DROP DATABASE IF EXISTS " . $dbName);
    }

    /**
     * Create all tables
     */
    public function createTables()
    {
        $db = $this->getDb();
        $prefixTables = $this->getTablePrefix();

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
        $db = $this->getDb();
        $db->query("INSERT IGNORE INTO " . Common::prefixTable("user") . "
                    VALUES ( 'anonymous', '', 'anonymous', 'anonymous@example.org', 'anonymous', 0, '" . Date::factory('now')->getDatetime() . "' );");
    }

    /**
     * Truncate all tables
     */
    public function truncateAllTables()
    {
        $tables = $this->getAllExistingTables();
        foreach ($tables as $table) {
            Db::query("TRUNCATE `$table`");
        }
    }

    private function getTablePrefix()
    {
        $dbInfos      = Db::getDatabaseConfig();
        $prefixTables = $dbInfos['tables_prefix'];

        return $prefixTables;
    }

    private function getTableEngine()
    {
        $dbInfos = Db::getDatabaseConfig();
        $engine  = $dbInfos['type'];

        return $engine;
    }

    private function getDb(){
        return Db::get();
    }

    private function getDbName()
    {
        $dbInfos = Db::getDatabaseConfig();
        $dbName  = $dbInfos['dbname'];

        return $dbName;
    }

    private function getAllExistingTables($prefixTables = false)
    {
        if (empty($prefixTables)) {
            $prefixTables = $this->getTablePrefixEscaped();
        }

        return Db::get()->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "%'");
    }

    private function getTablePrefixEscaped()
    {
        $prefixTables = $this->getTablePrefix();
        // '_' matches any character; force it to be literal
        $prefixTables = str_replace('_', '\_', $prefixTables);
        return $prefixTables;
    }
}
