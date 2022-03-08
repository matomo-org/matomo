<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations;

use Piwik\ViewDataTable\Manager as ViewDataTableManager;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/JqplotDataGenerator.php';

/**
 * This plugin contains all core visualizations, such as the normal HTML table and
 * jqPlot graphs.
 */
class CoreVisualizations extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'UsersManager.deleteUser'                => 'deleteUser',
        );
    }

    public function deleteUser($userLogin)
    {
        ViewDataTableManager::clearUserViewDataTableParameters($userLogin);
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.less";
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.less";

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
        $translationKeys[] = 'General_EvolutionSummaryGeneric';
        $translationKeys[] = 'General_IncompletePeriod';
    }
}
