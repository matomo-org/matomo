<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\System;

use Piwik\Http;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

/**
 * @group BulkTracking
 * @group TrackerTest
 * @group Tracker
 * @group Plugins
 */
class TrackerTest extends SystemTestCase
{
    public static $fixture = null;

    /**
     * @var \MatomoTracker
     */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $idSite = 1;
        $dateTime = '2014-01-01 00:00:01';

        if (!Fixture::siteCreated($idSite)) {
            Fixture::createWebsite($dateTime);
        }

        $this->tracker = Fixture::getTracker($idSite, $dateTime, $defaultInit = true);
        $this->tracker->enableBulkTracking();
    }

    public function testResponseShouldContainBulkTrackingApiResponse()
    {
        $this->tracker->doTrackPageView('Test');
        $this->tracker->doTrackPageView('Test');

        // test skipping invalid site errors
        $this->tracker->setIdSite(5);
        $this->tracker->doTrackPageView('Test');

        $this->tracker->setIdSite(1);
        $this->tracker->doTrackPageView('Test');

        // test skipping invalid request parameter errors
        $this->tracker->setDebugStringAppend('cid=abc');
        $this->tracker->doTrackPageView('Test');

        $this->tracker->DEBUG_APPEND_URL = '';

        // another invalid one to further test the invalid request indices in the result
        $this->tracker->setIdSite(7);
        $this->tracker->doTrackPageView('Test');

        $response = $this->tracker->doBulkTrack();

        $this->assertEquals('{"status":"success","tracked":3,"invalid":3,"invalid_indices":[2,4,5]}', $response);
    }

    public function testResponseShouldReportCustomActionRequestNotHandledByAnyPluginAsInvalid()
    {
        $this->tracker->doTrackPageView('Test');

        $this->tracker->setDebugStringAppend('ca=1');
        $this->tracker->doTrackPageView('Test');
        $this->tracker->DEBUG_APPEND_URL = '';

        $response = $this->tracker->doBulkTrack();

        $this->assertEquals('{"status":"success","tracked":1,"invalid":1,"invalid_indices":[1]}', $response);
    }

    public function testResponseShouldSend400ResponseCodeIfBodyIsNotValidJson()
    {
        $response = $this->sendBulkRequest('{"requests":["?idsite=1&rec=1"');

        $this->assertEquals(400, $response['status']);
        $this->assertEquals('{"status":"error","tracked":0,"invalid":0}', $response['data']);
    }

    public function testResponseShouldSend401Or403ResponseCodeIfAuthenticationIsRequiredButMissingOrInsufficient()
    {
        $testingEnvironment = new TestingEnvironmentVariables();
        $testingEnvironment->overrideConfig('Tracker', 'bulk_requests_require_authentication', 1);
        $testingEnvironment->save();

        try {
            $response = $this->sendBulkRequest('{"requests":["?idsite=1&rec=1"]}');
            $this->assertEquals(401, $response['status']);

            $response = $this->sendBulkRequest('{"requests":["?idsite=1&rec=1"],"token_auth":"' . md5('invalid') . '"}');
            $this->assertEquals(403, $response['status']);

            $response = $this->sendBulkRequest('{"requests":["?idsite=1&rec=1"],"token_auth":"' . Fixture::getTokenAuth() . '"}');
            $this->assertEquals(200, $response['status']);
        } finally {
            $testingEnvironment->removeOverriddenConfig('Tracker', 'bulk_requests_require_authentication');
            $testingEnvironment->save();
        }
    }

    private function sendBulkRequest(string $body): array
    {
        return Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            Fixture::getTrackerUrl(),
            10,
            getExtendedInfo: true,
            httpMethod: 'POST',
            requestBody: $body
        );
    }
}
