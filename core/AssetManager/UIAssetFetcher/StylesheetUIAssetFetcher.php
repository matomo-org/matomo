<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager\UIAssetFetcher;

use Piwik\AssetManager\UIAssetFetcher;
use Piwik\Piwik;

class StylesheetUIAssetFetcher extends UIAssetFetcher
{
    protected function getPriorityOrder()
    {
        return array(
            'libs/',
            'plugins/CoreHome/stylesheets/color_manager.css', // must be before other Piwik stylesheets
            'plugins/Zeitgeist/stylesheets/base.less',
            'plugins/Zeitgeist/stylesheets/',
            'plugins/',
            'plugins/Dashboard/stylesheets/dashboard.less',
            'tests/',
        );
    }

    protected function retrieveFileLocations()
    {
        /**
         * Triggered when gathering the list of all stylesheets (CSS and LESS) needed by
         * Piwik and its plugins.
         *
         * Plugins that have stylesheets should use this event to make those stylesheets
         * load.
         *
         * Stylesheets should be placed within a **stylesheets** subdirectory in your plugin's
         * root directory.
         *
         * **Example**
         *
         *     public function getStylesheetFiles(&$stylesheets)
         *     {
         *         $stylesheets[] = "plugins/MyPlugin/stylesheets/myfile.less";
         *         $stylesheets[] = "plugins/MyPlugin/stylesheets/myotherfile.css";
         *     }
         *
         * @param string[] &$stylesheets The list of stylesheet paths.
         */
        Piwik::postEvent('AssetManager.getStylesheetFiles', array(&$this->fileLocations));

        $this->addThemeFiles();
    }

    protected function addThemeFiles()
    {
        $themeStylesheet = $this->getTheme()->getStylesheet();

        if($themeStylesheet) {
            $this->fileLocations[] = $themeStylesheet;
        }
    }
}
