<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Settings;

/**
 * @group SettingsTest
 * @group TrackerVisitSettingsTest
 * @group Tracker
 */
class SettingsTest extends IntegrationTestCase
{
    /**
     * @var string
     */
    protected $ip = '123.30.30.30';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        Cache::deleteTrackerCache();

        $_SERVER['HTTP_USER_AGENT'] = '';
    }

    public function testGetConfigIdIsSame()
    {
        $request1 = $this->makeRequest(array('idsite' => 1));
        $settings1 = $this->makeSettings();

        $request2 = $this->makeRequest(array('idsite' => 1));
        $settings2 = $this->makeSettings();

        $this->assertEquals($settings1->getConfigId($request1, $this->ip), $settings2->getConfigId($request2, $this->ip));
    }


    public function testGetConfigIdIsSameWhenConfiguredUserHasSameFingerprintAcrossWebsites()
    {
        $isSameFingerprintAcrossWebsites = true;

        $request1 = $this->makeRequest(array('idsite' => 1));
        $settingsSite1 = $this->makeSettings($isSameFingerprintAcrossWebsites);

        $request2 = $this->makeRequest(array('idsite' => 2));
        $settingsSite2 = $this->makeSettings($isSameFingerprintAcrossWebsites);

        $this->assertEquals($settingsSite1->getConfigId($request1, $this->ip), $settingsSite2->getConfigId($request2, $this->ip));
    }

    public function testGetConfigIdIsDifferentWhenConfiguredUserHasDifferentFingerprintAcrossWebsites()
    {
        $isSameFingerprintAcrossWebsites = false;

        $request1 = $this->makeRequest(array('idsite' => 1));
        $settingsSite1 = $this->makeSettings($isSameFingerprintAcrossWebsites);

        $request2 = $this->makeRequest(array('idsite' => 2));
        $settingsSite2 = $this->makeSettings($isSameFingerprintAcrossWebsites);

        $this->assertNotSame($settingsSite1->getConfigId($request1, $this->ip), $settingsSite2->getConfigId($request2, $this->ip));
    }

    public function testGetConfigIdIsSameWhenBrowserSamebutDifferentUserAgent()
    {
        $request1 = $this->makeRequest(array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsFirefox = $this->makeSettings();

        $request2 = $this->makeRequest(array('ua' => 'Mozilla/5.0 (Macintosh; Extra; string; here; Hello; world; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsSlightlyDifferentUserAgent = $this->makeSettings();

        $this->assertSame($settingsSlightlyDifferentUserAgent->getConfigId($request1, $this->ip), $settingsFirefox->getConfigId($request2, $this->ip));
    }

    public function testGetConfigIdIsDifferentWhenBrowserChanges()
    {
        $request1 = $this->makeRequest(array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsFirefox = $this->makeSettings();

        $request2 = $this->makeRequest(array('ua' => 'Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.133 Mobile Safari/535.19 '));
        $settingsChrome = $this->makeSettings();

        $this->assertNotSame($settingsChrome->getConfigId($request1, $this->ip), $settingsFirefox->getConfigId($request2, $this->ip));
    }

    public function testGetConfigIdIsDifferentWhenOSChanges()
    {
        $request1 = $this->makeRequest(array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsFirefoxMac = $this->makeSettings();

        $request2 = $this->makeRequest(array('ua' => 'Mozilla/5.0 (Linux; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsFirefoxLinux = $this->makeSettings();

        $this->assertNotSame($settingsFirefoxLinux->getConfigId($request1, $this->ip), $settingsFirefoxMac->getConfigId($request2, $this->ip));
    }

    public function testGetConfigIdIsDifferentWhenPluginChanges()
    {
        $params = array(
            'pdf' => 1,
            'cookie' => 1,
            'fla' => 0,
            'idsite' => 1,
        );
        $request1 = $this->makeRequest($params);
        $settingsWithoutFlash = $this->makeSettings();

        // activate flash
        $params['fla'] = 1;
        $request2 = $this->makeRequest($params);
        $settingsWithFlash = $this->makeSettings($params);

        $this->assertNotSame($settingsWithoutFlash->getConfigId($request1, $this->ip), $settingsWithFlash->getConfigId($request2, $this->ip));
    }

    public function testGetConfigIdIsDifferentWhenIPIsAnonimised()
    {
        $request1 = $this->makeRequest(array());
        $settingsIpIsNotAnon = $this->makeSettings(true);

        $request2 = $this->makeRequest(array());
        $settingsIpIsAnon = $this->makeSettings(true);

        $this->assertNotSame($settingsIpIsNotAnon->getConfigId($request1, '125.1.55.55'), $settingsIpIsAnon->getConfigId($request2, '125.1.0.0'));
    }

    public function testGetConfigIdIsSameWhenIPIsAnonimisedAndBothSame()
    {
        $request1 = $this->makeRequest(array());
        $settingsIpIsAnon = $this->makeSettings(true);

        $request2 = $this->makeRequest(array());
        $settingsIpIsAnonBis = $this->makeSettings(true);

        $this->assertSame($settingsIpIsAnonBis->getConfigId($request1, '125.2.0.0'), $settingsIpIsAnon->getConfigId($request2, '125.2.0.0'));
    }

    /**
     * @param $isSameFingerprintAcrossWebsites
     * @return Settings
     */
    protected function makeSettings($isSameFingerprintAcrossWebsites = false)
    {
        $settingsSite1 = new Settings($isSameFingerprintAcrossWebsites);
        return $settingsSite1;
    }

    /**
     * @param $extraParams array
     * @return Request
     */
    private function makeRequest($extraParams)
    {
        // default
        $params = array(
            'pdf' => 1,
            'cookie' => 1,
            'fla' => 0,
            'idsite' => 1,
        );
        $params = array_merge($params, $extraParams);
        $request = new Request($params);
        return $request;
    }
}
