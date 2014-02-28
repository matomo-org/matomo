<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\WidgetsList;

/**
 */
class Insights extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'WidgetsList.addWidgets' => 'addWidgets',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'ViewDataTable.addViewDataTable' => 'getAvailableVisualizations'
        );
    }

    public function getAvailableVisualizations(&$visualizations)
    {
        $visualizations[] = __NAMESPACE__ . '\\Visualizations\\Insight';
    }

    public function addWidgets()
    {
        WidgetsList::add('Insights_Category', 'Insights_OverviewWidgetTitle', 'Insights', 'getOverviewMoversAndShakers');
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Insights/javascripts/insightsDataTable.js";
    }

}
