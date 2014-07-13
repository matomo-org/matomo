<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\LocationProvider;

use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;

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
        $enableLanguageToCountryGuess = Config::getInstance()->Tracker['enable_language_to_country_guess'];

        if (empty($info['lang'])) {
            $info['lang'] = Common::getBrowserLanguage();
        }
        $country = Common::getCountry($info['lang'], $enableLanguageToCountryGuess, $info['ip']);

        $location = array(parent::COUNTRY_CODE_KEY => $country);
        $this->completeLocationResult($location);

        return $location;
    }

    /**
     * Returns whether this location provider is available.
     *
     * This implementation is always available.
     *
     * @return bool  always true
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
        $desc = Piwik::translate('UserCountry_DefaultLocationProviderDesc1') . ' '
            . Piwik::translate('UserCountry_DefaultLocationProviderDesc2',
                array('<strong>', '<em>', '</em>', '</strong>'))
            . '<p><em><a href="http://piwik.org/faq/how-to/#faq_163" target="_blank">'
            . Piwik::translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</em></a></p>';
        return array('id' => self::ID, 'title' => self::TITLE, 'description' => $desc, 'order' => 1);
    }
}

