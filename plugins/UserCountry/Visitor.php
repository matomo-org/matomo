<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tracker\Visit;
use Piwik\Url;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function getCountryCode()
    {
        return $this->details['location_country'];
    }

    public function getCountryName()
    {
        return countryTranslate($this->getCountryCode());
    }

    public function getCountryFlag()
    {
        return getFlagFromCode($this->getCountryCode());
    }

    public function getContinent()
    {
        return continentTranslate($this->getContinentCode());
    }

    public function getContinentCode()
    {
        return Common::getContinent($this->details['location_country']);
    }

    public function getCityName()
    {
        if (!empty($this->details['location_city'])) {
            return $this->details['location_city'];
        }

        return null;
    }

    public function getRegionName()
    {
        $region = $this->getRegionCode();
        if ($region != '' && $region != Visit::UNKNOWN_CODE) {
            return GeoIp::getRegionNameFromCodes(
                $this->details['location_country'], $region);
        }

        return null;
    }

    public function getRegionCode()
    {
        return $this->details['location_region'];
    }

    public function getPrettyLocation()
    {
        $parts = array();

        $city = $this->getCityName();
        if (!empty($city)) {
            $parts[] = $city;
        }
        $region = $this->getRegionName();
        if (!empty($region)) {
            $parts[] = $region;
        }

        // add country & return concatenated result
        $parts[] = $this->getCountryName();

        return implode(', ', $parts);
    }

    public function getLatitude()
    {
        if (!empty($this->details['location_latitude'])) {
            return $this->details['location_latitude'];
        }

        return null;
    }

    public function getLongitude()
    {
        if (!empty($this->details['location_longitude'])) {
            return $this->details['location_longitude'];
        }

        return null;
    }
}