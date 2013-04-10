<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 * The default LocationProvider, this LocationProvider guesses a visitor's country
 * using the language they use. This provider is not very accurate.
 *
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_LocationProvider_Default extends Piwik_UserCountry_LocationProvider
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
        $enableLanguageToCountryGuess = Piwik_Config::getInstance()->Tracker['enable_language_to_country_guess'];

        if (empty($info['lang'])) {
            $info['lang'] = Piwik_Common::getBrowserLanguage();
        }
        $country = Piwik_Common::getCountry($info['lang'], $enableLanguageToCountryGuess, $info['ip']);

        $location = array(parent::COUNTRY_CODE_KEY => $country);
        $this->completeLocationResult($location);

        return $location;
    }

    /**
     * Returns whether this location provider is available.
     *
     * This implementation is always available.
     *
     * @return true
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Returns whether this location provider is working correctly.
     *
     * This implementation is always working correctly.
     *
     * @return true
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
        return array(self::CONTINENT_CODE_KEY => true,
                     self::CONTINENT_NAME_KEY => true,
                     self::COUNTRY_CODE_KEY   => true,
                     self::COUNTRY_NAME_KEY   => true);
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
        $desc = Piwik_Translate('UserCountry_DefaultLocationProviderDesc1') . ' '
            . Piwik_Translate('UserCountry_DefaultLocationProviderDesc2',
                array('<strong>', '<em>', '</em>', '</strong>'))
            . '<p><em><a href="http://piwik.org/faq/how-to/#faq_163" target="_blank">'
            . Piwik_Translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</em></a></p>';
        return array('id' => self::ID, 'title' => self::TITLE, 'description' => $desc, 'order' => 1);
    }
}

