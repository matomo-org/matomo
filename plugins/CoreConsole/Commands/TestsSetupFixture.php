<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Config;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Url;
use Piwik\Tests\Framework\Fixture;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->addArgument('fixture', InputArgument::REQUIRED,
            "The class name of the fixture to apply. Doesn't need to have a namespace if it exists in the " .
            "Piwik\\Tests\\Fixtures namespace.");

        $this->addOption('db-name', null, InputOption::VALUE_REQUIRED,
            "The name of the database that will contain the fixture data. This option is required to be set.");
        $this->addOption('file', null, InputOption::VALUE_REQUIRED,
            "The file location of the fixture. If this option is included the file will be required explicitly.");
        $this->addOption('db-host', null, InputOption::VALUE_REQUIRED,
            "The hostname of the MySQL database to use. Uses the default config value if not specified.");
        $this->addOption('db-user', null, InputOption::VALUE_REQUIRED,
            "The name of the MySQL user to use. Uses the default config value if not specified.");
        $this->addOption('db-pass', null, InputOption::VALUE_REQUIRED,
            "The MySQL user password to use. Uses the default config value if not specified.");
        $this->addOption('teardown', null, InputOption::VALUE_NONE,
            "If specified, the fixture will be torn down and the database deleted. Won't work if the --db-name " .
            "option isn't supplied.");
        $this->addOption('persist-fixture-data', null, InputOption::VALUE_NONE,
            "If specified, the database will not be dropped after the fixture is setup. If the database already " .
            "and the fixture was successfully setup before, nothing will happen.");
        $this->addOption('drop', null, InputOption::VALUE_NONE,
            "Forces the database to be dropped before setting up the fixture. Should be used in conjunction with" .
            " --persist-fixture-data when updating a pre-existing test database.");
        $this->addOption('sqldump', null, InputOption::VALUE_REQUIRED,
            "Creates an SQL dump after setting up the fixture and outputs the dump to the file specified by this option.");
        $this->addOption('save-config', null, InputOption::VALUE_NONE,
            "Saves the current configuration file as a config for a new Piwik domain. For example save-config --piwik-domain=mytest.localhost.com will create "
          . "a mytest.config.ini.php file in the config/ directory. Using /etc/hosts you can redirect to 127.0.0.1 and use the saved "
          . "config.");
        $this->addOption('set-phantomjs-symlinks', null, InputOption::VALUE_NONE,
            "Used by UI tests. Creates symlinks to root directory in tests/PHPUnit/proxy.");
        $this->addOption('server-global', null, InputOption::VALUE_REQUIRED,
            "Used by UI tests. Sets the \$_SERVER global variable from a JSON string.");
        $this->addOption('plugins', null, InputOption::VALUE_REQUIRED,
            "Used by UI tests. Comma separated list of plugin names to activate and install when setting up a fixture.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverGlobal = $input->getOption('server-global');
        if ($serverGlobal) {
            $_SERVER = json_decode($serverGlobal, true);
        }

        $this->requireFixtureFiles($input);
        $this->setIncludePathAsInTestBootstrap();

        $host = Url::getHost();
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

        $fixture = $this->createFixture($input);

        $this->setupDatabaseOverrides($input, $fixture);

        // perform setup and/or teardown
        if ($input->getOption('teardown')) {
            $fixture->getTestEnvironment()->save();
            $fixture->performTearDown();
        } else {
            $fixture->performSetUp();
        }

        if ($input->getOption('set-phantomjs-symlinks')) {
            $this->createSymbolicLinksForUITests();
        }

        $this->writeSuccessMessage($output, array("Fixture successfully setup!"));

        $sqlDumpPath = $input->getOption('sqldump');
        if ($sqlDumpPath) {
            $this->createSqlDump($sqlDumpPath, $output);
        }

        if (!empty($configDomainToSave)) {
            Config::getInstance()->forceSave();
        }
    }

    private function createSymbolicLinksForUITests()
    {
        // make sure symbolic links exist (phantomjs doesn't support symlink-ing yet)
        foreach (array('libs', 'plugins', 'tests', 'piwik.js') as $linkName) {
            $linkPath = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/' . $linkName;
            if (!file_exists($linkPath)) {
                symlink(PIWIK_INCLUDE_PATH . '/' . $linkName, $linkPath);
            }
        }
    }

    private function createSqlDump($sqlDumpPath, OutputInterface $output)
    {
        $output->writeln("<info>Creating SQL dump...</info>");

        $databaseConfig = Config::getInstance()->database;
        $dbUser = $databaseConfig['username'];
        $dbPass = $databaseConfig['password'];
        $dbHost = $databaseConfig['host'];
        $dbName = $databaseConfig['dbname'];

        $command = "mysqldump --user='$dbUser' --password='$dbPass' --host='$dbHost' '$dbName' > '$sqlDumpPath'";
        $output->writeln("<info>Executing $command...</info>");
        passthru($command);

        $this->writeSuccessMessage($output, array("SQL dump created!"));
    }

    private function setupDatabaseOverrides(InputInterface $input, Fixture $fixture)
    {
        $testingEnvironment = $fixture->getTestEnvironment();

        $optionsToOverride = array(
            'dbname' => $fixture->getDbName(),
            'host' => $input->getOption('db-host'),
            'username' => $input->getOption('db-user'),
            'password' => $input->getOption('db-pass')
        );
        foreach ($optionsToOverride as $configOption => $value) {
            if ($value) {
                $configOverride = $testingEnvironment->configOverride;
                $configOverride['database_tests'][$configOption] = $configOverride['database'][$configOption] = $value;
                $testingEnvironment->configOverride = $configOverride;

                Config::getInstance()->database[$configOption] = $value;
            }
        }
    }

    private function createFixture(InputInterface $input)
    {
        $fixtureClass = $input->getArgument('fixture');
        if (class_exists("Piwik\\Tests\\Fixtures\\" . $fixtureClass)) {
            $fixtureClass = "Piwik\\Tests\\Fixtures\\" . $fixtureClass;
        }

        if (!class_exists($fixtureClass)) {
            throw new \Exception("Cannot find fixture class '$fixtureClass'.");
        }

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
            $fixture->extraPluginsToLoad = explode(',', $extraPluginsToLoad);
        }

        if ($fixture->createConfig) {
            Config::getInstance()->setTestEnvironment($pathLocal = null, $pathGlobal = null, $pathCommon = null, $allowSaving = true);
        }

        $fixture->createConfig = false;

        return $fixture;
    }

    private function requireFixtureFiles(InputInterface $input)
    {
        \Piwik\Loader::registerTestNamespace();

        require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/FakeAccess.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/IntegrationTestCase.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixture.php';

        $fixturesToLoad = array(
            '/tests/PHPUnit/Fixtures/*.php',
            '/tests/PHPUnit/UI/Fixtures/*.php',
            '/plugins/*/tests/Fixtures/*.php',
            '/plugins/*/Test/Fixtures/*.php',
        );
        foreach($fixturesToLoad as $fixturePath) {
            foreach (glob(PIWIK_INCLUDE_PATH . $fixturePath) as $file) {
                require_once $file;
            }
        }

        $file = $input->getOption('file');
        if ($file) {
            if (is_file($file)) {
                require_once $file;
            } else if (is_file(PIWIK_INCLUDE_PATH . '/' . $file)) {
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
