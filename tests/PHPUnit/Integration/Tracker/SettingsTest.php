<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        Cache::deleteTrackerCache();

        $_SERVER['HTTP_USER_AGENT'] = '';
    }

    public function test_getConfigId_isSame()
    {
        $settings1 = $this->makeSettings(array('idsite' => 1));
        $settings2 = $this->makeSettings(array('idsite' => 1));

        $this->assertEquals($settings1->getConfigId(), $settings2->getConfigId());
    }


    public function test_getConfigId_isSame_whenConfiguredUserHasSameFingerprintAcrossWebsites()
    {
        $isSameFingerprintAcrossWebsites = true;

        $settingsSite1 = $this->makeSettings(array('idsite' => 1), $isSameFingerprintAcrossWebsites);
        $settingsSite2 = $this->makeSettings(array('idsite' => 2), $isSameFingerprintAcrossWebsites);

        $this->assertEquals($settingsSite1->getConfigId(), $settingsSite2->getConfigId());
    }

    public function test_getConfigId_isDifferent_whenConfiguredUserHasDifferentFingerprintAcrossWebsites()
    {
        $isSameFingerprintAcrossWebsites = false;

        $settingsSite1 = $this->makeSettings(array('idsite' => 1), $isSameFingerprintAcrossWebsites);
        $settingsSite2 = $this->makeSettings(array('idsite' => 2), $isSameFingerprintAcrossWebsites);

        $this->assertNotSame($settingsSite1->getConfigId(), $settingsSite2->getConfigId());
    }

    public function test_getConfigId_isSame_whenBrowserSamebutDifferentUserAgent()
    {
        $settingsFirefox = $this->makeSettings(array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsSlightlyDifferentUserAgent = $this->makeSettings(array('ua' => 'Mozilla/5.0 (Macintosh; Extra; string; here; Hello; world; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));

        $this->assertSame($settingsSlightlyDifferentUserAgent->getConfigId(), $settingsFirefox->getConfigId());
    }

    public function test_getConfigId_isDifferent_whenBrowserChanges()
    {
        $settingsFirefox = $this->makeSettings(array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsChrome = $this->makeSettings(array('ua' => 'Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.133 Mobile Safari/535.19 '));

        $this->assertNotSame($settingsChrome->getConfigId(), $settingsFirefox->getConfigId());
    }

    public function test_getConfigId_isDifferent_whenOSChanges()
    {
        $settingsFirefoxMac = $this->makeSettings(array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'));
        $settingsFirefoxLinux = $this->makeSettings(array('ua' => 'Mozilla/5.0 (Linux; rv:33.0) Gecko/20100101 Firefox/33.0'));

        $this->assertNotSame($settingsFirefoxLinux->getConfigId(), $settingsFirefoxMac->getConfigId());
    }

    public function test_getConfigId_isDifferent_whenPluginChanges()
    {
        $params = array(
            'pdf' => 1,
            'cookie' => 1,
            'fla' => 0,
            'idsite' => 1,
        );
        $settingsWithoutFlash = $this->makeSettings($params);

        // activate flash
        $params['fla'] = 1;
        $settingsWithFlash = $this->makeSettings($params);

        $this->assertNotSame($settingsWithoutFlash->getConfigId(), $settingsWithFlash->getConfigId());
    }

    public function test_getConfigId_isDifferent_whenIPIsAnonimised()
    {
        $settingsIpIsNotAnon = $this->makeSettings(array(), true, '125.1.55.55');
        $settingsIpIsAnon = $this->makeSettings(array(), true, '125.1.0.0');

        $this->assertNotSame($settingsIpIsNotAnon->getConfigId(), $settingsIpIsAnon->getConfigId());
    }

    public function test_getConfigId_isSame_whenIPIsAnonimisedAndBothSame()
    {
        $settingsIpIsAnon = $this->makeSettings(array(), true, '125.2.0.0');
        $settingsIpIsAnonBis = $this->makeSettings(array(), true, '125.2.0.0');

        $this->assertSame($settingsIpIsAnonBis->getConfigId(), $settingsIpIsAnon->getConfigId());
    }

    /**
     * @param $params array
     * @param $isSameFingerprintAcrossWebsites
     * @param $ip
     * @return Settings
     */
    protected function makeSettings($params, $isSameFingerprintAcrossWebsites = false, $ip = null)
    {
        if(is_null($ip)) {
            $ip = $this->ip;
        }
        $requestSite1 = $this->makeRequest($params);
        $settingsSite1 = new Settings($requestSite1, $ip, $isSameFingerprintAcrossWebsites);
        return $settingsSite1;
    }

    /**
     * @param $extraParams array
     * @return array
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
