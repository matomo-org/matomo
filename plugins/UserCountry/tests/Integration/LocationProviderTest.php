<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Integration;

use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2\Php;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2\ServerModule;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UserCountry
 * @group LocationProvider
 */
class LocationProviderTest extends IntegrationTestCase
{
    public function testGetAllProviderInfo()
    {
        $allProviders = LocationProvider::getAllProviderInfo();

        // We currently have 4 Providers shipped with core
        $this->assertSame(4, count($allProviders));
        $this->assertEquals(['disabled', 'default', 'geoip2php', 'geoip2server'], array_keys($allProviders));
    }

    public function testGetAllProviderInfoWithDuplicateOrder()
    {
        \Piwik\Tests\Framework\Mock\LocationProvider::$locations = [
            [
                LocationProvider::CITY_NAME_KEY    => 'Manchaster',
                LocationProvider::REGION_CODE_KEY  => '15',
                LocationProvider::COUNTRY_CODE_KEY => 'US',
                LocationProvider::LATITUDE_KEY     => '12',
                LocationProvider::LONGITUDE_KEY    => '11',
                LocationProvider::ISP_KEY          => 'Facebook',
            ],
        ];

        LocationProvider::$providers = [
            new LocationProvider\DefaultProvider(),
            new Php(),
            new ServerModule(),
            new \Piwik\Tests\Framework\Mock\LocationProvider(),
        ];

        $allProviders = LocationProvider::getAllProviderInfo();

        $this->assertSame(4, count($allProviders));
        $this->assertEquals(['default', 'geoip2php', 'mock_provider', 'geoip2server'], array_keys($allProviders));
    }
}
