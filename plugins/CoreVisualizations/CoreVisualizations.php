<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreVisualizations\FeatureFlags\PlotLinesTweaks;
use Piwik\Plugins\CoreVisualizations\FeatureFlags\SparklinesRedesign;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
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
            'Template.bodyClass'                     => 'addBodyClass',
        );
    }

    public function deleteUser($userLogin)
    {
        ViewDataTableManager::clearUserViewDataTableParameters($userLogin);
    }

    public function addBodyClass(&$out, $type)
    {
        $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);

        // The sparklines redesign refreshes sparkline styling app-wide (gated by the flag),
        // so it is not limited to the dashboard/widgetized surfaces the plot-line tweak below
        // targets - sparklines also appear on other page types (e.g. admin).
        if ($featureFlagManager->isFeatureActive(SparklinesRedesign::class)) {
            $out .= ' sparklines-redesign-enabled';
        }

        if (!in_array($type, ['dashboard', 'widgetized'], true)) {
            return;
        }

        if ($featureFlagManager->isFeatureActive(PlotLinesTweaks::class)) {
            $out .= ' plotlines-tweaks-enabled';
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionBadge.less";
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.less";
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.less";
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPicker.less";
        $stylesheets[] = "plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.less";

        $stylesheets[] = "plugins/CoreVisualizations/stylesheets/dataTableVisualizations.less";
        $stylesheets[] = "plugins/CoreVisualizations/stylesheets/jqplot.less";
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
        $translationKeys[] = 'General_ChooseMetrics';
        $translationKeys[] = 'General_RecordsToPlot';
        $translationKeys[] = 'General_SaveImageOnYourComputer';
        $translationKeys[] = 'General_ExportAsImage';
        $translationKeys[] = 'General_NoDataForGraph';
        $translationKeys[] = 'General_EvolutionSummaryGeneric';
        $translationKeys[] = 'General_IncompletePeriod';
        $translationKeys[] = 'General_InvalidatedPeriod';
    }
}
