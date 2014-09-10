<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Test\Unit;

use Piwik\Plugins\UserCountry\LocationFetcherProvider;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * Class LocationFetcherProviderTest
 * @package Piwik\Plugins\UserCountry\Test\Unit
 *
 * @group UserCountry
 */
class LocationFetcherProviderTest extends ProviderTest
{
    public function test_isDefaultProvider_shouldReturnFalse_IfProviderIsNull()
    {
        $locationFetcherProvider = new LocationFetcherProvider();

        $this->assertFalse($locationFetcherProvider->isDefaultProvider(null));
    }

    public function test_isDefaultProvider_shouldReturnFalse_IfProviderIsSetButHasDifferentIdThanDefault()
    {
        $locationFetcherProvider = new LocationFetcherProvider(null, null, 'DefaultProviderId');

        $provider = $this->getProviderMock();

        $provider->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('AnotherProvider'));

        $this->assertFalse($locationFetcherProvider->isDefaultProvider($provider));
    }

    public function test_isDefaultProvider_shouldReturnTrue_IfProviderIsDefaultProvider()
    {
        $locationFetcherProvider = new LocationFetcherProvider(null, null, 'DefaultProviderId');

        $provider = $this->getProviderMock();

        $provider->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('DefaultProviderId'));

        $this->asserttrue($locationFetcherProvider->isDefaultProvider($provider));
    }

    public function test_getDefaultProvider_shouldRunGetProviderByIdCallbackOnceWithDefaultProviderIdAsParameter()
    {
        $providerGetterMock = $this->getMockBuilder('\Piwik\Plugins\UserCountry\Test\Mock\ProviderGetterMock')
            ->setMethods(array('getProviderById'))
            ->getMock();

        $providerGetterMock->expects($this->once())
            ->method('getProviderById')
            ->will($this->returnArgument(0));

        $locationFetcherProvider = new LocationFetcherProvider(
            null, array($providerGetterMock, 'getProviderById'), 'DefaultProviderId'
        );

        $this->assertEquals('DefaultProviderId', $locationFetcherProvider->getDefaultProvider());
    }

    public function test_get_shouldReturnDefaultProvider_IfCurrentProviderReturnFalse()
    {
        $providerGetterMock = $this->getMockBuilder('\Piwik\Plugins\UserCountry\Test\Mock\ProviderGetterMock')
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
                }
            ));

        $locationFetcherProvider = new LocationFetcherProvider(
            'CurrentProviderId', array($providerGetterMock, 'getProviderById'), 'DefaultProviderId'
        );

        $this->assertEquals('DefaultProvider', $locationFetcherProvider->get());
    }

    public function test_get_shouldReturnCurrentProvider_IfCurrentProviderIsSet()
    {
        $providerGetterMock = $this->getMockBuilder('\Piwik\Plugins\UserCountry\Test\Mock\ProviderGetterMock')
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
                }
            ));

        $locationFetcherProvider = new LocationFetcherProvider(
            'CurrentProviderId', array($providerGetterMock, 'getProviderById'), 'DefaultProviderId'
        );

        $this->assertEquals('CurrentProvider', $locationFetcherProvider->get());
    }
} 
