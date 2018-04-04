<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests;

use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\Plugins\UserCountry\API;
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
    
    public function setUp()
    {
        parent::setUp();

        $this->api = API::getInstance();

        // reset location providers as they might be manipulated by other tests
        LocationProvider::$providers = null;
        LocationProvider::getAllProviders();
    }

    public function test_setLocationProvider()
    {
        $locationProvider = LocationProvider\GeoIp2\Php::ID;
        $this->api->setLocationProvider($locationProvider);
        $this->assertEquals($locationProvider, Common::getCurrentLocationProviderId());

        $locationProvider = DefaultProvider::ID;
        $this->api->setLocationProvider($locationProvider);
        $this->assertEquals($locationProvider, Common::getCurrentLocationProviderId());
    }

    /**
     * @expectedException \Exception
     */
    public function test_setLocationProviderInvalid()
    {
        $locationProvider = 'invalidProvider';
        $this->api->setLocationProvider($locationProvider);
    }

    /**
     * @expectedException \Exception
     */
    public function test_setLocationProviderNoSuperUser()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $locationProvider = LocationProvider\GeoIp2\Php::ID;
        $this->api->setLocationProvider($locationProvider);
    }

    /**
     * @expectedException \Exception
     */
    public function test_setLocationProviderDisabledInConfig()
    {
        Config::getInstance()->General['enable_geolocation_admin'] = 0;

        $locationProvider = LocationProvider\GeoIp2\Php::ID;
        $this->api->setLocationProvider($locationProvider);
    }
}
