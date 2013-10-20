<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\ViewDataTable;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

/**
 * ViewDataTable Manager.
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class Manager
{

    /**
     * Returns the viewDataTable IDs of a visualization's class lineage.
     *
     * @see self::getVisualizationClassLineage
     *
     * @param string $klass The visualization class.
     *
     * @return array
     */
    public static function getIdsWithInheritance($klass)
    {
        $klasses = Common::getClassLineage($klass);

        $result = array();
        foreach ($klasses as $klass) {
            try {
                $result[] = $klass::getViewDataTableId();
            } catch (\Exception $e) {
                // in case $klass did not define an id: eg Plugin\ViewDataTable
                continue;
            }
        }

        return $result;
    }

    /**
     * Returns an array mapping viewDataTable IDs with information necessary for adding the
     * viewDataTables to the footer of DataTable views.
     *
     * @param array $viewDataTables An array mapping viewDataTable IDs w/ their associated classes.
     * @return array
     */
    public static function getViewDataTableInfoFor($viewDataTables)
    {
        $result = array();

        foreach ($viewDataTables as $vizId => $vizClass) {
            $result[$vizId] = array('table_icon' => $vizClass::FOOTER_ICON, 'title' => $vizClass::FOOTER_ICON_TITLE);
        }

        return $result;
    }

    /**
     * Returns all registered visualization classes. Uses the 'Visualization.getAvailable'
     * event to retrieve visualizations.
     *
     * @return array Array mapping visualization IDs with their associated visualization classes.
     * @throws \Exception If a visualization class does not exist or if a duplicate visualization ID
     *                   is found.
     * @return array
     */
    public static function getAvailableViewDataTables()
    {
        /** @var string[] $visualizations */
        $visualizations = array();

        /**
         * This event is used to gather all available DataTable visualizations. Callbacks should add visualization
         * class names to the incoming array.
         */
        Piwik::postEvent('ViewDataTable.addViewDataTable', array(&$visualizations));

        $result = array();

        foreach ($visualizations as $viz) {
            if (!class_exists($viz)) {
                throw new \Exception("Invalid visualization class '$viz' found in Visualization.getAvailableVisualizations.");
            }

            if (!is_subclass_of($viz, '\\Piwik\\Plugin\\ViewDataTable')) {
                throw new \Exception("ViewDataTable class '$viz' does not extend Plugin/ViewDataTable");
            }

            $vizId = $viz::getViewDataTableId();

            if (isset($result[$vizId])) {
                throw new \Exception("ViewDataTable ID '$vizId' is already in use!");
            }

            $result[$vizId] = $viz;
        }

        return $result;
    }

    /**
     * Returns all available visualizations that are not part of the CoreVisualizations plugin.
     *
     * @return array Array mapping visualization IDs with their associated visualization classes.
     */
    public static function getNonCoreViewDataTables()
    {
        $result = array();

        foreach (static::getAvailableViewDataTables() as $vizId => $vizClass) {
            if (false === strpos($vizClass, 'Piwik\\Plugins\\CoreVisualizations')
                && false === strpos($vizClass, 'Piwik\\Plugins\\Goals\\Visualizations\\Goals')) {
                $result[$vizId] = $vizClass;
            }
        }

        return $result;
    }

    public static function configureFooterIcons(&$result, ViewDataTable $view)
    {
        // add normal view icons (eg, normal table, all columns, goals)
        $normalViewIcons = array(
            'class'   => 'tableAllColumnsSwitch',
            'buttons' => array(),
        );

        if ($view->config->show_table) {
            $normalViewIcons['buttons'][] = array(
                'id'    => 'table',
                'title' => Piwik::translate('General_DisplaySimpleTable'),
                'icon'  => 'plugins/Zeitgeist/images/table.png',
            );
        }

        if ($view->config->show_table_all_columns) {
            $normalViewIcons['buttons'][] = array(
                'id'    => 'tableAllColumns',
                'title' => Piwik::translate('General_DisplayTableWithMoreMetrics'),
                'icon'  => 'plugins/Zeitgeist/images/table_more.png'
            );
        }

        if ($view->config->show_goals) {
            if (Common::getRequestVar('idGoal', false) == 'ecommerceOrder') {
                $icon = 'plugins/Zeitgeist/images/ecommerceOrder.gif';
            } else {
                $icon = 'plugins/Zeitgeist/images/goal.png';
            }

            $normalViewIcons['buttons'][] = array(
                'id'    => 'tableGoals',
                'title' => Piwik::translate('General_DisplayTableWithGoalMetrics'),
                'icon'  => $icon
            );
        }

        if ($view->config->show_ecommerce) {
            $normalViewIcons['buttons'][] = array(
                'id'    => 'ecommerceOrder',
                'title' => Piwik::translate('General_EcommerceOrders'),
                'icon'  => 'plugins/Zeitgeist/images/ecommerceOrder.gif',
                'text'  => Piwik::translate('General_EcommerceOrders')
            );

            $normalViewIcons['buttons'][] = array(
                'id'    => 'ecommerceAbandonedCart',
                'title' => Piwik::translate('General_AbandonedCarts'),
                'icon'  => 'plugins/Zeitgeist/images/ecommerceAbandonedCart.gif',
                'text'  => Piwik::translate('General_AbandonedCarts')
            );
        }

        if (!empty($normalViewIcons['buttons'])) {
            $result[] = $normalViewIcons;
        }

        // add graph views
        $graphViewIcons = array(
            'class'   => 'tableGraphViews tableGraphCollapsed',
            'buttons' => array(),
        );

        if ($view->config->show_all_views_icons) {
            if ($view->config->show_bar_chart) {
                $graphViewIcons['buttons'][] = array(
                    'id'    => 'graphVerticalBar',
                    'title' => Piwik::translate('General_VBarGraph'),
                    'icon'  => 'plugins/Zeitgeist/images/chart_bar.png'
                );
            }

            if ($view->config->show_pie_chart) {
                $graphViewIcons['buttons'][] = array(
                    'id'    => 'graphPie',
                    'title' => Piwik::translate('General_Piechart'),
                    'icon'  => 'plugins/Zeitgeist/images/chart_pie.png'
                );
            }

            if ($view->config->show_tag_cloud) {
                $graphViewIcons['buttons'][] = array(
                    'id'    => 'cloud',
                    'title' => Piwik::translate('General_TagCloud'),
                    'icon'  => 'plugins/Zeitgeist/images/tagcloud.png'
                );
            }

            if ($view->config->show_non_core_visualizations) {
                $nonCoreVisualizations    = static::getNonCoreViewDataTables();
                $nonCoreVisualizationInfo = static::getViewDataTableInfoFor($nonCoreVisualizations);

                foreach ($nonCoreVisualizationInfo as $format => $info) {
                    $graphViewIcons['buttons'][] = array(
                        'id'    => $format,
                        'title' => Piwik::translate($info['title']),
                        'icon'  => $info['table_icon']
                    );
                }
            }
        }

        if (!empty($graphViewIcons['buttons'])) {
            $result[] = $graphViewIcons;
        }
    }
}