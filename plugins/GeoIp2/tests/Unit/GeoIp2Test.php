<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\GeoIp2\tests\Unit;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\GeoIp2\GeoIP2AutoUpdater;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Exception;

class GeoIp2Test extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that redundant checks work.
     *
     * @group Plugins
     */
    public function testGeoIpUpdaterRedundantChecks()
    {
        LocationProvider::$providers = null;

        // create empty ISP file
        $this->createEmptyISPFile();

        // run redundant checks
        $updater = new PiwikGeoIp2GeoIP2AutoUpdaterPublicTest();
        $updater->performRedundantDbChecks();

        // check that files are renamed correctly
        $this->checkBrokenGeoIPState();

        // create empty file again & run checks again
        $this->createEmptyISPFile();
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
        // unset translations, otherwise Exception message will be translated
        StaticContainer::get('Piwik\Translation\Translator')->reset();

        $updater = new PiwikGeoIp2GeoIP2AutoUpdaterPublicTest();
        try {
            $updater->downloadFile('loc', $url);
            $this->fail("Downloading invalid url succeeded!");
        } catch (Exception $ex) {
            $this->assertEquals("GeoIp2_UnsupportedArchiveType", $ex->getMessage());
        }
    }

    public function getInvalidGeoIpUrlsToTest()
    {
        return array(array("http://localhost/tests/resources/geoip.tar"),
                     array("http://localhost/tests/resources/geoip.tar.bz2"),
                     array("http://localhost/tests/resources/geoip.dat"));
    }

    protected $backUpNames;

    public function setUp(): void
    {
        $this->backUpNames = GeoIp2::$dbNames;

        GeoIp2::$dbNames = [
            'loc' => ['DBIP-City.mmdb'],
            'isp' => ['DBIP-ISP.mmdb']
        ];
    }

    public function tearDown(): void
    {
        GeoIp2::$dbNames = $this->backUpNames;

        $geoIpDirPath = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';
        $filesToRemove = array('DBIP-ISP.mmdb.broken', 'DBIP-ISP.mmdb');

        foreach ($filesToRemove as $name) {
            $path = $geoIpDirPath . '/' . $name;
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    private function createEmptyISPFile()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';

        $fd = fopen($geoIpDir . '/DBIP-ISP.mmdb', 'w');
        fclose($fd);
    }

    private function checkBrokenGeoIPState()
    {
        $geoIpDir = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';

        $this->assertFalse(file_exists($geoIpDir . '/DBIP-City.mmdb.broken'));

        $this->assertFalse(file_exists($geoIpDir . '/DBIP-ISP.mmdb'));
        $this->assertTrue(file_exists($geoIpDir . '/DBIP-ISP.mmdb.broken'));
    }
}

class PiwikGeoIp2GeoIP2AutoUpdaterPublicTest extends GeoIP2AutoUpdater
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
