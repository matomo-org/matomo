<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations;

use Piwik\ViewDataTable\Manager as ViewDataTableManager;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/JqplotDataGenerator.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/Cloud.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph.php';

/**
 * This plugin contains all core visualizations, such as the normal HTML table and
 * jqPlot graphs.
 */
class CoreVisualizations extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'ViewDataTable.addViewDataTable'         => 'getAvailableDataTableVisualizations',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'UsersManager.deleteUser'                => 'deleteUser'
        );
    }

    public function deleteUser($userLogin)
    {
        ViewDataTableManager::clearUserViewDataTableParameters($userLogin);
    }

    public function getAvailableDataTableVisualizations(&$visualizations)
    {
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\Sparkline';
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\HtmlTable';
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\HtmlTable\\AllColumns';
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\Cloud';
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\JqplotGraph\\Pie';
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\JqplotGraph\\Bar';
        $visualizations[] = 'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\JqplotGraph\\Evolution';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CoreVisualizations/stylesheets/dataTableVisualizations.less";
        $stylesheets[] = "plugins/CoreVisualizations/stylesheets/jqplot.css";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/CoreVisualizations/javascripts/seriesPicker.js";
        $jsFiles[] = "plugins/CoreVisualizations/javascripts/jqplot.js";
        $jsFiles[] = "plugins/CoreVisualizations/javascripts/jqplotBarGraph.js";
        $jsFiles[] = "plugins/CoreVisualizations/javascripts/jqplotPieGraph.js";
        $jsFiles[] = "plugins/CoreVisualizations/javascripts/jqplotEvolutionGraph.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_MetricsToPlot';
        $translationKeys[] = 'General_MetricToPlot';
        $translationKeys[] = 'General_RecordsToPlot';
        $translationKeys[] = 'General_SaveImageOnYourComputer';
        $translationKeys[] = 'General_ExportAsImage';
        $translationKeys[] = 'General_NoDataForGraph';
    }
}
