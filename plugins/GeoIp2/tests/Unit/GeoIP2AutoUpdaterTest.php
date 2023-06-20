<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\GeoIp2\tests\Unit;

use Piwik\Config;
use Piwik\Plugins\GeoIp2\GeoIP2AutoUpdater;

class PublicGeoIP2AutoUpdater extends GeoIP2AutoUpdater
{
    public static function isPaidDbIpUrl($url)
    {
        return parent::isPaidDbIpUrl($url);
    }

    public function fetchPaidDbIpUrl($url)
    {
        return parent::fetchPaidDbIpUrl($url);
    }

    public static function checkGeoIPUpdateUrl($url) {
        return parent::checkGeoIPUpdateUrl($url);
    }
}

/**
 * @group GeoIP2AutoUpdater
 */
class GeoIP2AutoUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getPaidDbTestUrls
     */
    public function testIsPaidDbIpUrl($expected, $url)
    {
        $this->assertEquals($expected, PublicGeoIP2AutoUpdater::isPaidDbIpUrl($url));
    }

    public function getPaidDbTestUrls()
    {
        return [
            [true, 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-country/'],
            [true, 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-location/mmdb/'],
            [true, 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-location-isp/mmdb/url'],
            [false, 'https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb'],
            [false, 'https://download.db-ip.com/free/dbip-country-lite-2020-02.mmdb.gz'],
        ];
    }

    /**
     * @dataProvider getDbTestUrls
     */
    public function testIsDbIpUrl($expected, $url)
    {
        $this->assertEquals($expected, GeoIP2AutoUpdater::isDbIpUrl($url));
    }

    public function getDbTestUrls()
    {
        return [
            [true, 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-country/'],
            [true, 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-location/mmdb/'],
            [true, 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-location-isp/mmdb/url'],
            [true, 'https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb'],
            [true, 'https://download.db-ip.com/free/dbip-country-lite-2020-02.mmdb.gz'],
            [false, 'https://dbip.com/free/dbip-country-lite-2020-02.mmdb.gz'],
        ];
    }

    public function testFetchPaidUrlForPlainUrl()
    {
        $url = 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-country/mmdb/url';

        $mock = $this->getMockBuilder(PublicGeoIP2AutoUpdater::class)
            ->setMethods(['fetchUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())->method('fetchUrl')->with($url)->willReturn('https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb');

        $determinedUrl = $mock->fetchPaidDbIpUrl($url);

        $this->assertEquals('https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb', $determinedUrl);
    }

    public function testFetchPaidUrlForMmdbJson()
    {
        $url = 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-country/mmdb';

        $mock = $this->getMockBuilder(PublicGeoIP2AutoUpdater::class)
            ->setMethods(['fetchUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())->method('fetchUrl')->with($url)->willReturn('
{
    "url": "https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb",
    "name": "dbip-country-2020-02-22.mmdb.gz",
    "date": "February 22nd 2020",
    "size": 8807222,
    "rows": 808624,
    "md5sum": "dd8250ca45ad42dd5c3a63670ff46968",
    "sha1sum": "062615e15dd1496ac9fddc311231efa2d75f09d6"
}');

        $determinedUrl = $mock->fetchPaidDbIpUrl($url);

        $this->assertEquals('https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb', $determinedUrl);
    }

    public function testFetchPaidUrlForFullJson()
    {
        $url = 'https://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-country';

        $mock = $this->getMockBuilder(PublicGeoIP2AutoUpdater::class)
            ->setMethods(['fetchUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())->method('fetchUrl')->with($url)->willReturn('
{
    "csv": {
        "url": "https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.csv",
        "name": "dbip-country-2020-02-22.csv.gz",
        "date": "February 22nd 2020",
        "size": 35113592,
        "rows": 808314,
        "md5sum": "22cd9abcc07e6b5c1c0fe89eef4503e2",
        "sha1sum": "d3cc6e7ed30cc58abcc77ae73318d63af2687b06",
        "version": 3
    },
    "mmdb": {
        "url": "https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb",
        "name": "dbip-country-2020-02-22.mmdb.gz",
        "date": "February 22nd 2020",
        "size": 8807222,
        "rows": 808314,
        "md5sum": "dd8250ca45ad42c55abc63670ff46968",
        "sha1sum": "062615e15e01496ac9fddabc1231efa2d75f09d6"
    }
}');

        $determinedUrl = $mock->fetchPaidDbIpUrl($url);

        $this->assertEquals('https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb', $determinedUrl);
    }

    /**
     * @dataProvider getUpdaterUrlOptions
     */
    public function testInvalidUpdateOptions($url, $valid)
    {
        if (!$valid) {
            $this->expectException(\Exception::class);
        } else {
            $this->expectNotToPerformAssertions();
        }
        PublicGeoIP2AutoUpdater::checkGeoIPUpdateUrl($url);
    }

    public function getUpdaterUrlOptions()
    {
        return [
            ['https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-ASN&license_key=YOUR_LICENSE_KEY&suffix=tar.gz', true],
            ['https://download.db-ip.com/key/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb', true],
            ['https://download.db-ip.com/free/dbip-city-lite-2020-01.mmdb.gz', true],
            ['https://db-ip.com/account/ad446bf4cb9a44e5ff3f215deabc710f12f3/db/ip-to-country/mmdb', true],
            ['https://www.ip2location.com/download/?token={DOWNLOAD_TOKEN}&file={DATABASE_CODE}', true],
            ['https://download.maxmind.com.fake.org/app/geoip_download?edition_id=GeoLite2-ASN&license_key=YOUR_LICENSE_KEY&suffix=tar.gz', false],
            ['https://fakemaxmind.com/ad446bf4cb9a44e4fff3f215deabc710f12f3.mmdb', false],
            ['https://fake-db-ip.com/account/ad446bf4cb9a44e5ff3f215deabc710f12f3/db/ip-to-country/mmdb', false],
            ['http://my.custom.host/download.tar.gz', false],
            ['phar://local/input.file', false],
            ['ftp://db-ip.com/account/ad446bf4cb9a44e4fff3f215deabc710f12f3/db/ip-to-country/mmdb', false],
            ['http://matomo.org/download/geoip.mmdb', false],
        ];
    }

    public function testsAdditionalGeoIPHostConfig()
    {
        $this->expectNotToPerformAssertions();
        Config::getInstance()->General['geolocation_download_from_trusted_hosts'][] = 'matomo.org';
        PublicGeoIP2AutoUpdater::checkGeoIPUpdateUrl('http://matomo.org/download/geoip.mmdb');
    }
}
