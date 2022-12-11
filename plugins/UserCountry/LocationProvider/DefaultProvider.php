<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\LocationProvider;

use Matomo\Network\IP;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\Provider\Provider as ProviderProvider;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tracker\TrackerConfig;

/**
 * The default LocationProvider, this LocationProvider guesses a visitor's country
 * using the language they use. This provider is not very accurate.
 *
 */
class DefaultProvider extends LocationProvider
{
    const ID = 'default';
    const TITLE = 'General_Default';

    /**
     * Guesses a visitor's location using a visitor's browser language.
     *
     * @param array $info Contains 'ip' & 'lang' keys.
     * @return array Contains the guessed country code mapped to LocationProvider::COUNTRY_CODE_KEY.
     */
    public function getLocation($info)
    {
        $country = $this->getCountryUsingProviderExtensionIfAvailable($info['ip']);

        if (empty($country)) {
            $enableLanguageToCountryGuess = Config::getInstance()->Tracker['enable_language_to_country_guess'];

            if (empty($info['lang'])) {
                $info['lang'] = Common::getBrowserLanguage();
            }
            $country = Common::getCountry($info['lang'], $enableLanguageToCountryGuess, $info['ip']);
        }

        $location = [parent::COUNTRY_CODE_KEY => $country];
        $this->completeLocationResult($location);

        return $location;
    }


    private function getCountryUsingProviderExtensionIfAvailable($ipAddress)
    {
        if (
            !Manager::getInstance()->isPluginInstalled('Provider')
            || !class_exists('Piwik\Plugins\Provider\Provider')
            || Common::getRequestVar('dp', 0, 'int') === 1
        ) {
            return false;
        }

        $privacyConfig = new PrivacyManagerConfig();

        // when using anonymized ip for enrichment we skip this check
        if ($privacyConfig->useAnonymizedIpForVisitEnrichment) {
            return false;
        }

        $hostname = $this->getHost($ipAddress);
        $hostnameExtension = ProviderProvider::getCleanHostname($hostname);

        $hostnameDomain = substr($hostnameExtension, 1 + strrpos($hostnameExtension, '.'));
        if ($hostnameDomain == 'uk') {
            $hostnameDomain = 'gb';
        }

        /** @var RegionDataProvider $regionDataProvider */
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        if (array_key_exists($hostnameDomain, $regionDataProvider->getCountryList())) {
            return $hostnameDomain;
        }

        return false;
    }

    /**
     * Returns the hostname given the IP address string
     *
     * @param string $ipStr IP Address
     * @return string hostname (or human-readable IP address)
     */
    protected function getHost($ipStr)
    {
        $ip = IP::fromStringIP($ipStr);

        $host = $ip->getHostname();
        $host = ($host === null ? $ipStr : $host);

        return trim(strtolower($host));
    }

    /**
     * Returns whether this location provider is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return !!TrackerConfig::getConfigValue('enable_default_location_provider');
    }


    /**
     * Returns whether this location provider is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return !!TrackerConfig::getConfigValue('enable_default_location_provider');
    }

    /**
     * Returns whether this location provider is working correctly.
     *
     * This implementation is always working correctly.
     *
     * @return bool  always true
     */
    public function isWorking()
    {
        return true;
    }

    /**
     * Returns an array describing the types of location information this provider will
     * return.
     *
     * This provider supports the following types of location info:
     * - continent code
     * - continent name
     * - country code
     * - country name
     *
     * @return array
     */
    public function getSupportedLocationInfo()
    {
        return [self::CONTINENT_CODE_KEY => true,
                     self::CONTINENT_NAME_KEY => true,
                     self::COUNTRY_CODE_KEY   => true,
                     self::COUNTRY_NAME_KEY   => true];
    }

    /**
     * Returns information about this location provider. Contains an id, title & description:
     *
     * array(
     *     'id' => 'default',
     *     'title' => '...',
     *     'description' => '...'
     * );
     *
     * @return array
     */
    public function getInfo()
    {
        $desc = '<p>' . Piwik::translate('UserCountry_DefaultLocationProviderDesc1') . ' '
            . Piwik::translate(
                'UserCountry_DefaultLocationProviderDesc2',
                ['<strong>', '', '', '</strong>']
            )
            . '</p><p><a href="https://matomo.org/faq/how-to/faq_163" rel="noreferrer noopener"  target="_blank">'
            . Piwik::translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</a></p>';
        return ['id' => self::ID, 'title' => self::TITLE, 'description' => $desc, 'order' => 1];
    }

    public function getUsageWarning(): ?string
    {
        $comment = Piwik::translate('UserCountry_DefaultLocationProviderDesc1') . ' ';
        $comment .= Piwik::translate('UserCountry_DefaultLocationProviderDesc2', [
            '<a href="https://matomo.org/docs/geo-locate/" rel="noreferrer noopener" target="_blank">', '', '', '</a>'
        ]);

        return $comment;
    }
}
