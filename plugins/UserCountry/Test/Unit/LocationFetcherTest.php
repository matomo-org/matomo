<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Test\Unit;

use PHPUnit_Framework_MockObject_MockObject;
use Piwik\Plugins\UserCountry\LocationFetcher;
use Piwik\Plugins\UserCountry\LocationFetcherProvider;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tracker\Visit;

/**
 * Class LocationFetcherTest
 * @package Piwik\Plugins\UserCountry\Test\Unit
 *
 * @group UserCountry
 */
class LocationFetcherTest extends ProviderTest
{
    public function test_getLocation_shouldReturnLocationForProvider_IfLocationIsSetForCurrentProvider()
    {
        $location = array(
            'city' => 'Wroclaw',
            'country_code' => 'pl'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $locationFetcherProvider = $this->getLocationFetcherProviderMock($provider);

        $locationFetcher = new LocationFetcher($locationFetcherProvider);

        $this->assertEquals(
            $location,
            $locationFetcher->getLocation(array('ip' => '127.0.0.1'))
        );
    }

    public function test_getLocation_shouldReturnLocationForProvider_IfLocationCountryCodeIsNotSetShouldSetAsxx()
    {
        $location = array(
            'city' => 'Wroclaw'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $locationFetcherProvider = $this->getLocationFetcherProviderMock($provider);

        $locationFetcher = new LocationFetcher($locationFetcherProvider);

        $this->assertEquals(
            array_merge(
                $location,
                array(
                    'country_code' => Visit::UNKNOWN_CODE
                )
            ),
            $locationFetcher->getLocation(array('ip' => '127.0.0.2'))
        );
    }

    public function test_getLocation_shouldReturnLocationForProviderAndReadFromCacheIfIPIsNotChanged()
    {
        $locations = array(
            'pl' => array(
                'city' => 'Wroclaw',
                'country_code' => 'pl'
            ),

            'nz' => array(
                'city' => 'Wellington',
                'country_code' => 'nz'
            ),
        );

        $poland = $this->getProviderMock();
        $poland->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($locations['pl']));

        $locationFetcherProvider = $this->getLocationFetcherProviderMock($poland);

        $locationFetcher = new LocationFetcher($locationFetcherProvider);
        $locationFetcher->getLocation(array('ip' => '10.0.0.1'));

        $nz = $this->getProviderMock();
        $nz->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($locations['nz']));

        $locationFetcherProvider = $this->getLocationFetcherProviderMock($nz);

        $locationFetcher = new LocationFetcher($locationFetcherProvider);
        $locationFetcher->getLocation(array('ip' => '10.0.0.2'));

        $locationFetcher = new LocationFetcher($this->getLocationFetcherProviderMock());

        $this->assertEquals(
            $locations,
            array(
                'pl' => $locationFetcher->getLocation(array('ip' => '10.0.0.1')),
                'nz' => $locationFetcher->getLocation(array('ip' => '10.0.0.2'))
            )
        );
    }

    public function test_getLocationDetail_shouldReturnFalse_IfFieldIsUndefined()
    {
        $location = array(
            'city' => 'Wroclaw'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $locationFetcherProvider = $this->getLocationFetcherProviderMock($provider);

        $locationFetcher = new LocationFetcher($locationFetcherProvider);

        $this->assertFalse($locationFetcher->getLocationDetail(array('ip' => '192.168.0.1'), 'region'));
    }

    public function test_getLocationDetail_shouldReturnValue_IfFieldIsDefined()
    {
        $location = array(
            'city' => 'Wroclaw'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $locationFetcherProvider = $this->getLocationFetcherProviderMock($provider);

        $locationFetcher = new LocationFetcher($locationFetcherProvider);

        $this->assertEquals('xx', $locationFetcher->getLocationDetail(array('ip' => '192.168.0.2'), 'country_code'));
    }

    /**
     * @param LocationProvider $provider
     * @return PHPUnit_Framework_MockObject_MockObject|LocationFetcherProvider
     */
    public function getLocationFetcherProviderMock(LocationProvider $provider = null)
    {
        /**
         * @var PHPUnit_Framework_MockObject_MockObject|LocationFetcherProvider $mock
         */
        $mock = $this->getMockBuilder('\Piwik\Plugins\UserCountry\LocationFetcherProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        if ($provider !== null) {
            $mock->expects($this->once())
                ->method('get')
                ->will($this->returnValue($provider));
        }

        return $mock;
    }
} 
