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
use Piwik\Plugins\UserCountry\LocationFetcher;
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

        $locationFetcher = $this->getLocationFetcherWithProviderMock($provider, 'mock_provider');

        $this->assertEquals(
            $location,
            $locationFetcher->getLocation(array('ip' => '127.0.0.1'))
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

        $locationFetcher = $this->getLocationFetcherWithProviderMock($provider, 'mock_provider');

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

        $locationFetcher = $this->getLocationFetcherWithProviderMock($poland, 'mock_provider');
        $locationFetcher->getLocation(array('ip' => '10.0.0.1'));

        $nz = $this->getProviderMock();
        $nz->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($locations['nz']));

        $locationFetcher = $this->getLocationFetcherWithProviderMock($nz, 'mock_provider');
        $locationFetcher->getLocation(array('ip' => '10.0.0.2'));

        $this->assertEquals(
            $locations,
            array(
                'pl' => $locationFetcher->getLocation(array('ip' => '10.0.0.1')),
                'nz' => $locationFetcher->getLocation(array('ip' => '10.0.0.2'))
            )
        );
    }

    public function test_get_shouldReturnDefaultProvider_IfCurrentProviderReturnFalse()
    {
        $providerGetterMock = $this->getMockBuilder('\Piwik\Plugins\UserCountry\tests\Mock\ProviderGetterMock')
            ->setMethods(array('getProviderById'))
            ->getMock();

        $providerGetterMock->expects($this->exactly(2))
            ->method('getProviderById')
            ->will($this->returnCallback(
                function ($id)
                {
                    switch ($id) {
                        case 'CurrentProviderId':
                            return false;

                        case 'DefaultProviderId':
                            return 'DefaultProvider';
                    }

                    return false;
                }
            ));

        $locationFetcher = new LocationFetcher(
            'CurrentProviderId', array($providerGetterMock, 'getProviderById'), 'DefaultProviderId'
        );

        $this->assertEquals('DefaultProvider', $locationFetcher->getProvider('DefaultProviderId'));
    }

    public function test_get_shouldReturnCurrentProvider_IfCurrentProviderIsSet()
    {
        $providerGetterMock = $this->getMockBuilder('\Piwik\Plugins\UserCountry\tests\Mock\ProviderGetterMock')
            ->setMethods(array('getProviderById'))
            ->getMock();

        $providerGetterMock->expects($this->once())
            ->method('getProviderById')
            ->will($this->returnCallback(
                function ($id)
                {
                    switch ($id) {
                        case 'CurrentProviderId':
                            return 'CurrentProvider';

                        case 'DefaultProviderId':
                            return 'DefaultProvider';
                    }

                    return false;
                }
            ));

        $locationFetcher = new LocationFetcher(
            'CurrentProviderId', array($providerGetterMock, 'getProviderById')
        );

        $this->assertEquals('CurrentProvider', $locationFetcher->getProvider('DefaultProviderId'));
    }

    /**
     * @param $provider
     * @param string $currentLocationProviderId
     * @return PHPUnit_Framework_MockObject_MockObject|LocationFetcher
     */
    protected function getLocationFetcherWithProviderMock($provider, $currentLocationProviderId = null)
    {
        $locationFetcher = $this->getMockBuilder('\Piwik\Plugins\UserCountry\LocationFetcher')
            ->setConstructorArgs(array($currentLocationProviderId))
            ->setMethods(array('getProvider'))
            ->getMock();

        $locationFetcher->expects($this->once())
            ->method('getProvider')
            ->willReturn($provider);

        return $locationFetcher;
    }
}
