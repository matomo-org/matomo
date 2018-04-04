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
use Piwik\Plugins\UserCountry\GeoIP2AutoUpdater;
use Piwik\Plugins\UserCountry\GeoIPLegacyAutoUpdater;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Exception;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/UserCountry.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 * @group Plugins
 */
class UserCountryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFlagFromCode()
    {
        $flag = \Piwik\Plugins\UserCountry\getFlagFromCode("us");
        $this->assertEquals(basename($flag), "us.png");
    }

    public function testGetFlagFromInvalidCode()
    {
        $flag = \Piwik\Plugins\UserCountry\getFlagFromCode("foo");
        $this->assertEquals(basename($flag), "xx.png");
    }

    public function testFlagsAndContinents()
    {
        /** @var RegionDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $continents = $dataProvider->getContinentList();
        $countries = $dataProvider->getCountryList(true);

        // Get list of existing flag icons
        $flags = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Morpheus/icons/dist/flags/');

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
     */
    public function testGeoIpLegacyUpdaterRedundantChecks()
    {
        GeoIp::$geoIPDatabaseDir = 'tmp/geoip-files';
        LocationProvider::$providers = null;

        // create empty ISP & Org files
        $this->createEmptyISPOrgLegacyFiles();

        // run redundant checks
        $updater = new Piwik_UserCountry_GeoIPLegacyAutoUpdater_publictest();
        $updater->performRedundantDbChecks();

        // check that files are renamed correctly
        $this->checkBrokenGeoIPLegacyState();

        // create empty files again & run checks again
        $this->createEmptyISPOrgLegacyFiles();
        $updater->performRedundantDbChecks();

        // check that w/ broken files already there, redundant checks still work correctly
        $this->checkBrokenGeoIPLegacyState();
    }

    /**
     * Test that redundant checks work.
     */
    public function testGeoIp2UpdaterRedundantChecks()
    {
        GeoIp2::$geoIPDatabaseDir = 'tmp/geoip-files';
        LocationProvider::$providers = null;

        // create empty ISP & Org files
        $this->createEmptyGeoIp2ISPFiles();

        // run redundant checks
        $updater = new Piwik_UserCountry_GeoIP2AutoUpdater_publictest();
        $updater->performRedundantDbChecks();

        // check that files are renamed correctly
        $this->checkBrokenGeoIP2State();

        // create empty files again & run checks again
        $this->createEmptyGeoIp2ISPFiles();
        $updater->performRedundantDbChecks();

        // check that w/ broken files already there, redundant checks still work correctly
        $this->checkBrokenGeoIP2State();
    }

    /**
     * @dataProvider getInvalidGeoIpLegacyUrlsToTest
     */
    public function testGeoIpLegacyDownloadInvalidUrl($url)
    {
        // unset translations, otherwise Exception message will be translated
        StaticContainer::get('Piwik\Translation\Translator')->reset();

        $updater = new Piwik_UserCountry_GeoIPLegacyAutoUpdater_publictest();
        try {
            $updater->downloadFile('loc', $url);
            $this->fail("Downloading invalid url succeeded!");
        } catch (Exception $ex) {
            $this->assertEquals("UserCountry_UnsupportedArchiveType", $ex->getMessage());
        }
    }

    public function getInvalidGeoIpLegacyUrlsToTest()
    {
        return array(array("http://localhost/tests/resources/geoip.tar"),
                     array("http://localhost/tests/resources/geoip.tar.bz2"),
                     array("http://localhost/tests/resources/geoip.dat"));
    }

    /**
     * @dataProvider getInvalidGeoIp2UrlsToTest
     */
    public function testGeoIp2DownloadInvalidUrl($url)
    {
        // unset translations, otherwise Exception message will be translated
        StaticContainer::get('Piwik\Translation\Translator')->reset();

        $updater = new Piwik_UserCountry_GeoIP2AutoUpdater_publictest();
        try {
            $updater->downloadFile('loc', $url);
            $this->fail("Downloading invalid url succeeded!");
        } catch (Exception $ex) {
            $this->assertEquals("UserCountry_UnsupportedArchiveType", $ex->getMessage());
        }
    }

    public function getInvalidGeoIp2UrlsToTest()
    {
        return array(array("http://localhost/tests/resources/GeoIP2-City.tar"),
                     array("http://localhost/tests/resources/GeoIP2-City.tar.bz2"),
                     array("http://localhost/tests/resources/GeoIP2-City.mmdb"));
    }

    public function setUp()
    {
        @mkdir(PIWIK_INCLUDE_PATH . '/tmp/geoip-files');
        // empty
    }

    public function tearDown()
    {
        $geoIpDirPath = PIWIK_INCLUDE_PATH . '/tmp/geoip-files';
        $filesToRemove = array('GeoIPISP.dat.broken', 'GeoIPOrg.dat.broken', 'GeoIPISP.dat', 'GeoIPOrg.dat',
                               'GeoIP2-City.mmdb', 'GeoIP2-City.mmdb.broken', 'GeoIP2-ISP.mmdb', 'GeoIP2-ISP.mmdb.broken');

        foreach ($filesToRemove as $name) {
            $path = $geoIpDirPath . '/' . $name;
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        @rmdir($geoIpDirPath);
    }

    private function createEmptyISPOrgLegacyFiles()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tmp/geoip-files';

        $fd = fopen($geoIpDir . '/GeoIPISP.dat', 'w');
        fclose($fd);

        $fd = fopen($geoIpDir . '/GeoIPOrg.dat', 'w');
        fclose($fd);
    }

    private function checkBrokenGeoIPLegacyState()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tmp/geoip-files';

        $this->assertFalse(file_exists($geoIpDir . '/GeoIPCity.dat.broken'));

        $this->assertFalse(file_exists($geoIpDir . '/GeoIPISP.dat'));
        $this->assertTrue(file_exists($geoIpDir . '/GeoIPISP.dat.broken'));

        $this->assertFalse(file_exists($geoIpDir . '/GeoIPOrg.dat'));
        $this->assertTrue(file_exists($geoIpDir . '/GeoIPOrg.dat.broken'));
    }

    private function createEmptyGeoIp2ISPFiles()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tmp/geoip-files';

        $fd = fopen($geoIpDir . '/GeoIP2-ISP.mmdb', 'w');
        fclose($fd);
    }

    private function checkBrokenGeoIP2State()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tmp/geoip-files';

        $this->assertFalse(file_exists($geoIpDir . '/GeoIP2-City.mmdb.broken'));

        $this->assertFalse(file_exists($geoIpDir . '/GeoIP2-ISP.mmdb'));
        $this->assertTrue(file_exists($geoIpDir . '/GeoIP2-ISP.mmdb.broken'));
    }
}

class Piwik_UserCountry_GeoIPLegacyAutoUpdater_publictest extends GeoIPLegacyAutoUpdater
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

class Piwik_UserCountry_GeoIP2AutoUpdater_publictest extends GeoIP2AutoUpdater
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
