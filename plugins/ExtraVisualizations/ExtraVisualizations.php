<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ExtraVisualizations
 */

use Piwik\Plugin;

require_once PIWIK_INCLUDE_PATH . '/plugins/ExtraVisualizations/Visualizations/Treemap.php';

/**
 * This plugin contains spme extra DataTable visualizations, such as Treemap.
 */
class Piwik_ExtraVisualizations extends Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles'              => 'getCssFiles',
            'AssetManager.getJsFiles'               => 'getJsFiles',
            'DataTableVisualization.getAvailable'   => 'getAvailableDataTableVisualizations',
        );
    }

    public function getAvailableDataTableVisualizations(&$visualizations)
    {
        $visualizations[] = "Piwik\\Plugins\\ExtraVisualizations\\Visualizations\\Treemap";
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/CoreVisualizations/stylesheets/dataTableVisualizations.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        //$jsFiles[] = "";
    }
}