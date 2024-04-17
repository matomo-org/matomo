<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\GeoIp2\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Plugins\UserCountry\VisitorGeolocator;
use Piwik\Tests\Framework\Fixture;

/**
 * @group GeoIp2
 */
class LocationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGeoIP2City()
    {
        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-City.mmdb'], 'isp' => []]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->assertEquals([
            'continent_name' => 'Europe',
            'continent_code' => 'EU',
            'country_code' => 'FR',
            'country_name' => 'France',
            'city_name' => 'Besançon',
            'lat' => 47.249,
            'long' => 6.018,
            'postal_code' => '25000',
            'region_code' => 'BFC',
            'region_name' => 'Bourgogne-Franche-Comte',
        ], $result);
    }

    public function testGeoIP2CityWithoutRegionIsoCode()
    {
        // The IP 99.99.99.99 will only return a region name, based on that the region code should be determined
        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-City.mmdb'], 'isp' => []]);
        $result = $locationProvider->getLocation(['ip' => '99.99.99.99']);

        $this->assertEquals([
            'continent_name' => 'North America',
            'continent_code' => 'NA',
            'country_code' => 'US',
            'country_name' => 'United States',
            'city_name' => 'Englewood Cliffs',
            'lat' => 40.892,
            'long' => -73.947,
            'postal_code' => null,
            'region_code' => 'NJ',
            'region_name' => 'New Jersey',
        ], $result);
    }

    public function testGeoIP2CityWithIncorrectlyPrefixedRegionIsoCode()
    {
        // The IP 88.88.88.88 will return a region code that is prefixed with the country code, e.g. US-NJ instead of NJ
        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-City.mmdb'], 'isp' => []]);
        $result = $locationProvider->getLocation(['ip' => '88.88.88.88']);

        $this->assertEquals([
            'continent_name' => 'North America',
            'continent_code' => 'NA',
            'country_code' => 'US',
            'country_name' => 'United States',
            'city_name' => 'Englewood Cliffs',
            'lat' => 40.892,
            'long' => -73.947,
            'postal_code' => null,
            'region_code' => 'NJ',
            'region_name' => 'New Jersey',
        ], $result);
    }

    public function testGeoIP2Country()
    {
        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-Country.mmdb'], 'isp' => []]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->assertEquals([
            'continent_name' => 'Europe',
            'continent_code' => 'EU',
            'country_code' => 'FR',
            'country_name' => 'France',
        ], $result);
    }

    public function testGeoIP2ASN()
    {
        $locationProvider = new GeoIp2\Php(['loc' => [], 'isp' => ['GeoLite2-ASN.mmdb']]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->assertEquals([
            'isp' => 'Matomo Internet',
            'org' => 'Matomo Internet',
        ], $result);
    }

    public function testGeoIP2ISP()
    {
        $locationProvider = new GeoIp2\Php(['loc' => [], 'isp' => ['GeoIP2-ISP.mmdb']]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->assertEquals([
            'isp' => 'Matomo Internet',
            'org' => 'Innocraft'
        ], $result);
    }

    public function testGeoIP2ISP_whenIspDisabled_IspNotReturnsAnyResult()
    {
        $this->setIspEnabled(false);

        $locationProvider = new GeoIp2\Php(['loc' => [], 'isp' => ['GeoIP2-ISP.mmdb']]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->setIspEnabled(true);

        $this->assertFalse($result);
    }

    public function testGeoIP2ISP_whenIspDisabled_LocStillReturnsResult()
    {
        $this->setIspEnabled(false);

        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-Country.mmdb'], 'isp' => []]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->setIspEnabled(true);

        $this->assertNotEmpty($result);
    }

    private function setIspEnabled($enabled)
    {
        StaticContainer::getContainer()->set('geopip2.ispEnabled', $enabled);
    }

    public function testGeoIP2CityAndISP()
    {
        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-City.mmdb'], 'isp' => ['GeoIP2-ISP.mmdb']]);
        $result = $locationProvider->getLocation(['ip' => '194.57.91.215']);

        $this->assertEquals([
            'continent_name' => 'Europe',
            'continent_code' => 'EU',
            'country_code' => 'FR',
            'country_name' => 'France',
            'city_name' => 'Besançon',
            'lat' => 47.249,
            'long' => 6.018,
            'postal_code' => '25000',
            'region_code' => 'BFC',
            'region_name' => 'Bourgogne-Franche-Comte',
            'isp' => 'Matomo Internet',
            'org' => 'Innocraft'
        ], $result);
    }

    public function testGeoIP2NoResultFallback()
    {
        Fixture::loadAllTranslations();
        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-City.mmdb'], 'isp' => []]);
        $geolocator = new VisitorGeolocator($locationProvider, new DefaultProvider());

        $result = $geolocator->getLocation(['ip' => '221.0.0.9', 'lang' => 'de-ch'], false);

        $this->assertEquals([
            'country_code' => 'ch',
            'country_name' => 'Switzerland',
            'continent_code' => 'eur',
            'continent_name' => 'Europe',
        ], $result);
    }

    public function testGeoIP2NoResultFallbackDisabled()
    {
        Fixture::loadAllTranslations();
        Config::getInstance()->Tracker['enable_default_location_provider'] = 0;

        $locationProvider = new GeoIp2\Php(['loc' => ['GeoIP2-City.mmdb'], 'isp' => []]);
        $geolocator = new VisitorGeolocator($locationProvider);

        $result = $geolocator->getLocation(['ip' => '221.0.0.9', 'lang' => 'de-ch'], false);

        $this->assertEquals([
            'country_code' => 'xx',
        ], $result);
    }
}
