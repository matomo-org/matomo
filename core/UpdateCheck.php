<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Plugins\SitesManager\API;

/**
 * Class to check if a newer version of Piwik is available
 *
 */
class UpdateCheck
{
    const CHECK_INTERVAL = 28800; // every 8 hours
    const UI_CLICK_CHECK_INTERVAL = 10; // every 10s when user clicks UI link
    const LAST_TIME_CHECKED = 'UpdateCheck_LastTimeChecked';
    const LATEST_VERSION = 'UpdateCheck_LatestVersion';
    const SOCKET_TIMEOUT = 2;

    private static function isAutoUpdateEnabled()
    {
        return (bool) Config::getInstance()->General['enable_auto_update'];
    }

    /**
     * Check for a newer version
     *
     * @param bool $force Force check
     * @param int $interval Interval used for update checks
     */
    public static function check($force = false, $interval = null)
    {
        if(!self::isAutoUpdateEnabled()) {
            return;
        }

        if ($interval === null) {
            $interval = self::CHECK_INTERVAL;
        }

        $lastTimeChecked = Option::get(self::LAST_TIME_CHECKED);
        if ($force
            || $lastTimeChecked === false
            || time() - $interval > $lastTimeChecked
        ) {
            // set the time checked first, so that parallel Piwik requests don't all trigger the http requests
            Option::set(self::LAST_TIME_CHECKED, time(), $autoLoad = 1);
            $parameters = array(
                'piwik_version' => Version::VERSION,
                'php_version'   => PHP_VERSION,
                'url'           => Url::getCurrentUrlWithoutQueryString(),
                'trigger'       => Common::getRequestVar('module', '', 'string'),
                'timezone'      => API::getInstance()->getDefaultTimezone(),
            );

            $url = Config::getInstance()->General['api_service_url']
                . '/1.0/getLatestVersion/'
                . '?' . http_build_query($parameters, '', '&');
            $timeout = self::SOCKET_TIMEOUT;

            if (@Config::getInstance()->Debug['allow_upgrades_to_beta']) {
                $url = 'http://builds.piwik.org/LATEST_BETA';
            }

            try {
                $latestVersion = Http::sendHttpRequest($url, $timeout);
                if (!preg_match('~^[0-9][0-9a-zA-Z_.-]*$~D', $latestVersion)) {
                    $latestVersion = '';
                }
            } catch (Exception $e) {
                // e.g., disable_functions = fsockopen; allow_url_open = Off
                $latestVersion = '';
            }
            Option::set(self::LATEST_VERSION, $latestVersion);
        }
    }

    /**
     * Returns the latest available version number. Does not perform a check whether a later version is available.
     *
     * @return false|string
     */
    public static function getLatestVersion()
    {
        return Option::get(self::LATEST_VERSION);
    }

    /**
     * Returns version number of a newer Piwik release.
     *
     * @return string|bool  false if current version is the latest available,
     *                       or the latest version number if a newest release is available
     */
    public static function isNewestVersionAvailable()
    {
        $latestVersion = self::getLatestVersion();
        if (!empty($latestVersion)
            && version_compare(Version::VERSION, $latestVersion) == -1
        ) {
            return $latestVersion;
        }
        return false;
    }
}
