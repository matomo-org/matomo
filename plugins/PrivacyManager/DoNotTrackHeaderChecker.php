<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Tracker\IgnoreCookie;

/**
 * Excludes visits where user agent's request contains either:
 *
 * - X-Do-Not-Track header (used by AdBlockPlus and NoScript)
 * - DNT header (used by Mozilla)
 *
 */
class DoNotTrackHeaderChecker
{
    protected $config;

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

            IgnoreCookie::deleteThirdPartyCookieUIDIfExists();

            // this is an optional supplement to the site's tracking status resource at:
            //     /.well-known/dnt
            // per Tracking Preference Expression
            
            //Tracking Preference Expression has been updated to require Tk: N rather than Tk: 1
            Common::sendHeader('Tk: N');
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

        $shouldIgnore = false;

        Piwik::postEvent('PrivacyManager.shouldIgnoreDnt', array(&$shouldIgnore));
        if($shouldIgnore) {
            Common::printDebug("DoNotTrack header ignored by Matomo because of a plugin");
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
}
