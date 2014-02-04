<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Tracker\Request;

/**
 * Excludes visits where user agent's request contains either:
 * 
 * - X-Do-Not-Track header (used by AdBlockPlus and NoScript)
 * - DNT header (used by Mozilla)
 * 
 */
class DoNotTrackHeaderChecker
{
    /**
     * Checks for DoNotTrack headers and if found, sets `$exclude` to `true`.
     */
    public function checkHeaderInTracker(&$exclude)
    {
        if (!$this->isActive()
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
     * Deactivates DoNotTrack header checking. This function will not be called by the Tracker.
     */
    public static function deactivate()
    {
        $config = new Config();
        $config->doNotTrackEnabled = false;
    }

    /**
     * Activates DoNotTrack header checking. This function will not be called by the Tracker.
     */
    public static function activate()
    {
        $config = new Config();
        $config->doNotTrackEnabled = true;
    }

    /**
     * Returns true if server side DoNotTrack support is enabled, false if otherwise.
     *
     * @return bool
     */
    public static function isActive()
    {
        $config = new Config();
        return $config->doNotTrackEnabled;
    }
}
