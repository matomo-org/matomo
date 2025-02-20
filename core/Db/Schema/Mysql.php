<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

use Exception;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db\SchemaInterface;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Version;

/**
 * MySQL schema
 */
class Mysql implements SchemaInterface
{
    public const OPTION_NAME_MATOMO_INSTALL_VERSION = 'install_version';
    public const MAX_TABLE_NAME_LENGTH = 64;

    private $tablesInstalled = null;

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql()
    {
        $prefixTables = $this->getTablePrefix();
        $tableOptions = $this->getTableCreateOptions();

        $tables = array(
            'user'    => "CREATE TABLE {$prefixTables}user (
                          login VARCHAR(100) NOT NULL,
                          password VARCHAR(255) NOT NULL,
                          email VARCHAR(100) NOT NULL,
                          twofactor_secret VARCHAR(40) NOT NULL DEFAULT '',
                          superuser_access TINYINT(2) unsigned NOT NULL DEFAULT '0',
                          date_registered TIMESTAMP NULL,
                          ts_password_modified TIMESTAMP NULL,
                          idchange_last_viewed INTEGER UNSIGNED NULL,
                          invited_by VARCHAR(100) NULL,
                          invite_token VARCHAR(191) NULL,
                          invite_link_token VARCHAR(191) NULL,
                          invite_expired_at TIMESTAMP NULL,
                          invite_accept_at TIMESTAMP NULL,
                          ts_changes_shown TIMESTAMP NULL,
                            PRIMARY KEY(login),
                            UNIQUE INDEX `uniq_email` (`email`)
                          ) $tableOptions
            ",
            'user_token_auth' => "CREATE TABLE {$prefixTables}user_token_auth (
                          idusertokenauth BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          login VARCHAR(100) NOT NULL,
                          description VARCHAR(" . Model::MAX_LENGTH_TOKEN_DESCRIPTION . ") NOT NULL,
                          password VARCHAR(191) NOT NULL,
                          hash_algo VARCHAR(30) NOT NULL,
                          system_token TINYINT(1) NOT NULL DEFAULT 0,
                          last_used DATETIME NULL,
                          date_created DATETIME NOT NULL,
                          date_expired DATETIME NULL,
                          secure_only TINYINT(2) unsigned NOT NULL DEFAULT '0',
                            PRIMARY KEY(idusertokenauth),
                            UNIQUE KEY uniq_password(password)
                          ) $tableOptions
            ",

