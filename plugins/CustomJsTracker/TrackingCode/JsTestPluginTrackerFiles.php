<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomJsTracker\TrackingCode;

/**
 * Used for when running Piwik tracker tests. We simply include all custom tracker files there.
 */
class JsTestPluginTrackerFiles extends PluginTrackerFiles
{

    public function __construct()
    {
        parent::__construct();
        $this->ignoreMinified = true;
    }

    protected function getDirectoriesToLook()
    {
        $dirs = array();
        $trackerFiles = \_glob(PIWIK_DOCUMENT_ROOT . '/plugins/*/' . self::TRACKER_FILE);
        foreach ($trackerFiles as $trackerFile) {
            $pluginName = $this->getPluginNameFromFile($trackerFile);

            if ($pluginName === 'PrivacyManager') {
                continue; // ignore tracker.js of PrivacyManager, as it would disable Cookies
            }

            $dirs[$pluginName] = dirname($trackerFile) . '/';
        }
        return $dirs;
    }

    protected function getPluginNameFromFile($file)
    {
        $file = str_replace(array(PIWIK_DOCUMENT_ROOT . '/plugins/', self::TRACKER_FILE), '', $file);
        return trim($file, '/');
    }

    protected function isPluginActivated($pluginName)
    {
        return true;
    }

}
