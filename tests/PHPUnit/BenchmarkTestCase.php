<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Config;
use Piwik\Db;
use Piwik\Common;
use Piwik\Plugins\Goals\API;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/IntegrationTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/LocalTracker.php';

// require fixtures
foreach (glob(PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Benchmarks/Fixtures/*.php') as $file) {
    require_once $file;
}

/**
 * Base class for benchmarks.
 */
abstract class BenchmarkTestCase extends IntegrationTestCase
{
    protected static $fixture;

    public static function setUpBeforeClass()
    {
        $dbName = false;
        if (!empty($GLOBALS['PIWIK_BENCHMARK_DATABASE'])) {
            $dbName = $GLOBALS['PIWIK_BENCHMARK_DATABASE'];
        }

        // connect to database
        self::createTestConfig();
        self::connectWithoutDatabase();

        // create specified fixture (global var not set, use default no-data fixture (see end of this file))
        if (empty($GLOBALS['PIWIK_BENCHMARK_FIXTURE'])) {
            $fixtureName = 'Piwik_Test_Fixture_EmptyOneSite';
        } else {
            $fixtureName = 'Piwik_Test_Fixture_' . $GLOBALS['PIWIK_BENCHMARK_FIXTURE'];
        }
        self::$fixture = new $fixtureName;

        // figure out if the desired fixture has already been setup, and if not empty the database
        $installedFixture = false;
        try {
            if (isset(self::$fixture->tablesPrefix)) {
                Config::getInstance()->database['tables_prefix'] = self::$fixture->tablesPrefix;
            }

            Db::query("USE " . $dbName);
            $installedFixture = \Piwik\Option::get('benchmark_fixture_name');
        } catch (Exception $ex) {
            // ignore
        }

        $createEmptyDatabase = $fixtureName != $installedFixture;
        parent::_setUpBeforeClass($dbName, $createEmptyDatabase, $createConfig = false, $installPlugins = true);

        // if we created an empty database, setup the fixture
        if ($createEmptyDatabase) {
            self::$fixture->setUp();
            \Piwik\Option::set('benchmark_fixture_name', $fixtureName);
        }
    }

    public static function tearDownAfterClass()
    {
        // only drop the database if PIWIK_BENCHMARK_DATABASE isn't set
        $dropDatabase = empty($GLOBALS['PIWIK_BENCHMARK_DATABASE']);
        parent::_tearDownAfterClass($dropDatabase);
    }

    /**
     * Creates a tracking object that invokes the tracker directly (w/o going through HTTP).
     */
    public static function getLocalTracker($idSite)
    {
        $t = new Piwik_LocalTracker($idSite, Test_Piwik_BaseFixture::getTrackerUrl());
        $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)");
        $t->setBrowserLanguage('fr');
        $t->setLocalTime('12:34:06');
        $t->setResolution(1024, 768);
        $t->setBrowserHasCookies(true);
        $t->setPlugins($flash = true, $java = true, $director = false);
        $t->setTokenAuth(Test_Piwik_BaseFixture::getTokenAuth());
        return $t;
    }
}

/**
 * Reusable fixture. Adds one site w/ goals and no visit data.
 */
class Piwik_Test_Fixture_EmptyOneSite
{
    public $date = '2010-01-01';
    public $period = 'day';
    public $idSite = 1;

    public function setUp()
    {
        // add one site
        Test_Piwik_BaseFixture::createWebsite(
            $this->date, $ecommerce = 1, $siteName = "Site #0", $siteUrl = "http://whatever.com/");

        // add two goals
        $goals = API::getInstance();
        $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains');
    }
}
