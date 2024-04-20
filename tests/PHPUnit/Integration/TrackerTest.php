<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\Mock\Tracker\Handler;
use Piwik\Tests\Framework\Mock\Tracker\RequestSet;
use Piwik\Tracker;
use Piwik\Tracker\Request;

/**
 * @group TrackerTest
 * @group Tracker
 */
class TrackerTest extends IntegrationTestCase
{
    /**
     * @var TestTracker
     */
    private $tracker;

    /**
     * @var Request
     */
    private $request;

    private $iniTimeZone;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');

        $this->tracker = new TestTracker();
        $this->request = $this->buildRequest(['idsite' => 1]);

        $this->iniTimeZone = ini_get('date.timezone');
    }

    public function tearDown(): void
    {
        $this->restoreConfigFile();

        if ($this->tracker) {
            $this->tracker->disconnectDatabase();
        }

        if (array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS)) {
            unset($GLOBALS['PIWIK_TRACKER_DEBUG']);
        }

        ini_set('date.timezone', $this->iniTimeZone);

        parent::tearDown();
    }

    public function testIsInstalledShouldReturnTrueAsPiwikIsInstalled()
    {
        $this->assertTrue($this->tracker->isInstalled());
    }

    public function testShouldRecordStatisticsShouldReturnTrueIfEnabledWhichItIsByDefault()
    {
        $this->assertTrue($this->tracker->shouldRecordStatistics());
    }

    public function testShouldRecordStatisticsShouldReturnFalseIfEnabledButNotInstalled()
    {
        $this->tracker->setIsNotInstalled();
        $this->assertFalse($this->tracker->shouldRecordStatistics());
    }

    public function testShouldRecordStatisticsShouldReturnFalseIfDisabledButInstalled()
    {
        $oldConfig = Tracker\TrackerConfig::getConfigValue('record_statistics');
        Tracker\TrackerConfig::setConfigValue('record_statistics', 0);

        $this->assertFalse($this->tracker->shouldRecordStatistics());

        Tracker\TrackerConfig::setConfigValue('record_statistics', $oldConfig); // reset
    }

    public function testLoadTrackerEnvironmentShouldSetGlobalsDebugVarWhichShouldBeDisabledByDefault()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        Tracker::loadTrackerEnvironment();

        $this->assertFalse($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function testLoadTrackerEnvironmentShouldSetGlobalsDebugVar()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        $oldConfig = Tracker\TrackerConfig::getConfigValue('debug');
        Tracker\TrackerConfig::setConfigValue('debug', 1);

        Tracker::loadTrackerEnvironment();
        $this->assertTrue($this->tracker->isDebugModeEnabled());

        Tracker\TrackerConfig::setConfigValue('debug', $oldConfig); // reset

        $this->assertTrue($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function testLoadTrackerEnvironmentShouldEnableTrackerMode()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        $this->assertFalse(SettingsServer::isTrackerApiRequest());

        Tracker::loadTrackerEnvironment();

        $this->assertTrue(SettingsServer::isTrackerApiRequest());
    }

    public function testLoadTrackerEnvironmentShouldNotThrowWhenConfigNotFound()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        $this->assertFalse(SettingsServer::isTrackerApiRequest());

        $this->assertTrue(is_readable(Config::getInstance()->getLocalPath()));

        $this->removeConfigFile();

        $this->assertFalse(is_readable(Config::getInstance()->getLocalPath()));

        Tracker::loadTrackerEnvironment();

        $this->assertTrue(SettingsServer::isTrackerApiRequest());

        //always reset on the test itself
        $this->restoreConfigFile();
    }

    public function testIsDatabaseConnectedShouldReturnFalseIfNotConnected()
    {
        $this->tracker->disconnectDatabase();

        $this->assertFalse($this->tracker->isDatabaseConnected());
    }

    public function testGetDatabaseShouldReturnDbInstance()
    {
        $db = $this->tracker->getDatabase();

        $this->assertInstanceOf('Piwik\\Tracker\\Db', $db);
    }

    public function testIsDatabaseConnectedShouldReturnTrueWhenDbIsConnected()
    {
        $db = $this->tracker->getDatabase(); // make sure connected
        $this->assertNotEmpty($db);

        $this->assertTrue($this->tracker->isDatabaseConnected());
    }

    public function testDisconnectDatabaseShouldDisconnectDb()
    {
        $this->tracker->getDatabase(); // make sure connected
        $this->assertTrue($this->tracker->isDatabaseConnected());

        $this->tracker->disconnectDatabase();

        $this->assertFalse($this->tracker->isDatabaseConnected());
    }

    public function testTrackRequestShouldNotTrackAnythingIfRequestIsEmpty()
    {
        $called = false;
        Piwik::addAction('Tracker.makeNewVisitObject', function () use (&$called) {
            $called = true;
        });

        $this->tracker->trackRequest(new Request([]));

        $this->assertFalse($called);
    }

    public function testTrackRequestShouldTrackIfRequestIsNotEmpty()
    {
        $called = false;
        Piwik::addAction('Tracker.makeNewVisitObject', function () use (&$called) {
            $called = true;
        });

        $this->tracker->trackRequest($this->request);

        $this->assertTrue($called);
    }

    public function testTrackRequestShouldIncreaseLoggedRequestsCounter()
    {
        $this->tracker->trackRequest($this->request);
        $this->assertSame(1, $this->tracker->getCountOfLoggedRequests());

        $this->tracker->trackRequest($this->request);
        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function testTrackRequestShouldIncreaseLoggedRequestsCounterEvenIfRequestIsEmpty()
    {
        $request = $this->buildRequest([]);
        $this->assertTrue($request->isEmptyRequest());

        $this->tracker->trackRequest($request);
        $this->assertSame(1, $this->tracker->getCountOfLoggedRequests());

        $this->tracker->trackRequest($request);
        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function testTrackRequestShouldActuallyTrack()
    {
        $request = $this->buildRequest(['idsite' => 1, 'url' => 'http://www.example.com', 'action_name' => 'test', 'rec' => 1]);
        $this->tracker->trackRequest($request);

        $this->assertActionEquals('test', 1);
        $this->assertActionEquals('example.com', 2);
    }

    public function testTrackRequestShouldTrackOutlinkWithFragment()
    {
        $request = $this->buildRequest(['idsite' => 1, 'link' => 'http://example.com/outlink#fragment-here', 'rec' => 1]);
        $this->tracker->trackRequest($request);

        $this->assertActionEquals('http://example.com/outlink#fragment-here', 1);
    }

    public function testTrackRequestShouldTrackDownloadWithFragment()
    {
        $request = $this->buildRequest(['idsite' => 1, 'download' => 'http://example.com/file.zip#fragment-here&pk_campaign=Campaign param accepted here', 'rec' => 1]);
        $this->tracker->trackRequest($request);

        $this->assertActionEquals('http://example.com/file.zip#fragment-here&amp;pk_campaign=Campaign param accepted here', 1);
    }

    public function testMainShouldReturnEmptyPiwikResponseIfNoRequestsAreGiven()
    {
        $requestSet = $this->getEmptyRequestSet();
        $requestSet->setRequests([]);

        $response = $this->tracker->main($this->getDefaultHandler(), $requestSet);

        $expected = "This resource is part of Matomo. Keep full control of your data with the leading free and open source <a href='https://matomo.org' target='_blank' rel='noopener noreferrer nofollow'>web analytics & conversion optimisation platform</a>.<br>\nThis file is the endpoint for the Matomo tracking API. If you want to access the Matomo UI or use the Reporting API, please use <a href='index.php'>index.php</a> instead.\n";
        $this->assertEquals($expected, $response);
    }

    public function testMainShouldReturnApiResponseIfRequestsAreGiven()
    {
        $response = $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        Fixture::checkResponse($response);
    }

    public function testMainShouldReturnNotReturnAnyApiResponseIfImageIsDisabled()
    {
        $_GET['send_image'] = '0';

        $response = $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        unset($_GET['send_image']);

        $this->assertEquals('', $response);
    }

    public function testMainShouldHandPreflightCorsRequestWithoutTracking()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] = 'GET';

        $handler = $this->getMockBuilder(Handler::class)->getMock();
        $handler->expects($this->never())->method('init');

        $response = $this->tracker->main($handler, $this->getRequestSetWithRequests());
        $this->assertNull($response);

        $this->assertSame(0, $this->tracker->getCountOfLoggedRequests());
    }

    public function testMainShouldActuallyTrackNumberOfTrackedRequests()
    {
        $this->assertSame(0, $this->tracker->getCountOfLoggedRequests());

        $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function testMainShouldNotTrackAnythingButStillReturnApiResponseIfNotInstalledOrShouldNotRecordStats()
    {
        $this->tracker->setIsNotInstalled();
        $response = $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        Fixture::checkResponse($response);
        $this->assertSame(0, $this->tracker->getCountOfLoggedRequests());
    }

    public function testMainShouldReadValuesFromGETandPOSTifNoRequestSet()
    {
        $_GET  = ['idsite' => '1'];
        $_POST = ['url' => 'http://localhost/post'];

        $requestSet = $this->getEmptyRequestSet();
        $response   = $this->tracker->main($this->getDefaultHandler(), $requestSet);

        $_GET  = [];
        $_POST = [];

        Fixture::checkResponse($response);
        $this->assertSame(1, $this->tracker->getCountOfLoggedRequests());

        $identifiedRequests = $requestSet->getRequests();
        $this->assertCount(1, $identifiedRequests);
        $this->assertEquals(
            ['idsite' => '1', 'url' => 'http://localhost/post'],
            $identifiedRequests[0]->getParams()
        );
    }

    public function testMainShouldPostEndEvent()
    {
        $called = false;
        Piwik::addAction('Tracker.end', function () use (&$called) {
            $called = true;
        });

        $this->tracker->main(new Handler(), new RequestSet());

        $this->assertTrue($called);
    }

    public function testMainShouldPostEndEventEvenIfShouldNotRecordStats()
    {
        $called = false;
        Piwik::addAction('Tracker.end', function () use (&$called) {
            $called = true;
        });

        $handler = new Handler();

        $this->tracker->disableRecordStatistics();
        $this->tracker->main($handler, new RequestSet());

        $this->assertFalse($handler->isProcessed);
        $this->assertTrue($called);
    }

    public function testMainShouldPostEndEventEvenIfThereIsAnException()
    {
        $called = false;
        Piwik::addAction('Tracker.end', function () use (&$called) {
            $called = true;
        });

        $handler = new Handler();
        $handler->enableTriggerExceptionInProcess();

        $requestSet = new RequestSet();
        $requestSet->setRequests([$this->buildRequest(1), $this->buildRequest(1)]);

        $this->tracker->main($handler, $requestSet);

        $this->assertTrue($handler->isOnException);
        $this->assertTrue($called);
    }

    public function testArchiveInvalidationDifferentServerAndWebsiteTimezones()
    {
        // Server timezone is UTC
        ini_set('date.timezone', 'UTC');

        // Website timezone is New York
        $idSite = Fixture::createWebsite(
            '2014-01-01 00:00:00',
            0,
            false,
            false,
            1,
            null,
            null,
            'America/New_York'
        );

        // It's 3 April in UTC but 2 April in New York
        Date::$now = 1554257039;

        $this->tracker = new TestTracker();

        $this->request = $this->buildRequest(['idsite' => $idSite]);
        $this->request->setParam('rec', 1);
        $this->request->setCurrentTimestamp(Date::$now);
        $this->tracker->trackRequest($this->request);

        // make sure today archives are not invalidated
        $this->assertEquals([], Option::getLike('report_to_invalidate_2_2019-04-02%'));
    }

    public function testTrackingNewVisitOfKnownVisitor()
    {
        Fixture::createWebsite('2015-01-01 00:00:00');

        // track one visit
        $t = self::$fixture->getTracker($idSite = 1, '2015-01-01', $defaultInit = true, $useLocalTracker = true);
        $t->setForceVisitDateTime('2015-08-06 07:53:09');
        $t->setNewVisitorId();
        Fixture::checkResponse($t->doTrackPageView('page view'));

        // track action 2 seconds later w/ new_visit=1
        $t->setForceVisitDateTime('2015-08-06 07:53:11');
        $t->setCustomTrackingParameter('new_visit', '1');
        Fixture::checkResponse($t->doTrackPageView('page view 2'));

        $this->assertEquals(2, $this->getVisitCount());
        $this->assertEquals(1, $this->getReturningVisitorCount());
    }

    private function getDefaultHandler()
    {
        return new Tracker\Handler();
    }

    private function getEmptyRequestSet()
    {
        return new RequestSet();
    }

    private function getRequestSetWithRequests()
    {
        $requestSet = $this->getEmptyRequestSet();
        $requestSet->setRequests([
            $this->buildRequest(['idsite' => '1', 'url' => 'http://localhost']),
            $this->buildRequest(['idsite' => '1', 'url' => 'http://localhost/test'])
        ]);

        return $requestSet;
    }

    private function assertActionEquals($expected, $idaction)
    {
        $actionName = Tracker::getDatabase()->fetchOne("SELECT name FROM " . Common::prefixTable('log_action') . " WHERE idaction = ?", [$idaction]);
        $this->assertEquals($expected, $actionName);
    }

    private function buildRequest($params)
    {
        return new Request($params);
    }

    /**
     * @return string
     */
    protected function getLocalConfigPath()
    {
        return PIWIK_USER_PATH . '/config/config.ini.php';
    }

    /**
     * @return string
     */
    protected function getLocalConfigPathMoved()
    {
        return PIWIK_USER_PATH . '/config/tmp-config.ini.php';
    }

    protected function removeConfigFile()
    {
        rename($this->getLocalConfigPath(), $this->getLocalConfigPathMoved());
    }

    protected function restoreConfigFile()
    {
        if (file_exists($this->getLocalConfigPathMoved())) {
            rename($this->getLocalConfigPathMoved(), $this->getLocalConfigPath());
        }
    }

    private function getVisitCount()
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_visit'));
    }

    private function getReturningVisitorCount()
    {
        return Db::fetchOne("SELECT COUNT(DISTINCT idvisitor) FROM " . Common::prefixTable('log_visit') . ' WHERE visitor_returning = 1');
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }
}

class TestTracker extends Tracker
{
    public $record;

    public function __construct()
    {
        parent::__construct();

        $this->isInstalled = true;
        $this->record = true;
    }

    public function setIsNotInstalled()
    {
        $this->isInstalled = false;
    }

    public function disconnectDatabase()
    {
        parent::disconnectDatabase();
    }

    public function shouldRecordStatistics()
    {
        if (! $this->record) {
            return false;
        }
        return parent::shouldRecordStatistics();
    }

    public function disableRecordStatistics()
    {
        $this->record = false;
    }
}
