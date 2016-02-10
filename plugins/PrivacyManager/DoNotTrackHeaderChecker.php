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
 * Note: visits from Internet Explorer and other browsers that have DoNoTrack enabled by default will be tracked anyway.
 */
class DoNotTrackHeaderChecker
{
    /**
     * @param Config $config
     */
    public function __construct($config = null)
    {
        $this->config = $config ?: new Config();
    }

    /**
     * Checks for DoNotTrack headers and if found, sets `$exclude` to `true`.
     */
    public function checkHeaderInTracker(&$exclude)
    {
        if ($exclude) {
            Common::printDebug("Visit is already excluded, no need to check DoNotTrack support.");
            return;
        }

        $exclude = $this->isDoNotTrackFound();

        if($exclude) {

            $trackingCookie = IgnoreCookie::getTrackingCookie();
            $trackingCookie->delete();

            // this is an optional supplement to the site's tracking status resource at:
            //     /.well-known/dnt
            // per Tracking Preference Expression (draft)
            header('Tk: 1');
        }
    }

    /**
     * @return bool
     */
    public function isDoNotTrackFound()
    {
        if (!$this->isActive()) {
            Common::printDebug("DoNotTrack support is not enabled, skip check");
            return false;
        }

        if (!$this->isHeaderDntFound()) {
            Common::printDebug("DoNotTrack header not found");
            return false;
        }

        $request = new Request($_REQUEST);
        $userAgent = $request->getUserAgent();

        if ($this->isUserAgentWithDoNotTrackAlwaysEnabled($userAgent)) {
            Common::printDebug("INTERNET EXPLORER enable DoNotTrack by default; so Piwik ignores DNT IE browsers...");
            return false;
        }

        Common::printDebug("DoNotTrack header found!");
        return true;
    }

    /**
     * Deactivates DoNotTrack header checking. This function will not be called by the Tracker.
     */
    public function deactivate()
    {
        $this->config->doNotTrackEnabled = false;
    }

    /**
     * Activates DoNotTrack header checking. This function will not be called by the Tracker.
     */
    public function activate()
    {
        $this->config->doNotTrackEnabled = true;
    }

    /**
     * Returns true if server side DoNotTrack support is enabled, false if otherwise.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->config->doNotTrackEnabled;
    }

    /**
     * @return bool
     */
    protected function isHeaderDntFound()
    {
        return (isset($_SERVER['HTTP_X_DO_NOT_TRACK']) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1')
            || (isset($_SERVER['HTTP_DNT']) && substr($_SERVER['HTTP_DNT'], 0, 1) === '1');
    }

    /**
     *
     * @param $userAgent
     * @return bool
     */
    protected function isUserAgentWithDoNotTrackAlwaysEnabled($userAgent)
    {
        $browsersWithDnt = $this->getBrowsersWithDNTAlwaysEnabled();
        foreach($browsersWithDnt as $userAgentBrowserFragment) {
            if (stripos($userAgent, $userAgentBrowserFragment) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Some browsers have DNT enabled by default. For those we will ignore DNT and always track those users.
     *
     * @return array
     */
    protected function getBrowsersWithDNTAlwaysEnabled()
    {
        return array(
            // IE
            'MSIE',
            'Trident',

            // Maxthon
            'Maxthon',
            
            // Epiphany - https://github.com/piwik/piwik/issues/8682
            'Epiphany',
        );
    }
}
