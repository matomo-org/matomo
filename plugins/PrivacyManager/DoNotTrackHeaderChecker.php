<?php
/**
 * Piwik - free/libre analytics platform
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
        if ($exclude) {
            Common::printDebug("Visit is already excluded, no need to check DoNotTrack support.");
            return;
        }

        if (!$this->isActive()) {
            Common::printDebug("DoNotTrack support is not enabled, skip check");
            return;
        }

        if ((isset($_SERVER['HTTP_X_DO_NOT_TRACK']) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1')
            || (isset($_SERVER['HTTP_DNT']) && substr($_SERVER['HTTP_DNT'], 0, 1) === '1')
        ) {
            $request = new Request($_REQUEST);
            $ua = $request->getUserAgent();
            if (strpos($ua, 'MSIE') !== false
                || strpos($ua, 'Trident') !== false) {
                Common::printDebug("INTERNET EXPLORER enable DoNotTrack by default; so Piwik ignores DNT IE browsers...");
                return;
            }

            Common::printDebug("DoNotTrack header found!");

            $exclude = true;

            $trackingCookie = IgnoreCookie::getTrackingCookie();
            $trackingCookie->delete();

            // this is an optional supplement to the site's tracking status resource at:
            //     /.well-known/dnt
            // per Tracking Preference Expression (draft)
            header('Tk: 1');
        } else {
            Common::printDebug("DoNotTrack header not found");
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
