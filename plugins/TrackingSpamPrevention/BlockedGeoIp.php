<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\VisitorGeolocator;

class BlockedGeoIp
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function __construct(SystemSettings $settings)
    {
        $this->settings = $settings;
    }

    public function detectLocation($ip, $language)
    {
        // we need to use both IP and language so it will use the same cache key as in core!
        $visitorLocator = new VisitorGeolocator();
        $info = array('lang' => $language, 'ip' => $ip);
        return $visitorLocator->getLocation($info, $useClassCache = true);
    }

    public function isExcludedCountry($ip, $language, $excludedCountries, $includedCountries)
    {
        if (empty($excludedCountries) && empty($includedCountries)) {
            return false;
        }

        $result = $this->detectLocation($ip, $language);
        if (empty($result[LocationProvider::COUNTRY_CODE_KEY])) {
            return false;
        }

        $countryCode = strtolower($result[LocationProvider::COUNTRY_CODE_KEY]);

        if (!empty($includedCountries) && in_array($countryCode, $includedCountries, true)) {
            return false;
        } elseif (!empty($includedCountries)) {
            return true;
        }

        if (!empty($excludedCountries) && in_array($countryCode, $excludedCountries, true)) {
            return true;
        }

        return false;
    }

    public function isExcludedProvider($ip, $language)
    {
        $blockedProviders = $this->settings->getBlockedOrganisations();

        if (empty($blockedProviders) || !(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('UserCountry'))) {
            return false;
        }
        $result = $this->detectLocation($ip, $language);

        if (!empty($result[LocationProvider::ORG_KEY])) {
            $org = $result[LocationProvider::ORG_KEY];
            foreach ($blockedProviders as $blockedProvider) {
                if (!empty($blockedProvider) && stripos($org, $blockedProvider) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