            'twofactor_recovery_code'    => "CREATE TABLE {$prefixTables}twofactor_recovery_code (
                          idrecoverycode BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          login VARCHAR(100) NOT NULL,
                          recovery_code VARCHAR(40) NOT NULL,
                            PRIMARY KEY(idrecoverycode)
                          ) $tableOptions
            ",

            'access'  => "CREATE TABLE {$prefixTables}access (
                          idaccess INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                          login VARCHAR(100) NOT NULL,
                          idsite INTEGER UNSIGNED NOT NULL,
                          access VARCHAR(50) NULL,
                            PRIMARY KEY(idaccess),
                            INDEX index_loginidsite (login, idsite)
                          ) $tableOptions
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
                            exclude_unknown_urls TINYINT(1) DEFAULT 0,
                            excluded_ips TEXT NOT NULL,
                            excluded_parameters TEXT NOT NULL,
                            excluded_user_agents TEXT NOT NULL,
                            excluded_referrers TEXT NOT NULL,
                            `group` VARCHAR(250) NOT NULL,
                            `type` VARCHAR(255) NOT NULL,
                            keep_url_fragment TINYINT NOT NULL DEFAULT 0,
                            creator_login VARCHAR(100) NULL,
                              PRIMARY KEY(idsite)
                            ) $tableOptions
            ",

            'plugin_setting' => "CREATE TABLE {$prefixTables}plugin_setting (
                              `plugin_name` VARCHAR(60) NOT NULL,
                              `setting_name` VARCHAR(255) NOT NULL,
                              `setting_value` LONGTEXT NOT NULL,
                              `json_encoded` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                              `user_login` VARCHAR(100) NOT NULL DEFAULT '',
                              `idplugin_setting` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                              PRIMARY KEY (idplugin_setting),
                              INDEX(plugin_name, user_login)
                            ) $tableOptions
            ",

            'site_setting'    => "CREATE TABLE {$prefixTables}site_setting (
                              idsite INTEGER(10) UNSIGNED NOT NULL,
                              `plugin_name` VARCHAR(60) NOT NULL,
                              `setting_name` VARCHAR(255) NOT NULL,
                              `setting_value` LONGTEXT NOT NULL,
                              `json_encoded` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                              `idsite_setting` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                              PRIMARY KEY (idsite_setting),
                              INDEX(idsite, plugin_name)
                            ) $tableOptions
            ",

            'site_url'    => "CREATE TABLE {$prefixTables}site_url (
                              idsite INTEGER(10) UNSIGNED NOT NULL,
                              url VARCHAR(190) NOT NULL,
                                PRIMARY KEY(idsite, url)
                              ) $tableOptions
            ",

            'goal'       => "CREATE TABLE `{$prefixTables}goal` (
                              `idsite` int(11) NOT NULL,
                              `idgoal` int(11) NOT NULL,
                              `name` varchar(50) NOT NULL,
                              `description` varchar(255) NOT NULL DEFAULT '',
                              `match_attribute` varchar(20) NOT NULL,
                              `pattern` varchar(255) NOT NULL,
                              `pattern_type` varchar(25) NOT NULL,
                              `case_sensitive` tinyint(4) NOT NULL,
                              `allow_multiple` tinyint(4) NOT NULL,
                              `revenue` DOUBLE NOT NULL,
                              `deleted` tinyint(4) NOT NULL default '0',
                              `event_value_as_revenue` tinyint(4) NOT NULL default '0',
                                PRIMARY KEY  (`idsite`,`idgoal`)
                              ) $tableOptions
            ",

            'logger_message'      => "CREATE TABLE {$prefixTables}logger_message (
                                      idlogger_message INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                                      tag VARCHAR(50) NULL,
                                      timestamp TIMESTAMP NULL,
                                      level VARCHAR(16) NULL,
                                      message TEXT NULL,
                                        PRIMARY KEY(idlogger_message)
                                      ) $tableOptions
            ",

            'log_action'          => "CREATE TABLE {$prefixTables}log_action (
                                      idaction INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      name VARCHAR(4096),
                                      hash INTEGER(10) UNSIGNED NOT NULL,
                                      type TINYINT UNSIGNED NULL,
                                      url_prefix TINYINT(2) NULL,
                                        PRIMARY KEY(idaction),
                                        INDEX index_type_hash (type, hash)
                                      ) $tableOptions
            ",

            'log_visit'   => "CREATE TABLE {$prefixTables}log_visit (
                              idvisit BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                              idsite INTEGER(10) UNSIGNED NOT NULL,
                              idvisitor BINARY(8) NOT NULL,
                              visit_last_action_time DATETIME NOT NULL,
                              config_id BINARY(8) NOT NULL,
                              location_ip VARBINARY(16) NOT NULL,
                                PRIMARY KEY(idvisit),
                                INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time),
                                INDEX index_idsite_datetime (idsite, visit_last_action_time),
                                INDEX index_idsite_idvisitor_time (idsite, idvisitor, visit_last_action_time DESC)
                              ) $tableOptions
            ",

            'log_conversion_item'   => "CREATE TABLE `{$prefixTables}log_conversion_item` (
                                        idsite int(10) UNSIGNED NOT NULL,
                                        idvisitor BINARY(8) NOT NULL,
                                        server_time DATETIME NOT NULL,
                                        idvisit BIGINT(10) UNSIGNED NOT NULL,
                                        idorder varchar(100) NOT NULL,
                                        idaction_sku INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_name INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_category INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_category2 INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_category3 INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_category4 INTEGER(10) UNSIGNED NOT NULL,
                                        idaction_category5 INTEGER(10) UNSIGNED NOT NULL,
                                        price DOUBLE NOT NULL,
                                        quantity INTEGER(10) UNSIGNED NOT NULL,
                                        deleted TINYINT(1) UNSIGNED NOT NULL,
                                          PRIMARY KEY(idvisit, idorder, idaction_sku),
                                          INDEX index_idsite_servertime ( idsite, server_time )
                                        ) $tableOptions
            ",

            'log_conversion'      => "CREATE TABLE `{$prefixTables}log_conversion` (
                                      idvisit BIGINT(10) unsigned NOT NULL,
                                      idsite int(10) unsigned NOT NULL,
                                      idvisitor BINARY(8) NOT NULL,
                                      server_time datetime NOT NULL,
                                      idaction_url INTEGER(10) UNSIGNED default NULL,
                                      idlink_va BIGINT(10) UNSIGNED default NULL,
                                      idgoal int(10) NOT NULL,
                                      buster int unsigned NOT NULL,
                                      idorder varchar(100) default NULL,
                                      items SMALLINT UNSIGNED DEFAULT NULL,
                                      url VARCHAR(4096) NOT NULL,
                                      revenue DOUBLE default NULL,
                                      revenue_shipping DOUBLE default NULL,
                                      revenue_subtotal DOUBLE default NULL,
                                      revenue_tax DOUBLE default NULL,
                                      revenue_discount DOUBLE default NULL,
                                      pageviews_before SMALLINT UNSIGNED DEFAULT NULL,
                                        PRIMARY KEY (idvisit, idgoal, buster),
                                        UNIQUE KEY unique_idsite_idorder (idsite, idorder),
                                        INDEX index_idsite_datetime ( idsite, server_time )
                                      ) $tableOptions
            ",

            'log_link_visit_action' => "CREATE TABLE {$prefixTables}log_link_visit_action (
                                        idlink_va BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        idsite int(10) UNSIGNED NOT NULL,
                                        idvisitor BINARY(8) NOT NULL,
                                        idvisit BIGINT(10) UNSIGNED NOT NULL,
                                        idaction_url_ref INTEGER(10) UNSIGNED NULL DEFAULT 0,
                                        idaction_name_ref INTEGER(10) UNSIGNED NULL,
                                        custom_float DOUBLE NULL DEFAULT NULL,
                                        pageview_position MEDIUMINT UNSIGNED DEFAULT NULL,
                                          PRIMARY KEY(idlink_va),
                                          INDEX index_idvisit(idvisit)
                                        ) $tableOptions
            ",

            'log_profiling'   => "CREATE TABLE {$prefixTables}log_profiling (
                                  query TEXT NOT NULL,
                                  count INTEGER UNSIGNED NULL,
                                  sum_time_ms FLOAT NULL,
                                  idprofiling BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                    PRIMARY KEY (idprofiling),
                                    UNIQUE KEY query(query(100))
                                  ) $tableOptions
            ",

            'option'        => "CREATE TABLE `{$prefixTables}option` (
                                option_name VARCHAR( 191 ) NOT NULL,
                                option_value LONGTEXT NOT NULL,
                                autoload TINYINT NOT NULL DEFAULT '1',
                                  PRIMARY KEY ( option_name ),
                                  INDEX autoload( autoload )
                                ) $tableOptions
            ",

            'session'       => "CREATE TABLE {$prefixTables}session (
                                id VARCHAR( 191 ) NOT NULL,
                                modified INTEGER,
                                lifetime INTEGER,
                                data MEDIUMTEXT,
                                  PRIMARY KEY ( id )
                                ) $tableOptions
            ",

            'archive_numeric'     => "CREATE TABLE {$prefixTables}archive_numeric (
                                      idarchive INTEGER UNSIGNED NOT NULL,
                                      name VARCHAR(190) NOT NULL,
                                      idsite INTEGER UNSIGNED NULL,
                                      date1 DATE NULL,
                                      date2 DATE NULL,
                                      period TINYINT UNSIGNED NULL,
                                      ts_archived DATETIME NULL,
                                      value DOUBLE NULL,
                                        PRIMARY KEY(idarchive, name),
                                        INDEX index_idsite_dates_period(idsite, date1, date2, period, name(6)),
                                        INDEX index_period_archived(period, ts_archived)
                                      ) $tableOptions
            ",

            'archive_blob'        => "CREATE TABLE {$prefixTables}archive_blob (
                                      idarchive INTEGER UNSIGNED NOT NULL,
                                      name VARCHAR(190) NOT NULL,
                                      idsite INTEGER UNSIGNED NULL,
                                      date1 DATE NULL,
                                      date2 DATE NULL,
                                      period TINYINT UNSIGNED NULL,
                                      ts_archived DATETIME NULL,
                                      value MEDIUMBLOB NULL,
                                        PRIMARY KEY(idarchive, name),
                                        INDEX index_period_archived(period, ts_archived)
                                      ) $tableOptions
            ",

            'archive_invalidations' => "CREATE TABLE `{$prefixTables}archive_invalidations` (
                                            idinvalidation BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                            idarchive INTEGER UNSIGNED NULL,
                                            name VARCHAR(255) NOT NULL,
                                            idsite INTEGER UNSIGNED NOT NULL,
                                            date1 DATE NOT NULL,
                                            date2 DATE NOT NULL,
                                            period TINYINT UNSIGNED NOT NULL,
                                            ts_invalidated DATETIME NULL,
                                            ts_started DATETIME NULL,
                                            status TINYINT(1) UNSIGNED DEFAULT 0,
                                            `report` VARCHAR(255) NULL,
                                            processing_host VARCHAR(100) NULL DEFAULT NULL,
                                            process_id VARCHAR(15) NULL DEFAULT NULL,
                                            PRIMARY KEY(idinvalidation),
                                            INDEX index_idsite_dates_period_name(idsite, date1, period)
                                        ) $tableOptions
            ",

            'sequence'        => "CREATE TABLE {$prefixTables}sequence (
                                      `name` VARCHAR(120) NOT NULL,
                                      `value` BIGINT(20) UNSIGNED NOT NULL ,
                                      PRIMARY KEY(`name`)
                                  ) $tableOptions
            ",

            'brute_force_log'        => "CREATE TABLE {$prefixTables}brute_force_log (
                                      `id_brute_force_log` bigint(11) NOT NULL AUTO_INCREMENT,
                                      `ip_address` VARCHAR(60) DEFAULT NULL,
                                      `attempted_at` datetime NOT NULL,
                                      `login` VARCHAR(100) NULL,
                                        INDEX index_ip_address(ip_address),
                                      PRIMARY KEY(`id_brute_force_log`)
                                      ) $tableOptions
            ",

            'tracking_failure'        => "CREATE TABLE {$prefixTables}tracking_failure (
                                      `idsite` BIGINT(20) UNSIGNED NOT NULL ,
                                      `idfailure` SMALLINT UNSIGNED NOT NULL ,
                                      `date_first_occurred` DATETIME NOT NULL ,
                                      `request_url` MEDIUMTEXT NOT NULL ,
                                      PRIMARY KEY(`idsite`, `idfailure`)
                                  ) $tableOptions
            ",
            'locks'                   => "CREATE TABLE `{$prefixTables}locks` (
                                      `key` VARCHAR(" . Lock::MAX_KEY_LEN . ") NOT NULL,
                                      `value` VARCHAR(255) NULL DEFAULT NULL,
                                      `expiry_time` BIGINT UNSIGNED DEFAULT 9999999999,
                                      PRIMARY KEY (`key`)
                                  ) $tableOptions
            ",
            'changes'             => "CREATE TABLE `{$prefixTables}changes` (
                                      `idchange` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `created_time` DATETIME NOT NULL,
                                      `plugin_name` VARCHAR(60) NOT NULL,
                                      `version` VARCHAR(20) NOT NULL, 
                                      `title` VARCHAR(255) NOT NULL,                                      
                                      `description` TEXT NULL,
                                      `link_name` VARCHAR(255) NULL,
                                      `link` VARCHAR(255) NULL,       
                                      PRIMARY KEY(`idchange`),
                                      UNIQUE KEY unique_plugin_version_title (`plugin_name`, `version`, `title`(100))                            
                                  ) $tableOptions
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

        $allColumns = $db->fetchAll("SHOW COLUMNS FROM " . $tableName);

        $fields = array();
        foreach ($allColumns as $column) {
            $fields[trim($column['Field'])] = $column;
        }

        return $fields;
    }

    /**
     * Get list of tables installed (including tables defined by deactivated plugins)
     *
     * @param bool $forceReload Invalidate cache
     * @return array  installed Tables
     */
    public function getTablesInstalled($forceReload = true)
    {
        if (
            is_null($this->tablesInstalled)
            || $forceReload === true
        ) {
            $db = $this->getDb();
            $prefixTables = $this->getTablePrefixEscaped();

            $allTables = $this->getAllExistingTables($prefixTables);

            // all the tables to be installed
            $allMyTables = $this->getTablesNames();

            /**
             * Triggered when detecting which tables have already been created by Matomo.
             * This should be used by plugins to define it's database tables. Table names need to be added prefixed.
             *
             * **Example**
             *
             *     Piwik::addAction('Db.getTablesInstalled', function(&$allTablesInstalled) {
             *         $allTablesInstalled = 'log_custom';
             *     });
             * @param array $result
             */
            if (count($allTables) && empty($GLOBALS['DISABLE_GET_TABLES_INSTALLED_EVENTS_FOR_TEST'])) {
                Manager::getInstance()->loadPlugins(Manager::getAllPluginsNames());
                Piwik::postEvent('Db.getTablesInstalled', [&$allMyTables]);
                Manager::getInstance()->unloadPlugins();
                Manager::getInstance()->loadActivatedPlugins();
            }

            // we get the intersection between all the tables in the DB and the tables to be installed
            $tablesInstalled = array_intersect($allMyTables, $allTables);

            // at this point we have the static list of core tables, but let's add the monthly archive tables
            $allArchiveNumeric = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_numeric%'");
            $allArchiveBlob    = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_blob%'");

            $allTablesReallyInstalled = array_merge($tablesInstalled, $allArchiveNumeric, $allArchiveBlob);

            $allTablesReallyInstalled = array_unique($allTablesReallyInstalled);

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

        $createOptions = $this->getDatabaseCreateOptions();
        $dbName = str_replace('`', '', $dbName);

        Db::exec("CREATE DATABASE IF NOT EXISTS `$dbName` $createOptions");
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
        $statement = sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` ( %s ) %s;",
            Common::prefixTable($nameWithoutPrefix),
            $createDefinition,
            $this->getTableCreateOptions()
        );

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
        $dbName = str_replace('`', '', $dbName);
        Db::exec("DROP DATABASE IF EXISTS `" . $dbName . "`");
    }

    /**
     * Create all tables
     */
    public function createTables()
    {
        $db = $this->getDb();
        $prefixTables = $this->getTablePrefix();

        $tablesAlreadyInstalled = $this->getAllExistingTables($prefixTables);
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
        $now = Date::factory('now')->getDatetime();
        // The anonymous user is the user that is assigned by default
        // note that the token_auth value is anonymous, which is assigned by default as well in the Login plugin
        $db = $this->getDb();
        $db->query("INSERT IGNORE INTO " . Common::prefixTable("user") . "
                    (`login`, `password`, `email`, `twofactor_secret`, `superuser_access`, `date_registered`, `ts_password_modified`,
                    `idchange_last_viewed`)
                    VALUES ( 'anonymous', '', 'anonymous@example.org', '', 0, '$now', '$now' , NULL);");

        $model = new Model();
        $model->addTokenAuth('anonymous', 'anonymous', 'anonymous default token', $now);
    }

    /**
     * Records the Matomo version a user used when installing this Matomo for the first time
     */
    public function recordInstallVersion()
    {
        if (!self::getInstallVersion()) {
            Option::set(self::OPTION_NAME_MATOMO_INSTALL_VERSION, Version::VERSION);
        }
    }

    /**
     * Returns which Matomo version was used to install this Matomo for the first time.
     */
    public function getInstallVersion()
    {
        Option::clearCachedOption(self::OPTION_NAME_MATOMO_INSTALL_VERSION);
        $version = Option::get(self::OPTION_NAME_MATOMO_INSTALL_VERSION);
        if (!empty($version)) {
            return $version;
        }
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

    /**
     * Adds a MAX_EXECUTION_TIME hint into a SELECT query if $limit is bigger than 0
     *
     * @param string $sql  query to add hint to
     * @param float $limit  time limit in seconds
     * @return string
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string
    {
        if ($limit <= 0) {
            return $sql;
        }

        $sql = trim($sql);
        $pos = stripos($sql, 'SELECT');
        $isMaxExecutionTimeoutAlreadyPresent = (stripos($sql, 'MAX_EXECUTION_TIME(') !== false);
        if ($pos !== false && !$isMaxExecutionTimeoutAlreadyPresent) {
            $timeInMs = $limit * 1000;
            $timeInMs = (int) $timeInMs;
            $maxExecutionTimeHint = ' /*+ MAX_EXECUTION_TIME(' . $timeInMs . ') */ ';

            $sql = substr_replace($sql, 'SELECT ' . $maxExecutionTimeHint, $pos, strlen('SELECT'));
        }

        return $sql;
    }

    public function supportsComplexColumnUpdates(): bool
    {
        return true;
    }

    /**
     * Returns the default collation for a charset.
     *
     * Will return an empty string for an unknown charset
     * (can happen for alias charsets like "utf8").
     *
     * @param string $charset
     *
     * @return string
     * @throws Exception
     */
    public function getDefaultCollationForCharset(string $charset): string
    {
        $result = $this->getDb()->fetchRow('SHOW CHARACTER SET WHERE `Charset` = ?', [$charset]);

        return $result['Default collation'] ?? '';
    }

    public function getDefaultPort(): int
    {
        return 3306;
    }

    public function getTableCreateOptions(): string
    {
        $engine = $this->getTableEngine();
        $charset = $this->getUsedCharset();
        $collation = $this->getUsedCollation();
        $rowFormat = $this->getTableRowFormat();

        $options = "ENGINE=$engine DEFAULT CHARSET=$charset";

        if ('' !== $collation) {
            $options .= " COLLATE=$collation";
        }

        if ('' !== $rowFormat) {
            $options .= " $rowFormat";
        }

        return $options;
    }

    public function optimizeTables(array $tables, bool $force = false): bool
    {
        $optimize = Config::getInstance()->General['enable_sql_optimize_queries'];

        if (
            empty($optimize)
            && !$force
        ) {
            return false;
        }

        if (empty($tables)) {
            return false;
        }

        if (
            !$this->isOptimizeInnoDBSupported()
            && !$force
        ) {
            // filter out all InnoDB tables
            $myisamDbTables = array();
            foreach ($this->getTableStatus() as $row) {
                if (
                    strtolower($row['Engine']) == 'myisam'
                    && in_array($row['Name'], $tables)
                ) {
                    $myisamDbTables[] = $row['Name'];
                }
            }

            $tables = $myisamDbTables;
        }

        if (empty($tables)) {
            return false;
        }

        // optimize the tables
        $success = true;
        foreach ($tables as &$t) {
            $ok = Db::query('OPTIMIZE TABLE ' . $t);
            if (!$ok) {
                $success = false;
            }
        }

        return $success;
    }

    public function isOptimizeInnoDBSupported(): bool
    {
        $version = strtolower($this->getVersion());

        // Note: This check for MariaDb is here on purpose, so it's working correctly for people
        // having MySQL still configured, when using MariaDb
        if (strpos($version, "mariadb") === false) {
            return false;
        }

        $semanticVersion = strstr($version, '-', $beforeNeedle = true);
        return version_compare($semanticVersion, '10.1.1', '>=');
    }

    public function supportsSortingInSubquery(): bool
    {
        return true;
    }

    public function getSupportedReadIsolationTransactionLevel(): string
    {
        return 'READ UNCOMMITTED';
    }

    protected function getDatabaseCreateOptions(): string
    {
        $charset = DbHelper::getDefaultCharset();
        $collation = $this->getDefaultCollationForCharset($charset);

        $options = "DEFAULT CHARACTER SET $charset";

        if ('' !== $collation) {
            $options .= " COLLATE $collation";
        }

        return $options;
    }

    protected function getTableEngine()
    {
        return $this->getDbSettings()->getEngine();
    }

    protected function getTableRowFormat(): string
    {
        return $this->getDbSettings()->getRowFormat();
    }

    protected function getUsedCharset(): string
    {
        return $this->getDbSettings()->getUsedCharset();
    }

    protected function getUsedCollation(): string
    {
        return $this->getDbSettings()->getUsedCollation();
    }

    private function getTablePrefix()
    {
        return $this->getDbSettings()->getTablePrefix();
    }

    protected function getVersion(): string
    {
        return Db::fetchOne("SELECT VERSION()");
    }

    protected function getTableStatus()
    {
        return Db::fetchAll("SHOW TABLE STATUS");
    }

    private function getDb()
    {
        return Db::get();
    }

    private function getDbSettings()
    {
        return new Db\Settings();
    }

    private function getDbName()
    {
        return $this->getDbSettings()->getDbName();
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
