<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/UserCountry.php';
require_once 'UserCountry/functions.php';
require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';

class Test_Piwik_UserCountry extends PHPUnit_Framework_Testcase
{
    /**
     *
     * @group Plugins
     * @group UserCountry
     */
    public function testGetFlagFromCode()
    {
        $flag = Piwik_getFlagFromCode("us");
        $this->assertEquals(basename($flag), "us.png");
    }

    /**
     *
     * @group Plugins
     * @group UserCountry
     */
    public function testGetFlagFromInvalidCode()
    {
        $flag = Piwik_getFlagFromCode("foo");
        $this->assertEquals(basename($flag), "xx.png");
    }

    /**
     *
     * @group Plugins
     * @group UserCountry
     */
    public function testFlagsAndContinents()
    {
        require PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/Countries.php';

        $continents = $GLOBALS['Piwik_ContinentList'];
        $countries = array_merge($GLOBALS['Piwik_CountryList'], $GLOBALS['Piwik_CountryList_Extras']);

        // Get list of existing flag icons
        $flags = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/UserCountry/flags/');

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

    // test that redundant checks work
    public function testGeoIpUpdaterRedundantChecks()
    {
        Piwik_UserCountry_LocationProvider_GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        Piwik_UserCountry_LocationProvider::$providers = null;

        // create empty ISP & Org files
        $this->createEmptyISPOrgFiles();

        // run redundant checks
        $updater = new Piwik_UserCountry_GeoIPAutoUpdater_publictestRedundantChecks();
        $updater->performRedundantDbChecks();

        // check that files are renamed correctly
        $this->checkBrokenGeoIPState();

        // create empty files again & run checks again
        $this->createEmptyISPOrgFiles();
        $updater->performRedundantDbChecks();

        // check that w/ broken files already there, redundant checks still work correctly
        $this->checkBrokenGeoIPState();
    }

    public function setUp()
    {
        Piwik::$shouldLog = null;
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

class Piwik_UserCountry_GeoIPAutoUpdater_publictestRedundantChecks extends Piwik_UserCountry_GeoIPAutoUpdater
{
    public function performRedundantDbChecks()
    {
        parent::performRedundantDbChecks();
    }
}
