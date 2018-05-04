<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Config;
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

    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');

        $this->tracker = new TestTracker();
        $this->request = $this->buildRequest(array('idsite' => 1));
    }

    public function tearDown()
    {
        $this->restoreConfigFile();
        
        if($this->tracker) {
            $this->tracker->disconnectDatabase();
        }
        
        if (array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS)) {
            unset($GLOBALS['PIWIK_TRACKER_DEBUG']);
        }
        
        parent::tearDown();
    }

    public function test_isInstalled_shouldReturnTrue_AsPiwikIsInstalled()
    {
        $this->assertTrue($this->tracker->isInstalled());
    }

    public function test_shouldRecordStatistics_shouldReturnTrue_IfEnabled_WhichItIsByDefault()
    {
        $this->assertTrue($this->tracker->shouldRecordStatistics());
    }

    public function test_shouldRecordStatistics_shouldReturnFalse_IfEnabledButNotInstalled()
    {
        $this->tracker->setIsNotInstalled();
        $this->assertFalse($this->tracker->shouldRecordStatistics());
    }

    public function test_shouldRecordStatistics_shouldReturnFalse_IfDisabledButInstalled()
    {
        $oldConfig = Tracker\TrackerConfig::getConfigValue('record_statistics');
        Tracker\TrackerConfig::setConfigValue('record_statistics', 0);

        $this->assertFalse($this->tracker->shouldRecordStatistics());

        Tracker\TrackerConfig::setConfigValue('record_statistics', $oldConfig); // reset
    }

    public function test_loadTrackerEnvironment_shouldSetGlobalsDebugVar_WhichShouldBeDisabledByDefault()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        Tracker::loadTrackerEnvironment();

        $this->assertFalse($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function test_loadTrackerEnvironment_shouldSetGlobalsDebugVar()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        $oldConfig = Tracker\TrackerConfig::getConfigValue('debug');
        Tracker\TrackerConfig::setConfigValue('debug', 1);

        Tracker::loadTrackerEnvironment();
        $this->assertTrue($this->tracker->isDebugModeEnabled());

        Tracker\TrackerConfig::setConfigValue('debug', $oldConfig); // reset

        $this->assertTrue($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function test_loadTrackerEnvironment_shouldEnableTrackerMode()
    {
        $this->assertTrue(!array_key_exists('PIWIK_TRACKER_DEBUG', $GLOBALS));

        $this->assertFalse(SettingsServer::isTrackerApiRequest());

        Tracker::loadTrackerEnvironment();

        $this->assertTrue(SettingsServer::isTrackerApiRequest());
    }

    public function test_loadTrackerEnvironment_shouldNotThrow_whenConfigNotFound()
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

    public function test_isDatabaseConnected_shouldReturnFalse_IfNotConnected()
    {
        $this->tracker->disconnectDatabase();

        $this->assertFalse($this->tracker->isDatabaseConnected());
    }

    public function test_getDatabase_shouldReturnDbInstance()
    {
        $db = $this->tracker->getDatabase();

        $this->assertInstanceOf('Piwik\\Tracker\\Db', $db);
    }

    public function test_isDatabaseConnected_shouldReturnTrue_WhenDbIsConnected()
    {
        $db = $this->tracker->getDatabase(); // make sure connected
        $this->assertNotEmpty($db);

        $this->assertTrue($this->tracker->isDatabaseConnected());
    }

    public function test_disconnectDatabase_shouldDisconnectDb()
    {
        $this->tracker->getDatabase(); // make sure connected
        $this->assertTrue($this->tracker->isDatabaseConnected());

        $this->tracker->disconnectDatabase();

        $this->assertFalse($this->tracker->isDatabaseConnected());
    }

    public function test_trackRequest_shouldNotTrackAnything_IfRequestIsEmpty()
    {
        $called = false;
        Piwik::addAction('Tracker.makeNewVisitObject', function () use (&$called) {
            $called = true;
        });

        $this->tracker->trackRequest(new Request(array()));

        $this->assertFalse($called);
    }

    public function test_trackRequest_shouldTrack_IfRequestIsNotEmpty()
    {
        $called = false;
        Piwik::addAction('Tracker.makeNewVisitObject', function () use (&$called) {
            $called = true;
        });

        $this->tracker->trackRequest($this->request);

        $this->assertTrue($called);
    }

    public function test_trackRequest_shouldIncreaseLoggedRequestsCounter()
    {
        $this->tracker->trackRequest($this->request);
        $this->assertSame(1, $this->tracker->getCountOfLoggedRequests());

        $this->tracker->trackRequest($this->request);
        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_trackRequest_shouldIncreaseLoggedRequestsCounter_EvenIfRequestIsEmpty()
    {
        $request = $this->buildRequest(array());
        $this->assertTrue($request->isEmptyRequest());

        $this->tracker->trackRequest($request);
        $this->assertSame(1, $this->tracker->getCountOfLoggedRequests());

        $this->tracker->trackRequest($request);
        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_trackRequest_shouldActuallyTrack()
    {
        $request = $this->buildRequest(array('idsite' => 1, 'url' => 'http://www.example.com', 'action_name' => 'test', 'rec' => 1));
        $this->tracker->trackRequest($request);

        $this->assertActionEquals('test', 1);
        $this->assertActionEquals('example.com', 2);
    }

    public function test_trackRequest_shouldTrackOutlinkWithFragment()
    {
        $request = $this->buildRequest(array('idsite' => 1, 'link' => 'http://example.com/outlink#fragment-here', 'rec' => 1));
        $this->tracker->trackRequest($request);

        $this->assertActionEquals('http://example.com/outlink#fragment-here', 1);
    }

    public function test_trackRequest_shouldTrackDownloadWithFragment()
    {
        $request = $this->buildRequest(array('idsite' => 1, 'download' => 'http://example.com/file.zip#fragment-here&pk_campaign=Campaign param accepted here', 'rec' => 1));
        $this->tracker->trackRequest($request);

        $this->assertActionEquals('http://example.com/file.zip#fragment-here&amp;pk_campaign=Campaign param accepted here', 1);
    }

    public function test_main_shouldReturnEmptyPiwikResponse_IfNoRequestsAreGiven()
    {
        $requestSet = $this->getEmptyRequestSet();
        $requestSet->setRequests(array());

        $response = $this->tracker->main($this->getDefaultHandler(), $requestSet);

        $expected = "This resource is part of Matomo. Keep full control of your data with the leading free and open source <a href='https://matomo.org' target='_blank'>digital analytics platform</a> for web and mobile.";
        $this->assertEquals($expected, $response);
    }

    public function test_main_shouldReturnApiResponse_IfRequestsAreGiven()
    {
        $response = $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        Fixture::checkResponse($response);
    }

    public function test_main_shouldReturnNotReturnAnyApiResponse_IfImageIsDisabled()
    {
        $_GET['send_image'] = '0';

        $response = $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        unset($_GET['send_image']);

        $this->assertEquals('', $response);
    }

    public function test_main_shouldActuallyTrackNumberOfTrackedRequests()
    {
        $this->assertSame(0, $this->tracker->getCountOfLoggedRequests());

        $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_main_shouldNotTrackAnythingButStillReturnApiResponse_IfNotInstalledOrShouldNotRecordStats()
    {
        $this->tracker->setIsNotInstalled();
        $response = $this->tracker->main($this->getDefaultHandler(), $this->getRequestSetWithRequests());

        Fixture::checkResponse($response);
        $this->assertSame(0, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_main_shouldReadValuesFromGETandPOSTifNoRequestSet()
    {
        $_GET  = array('idsite' => '1');
        $_POST = array('url' => 'http://localhost/post');

        $requestSet = $this->getEmptyRequestSet();
        $response   = $this->tracker->main($this->getDefaultHandler(), $requestSet);

        $_GET  = array();
        $_POST = array();

        Fixture::checkResponse($response);
        $this->assertSame(1, $this->tracker->getCountOfLoggedRequests());

        $identifiedRequests = $requestSet->getRequests();
        $this->assertCount(1, $identifiedRequests);
        $this->assertEquals(array('idsite' => '1', 'url' => 'http://localhost/post'),
                            $identifiedRequests[0]->getParams());
    }

    public function test_main_shouldPostEndEvent()
    {
        $called = false;
        Piwik::addAction('Tracker.end', function () use (&$called) {
            $called = true;
        });

        $this->tracker->main(new Handler(), new RequestSet());

        $this->assertTrue($called);
    }

    public function test_main_shouldPostEndEvent_EvenIfShouldNotRecordStats()
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

    public function test_main_shouldPostEndEvent_EvenIfThereIsAnException()
    {
        $called = false;
        Piwik::addAction('Tracker.end', function () use (&$called) {
            $called = true;
        });

        $handler = new Handler();
        $handler->enableTriggerExceptionInProcess();

        $requestSet = new RequestSet();
        $requestSet->setRequests(array($this->buildRequest(1), $this->buildRequest(1)));

        $this->tracker->main($handler, $requestSet);

        $this->assertTrue($handler->isOnException);
        $this->assertTrue($called);
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
        $requestSet->setRequests(array(
            $this->buildRequest(array('idsite' => '1', 'url' => 'http://localhost')),
            $this->buildRequest(array('idsite' => '1', 'url' => 'http://localhost/test'))
        ));

        return $requestSet;
    }

    private function assertActionEquals($expected, $idaction)
    {
        $actionName = Tracker::getDatabase()->fetchOne("SELECT name FROM " . Common::prefixTable('log_action') . " WHERE idaction = ?", array($idaction));
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
        if(file_exists($this->getLocalConfigPathMoved())){
            rename($this->getLocalConfigPathMoved(), $this->getLocalConfigPath());
        }
    }
}

class TestTracker extends Tracker
{
    public function __construct()
    {
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
