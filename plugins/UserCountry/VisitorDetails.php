<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Common;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
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
            return getRegionNameFromCodes(
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


    private $cities     = array();
    private $countries  = array();
    private $continents = array();

    public function initProfile($visits, &$profile)
    {
        $this->cities          = array();
        $this->continents      = array();
        $this->countries       = array();
        $profile['hasLatLong'] = false;
    }

    public function handleProfileVisit($visit, &$profile)
    {
        // realtime map only checks for latitude
        $hasLatitude = $visit->getColumn('latitude') !== false;
        if ($hasLatitude) {
            $profile['hasLatLong'] = true;
        }

        $countryCode = $visit->getColumn('countryCode');
        if (!isset($this->countries[$countryCode])) {
            $this->countries[$countryCode] = 0;
        }
        ++$this->countries[$countryCode];

        $continentCode = $visit->getColumn('continentCode');
        if (!isset($this->continents[$continentCode])) {
            $this->continents[$continentCode] = 0;
        }
        ++$this->continents[$continentCode];

        if ($countryCode && !array_key_exists($countryCode, $this->cities)) {
            $this->cities[$countryCode] = array();
        }
        $city = $visit->getColumn('city');
        if (!empty($city)) {
            $this->cities[$countryCode][] = $city;
        }
    }

    public function finalizeProfile($visits, &$profile)
    {
        // transform country/continents/search keywords into something that will look good in XML
        $profile['countries'] = $profile['continents'] = array();

        // sort by visit/action
        asort($this->continents);
        foreach ($this->continents as $continentCode => $nbVisits) {
            $profile['continents'][] = array(
                'continent'  => $continentCode,
                'nb_visits'  => $nbVisits,
                'prettyName' => \Piwik\Plugins\UserCountry\continentTranslate($continentCode)
            );
        }

        // sort by visit/action
        asort($this->countries);

        foreach ($this->countries as $countryCode => $nbVisits) {
            $countryInfo = array(
                'country'    => $countryCode,
                'nb_visits'  => $nbVisits,
                'flag'       => \Piwik\Plugins\UserCountry\getFlagFromCode($countryCode),
                'prettyName' => \Piwik\Plugins\UserCountry\countryTranslate($countryCode)
            );
            if (!empty($this->cities[$countryCode])) {
                $countryInfo['cities'] = array_unique($this->cities[$countryCode]);
            }
            $profile['countries'][] = $countryInfo;
        }
    }
}