<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

class CustomDirPlugin extends \Piwik\Plugin
{
    public function postLoad()
    {
        // we make sure auto loading works for these directories
        return new CustomClass();
    }

    public function isTrackerPlugin()
    {
        return true;
    }
}
