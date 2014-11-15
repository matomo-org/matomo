<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group TrackerTest
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
    }

    public function test_response_ShouldContainAnImage()
    {
        $response = $this->tracker->doTrackPageView('Test');

        Fixture::checkResponse($response);
        $this->assertNotEmpty($response);
    }

    public function test_response_ShouldBeEmpty_IfImageIsDisabled()
    {
        $this->tracker->disableSendImageResponse();

        $response = $this->tracker->doTrackPageView('Test');

        $this->assertSame('', $response);
    }

    public function test_response_ShouldSend200ResponseCode_IfImageIsEnabled()
    {
        $url = $this->tracker->getUrlTrackPageView('Test');

        $this->assertResponseCode(200, $url);
    }

    public function test_response_ShouldSend204ResponseCode_IfImageIsDisabled()
    {
        $url = $this->tracker->getUrlTrackPageView('Test');
        $url .= '&send_image=0';

        $this->assertResponseCode(204, $url);
    }

}
