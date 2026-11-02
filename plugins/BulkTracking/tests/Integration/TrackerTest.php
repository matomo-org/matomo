<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\EventDispatcher;
use Piwik\Plugins\BulkTracking\tests\Framework\Mock\Tracker\Response;
use Piwik\Plugins\BulkTracking\tests\Framework\TestCase\BulkTrackingTestCase;
use Piwik\Plugins\BulkTracking\Tracker\Handler;
use Piwik\Tests\Framework\DbErrorRaiser;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker;
use Piwik\Tests\Framework\Mock\Tracker\RequestSet;

/**
 * @group TrackerTest
 * @group Tracker
 */
class TrackerTest extends BulkTrackingTestCase
{
    /**
     * @var Tracker
     */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->tracker = new Tracker();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');

        $this->injectRawDataToBulk($this->getDummyRequest());
        DbErrorRaiser::install();
    }

    public function tearDown(): void
    {
        DbErrorRaiser::uninstall();
        parent::tearDown();
    }

    public function testMainShouldIncreaseLoggedRequestsCounter()
    {
        $this->tracker->main($this->getHandler(), $this->getEmptyRequestSet());

        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function testMainShouldUseBulkHandler()
    {
        $handler = $this->getHandler();
        $this->assertTrue($handler instanceof Handler);
    }

    public function testMainShouldReturnBulkTrackingResponse()
    {
        $response = $this->tracker->main($this->getHandler(), $this->getEmptyRequestSet());

        $this->assertSame('{"status":"success","tracked":2,"invalid":0}', $response);
    }

    public function testMainShouldReturnErrorResponseInCaseOfAnyError()
    {
        $requestSet = new RequestSet();
        $requestSet->enableThrowExceptionOnInit();

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $requestSet);

        $this->assertSame('{"status":"error","tracked":0,"invalid":0}', $response);
    }

    public function testMainShouldReturnErrorResponseIfNotAuthorized()
    {
        $this->injectRawDataToBulk($this->getDummyRequest(), true);

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $this->getEmptyRequestSet());

        $this->assertSame('{"status":"error","tracked":0,"invalid":0}', $response);
    }

    public function testMainShouldActuallyTrack()
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

    public function testMainShouldReportInvalidIndicesIfInvalidRequestsIncludedAndRequestAuthenticated()
    {
        $this->injectRawDataToBulk($this->getDummyRequest($token = Fixture::getTokenAuth(), $idSite = array(1, -100)));

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $this->getEmptyRequestSet());

        $this->assertEquals('{"status":"success","tracked":1,"invalid":1,"invalid_indices":[1]}', $response);
    }

    public function testMainShouldReportInvalidCountIfInvalidRequestsIncludedAndRequestNotAuthenticated()
    {
        $this->injectRawDataToBulk($this->getDummyRequest($token = null, $idSite = array(1, -100)));

        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        $response = $this->tracker->main($handler, $this->getEmptyRequestSet());

        $this->assertEquals('{"status":"success","tracked":1,"invalid":1}', $response);
    }

    /**
     * @dataProvider getErrorsThatEndTheTransaction
     */
    public function testMainStoresNothingWhenARequestEndsTheTransaction(int $errorNumber, string $message, bool $isCaught)
    {
        $this->raiseErrorWhenTrackingSite(2, $errorNumber, $message, $isCaught);

        $response = $this->trackBulkRequest();

        $this->assertStringStartsWith('{"status":"error"', $response);
        $this->assertEmpty($this->getIdVisit(1), 'The request tracked before the error must be rolled back too');
        $this->assertEmpty($this->getIdVisit(2));
    }

    public function getErrorsThatEndTheTransaction(): iterable
    {
        $deadlock = 'Deadlock found when trying to get lock; try restarting transaction';
        $snapshotConflict = "Record has changed since last read in table 'log_visit'";

        yield 'deadlock caught by a tracking step' => [1213, $deadlock, true];
        yield 'deadlock not caught' => [1213, $deadlock, false];
        yield 'snapshot isolation conflict caught by a tracking step' => [1020, $snapshotConflict, true];
        yield 'snapshot isolation conflict not caught' => [1020, $snapshotConflict, false];
    }

    public function testMainKeepsTrackingAfterACaughtDeadlockWhenTransactionsAreDisabled()
    {
        $this->setTrackerConfig('bulk_requests_use_transaction', 0);
        $this->raiseErrorWhenTrackingSite(2, 1213, 'Deadlock found when trying to get lock; try restarting transaction', true);

        $response = $this->trackBulkRequest();

        $this->assertSame('{"status":"success","tracked":2,"invalid":0}', $response);
        $this->assertNotEmpty($this->getIdVisit(1));
        $this->assertNotEmpty($this->getIdVisit(2));
    }

    public function testMainSkipsOnlyTheRequestThatHitALockWaitTimeout()
    {
        $this->raiseErrorWhenTrackingSite(2, 1205, 'Lock wait timeout exceeded; try restarting transaction', false);

        $response = $this->trackBulkRequest();

        $this->assertStringStartsWith('{"status":"success"', $response);
        $this->assertNotEmpty($this->getIdVisit(1));
        $this->assertEmpty($this->getIdVisit(2));
    }

    private function raiseErrorWhenTrackingSite(int $idSite, int $errorNumber, string $message, bool $isCaught): void
    {
        EventDispatcher::getInstance()->addObserver(
            'Tracker.isExcludedVisit',
            function (&$excluded, Tracker\Request $request) use ($idSite, $errorNumber, $message, $isCaught) {
                if ($request->getIdSite() !== $idSite) {
                    return;
                }

                try {
                    DbErrorRaiser::raise(Tracker::getDatabase(), $errorNumber, $message);
                } catch (\Exception $e) {
                    if (!$isCaught) {
                        throw $e;
                    }
                    // like a tracking step that logs a failed write and carries on
                }
            }
        );
    }

    private function trackBulkRequest(): string
    {
        $handler = $this->getHandler();
        $handler->setResponse(new Response());

        return $this->tracker->main($handler, $this->getEmptyRequestSet());
    }

    private function setTrackerConfig(string $name, $value): void
    {
        $tracker = Config::getInstance()->Tracker;
        $tracker[$name] = $value;
        Config::getInstance()->Tracker = $tracker;
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
