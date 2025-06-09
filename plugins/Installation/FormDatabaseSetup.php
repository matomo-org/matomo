<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation;

use Exception;
use HTML_QuickForm2_DataSource_Array;
use HTML_QuickForm2_Factory;
use HTML_QuickForm2_Rule;
use Piwik\Config;
use Piwik\Db;
use Piwik\Db\Adapter;
use Piwik\DbHelper;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\QuickForm2;
use Zend_Db_Adapter_Exception;

/**
 * phpcs:ignoreFile PSR1.Classes.ClassDeclaration.MultipleClasses
 */
class FormDatabaseSetup extends QuickForm2
{
    const MASKED_PASSWORD_VALUE = '**********';

    function __construct($id = 'databasesetupform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes = array('autocomplete' => 'off'), $trackSubmit);
    }

    function init()
    {
        HTML_QuickForm2_Factory::registerRule('checkValidFilename',
                                              'Piwik\Plugins\Installation\FormDatabaseSetupRuleCheckValidFilename'
        );
        HTML_QuickForm2_Factory::registerRule('checkValidDbname',
                                              'Piwik\Plugins\Installation\FormDatabaseSetupRuleCheckValidDbname'
        );
        HTML_QuickForm2_Factory::registerRule('checkUserPrivileges',
                                              'Piwik\Plugins\Installation\RuleCheckUserPrivileges'
        );

        $availableAdapters = Adapter::getAdapters();
        $adapters = array();
        foreach ($availableAdapters as $adapter) {
            $adapters[$adapter] = $adapter;
            if (Adapter::isRecommendedAdapter($adapter)) {
                $adapters[$adapter] .= ' (' . Piwik::translate('General_Recommended') . ')';
            }
        }

        $this->addElement('text', 'host')
            ->setLabel(Piwik::translate('Installation_DatabaseSetupServer'))
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_DatabaseSetupServer')));

        $user = $this->addElement('text', 'username')
            ->setLabel(Piwik::translate('Installation_DatabaseSetupLogin'));
        $user->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_DatabaseSetupLogin')));
        $requiredPrivileges = RuleCheckUserPrivileges::getRequiredPrivilegesPretty();
        $user->addRule('checkUserPrivileges',
            Piwik::translate('Installation_InsufficientPrivilegesMain', $requiredPrivileges . '<br/><br/>') .
            Piwik::translate('Installation_InsufficientPrivilegesHelp'));

        $this->addElement('password', 'password')
            ->setLabel(Piwik::translate('General_Password'));

        $item = $this->addElement('text', 'dbname')
            ->setLabel(Piwik::translate('Installation_DatabaseSetupDatabaseName'));
        $item->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_DatabaseSetupDatabaseName')));
        $item->addRule('checkValidDbname', Piwik::translate('General_NotValid', Piwik::translate('Installation_DatabaseSetupDatabaseName')));

        $this->addElement('text', 'tables_prefix')
            ->setLabel(Piwik::translate('Installation_DatabaseSetupTablePrefix'))
            ->addRule('checkValidFilename', Piwik::translate('General_NotValid', Piwik::translate('Installation_DatabaseSetupTablePrefix')));

        $this->addElement('select', 'adapter')
            ->setLabel(Piwik::translate('Installation_DatabaseSetupAdapter'))
            ->loadOptions($adapters)
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_DatabaseSetupAdapter')));

        $this->addElement('select', 'schema')
            ->setLabel(Piwik::translate('Installation_DatabaseSetupEngine'))
            ->loadOptions(['Mysql' => 'MySQL', 'Mariadb' => 'MariaDB'])
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_DatabaseSetupEngine')));

        $this->addElement('submit', 'submit', array('value' => Piwik::translate('General_Next') . ' »', 'class' => 'btn'));

        $defaultDatabaseType = Config::getInstance()->database['type'];
        $this->addElement( 'hidden', 'type')->setLabel('Database engine');


        $defaults = array(
            'host'          => '127.0.0.1',
            'type'          => $defaultDatabaseType,
            'tables_prefix' => 'matomo_',
            'schema'        => 'Mysql',
            'port'          => '3306',
        );

        $defaultsEnvironment = array('host', 'adapter', 'tables_prefix', 'username', 'schema', 'password', 'dbname');
        foreach ($defaultsEnvironment as $name) {
            $envValue = $this->getEnvironmentSetting($name);

            if (null !== $envValue) {
                $defaults[$name] = $envValue;
            }
        }

        if (array_key_exists('password', $defaults)) {
            $defaults['password'] = self::MASKED_PASSWORD_VALUE; // ensure not to show password in UI
        }

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array($defaults));
    }

    private function getEnvironmentSetting(string $name): ?string
    {
        $envName = 'DATABASE_' . strtoupper($name); // fyi getenv is case insensitive
        $envNameMatomo = 'MATOMO_' . $envName;
        if (is_string(getenv($envNameMatomo))) {
            return getenv($envNameMatomo);
        } elseif (is_string(getenv($envName))) {
            return getenv($envName);
        }

        return null;
    }

    /**
     * Creates database object based on form data.
     *
     * @throws Exception|Zend_Db_Adapter_Exception
     * @return array The database connection info. Can be passed into Piwik::createDatabaseObject.
     */
    public function createDatabaseObject()
    {
        $dbname = trim($this->getSubmitValue('dbname'));
        if (empty($dbname)) // disallow database object creation w/ no selected database
        {
            throw new Exception("No database name");
        }

        $adapter = $this->getSubmitValue('adapter');
        $host = $this->getSubmitValue('host');
        $tables_prefix = $this->getSubmitValue('tables_prefix');

        $password = $this->getSubmitValue('password');
        $passwordFromEnv = $this->getEnvironmentSetting('password');

        if ($password === self::MASKED_PASSWORD_VALUE && null !== $passwordFromEnv) {
            $password = $passwordFromEnv;
        }

        $schema = $this->getSubmitValue('schema');

        $dbInfos = array(
            'host'          => (is_null($host)) ? $host : trim($host),
            'username'      => $this->getSubmitValue('username'),
            'password'      => $password,
            'dbname'        => $dbname,
            'tables_prefix' => (is_null($tables_prefix)) ? $tables_prefix : trim($tables_prefix),
            'adapter'       => $adapter,
            'port'          => Db\Schema::getDefaultPortForSchema($schema),
            'schema'        => $schema,
            'type'          => $this->getSubmitValue('type'),
            'enable_ssl'    => false
        );

        $extractedHostAndPort = HostPortExtractor::extract($dbInfos['host']);
        if (!is_null($extractedHostAndPort)) {
            $dbInfos['host'] = $extractedHostAndPort->host;
            $dbInfos['port'] = $extractedHostAndPort->port;
        }

        try {
            @Db::createDatabaseObject($dbInfos);
        } catch (Zend_Db_Adapter_Exception $e) {
            $db = Adapter::factory($adapter, $dbInfos, $connect = false);

            // database not found, we try to create  it
            if ($db->isErrNo($e, '1049')) {
                $dbInfosConnectOnly = $dbInfos;
                $dbInfosConnectOnly['dbname'] = null;
                @Db::createDatabaseObject($dbInfosConnectOnly);
                @DbHelper::createDatabase($dbInfos['dbname']);

                // select the newly created database
                @Db::createDatabaseObject($dbInfos);
            } else {
                throw $e;
            }
        }

        return $dbInfos;
    }
}

