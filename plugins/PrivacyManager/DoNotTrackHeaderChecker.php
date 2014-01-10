<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package PrivacyManager
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Tracker\Request;
use Piwik\Tracker\Cache;
use Piwik\Option;

/**
 * Excludes visits where user agent's request contains either:
 * 
 * - X-Do-Not-Track header (used by AdBlockPlus and NoScript)
 * - DNT header (used by Mozilla)
 * 
 * @package PrivacyManager
 */
class DoNotTrackHeaderChecker
{
    const OPTION_NAME = "PrivacyManager.doNotTrackEnabled";

    /**
     * Checks for DoNotTrack headers and if found, sets `$exclude` to `true`.
     */
    public function checkHeaderInTracker(&$exclude)
    {
        if (!$this->isActiveInTracker()
            || $exclude
        ) {
            return;
        }

        if ((isset($_SERVER['HTTP_X_DO_NOT_TRACK']) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1')
            || (isset($_SERVER['HTTP_DNT']) && substr($_SERVER['HTTP_DNT'], 0, 1) === '1')
        ) {
            $request = new Request($_REQUEST);
            $ua = $request->getUserAgent();
            if (strpos($ua, 'MSIE 10') !== false
                || strpos($ua, 'Trident/7') !== false) {
                Common::printDebug("INTERNET EXPLORER 10 and 11 enable DoNotTrack by default; so Piwik ignores DNT for all IE10 + IE11 browsers...");
                return;
            }

            $exclude = true;
            Common::printDebug("DoNotTrack found.");

            $trackingCookie = IgnoreCookie::getTrackingCookie();
            $trackingCookie->delete();

            // this is an optional supplement to the site's tracking status resource at:
            //     /.well-known/dnt
            // per Tracking Preference Expression (draft)
            header('Tk: 1');
        }
    }

    /**
     * Returns true if DoNotTrack header checking is enabled. This function is called by the
     * Tracker.
     */
    private function isActiveInTracker()
    {
        $cache = Cache::getCacheGeneral();
        return !empty($cache[self::OPTION_NAME]);
    }

    /**
     * Caches the status of DoNotTrack checking (whether it is enabled or not).
     */
    public function setTrackerCacheGeneral(&$cacheContent)
    {
        $cacheContent[self::OPTION_NAME] = Option::get(self::OPTION_NAME);
    }

    /**
     * Deactivates DoNotTrack header checking. This function will not be called by the Tracker.
     */
    public static function deactivate()
    {
        Option::set(self::OPTION_NAME, 0);
        Cache::clearCacheGeneral();
    }

    /**
     * Activates DoNotTrack header checking. This function will not be called by the Tracker.
     */
    public static function activate()
    {
        Option::set(self::OPTION_NAME, 1);
        Cache::clearCacheGeneral();
    }

    /**
     * Returns true if server side DoNotTrack support is enabled, false if otherwise.
     *
     * @return bool
     */
    public static function isActive()
    {
        $active = Option::get(self::OPTION_NAME);
        return !empty($active);
    }
}