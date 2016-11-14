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
class TrackerResponseTest extends SystemTestCase
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

    public function test_response_ShouldSend400ResponseCode_IfSiteIdIsInvalid()
    {
        $url = $this->tracker->getUrlTrackPageView('Test');
        $url .= '&idsite=100';

        $this->assertResponseCode(400, $url);
    }

    public function test_response_ShouldSend400ResponseCode_IfSiteIdIsZero()
    {
        $url = $this->tracker->getUrlTrackPageView('Test');
        $url .= '&idsite=0';

        $this->assertResponseCode(400, $url);
    }

    public function test_response_ShouldSend400ResponseCode_IfInvalidRequestParameterIsGiven()
    {
        $url = $this->tracker->getUrlTrackPageView('Test');
        $url .= '&cid=' . str_pad('1', 16, '1');

        $this->assertResponseCode(200, $url);
        $this->assertResponseCode(400, $url . '1'); // has to be 16 char, but is 17 now
    }

    // See https://github.com/piwik/piwik/issues/7850 piwik.php is used by plugins and monitoring systems to test for Piwik installation.
    // it is important to return a 200 if someone does a GET request with no parameters
    public function test_response_ShouldReturnPiwikMessageWithHttp200_InCaseOfEmptyGETRequest()
    {
        $url = Fixture::getTrackerUrl();
        $this->assertResponseCode(200, $url);

        $expected = "This resource is part of Piwik. Keep full control of your data with the leading free and open source <a href='https://piwik.org' target='_blank'>digital analytics platform</a> for web and mobile.";
        $this->assertHttpResponseText($expected, $url);
    }

    public function test_response_ShouldReturnPiwikMessageWithHttp400_InCaseOfInvalidRequestOrIfNothingIsTracked()
    {
        $url = Fixture::getTrackerUrl();
        $this->assertResponseCode(400, $url . '?rec=1');

        $expected = "This resource is part of Piwik. Keep full control of your data with the leading free and open source <a href='https://piwik.org' target='_blank'>digital analytics platform</a> for web and mobile.";
        $this->assertHttpResponseText($expected, $url);
    }

}
