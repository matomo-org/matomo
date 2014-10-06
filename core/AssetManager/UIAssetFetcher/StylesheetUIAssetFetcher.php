<?php
/**
 * Piwik - free/libre analytics platform
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
        $theme = $this->getTheme();
        $themeName = $theme->getThemeName();

        $order = array(
            'libs/',
            'plugins/CoreHome/stylesheets/color_manager.css', // must be before other Piwik stylesheets
            'plugins/Morpheus/stylesheets/base.less',
        );

        if ($themeName === 'Morpheus') {
            $order[] = 'plugins\/((?!Morpheus).)*\/';
        } else {
            $order[] = sprintf('plugins\/((?!(Morpheus)|(%s)).)*\/', $themeName);
        }

        $order = array_merge(
            $order,
            array(
                'plugins/Dashboard/stylesheets/dashboard.less',
                'tests/',
            )
        );

        return $order;
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
        $theme = $this->getTheme();
        if (!$theme) {
            return;
        }
        $themeStylesheet = $this->getTheme()->getStylesheet();

        if ($themeStylesheet) {
            $this->fileLocations[] = $themeStylesheet;
        }
    }
}
