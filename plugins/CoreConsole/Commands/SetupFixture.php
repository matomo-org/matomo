<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Url;
use Piwik\Piwik;
use Piwik\Config;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console commands that sets up a fixture either in a local MySQL database or a remote one.
 *
 * TODO: use this console command in UI tests instead of setUpDatabase.php/tearDownDatabase.php scripts
 */
class SetupFixture extends ConsoleCommand
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbName = $input->getOption('db-name');
        if (!$dbName) {
            throw new \Exception("Required argument --db-name is not set.");
        }

        $this->requireFixtureFiles();
        $this->setIncludePathAsInTestBootstrap();

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

        if (empty(Url::getHost())) {
            Url::setHost('localhost');
        }

        // get the fixture class
        $fixtureClass = $input->getArgument('fixture');
        if (class_exists("Piwik\\Tests\\Fixtures\\" . $fixtureClass)) {
            $fixtureClass = "Piwik\\Tests\\Fixtures\\" . $fixtureClass;
        }

        if (!class_exists($fixtureClass)) {
            throw new \Exception("Cannot find fixture class '$fixtureClass'.");
        }

        // create the fixture
        $fixture = new $fixtureClass();
        $fixture->dbName = $dbName;
        $fixture->printToScreen = true;

        Config::getInstance()->setTestEnvironment();
        $fixture->createConfig = false;

        // setup database overrides
        $testingEnvironment = $fixture->getTestEnvironment();

        $optionsToOverride = array(
            'dbname' => $dbName,
            'host' => $input->getOption('db-host'),
            'user' => $input->getOption('db-user'),
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

        // perform setup and/or teardown
        if ($input->getOption('teardown')) {
            $testingEnvironment->save();
            $fixture->performTearDown();
        } else {
            $fixture->performSetUp();
        }

        $this->writeSuccessMessage($output, array("Fixture successfully setup!"));
    }

    private function requireFixtureFiles()
    {
        require_once "PHPUnit/Autoload.php";

        require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/FakeAccess.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';
        require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixture.php';

        $fixturesToLoad = array(
            '/tests/PHPUnit/Fixtures/*.php',
            '/tests/PHPUnit/UI/Fixtures/*.php',
        );
        foreach($fixturesToLoad as $fixturePath) {
            foreach (glob(PIWIK_INCLUDE_PATH . $fixturePath) as $file) {
                require_once $file;
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