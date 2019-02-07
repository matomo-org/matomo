<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

use Piwik\Common;
use Piwik\Db;

class CustomDirPlugin extends \Piwik\Plugin
{
    public function install()
    {
        // tracking requests will only succeed when plugin is executed in tracking mode because of not null :)
        $sql = 'ALTER TABLE ' . Common::prefixTable('log_visit') . ' ADD COLUMN custom_int BIGINT UNSIGNED NOT NULL;';
        Db::exec($sql);
    }

    public function uninstall()
    {
        $sql = 'ALTER TABLE ' . Common::prefixTable('log_visit') . ' DROP COLUMN custom_int;';
        Db::exec($sql);
    }

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
