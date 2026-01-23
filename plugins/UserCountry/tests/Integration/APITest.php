<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Integration;

use DateTime;
use Piwik\Access;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable\Row;
use Piwik\Plugins\UserCountry\API;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UserCountry
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        // reset location providers as they might be manipulated by other tests
        LocationProvider::$providers = null;
        LocationProvider::getAllProviders();
    }

    public function testSetLocationProvider()
    {
        $locationProvider = GeoIp2\Php::ID;
        $this->api->setLocationProvider($locationProvider);
        $this->assertEquals($locationProvider, Common::getCurrentLocationProviderId());

        $locationProvider = DefaultProvider::ID;
        $this->api->setLocationProvider($locationProvider);
        $this->assertEquals($locationProvider, Common::getCurrentLocationProviderId());
    }

    public function testGetCountry()
    {
        $dateTime = '2026-01-01 00:00:00';
        $idSite = static::$fixture::createWebsite(
            $dateTime,
            $ecommerce = 1,
        );

        $this->createManyEcommerceOrders($idSite, $dateTime, 100);

        $result = Request::processRequest('UserCountry.getCountry', [
            'idSite' => $idSite,
            'period' => 'year',
            'date' => $dateTime,
            'flat' => '1',
        ]);
        /** @var Row $row */
        foreach ($result->getRows() as $row) {
            var_dump($row->getColumns());
        }

    }

    public function createManyEcommerceOrders($siteId, $dateTime, $numberOfOrders)
    {
        static::$fixture::createSuperUser($removeExisting = true);

        $visitorIds = [
            'visit-1',
            'visit-2',
            'visit-3',
        ];

        $orderNumber = 1001;

        $dateTimeObj = new DateTime($dateTime);
        $interval = new \DateInterval('P1D');

        for ($i = 0; $i < $numberOfOrders; $i++) {
            $dateTimeStr = date_format($dateTimeObj, 'Y-m-d H:i:s');
            $tracker = static::$fixture::getTracker($siteId, $dateTimeStr, $defaultInit = true, $useLocal = true);
            $tracker->setIp('151.100.101.92'); // Italy
            $tracker->setVisitorId(substr(md5($visitorIds[$i % 3]), $offset = 0, $tracker::LENGTH_VISITOR_ID));
            $tracker->setTokenAuth(static::$fixture::getTokenAuth());
            $tracker->addEcommerceItem('SKU-' . ($orderNumber + $i), 'Product ' . ($orderNumber + $i), 'Category', 100, 1);
            static::$fixture::checkResponse($tracker->doTrackEcommerceOrder($orderNumber + $i, 111.11, 100, 11));
            $dateTimeObj = date_add($dateTimeObj, $interval);
        }
    }

    public function testSetLocationProviderInvalid()
    {
        $this->expectException(\Exception::class);

        $locationProvider = 'invalidProvider';
        $this->api->setLocationProvider($locationProvider);
    }

    public function testSetLocationProviderNoSuperUser()
    {
        $this->expectException(\Exception::class);

        Access::getInstance()->setSuperUserAccess(false);

        $locationProvider = GeoIp2\Php::ID;
        $this->api->setLocationProvider($locationProvider);
    }

    public function testSetLocationProviderDisabledInConfig()
    {
        $this->expectException(\Exception::class);

        Config::getInstance()->General['enable_geolocation_admin'] = 0;

        $locationProvider = GeoIp2\Php::ID;
        $this->api->setLocationProvider($locationProvider);
    }

    /**
     * @dataProvider getTestDataForGetLocationFromIP
     */
    public function testGetLocationFromIP($ipAddress, $expected, $ipAddressHeader = null)
    {
        if (!empty($ipAddressHeader)) {
            $_SERVER['REMOTE_ADDR'] = $ipAddressHeader;
        }

        // Default provider will guess the location based on HTTP_ACCEPT_LANGUAGE header
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en_US';

        $location = $this->api->getLocationFromIP($ipAddress);
        $this->assertEquals($expected, $location);
    }

    public function getTestDataForGetLocationFromIP()
    {
        return [
            ['113.62.1.1', [
                'country_code' => 'us',
                'continent_code' => 'amn',
                'continent_name' => 'Intl_Continent_amn',
                'country_name' => 'General_Unknown',
                'ip' => '113.62.1.1',
            ]],
            [null, [
                'country_code' => 'us',
                'continent_code' => 'amn',
                'continent_name' => 'Intl_Continent_amn',
                'country_name' => 'General_Unknown',
                'ip' => '151.100.101.92',
            ], '151.100.101.92'],
        ];
    }
}
