<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\Mock;

use Piwik\Option;
use Piwik\Plugins\UserCountry\LocationProvider as CountryLocationProvider;

/**
 * @since 2.8.0
 * @deprecated
 */
class LocationProvider extends CountryLocationProvider
{
    const ALL_LOCATIONS_OPTION_NAME = 'Tests.MockLocationProvider.locations';
    const CURRENT_LOCATION_OPTION_NAME = 'Tests.MockLocationProvider.currentLocation';
    const IP_TO_LOCATIONS_OPTION_NAME = 'Tests.MockLocationProvider.ipToLocations';

    const ID = 'mock_provider';

    public static $locations = array();
    private $currentLocation = 0;
    private $ipToLocations   = array();

    public function __construct()
    {
        self::$locations = self::getOptionValue(self::ALL_LOCATIONS_OPTION_NAME) ?: array();
        $this->currentLocation = self::getOptionValue(self::CURRENT_LOCATION_OPTION_NAME) ?: 0;
        $this->ipToLocations = self::getOptionValue(self::IP_TO_LOCATIONS_OPTION_NAME) ?: array();
    }

    public static function setLocations($locations)
    {
        self::$locations = $locations;

        self::setOptionValue(self::ALL_LOCATIONS_OPTION_NAME, self::$locations);
    }

    public function getLocation($info)
    {
        $ip = $info['ip'];

        if (isset($this->ipToLocations[$ip])) {
            $result = $this->ipToLocations[$ip];
        } else {
            $result = self::$locations[$this->currentLocation];
            $this->currentLocation = ($this->currentLocation + 1) % count(self::$locations);

            $this->ipToLocations[$ip] = $result;

            self::setOptionValue(self::CURRENT_LOCATION_OPTION_NAME, $this->currentLocation);
            self::setOptionValue(self::IP_TO_LOCATIONS_OPTION_NAME, $this->ipToLocations);
        }

        $this->completeLocationResult($result);

        return $result;
    }

    public function getInfo()
    {
        return array('id' => self::ID, 'title' => 'mock provider', 'description' => 'mock provider');
    }

    public function isAvailable()
    {
        return true;
    }

    public function isWorking()
    {
        return true;
    }

    public function getSupportedLocationInfo()
    {
        return array(); // unimplemented
    }

    private static function getOptionValue($name)
    {
        $value = Option::get($name);
        if (empty($value)) {
            return $value;
        } else {
            return unserialize($value);
        }
    }

    private static function setOptionValue($name, $value)
    {
        Option::set($name, serialize($value));
    }

    public static function setUpInTracker()
    {
        $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING'] = true;

        // tracker process has no installed plugins due to Config::setTestEnvironment, however local tracking
        // has installed plugins (specifically Provider). it must be installed in order to get correct results w/ mock
        // location provider but w/o LocalTracker
        \Piwik\Plugin\Manager::getInstance()->loadActivatedPlugins();
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();
    }
}
