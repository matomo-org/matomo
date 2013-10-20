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
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Plugins\Goals\Visualizations\Goals;

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
            $normalViewIcons['buttons'][] = static::getFooterIconFor(HtmlTable::ID);
        }

        if ($view->config->show_table_all_columns) {
            $normalViewIcons['buttons'][] = static::getFooterIconFor(HtmlTable\AllColumns::ID);
        }

        if ($view->config->show_goals) {
            $goalButton = static::getFooterIconFor(Goals::ID);
            if (Common::getRequestVar('idGoal', false) == 'ecommerceOrder') {
                $goalButton['icon'] = 'plugins/Zeitgeist/images/ecommerceOrder.gif';
            }

            $normalViewIcons['buttons'][] = $goalButton;
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
                $graphViewIcons['buttons'][] = static::getFooterIconFor(Bar::ID);
            }

            if ($view->config->show_pie_chart) {
                $graphViewIcons['buttons'][] = static::getFooterIconFor(Pie::ID);
            }

            if ($view->config->show_tag_cloud) {
                $graphViewIcons['buttons'][] = static::getFooterIconFor(Cloud::ID);
            }

            $nonCoreVisualizations = static::getNonCoreViewDataTables();

            foreach ($nonCoreVisualizations as $id => $klass) {
                $graphViewIcons['buttons'][] = static::getFooterIconFor($id);
            }
        }

        if (!empty($graphViewIcons['buttons'])) {
            $result[] = $graphViewIcons;
        }
    }

    /**
     * Returns an array with information necessary for adding the viewDataTable to the footer.
     *
     * @param string $viewDataTableId
     *
     * @return array
     */
    private static function getFooterIconFor($viewDataTableId)
    {
        $tables = static::getAvailableViewDataTables();

        $klass = $tables[$viewDataTableId];

        return array(
            'id'    => $klass::getViewDataTableId(),
            'title' => Piwik::translate($klass::FOOTER_ICON_TITLE),
            'icon'  => $klass::FOOTER_ICON,
        );
    }
}