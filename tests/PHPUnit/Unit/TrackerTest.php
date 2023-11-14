<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Tracker\Request;
use Piwik\Tests\Framework\Mock\Tracker\Handler;
use Piwik\Tests\Framework\Mock\Tracker\RequestSet;
use Piwik\Tracker;

/**
 * @group TrackerTest
 * @group Tracker
 */
class TrackerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestTracker
     */
    private $tracker;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var RequestSet
     */
    private $requestSet;

    private $time;

    public function setUp(): void
    {
        parent::setUp();

        $this->time = time();
        $this->tracker = new TestTracker();
        $this->handler = new Handler();
        $this->requestSet = new RequestSet();
        $this->requestSet->setRequests(array($this->buildRequest(1), $this->buildRequest(1)));
    }

    public function test_isDebugModeEnabled_shouldReturnFalse_ByDefault()
    {
        unset($GLOBALS['PIWIK_TRACKER_DEBUG']);
        $this->assertFalse($this->tracker->isDebugModeEnabled());
    }

    public function test_isDebugModeEnabled_shouldReturnFalse_IfDisabled()
    {
        $GLOBALS['PIWIK_TRACKER_DEBUG'] = false;

        $this->assertFalse($this->tracker->isDebugModeEnabled());

        unset($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function test_isDebugModeEnabled_shouldReturnTrue_IfEnabled()
    {
        $GLOBALS['PIWIK_TRACKER_DEBUG'] = true;

        $this->assertTrue($this->tracker->isDebugModeEnabled());

        unset($GLOBALS['PIWIK_TRACKER_DEBUG']);
    }

    public function test_main_shouldReturnFinishedResponse()
    {
        $response = $this->tracker->main($this->handler, $this->requestSet);

        $this->assertEquals('My Rendered Content', $response);
    }

    public function test_main_shouldReturnResponse_EvenWhenThereWasAnExceptionDuringProcess()
    {
        $this->handler->enableTriggerExceptionInProcess();
        $response = $this->tracker->main($this->handler, $this->requestSet);

        $this->assertEquals('My Exception During Process', $response);
    }

    public function test_main_shouldReturnResponse_EvenWhenThereWasAnExceptionDuringInitRequests()
    {
        $this->requestSet->enableThrowExceptionOnInit();
        $response = $this->tracker->main($this->handler, $this->requestSet);

        $this->assertEquals('Init requests and token auth exception', $response);
    }

    public function test_main_shouldTriggerHandlerInitAndFinishEvent()
    {
        $this->tracker->main($this->handler, $this->requestSet);

        $this->assertTrue($this->handler->isInit);
        $this->assertTrue($this->handler->isProcessed);
        $this->assertTrue($this->handler->isFinished);
        $this->assertFalse($this->handler->isOnException);
    }

    public function test_main_shouldTriggerHandlerInitAndFinishEvent_EvenIfShouldNotRecordStats()
    {
        $this->tracker->disableRecordStatistics();
        $this->tracker->main($this->handler, $this->requestSet);

        $this->assertTrue($this->handler->isInit);
        $this->assertFalse($this->handler->isProcessed);
        $this->assertTrue($this->handler->isFinished);
        $this->assertFalse($this->handler->isOnException);
    }

    public function test_main_shouldTriggerHandlerInitAndFinishEvent_EvenIfThereIsAnException()
    {
        $this->handler->enableTriggerExceptionInProcess();
        $this->tracker->main($this->handler, $this->requestSet);

        $this->assertTrue($this->handler->isInit);
        $this->assertTrue($this->handler->isFinished);
        $this->assertTrue($this->handler->isOnException);
    }

    public function test_track_shouldTrack_IfThereAreRequests()
    {
        $this->tracker->track($this->handler, $this->requestSet);

        $this->assertTrue($this->handler->isOnStartTrackRequests);
        $this->assertTrue($this->handler->isProcessed);
        $this->assertTrue($this->handler->isOnAllRequestsTracked);
        $this->assertFalse($this->handler->isOnException);
    }

    public function test_track_shouldNotTrackAnything_IfTrackingIsDisabled()
    {
        $this->tracker->disableRecordStatistics();
        $this->tracker->track($this->handler, $this->requestSet);

        $this->assertFalse($this->handler->isOnStartTrackRequests);
        $this->assertFalse($this->handler->isProcessed);
        $this->assertFalse($this->handler->isOnAllRequestsTracked);
        $this->assertFalse($this->handler->isOnException);
    }

    public function test_track_shouldNotTrackAnything_IfNoRequestsAreSet()
    {
        $this->requestSet->setRequests(array());
        $this->tracker->track($this->handler, $this->requestSet);

        $this->assertFalse($this->handler->isOnStartTrackRequests);
        $this->assertFalse($this->handler->isProcessed);
        $this->assertFalse($this->handler->isOnAllRequestsTracked);
        $this->assertFalse($this->handler->isOnException);
    }

    public function test_track_shouldNotCatchAnyException_IfExceptionWasThrown()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('My Exception During Process');

        $this->handler->enableTriggerExceptionInProcess();
        $this->tracker->track($this->handler, $this->requestSet);
    }

    public function test_getCountOfLoggedRequests_shouldReturnZero_WhenNothingTracked()
    {
        $this->assertEquals(0, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_hasLoggedRequests_shouldReturnFalse_WhenNothingTracked()
    {
        $this->assertFalse($this->tracker->hasLoggedRequests());
    }

    public function test_setCountOfLoggedRequests_shouldOverwriteNumberOfLoggedRequests()
    {
        $this->tracker->setCountOfLoggedRequests(5);
        $this->assertEquals(5, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_hasLoggedRequests_shouldReturnTrue_WhenSomeRequestsWereLogged()
    {
        $this->tracker->setCountOfLoggedRequests(1);
        $this->assertTrue($this->tracker->hasLoggedRequests());

        $this->tracker->setCountOfLoggedRequests(5);
        $this->assertTrue($this->tracker->hasLoggedRequests());

        $this->tracker->setCountOfLoggedRequests(0);
        $this->assertFalse($this->tracker->hasLoggedRequests());
    }

    private function buildRequest($idsite)
    {
        $request = new Request(array('idsite' => $idsite));
        $request->setCurrentTimestamp($this->time);

        return $request;
    }
}

class TestTracker extends Tracker
{
    private $record;

    public function __construct()
    {
        parent::__construct();
        $this->record = true;
    }

    public function shouldRecordStatistics()
    {
        return $this->record;
    }

    public function disableRecordStatistics()
    {
        $this->record = false;
    }
}
