<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomPiwikJs\TrackingCode;

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

    protected function isPluginActivated($pluginName)
    {
        return true;
    }

}
