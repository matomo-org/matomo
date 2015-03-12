<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\tests\Unit;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Piwik\Plugins\UserCountry\VisitorGeolocator;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Visit;
use Piwik\Tests\Framework\Mock\LocationProvider as MockLocationProvider;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Framework/Mock/LocationProvider.php';

/**
 * @group UserCountry
 */
class VisitorGeolocatorTest extends PHPUnit_Framework_TestCase
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

        $geolocator = new VisitorGeolocator($provider);

        $this->assertEquals(
            $location,
            $geolocator->getLocation(array('ip' => '127.0.0.1'))
        );
    }

    /**
     *
     */
    public function test_getLocation_shouldReturnLocationForProvider_IfLocationCountryCodeIsNotSetShouldSetAsxx()
    {
        $location = array(
            'city' => 'Wroclaw'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $geolocator = new VisitorGeolocator($provider);

        $this->assertEquals(
            array_merge(
                $location,
                array(
                    'country_code' => Visit::UNKNOWN_CODE
                )
            ),
            $geolocator->getLocation(array('ip' => '127.0.0.2'))
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

        $geolocator = new VisitorGeolocator($poland);
        $geolocator->getLocation(array('ip' => '10.0.0.1'));

        $nz = $this->getProviderMock();
        $nz->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($locations['nz']));

        $geolocator = new VisitorGeolocator($nz);
        $geolocator->getLocation(array('ip' => '10.0.0.2'));

        $this->assertEquals(
            $locations,
            array(
                'pl' => $geolocator->getLocation(array('ip' => '10.0.0.1')),
                'nz' => $geolocator->getLocation(array('ip' => '10.0.0.2'))
            )
        );
    }

    public function test_get_shouldReturnDefaultProvider_IfCurrentProviderReturnFalse()
    {
        Cache::setCacheGeneral(array('currentLocationProviderId' => 'nonexistant'));
        $geolocator = new VisitorGeolocator();

        $this->assertEquals(LocationProvider\DefaultProvider::ID, $geolocator->getProvider()->getId());
    }

    public function test_get_shouldReturnCurrentProvider_IfCurrentProviderIsSet()
    {
        Cache::setCacheGeneral(array('currentLocationProviderId' => MockLocationProvider::ID));
        $geolocator = new VisitorGeolocator();

        $this->assertEquals(MockLocationProvider::ID, $geolocator->getProvider()->getId());
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|LocationProvider
     */
    protected function getProviderMock()
    {
        return $this->getMockBuilder('\Piwik\Plugins\UserCountry\LocationProvider')
            ->setMethods(array('getId', 'getLocation', 'isAvailable', 'isWorking', 'getSupportedLocationInfo'))
            ->disableOriginalConstructor()
            ->getMock();
    }
}