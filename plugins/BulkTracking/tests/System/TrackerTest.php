<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
     * @var \PiwikTracker
     */
    private $tracker;

    public function setUp()
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

        $response = $this->tracker->doBulkTrack();

        $this->assertEquals('{"status":"success","tracked":2,"invalid":1}', $response);
    }
}