/**
 * Validation rule that checks that the supplied DB user has enough privileges.
 *
 * The following privileges are required for Matomo to run:
 * - CREATE
 * - ALTER
 * - SELECT
 * - INSERT
 * - UPDATE
 * - DELETE
 * - DROP
 * - CREATE TEMPORARY TABLES
 *
 */
class RuleCheckUserPrivileges extends HTML_QuickForm2_Rule
{
    public const TEST_TABLE_NAME = 'piwik_test_table';
    public const TEST_TEMP_TABLE_NAME = 'piwik_test_table_temp';

    /**
     * Checks that the DB user entered in the form has the necessary privileges for Piwik
     * to run.
     */
    public function validateOwner()
    {
        // try and create the database object
        try {
            $this->createDatabaseObject();
        } catch (Exception $ex) {
            if ($this->isAccessDenied($ex)) {
                return false;
            } else {
                return true; // if we can't create the database object, skip this validation
            }
        }

        $db = Db::get();

        try {
            // try to drop tables before running privilege tests
            $this->dropExtraTables($db);
        } catch (Exception $ex) {
            if ($this->isAccessDenied($ex)) {
                return false;
            } else {
                throw $ex;
            }
        }

        // check each required privilege by running a query that uses it
        foreach (self::getRequiredPrivileges() as $privilegeType => $queries) {
            if (!is_array($queries)) {
                $queries = array($queries);
            }

            foreach ($queries as $sql) {
                try {
                    if (in_array($privilegeType, array('SELECT'))) {
                        $ret = $db->fetchAll($sql);
                    } else {
                        $ret = $db->exec($sql);
                    }
                    // In case an exception is not thrown check the return
                    if ($ret === -1) {
                        return false;
                    }
                } catch (Exception $ex) {
                    if ($this->isAccessDenied($ex)) {
                        return false;
                    } else {
                        throw new Exception("Test SQL failed to execute: $sql\nError: " . $ex->getMessage());
                    }
                }
            }
        }

        // remove extra tables that were created
        $this->dropExtraTables($db);

        return true;
    }

