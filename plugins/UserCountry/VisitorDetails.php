<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Common;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Tracker\Visit;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['continent']     = $this->getContinent();
        $visitor['continentCode'] = $this->getContinentCode();
        $visitor['country']       = $this->getCountryName();
        $visitor['countryCode']   = $this->getCountryCode();
        $visitor['countryFlag']   = $this->getCountryFlag();
        $visitor['region']        = $this->getRegionName();
        $visitor['regionCode']    = $this->getRegionCode();
        $visitor['city']          = $this->getCityName();
        $visitor['location']      = $this->getPrettyLocation();
        $visitor['latitude']      = $this->getLatitude();
        $visitor['longitude']     = $this->getLongitude();
    }

    protected function getCountryCode()
    {
        return $this->details['location_country'];
    }

    protected function getCountryName()
    {
        return countryTranslate($this->getCountryCode());
    }

    protected function getCountryFlag()
    {
        return getFlagFromCode($this->getCountryCode());
    }

    protected function getContinent()
    {
        return continentTranslate($this->getContinentCode());
    }

    protected function getContinentCode()
    {
        return Common::getContinent($this->details['location_country']);
    }

    protected function getCityName()
    {
        if (!empty($this->details['location_city'])) {
            return $this->details['location_city'];
        }

        return null;
    }

    protected function getRegionName()
    {
        $region = $this->getRegionCode();
        if ($region != '' && $region != Visit::UNKNOWN_CODE) {
            return GeoIp::getRegionNameFromCodes(
                $this->details['location_country'], $region);
        }

        return null;
    }

    protected function getRegionCode()
    {
        return $this->details['location_region'];
    }

    protected function getPrettyLocation()
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

    protected function getLatitude()
    {
        if (!empty($this->details['location_latitude'])) {
            return $this->details['location_latitude'];
        }

        return null;
    }

    protected function getLongitude()
    {
        if (!empty($this->details['location_longitude'])) {
            return $this->details['location_longitude'];
        }

        return null;
    }
}