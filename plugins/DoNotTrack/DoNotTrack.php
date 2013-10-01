<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package DoNotTrack
 */
namespace Piwik\Plugins\DoNotTrack;

use Piwik\Common;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Tracker\Request;

/**
 * Ignore visits where user agent's request contains either:
 * - X-Do-Not-Track header (used by AdBlockPlus and NoScript)
 * - DNT header (used by Mozilla)
 *
 * @package DoNotTrack
 */
class DoNotTrack extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Tracker.isExcludedVisit' => 'checkHeader',
        );
    }

    function checkHeader(&$exclude)
    {
        if ((isset($_SERVER['HTTP_X_DO_NOT_TRACK']) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1')
            || (isset($_SERVER['HTTP_DNT']) && substr($_SERVER['HTTP_DNT'], 0, 1) === '1')
        ) {
            $request = new Request($_REQUEST);
            $ua = $request->getUserAgent();
            if (strpos($ua, 'MSIE 10') !== false) {
                Common::printDebug("INTERNET EXPLORER 10 Enables DNT by default, so Piwik ignores DNT for all IE10 browsers...");
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
}
