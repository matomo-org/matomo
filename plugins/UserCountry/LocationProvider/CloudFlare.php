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
 * This LocationProvider uses the HTTP_CF_IPCOUNTRY header provided by
 * CloudFlare to determine the user's location. It only works if your Piwik
 * installation runs behind CloudFlare.
 *
 * @author Gabriel Bauman <gabe@codehaus.org>
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_LocationProvider_CloudFlare extends Piwik_UserCountry_LocationProvider
{
    const ID = 'cloudflare';
    const TITLE = 'CloudFlare';

    /**
     * Uses the CloudFlare-provided HTTP header HTTP_CF_IPCOUNTRY to determine
     * the user's IP address.
     *
     * @param array $info Contains 'ip' & 'lang' keys.
     * @return array Contains the guessed country code mapped to LocationProvider::COUNTRY_CODE_KEY.
     */
    public function getLocation($info)
    {
        return array(parent::COUNTRY_CODE_KEY => $_SERVER["HTTP_CF_IPCOUNTRY"]);
    }

    /**
     * Returns whether this location provider is available.
     *
     * This implementation is only available if Piwik is behind CloudFlare.
     *
     * @return true
     */
    public function isAvailable()
    {
        return isset($_SERVER["HTTP_CF_IPCOUNTRY"]);
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
     * - country code
     *
     * @return array
     */
    public function getSupportedLocationInfo()
    {
        return array(self::COUNTRY_CODE_KEY   => true);
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
        $desc = 'This provider uses HTTP headers sent by CloudFlare to determine visitor location.';
        return array('id' => self::ID, 'title' => self::TITLE, 'description' => $desc);
    }
}