    /**
     * Returns an array describing the database privileges required for Matomo to run. The
     * array maps privilege names with one or more SQL queries that can be used to test
     * if the current user has the privilege.
     *
     * NOTE: LOAD DATA INFILE & LOCK TABLES privileges are not **required** so they're
     * not checked.
     *
     * @return array
     */
    public static function getRequiredPrivileges()
    {
        return array(
            'CREATE'                  => 'CREATE TABLE ' . self::TEST_TABLE_NAME . ' (
                               id INT AUTO_INCREMENT,
                               value INT,
                               PRIMARY KEY (id),
                               KEY index_value (value)
                           )',
            'ALTER'                   => 'ALTER TABLE ' . self::TEST_TABLE_NAME . '
                            ADD COLUMN other_value INT DEFAULT 0',
            'SELECT'                  => 'SELECT * FROM ' . self::TEST_TABLE_NAME,
            'INSERT'                  => 'INSERT INTO ' . self::TEST_TABLE_NAME . ' (value) VALUES (123)',
            'UPDATE'                  => 'UPDATE ' . self::TEST_TABLE_NAME . ' SET value = 456 WHERE id = 1',
            'DELETE'                  => 'DELETE FROM ' . self::TEST_TABLE_NAME . ' WHERE id = 1',
            'DROP'                    => 'DROP TABLE ' . self::TEST_TABLE_NAME,
            'CREATE TEMPORARY TABLES' => 'CREATE TEMPORARY TABLE ' . self::TEST_TEMP_TABLE_NAME . ' (
                                        id INT AUTO_INCREMENT,
                                        PRIMARY KEY (id)
                                     )',
        );
    }

    /**
     * Returns a string description of the database privileges required for Matomo to run.
     *
     * @return string
     */
    public static function getRequiredPrivilegesPretty()
    {
        return implode('<br/>', array_keys(self::getRequiredPrivileges()));
    }

    /**
     * Checks if an exception that was thrown after running a query represents an 'access denied'
     * error.
     *
     * @param Exception $ex The exception to check.
     * @return bool
     */
    private function isAccessDenied($ex)
    {
        //NOte: this code is duplicated in Tracker.php error handler
        return $ex->getCode() == 1044 || $ex->getCode() == 42000;
    }

    /**
     * Creates a database object using the connection information entered in the form.
     *
     * @return array
     */
    private function createDatabaseObject()
    {
        return $this->owner->getContainer()->createDatabaseObject();
    }

    /**
     * Drops the tables created by the privilege checking queries, if they exist.
     *
     * @param \Piwik\Db $db The database object to use.
     */
    private function dropExtraTables($db)
    {
        $db->query('DROP TABLE IF EXISTS ' . self::TEST_TABLE_NAME . ', ' . self::TEST_TEMP_TABLE_NAME);
    }
}

/**
 * Filename check for prefix
 *
 */
class FormDatabaseSetupRuleCheckValidFilename extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        $prefix = $this->owner->getValue();
        return empty($prefix)
        || Filesystem::isValidFilename($prefix);
    }
}

/**
 * Filename check for DB name
 *
 */
class FormDatabaseSetupRuleCheckValidDbname extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        $prefix = $this->owner->getValue();
        return empty($prefix)
        || DbHelper::isValidDbname($prefix);
    }
}
