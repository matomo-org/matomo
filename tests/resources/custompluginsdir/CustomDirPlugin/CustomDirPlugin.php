<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

use Piwik\Common;
use Piwik\Db;

class CustomDirPlugin extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CustomDirPlugin/stylesheets/test.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "tests/resources/custompluginsdir/javascripts/test.js";
    }

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
