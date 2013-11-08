<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Plugins\UserCountry\GeoIPAutoUpdater;
use Piwik\Plugins\UserCountry;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/UserCountry.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';
require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';

class Test_Piwik_UserCountry extends PHPUnit_Framework_Testcase
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
        require PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/Countries.php';

        $continents = $GLOBALS['Piwik_ContinentList'];
        $countries = array_merge($GLOBALS['Piwik_CountryList'], $GLOBALS['Piwik_CountryList_Extras']);

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
                unlink($path);
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
    public function performRedundantDbChecks()
    {
        parent::performRedundantDbChecks();
    }

    public function downloadFile($type, $url)
    {
        parent::downloadFile($type, $url);
    }
}
