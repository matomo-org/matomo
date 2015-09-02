<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Unit;

use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Plugins\UserCountry\GeoIPAutoUpdater;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry;
use Piwik\Plugins\UserCountry\LocationProvider;
use Exception;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/UserCountry.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class UserCountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group Plugins
     */
    public function testGetFlagFromCode()
    {
        $flag = \Piwik\Plugins\UserCountry\getFlagFromCode("us");
        $this->assertEquals(basename($flag), "us.png");
    }

    /**
     * @group Plugins
     */
    public function testGetFlagFromInvalidCode()
    {
        $flag = \Piwik\Plugins\UserCountry\getFlagFromCode("foo");
        $this->assertEquals(basename($flag), "xx.png");
    }

    /**
     * @group Plugins
     */
    public function testFlagsAndContinents()
    {
        /** @var RegionDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $continents = $dataProvider->getContinentList();
        $countries = $dataProvider->getCountryList(true);

        // Get list of existing flag icons
        $flags = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/UserCountry/images/flags/');

        // Get list of countries
        foreach ($countries as $country => $continent) {
            // test continent
            $this->assertContains($continent, $continents);

            // test flag
            $this->assertContains($country . '.png', $flags);
        }

        foreach ($flags as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            $country = substr($filename, 0, strpos($filename, '.png'));

            // test country
            $this->assertArrayHasKey($country, $countries, $filename);
        }
    }

    /**
     * Test that redundant checks work.
     *
     * @group Plugins
     */
    public function testGeoIpUpdaterRedundantChecks()
    {
        GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        LocationProvider::$providers = null;

        // create empty ISP & Org files
        $this->createEmptyISPOrgFiles();

        // run redundant checks
        $updater = new Piwik_UserCountry_GeoIPAutoUpdater_publictest();
        $updater->performRedundantDbChecks();

        // check that files are renamed correctly
        $this->checkBrokenGeoIPState();

        // create empty files again & run checks again
        $this->createEmptyISPOrgFiles();
        $updater->performRedundantDbChecks();

        // check that w/ broken files already there, redundant checks still work correctly
        $this->checkBrokenGeoIPState();
    }

    /**
     * @group Plugins
     *
     * @dataProvider getInvalidGeoIpUrlsToTest
     */
    public function testGeoIpDownloadInvalidUrl($url)
    {
        $updater = new Piwik_UserCountry_GeoIPAutoUpdater_publictest();
        try {
            $updater->downloadFile('loc', $url);
            $this->fail("Downloading invalid url succeeded!");
        } catch (Exception $ex) {
            $this->assertEquals("UserCountry_UnsupportedArchiveType", $ex->getMessage());
        }
    }

    public function getInvalidGeoIpUrlsToTest()
    {
        return array(array("http://localhost/tests/resources/geoip.tar"),
                     array("http://localhost/tests/resources/geoip.tar.bz2"),
                     array("http://localhost/tests/resources/geoip.dat"));
    }

    public function setUp()
    {
        // empty
    }

    public function tearDown()
    {
        $geoIpDirPath = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';
        $filesToRemove = array('GeoIPISP.dat.broken', 'GeoIPOrg.dat.broken', 'GeoIPISP.dat', 'GeoIPOrg.dat');

        foreach ($filesToRemove as $name) {
            $path = $geoIpDirPath . '/' . $name;
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    private function createEmptyISPOrgFiles()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';

        $fd = fopen($geoIpDir . '/GeoIPISP.dat', 'w');
        fclose($fd);

        $fd = fopen($geoIpDir . '/GeoIPOrg.dat', 'w');
        fclose($fd);
    }

    private function checkBrokenGeoIPState()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';

        $this->assertFalse(file_exists($geoIpDir . '/GeoIPCity.dat.broken'));

        $this->assertFalse(file_exists($geoIpDir . '/GeoIPISP.dat'));
        $this->assertTrue(file_exists($geoIpDir . '/GeoIPISP.dat.broken'));

        $this->assertFalse(file_exists($geoIpDir . '/GeoIPOrg.dat'));
        $this->assertTrue(file_exists($geoIpDir . '/GeoIPOrg.dat.broken'));
    }
}

class Piwik_UserCountry_GeoIPAutoUpdater_publictest extends GeoIPAutoUpdater
{
    public function __construct()
    {
        // empty
    }

    // during tests do not call the Log::error or they will be displayed in the output
    public function performRedundantDbChecks($logErrors = false)
    {
        parent::performRedundantDbChecks($logErrors);
    }

    public function downloadFile($type, $url)
    {
        parent::downloadFile($type, $url);
    }
}
