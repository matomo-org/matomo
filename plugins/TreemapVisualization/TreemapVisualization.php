<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package TreemapVisualization
 */

namespace Piwik\Plugins\TreemapVisualization;

/**
 * @see plugins/TreemapVisualization/Treemap.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/TreemapVisualization/Treemap.php';

/**
 * Plugin that contains the Treemap DataTable visualization.
 */
class TreemapVisualization extends \Piwik\Plugin
{
    /**
     * The list of Actions reports for whom the treemap should have a width of 100%.
     */
    private static $fullWidthActionsReports = array(
        'getPageUrls',
        'getEntryPageUrls',
        'getExitPageUrls',
        'getEntryPageTitles',
        'getExitPageTitles',
        'getPageTitles',
        'getOutlinks',
        'getDownloads',
    );

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles'            => 'getStylesheetFiles',
            'AssetManager.getJsFiles'             => 'getJsFiles',
            'DataTableVisualization.getAvailable' => 'getAvailableDataTableVisualizations',
            'ViewDataTable.configureReportView'   => 'configureReportViewForActions'
        );
    }

    public function getAvailableDataTableVisualizations(&$visualizations)
    {
        $visualizations[] = 'Piwik\\Plugins\\TreemapVisualization\\Treemap';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/TreemapVisualization/stylesheets/treemap.less';
        $stylesheets[] = 'plugins/TreemapVisualization/stylesheets/treemapColors.less';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'libs/Jit/jit-2.0.1-yc.js';
        $jsFiles[] = 'plugins/TreemapVisualization/javascripts/treemapViz.js';
    }

    public function configureReportViewForActions($view)
    {
        list($module, $method) = explode('.', $view->getReportApiMethod());

        // make sure treemap is shown on actions reports
        if ($module === 'Actions') {
            $view->show_all_views_icons = true;
            $view->show_bar_chart = false;
            $view->show_pie_chart = false;
            $view->show_tag_cloud = false;

            // for some actions reports, use all available space
            if (in_array($method, self::$fullWidthActionsReports)
                && $view->getViewDataTableId() == Treemap::ID
            ) {
                $view->datatable_css_class = 'infoviz-treemap-full-width';
                $view->visualization_properties->max_graph_elements = 50;
            }
        } else {
            if ($view->getViewDataTableId() == Treemap::ID) {
                $view->visualization_properties->max_graph_elements = max(10, $view->visualization_properties->max_graph_elements);
            }
        }
    }
}