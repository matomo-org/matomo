<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Integration;

use Piwik\Common;
use Piwik\Plugins\BulkTracking\tests\Framework\Mock\Tracker\Response;
use Piwik\Plugins\BulkTracking\tests\Framework\TestCase\BulkTrackingTestCase;
use Piwik\Plugins\BulkTracking\Tracker\Handler;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker;
use Piwik\Tests\Framework\Mock\Tracker\RequestSet;

class TestIntegrationTracker extends Tracker
{
    protected function loadTrackerPlugins()
    {
        // if we reload the plugins we would lose the injected data :(
    }
}

/**
 * @group TrackerTest
 * @group Tracker
 */
class TrackerTest extends BulkTrackingTestCase
{
    /**
     * @var TestIntegrationTracker
     */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->tracker = new TestIntegrationTracker();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');

        $this->injectRawDataToBulk($this->getDummyRequest());
    }

    public function test_main_shouldIncreaseLoggedRequestsCounter()
    {
        $this->tracker->main($this->getHandler(), $this->getEmptyRequestSet());

        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function test_main_shouldUseBulkHandler()
    {
        $handler = $this->getHandler();
        $this->assertTrue($handler instanceof Handler);
    }

    public function test_main_shouldReturnBulkTrackingResponse()
    {
        $response = $this->tracker->main($this->getHandler(), $this->getEmptyRequestSet());

        $this->assertSame('{"status":"success","tracked":2,"invalid":0}', $response);
    }

    public function test_main_shouldReturnErrorResponse_InCaseOfAnyError()
    {
        $requestSet = new RequestSet();
        $requestSet->enableThrowExceptionOnInit();

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $requestSet);

        $this->assertSame('{"status":"error","tracked":0,"invalid":0}', $response);
    }

    public function test_main_shouldReturnErrorResponse_IfNotAuthorized()
    {
        $this->injectRawDataToBulk($this->getDummyRequest(), true);

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $this->getEmptyRequestSet());

        $this->assertSame('{"status":"error","tracked":0,"invalid":0}', $response);
    }

    public function test_main_shouldActuallyTrack()
    {
        $this->assertEmpty($this->getIdVisit(1));
        $this->assertEmpty($this->getIdVisit(2));

        $requestSet = $this->getEmptyRequestSet();
        $this->tracker->main($this->getHandler(), $requestSet);

        $this->assertCount(2, $requestSet->getRequests(), 'Nothing tracked because it could not find 2 requests');

        $visit1 = $this->getIdVisit(1);
        $visit2 = $this->getIdVisit(2);

        $this->assertNotEmpty($visit1);
        $this->assertEquals(1, $visit1['idsite']);
        $this->assertNotEmpty($visit2);
        $this->assertEquals(2, $visit2['idsite']);

        $this->assertEmpty($this->getIdVisit(3));
    }

    public function test_main_shouldReportInvalidIndices_IfInvalidRequestsIncluded_AndRequestAuthenticated()
    {
        $this->injectRawDataToBulk($this->getDummyRequest($token = Fixture::getTokenAuth(), $idSite = array(1, -100)));

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $this->getEmptyRequestSet());

        $this->assertEquals('{"status":"success","tracked":1,"invalid":1,"invalid_indices":[1]}', $response);
    }

    public function test_main_shouldReportInvalidCount_IfInvalidRequestsIncluded_AndRequestNotAuthenticated()
    {
        $this->injectRawDataToBulk($this->getDummyRequest($token = null, $idSite = array(1, -100)));

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $this->getEmptyRequestSet());

        $this->assertEquals('{"status":"success","tracked":1,"invalid":1}', $response);
    }

    private function getHandler()
    {
        return Tracker\Handler\Factory::make();
    }

    private function getEmptyRequestSet()
    {
        return new Tracker\RequestSet();
    }

    private function getIdVisit($idVisit)
    {
        return Tracker::getDatabase()->fetchRow("SELECT * FROM " . Common::prefixTable('log_visit') . " WHERE idvisit = ?", array($idVisit));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }
}
