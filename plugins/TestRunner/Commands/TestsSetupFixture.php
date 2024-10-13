<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Application\Environment;
use Piwik\Config;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use Piwik\Url;
use Piwik\Tests\Framework\Fixture;

/**
 * Console commands that sets up a fixture either in a local MySQL database or a remote one.
 *
 * Examples:
 *
 * To setup a fixture provided by Piwik:
 *
 *     ./console tests:setup-fixture UITestFixture
 *
 * To setup your own fixture created solely for test purposes and stored outside of Piwik:
 *
 *     ./console tests:setup-fixture MyFixtureType --file=../devfixtures/MyFixtureType.php
 *
 * To setup a fixture or use existing data if present:
 *
 *     ./console tests:setup-fixture UITestFixture --persist-fixture-data
 *
 * To re-setup a fixture that is already present:
 *
 *     ./console tests:setup-fixture UITestFixture --persist-fixture-data --drop
 *
 * To create an SQL dump for a fixture:
 *
 *     ./console tests:setup-fixture OmniFixture --sqldump=OmniFixtureDump.sql
 */
class TestsSetupFixture extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:setup-fixture');
        $this->setDescription('Create a database and fill it with data using a Piwik test fixture.');

        $this->addRequiredArgument(
            'fixture',
            "The class name of the fixture to apply. Doesn't need to have a namespace if it exists in the " .
            "Piwik\\Tests\\Fixtures namespace."
        );

        $this->addRequiredValueOption(
            'db-name',
            null,
            "The name of the database that will contain the fixture data. This option is required to be set."
        );
        $this->addRequiredValueOption(
            'file',
            null,
            "The file location of the fixture. If this option is included the file will be required explicitly."
        );
        $this->addRequiredValueOption(
            'db-host',
            null,
            "The hostname of the MySQL database to use. Uses the default config value if not specified."
        );
        $this->addRequiredValueOption(
            'db-user',
            null,
            "The name of the MySQL user to use. Uses the default config value if not specified."
        );
        $this->addRequiredValueOption(
            'db-pass',
            null,
            "The MySQL user password to use. Uses the default config value if not specified."
        );
        $this->addNoValueOption(
            'teardown',
            null,
            "If specified, the fixture will be torn down and the database deleted. Won't work if the --db-name " .
            "option isn't supplied."
        );
        $this->addNoValueOption(
            'persist-fixture-data',
            null,
            "If specified, the database will not be dropped after the fixture is setup. If the database already " .
            "and the fixture was successfully setup before, nothing will happen."
        );
        $this->addNoValueOption(
            'drop',
            null,
            "Forces the database to be dropped before setting up the fixture. Should be used in conjunction with" .
            " --persist-fixture-data when updating a pre-existing test database."
        );
        $this->addRequiredValueOption(
            'sqldump',
            null,
            "Creates an SQL dump after setting up the fixture and outputs the dump to the file specified by this option."
        );
        $this->addNoValueOption(
            'save-config',
            null,
            "Saves the current configuration file as a config for a new Piwik domain. For example save-config --matomo-domain=mytest.localhost.com will create "
            . "a mytest.config.ini.php file in the config/ directory. Using /etc/hosts you can redirect to 127.0.0.1 and use the saved "
            . "config."
        );
        $this->addNoValueOption(
            'set-symlinks',
            null,
            "Used by UI tests. Creates symlinks to root directory in tests/PHPUnit/proxy."
        );
        $this->addRequiredValueOption(
            'server-global',
            null,
            "Used by UI tests. Sets the \$_SERVER global variable from a JSON string."
        );
        $this->addRequiredValueOption(
            'plugins',
            null,
            "Used by UI tests. Comma separated list of plugin names to activate and install when setting up a fixture."
        );
        $this->addNoValueOption('enable-logging', null, 'If enabled, tests will log to the configured log file.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        if (!defined('PIWIK_TEST_MODE')) {
            define('PIWIK_TEST_MODE', true);
        }

        if ($input->getOption('enable-logging')) {
            putenv("MATOMO_TESTS_ENABLE_LOGGING=1");
        }

        Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator(new TestingEnvironmentVariables()));

        $serverGlobal = $input->getOption('server-global');
        if ($serverGlobal) {
            $_SERVER = json_decode($serverGlobal, true);
        }

        // Tear down any DB that already exists
        Db::destroyDatabaseObject();

        if (Config::getInstance()->database_tests['tables_prefix'] !== '') {
            throw new \Exception("To generate OmniFixture for the UI tests, you must set an empty tables_prefix in [database_tests]");
        }

        $this->requireFixtureFiles();
        $this->setIncludePathAsInTestBootstrap();

        $host = Config::getHostname();
        if (empty($host)) {
            $host = 'localhost';
            Url::setHost('localhost');
        }

        $configDomainToSave = $input->getOption('save-config');
        if (!empty($configDomainToSave)) {
            $pathToDomainConfig = PIWIK_INCLUDE_PATH . '/config/' . $host . '.config.ini.php';

            if (!file_exists($pathToDomainConfig)) {
                link(PIWIK_INCLUDE_PATH . '/config/config.ini.php', $pathToDomainConfig);
            }
        }

        if ($input->getOption('set-symlinks')) {
            $this->createSymbolicLinksForUITests();
        }

        $fixture = $this->createFixture($allowSave = !empty($configDomainToSave));

        $this->setupDatabaseOverrides($fixture);

        // perform setup and/or teardown
        if ($input->getOption('teardown')) {
            $fixture->getTestEnvironment()->save();
            $fixture->performTearDown();
        } else {
            $fixture->performSetUp();
        }

        $this->writeSuccessMessage("Fixture successfully set up!");

        $sqlDumpPath = $input->getOption('sqldump');
        if ($sqlDumpPath) {
            $this->createSqlDump($sqlDumpPath);
        }

        if (!empty($configDomainToSave)) {
            Config::getInstance()->forceSave();
        }

        return self::SUCCESS;
    }

    private function createSymbolicLinksForUITests()
    {
        // make sure symbolic links exist (phantomjs doesn't support symlink-ing yet)
        foreach (array('libs', 'plugins', 'tests', 'misc', 'node_modules', 'piwik.js', 'matomo.js') as $linkName) {
            $linkPath = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/' . $linkName;
            if (!file_exists($linkPath)) {
                $target = PIWIK_INCLUDE_PATH . '/' . $linkName;
                $success = @symlink($target, $linkPath);
                // setting symlink might fail when the symlink already exists but pointing to a no longer existing path/file
                // eg when sometimes running it on a VM and sometimes on the VM's host itself.
                if (!$success) {
                    unlink($linkPath);
                    symlink($target, $linkPath);
                }
            }
        }
    }

    private function createSqlDump($sqlDumpPath)
    {
        $output = $this->getOutput();
        $output->writeln("<info>Creating SQL dump...</info>");

        $databaseConfig = Config::getInstance()->database;
        $dbUser = $databaseConfig['username'];
        $dbPass = $databaseConfig['password'];
        $dbHost = $databaseConfig['host'];
        $dbName = $databaseConfig['dbname'];

        $command = "mysqldump --user='$dbUser' --password='$dbPass' --host='$dbHost' '$dbName' > '$sqlDumpPath'";
        $output->writeln("<info>Executing $command...</info>");
        passthru($command);

        $this->writeSuccessMessage("SQL dump created!");
    }

    private function setupDatabaseOverrides(Fixture $fixture)
    {
        $input = $this->getInput();
        $testingEnvironment = $fixture->getTestEnvironment();

        $optionsToOverride = array(
            'dbname' => $fixture->getDbName(),
            'host' => $input->getOption('db-host'),
            'username' => $input->getOption('db-user'),
            'password' => $input->getOption('db-pass'),
            'tables_prefix' => '',
        );
        foreach ($optionsToOverride as $configOption => $value) {
            if ($value) {
                $testingEnvironment->overrideConfig('database_tests', $configOption, $value);
                Config::getInstance()->database[$configOption] = $value;
            }
        }
    }

    private function createFixture($allowSave)
    {
        $input = $this->getInput();
        $fixtureClass = $input->getArgument('fixture');
        if (class_exists("Piwik\\Tests\\Fixtures\\" . $fixtureClass)) {
            $fixtureClass = "Piwik\\Tests\\Fixtures\\" . $fixtureClass;
        }

        if (!class_exists($fixtureClass)) {
            throw new \Exception("Cannot find fixture class '$fixtureClass'.");
        }

        /** @var Fixture $fixture */
        $fixture = new $fixtureClass();
        $fixture->printToScreen = true;

        $dbName = $input->getOption('db-name');
        if ($dbName) {
            $fixture->dbName = $dbName;
        }

        if ($input->getOption('persist-fixture-data')) {
            $fixture->persistFixtureData = true;
        }

        if ($input->getOption('drop')) {
            $fixture->resetPersistedFixture = true;
        }

        $extraPluginsToLoad = $input->getOption('plugins');
        if ($extraPluginsToLoad) {
            $fixture->extraPluginsToLoad = array_merge($fixture->extraPluginsToLoad, explode(',', $extraPluginsToLoad));
            $fixture->extraPluginsToLoad = array_unique($fixture->extraPluginsToLoad);
        }

        $fixture->extraDiEnvironments = array('ui-test');

        return $fixture;
    }

    private function requireFixtureFiles()
    {
        $file = $this->getInput()->getOption('file');
        if ($file) {
            if (is_file($file)) {
                require_once $file;
            } elseif (is_file(PIWIK_INCLUDE_PATH . '/' . $file)) {
                require_once PIWIK_INCLUDE_PATH . '/' . $file;
            } else {
                throw new \Exception("Cannot find --file option file '$file'.");
            }
        }
    }

    private function setIncludePathAsInTestBootstrap()
    {
        if (!defined('PIWIK_INCLUDE_SEARCH_PATH')) {
            define('PIWIK_INCLUDE_SEARCH_PATH', get_include_path()
                . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core'
                . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
                . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
        }
        @ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
        @set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
    }
}
