<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\System;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

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

    public function test_response_ShouldContainBulkTrackingApiResponse()
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
}
