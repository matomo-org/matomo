<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Http;
use Piwik\Plugins\SitesManager\API;

/**
 * Adds one site and sends several invalid tracking requests. The result should be
 * one website with no visits.
 */
class Test_Piwik_Fixture_InvalidVisits extends Test_Piwik_BaseFixture
{
    public $idSite = 1;
    public $dateTime = '2009-01-04 00:11:42';

    public $trackInvalidRequests = true;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        if (!$this->trackInvalidRequests) {
            return;
        }

        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        API::getInstance()->setSiteSpecificUserAgentExcludeEnabled(true);
        API::getInstance()->setGlobalExcludedUserAgents('globalexcludeduseragent');

        // Trigger empty request
        $trackerUrl = self::getTrackerUrl();
        $response = Http::fetchRemoteFile($trackerUrl);
        self::assertTrue(strpos($response, 'is a free open source web') !== false, 'Piwik empty request response not correct: ' . $response);

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // test GoogleBot UA visitor
        $t->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        self::checkResponse($t->doTrackPageView('bot visit, please do not record'));

        // Test IP Exclusion works with or without IP exclusion
        foreach (array(false, true) as $enable) {
            $excludedIp = '154.1.12.34';
            API::getInstance()->updateSite($idSite, 'new site name', $url = array('http://site.com'), $ecommerce = 0, $ss = 1, $ss_kwd = '', $ss_cat = '', $excludedIp . ',1.2.3.4', $excludedQueryParameters = null, $timezone = null, $currency = null, $group = null, $startDate = null, $excludedUserAgents = 'excludeduseragentstring');

            // Enable IP Anonymization
            $t->DEBUG_APPEND_URL = '&forceIpAnonymization=' . (int)$enable;

            // test with excluded User Agent
            $t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729) (excludeduseragentstring)');
            $t->setIp('211.1.2.3');
            self::checkResponse($t->doTrackPageView('visit from excluded User Agent'));

            $t->setUserAgent('Mozilla/5.0 (Windows NT 6.1; rv:6.0) Gecko/20110814 Firefox/6.0 Google (+https://developers.google.com/+/web/snippet/)');
            self::checkResponse($t->doTrackPageView('visit from excluded User Agent'));

            // test w/ global excluded User Agent
            $t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729) (globalexcludeduseragent)');
            $t->setIp('211.1.2.3');
            self::checkResponse($t->doTrackPageView('visit from global excluded User Agent'));

            // test with excluded IP
            $t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)'); // restore normal user agent
            $t->setIp($excludedIp);
            self::checkResponse($t->doTrackPageView('visit from IP excluded'));

            // test with global list of excluded IPs
            $excludedIpBis = '145.5.3.4';
            API::getInstance()->setGlobalExcludedIps($excludedIpBis);
            $t->setIp($excludedIpBis);
            self::checkResponse($t->doTrackPageView('visit from IP globally excluded'));
        }

        try {
            @$t->setAttributionInfo(array());
            self::fail();
        } catch (Exception $e) {
        }

        try {
            $t->setAttributionInfo(json_encode('test'));
            self::fail();
        } catch (Exception $e) {
        }

        $t->setAttributionInfo(json_encode(array()));
    }
}
