<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Tracker\GoalManager;
use Piwik\Category\Subcategory;

/**
 *
 */
class Goals extends \Piwik\Plugin
{
    public static function getReportsWithGoalMetrics()
    {
        $dimensions = self::getAllReportsWithGoalMetrics();

        $dimensionsByGroup = array();
        foreach ($dimensions as $dimension) {
            $group = $dimension['category'];
            // move "Custom Variables" report to the "Goals/Sales by User attribute" category
            if ($dimension['module'] === 'CustomVariables'
                || $dimension['action'] == 'getVisitInformationPerServerTime') {
                $group = 'VisitsSummary_VisitsSummary';
            }
            unset($dimension['category']);
            $dimensionsByGroup[$group][] = $dimension;
        }

        return $dimensionsByGroup;
    }

    public static function getGoalColumns($idGoal)
    {
        $columns = array(
            'nb_conversions',
            'nb_visits_converted',
            'revenue',
        );
        if ($idGoal === false) {
            return $columns;
        }
        // Orders
        if ($idGoal === GoalManager::IDGOAL_ORDER) {
            $columns = array_merge($columns, array(
                                                  'revenue_subtotal',
                                                  'revenue_tax',
                                                  'revenue_shipping',
                                                  'revenue_discount',
                                             ));
        }
        // Abandoned carts & orders
        if ($idGoal <= GoalManager::IDGOAL_ORDER) {
            $columns[] = 'items';
        }
        return $columns;
    }

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Tracker.Cache.getSiteAttributes'        => 'fetchGoalsFromDb',
            'API.getReportMetadata.end'              => 'getReportMetadataEnd',
            'SitesManager.deleteSite.end'            => 'deleteSiteGoals',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Metrics.getDefaultMetricTranslations'   => 'addMetricTranslations',
            'Category.addSubcategories' => 'addSubcategories'
        );
        return $hooks;
    }

    public function addSubcategories(&$subcategories)
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');

        if (!$idSite) {
            // fallback for eg API.getReportMetadata which uses idSites
            $idSite = Common::getRequestVar('idSites', 0, 'int');

            if (!$idSite) {
                return;
            }
        }

        $goals = API::getInstance()->getGoals($idSite);

        $order = 900;
        foreach ($goals as $goal) {
            $category = new Subcategory();
            $category->setName($goal['name']);
            $category->setCategoryId('Goals_Goals');
            $category->setId($goal['idgoal']);
            $category->setOrder($order++);
            $subcategories[] = $category;
        }
    }


    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'orders'            => 'General_EcommerceOrders',
            'ecommerce_revenue' => 'General_ProductRevenue',
            'revenue_per_visit' => 'General_ColumnValuePerVisit',
            'quantity'          => 'General_Quantity',
            'avg_price'         => 'General_AveragePrice',
            'avg_quantity'      => 'General_AverageQuantity',
            'revenue_subtotal'  => 'General_Subtotal',
            'revenue_tax'       => 'General_Tax',
            'revenue_shipping'  => 'General_Shipping',
            'revenue_discount'  => 'General_Discount',
            'avg_order_revenue' => 'General_AverageOrderValue'
        );

        $metrics = array_map(array('\\Piwik\\Piwik', 'translate'), $metrics);

        $translations = array_merge($translations, $metrics);
    }

    /**
     * Delete goals recorded for this site
     */
    public function deleteSiteGoals($idSite)
    {
        $model = new Model();
        $model->deleteGoalsForSite($idSite);
    }

    /**
     * Returns the Metadata for the Goals plugin API.
     * The API returns general Goal metrics: conv, conv rate and revenue globally
     * and for each goal.
     *
     * Also, this will update metadata of all other reports that have Goal segmentation
     */
    public function getReportMetadataEnd(&$reports, $info)
    {
        // Processed in AddColumnsProcessedMetricsGoal
        // These metrics will also be available for some reports, for each goal
        // Example: Conversion rate for Goal 2 for the keyword 'piwik'
        $goalProcessedMetrics = array(
            'revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit'),
        );

        $goalMetrics = array(
            'nb_conversions'  => Piwik::translate('Goals_ColumnConversions'),
            'conversion_rate' => Piwik::translate('General_ColumnConversionRate'),
            'revenue'         => Piwik::translate('General_ColumnRevenue')
        );

        $reportsWithGoals = self::getAllReportsWithGoalMetrics();

        foreach ($reportsWithGoals as $reportWithGoals) {
            // Select this report from the API metadata array
            // and add the Goal metrics to it
            foreach ($reports as &$apiReportToUpdate) {
                if ($apiReportToUpdate['module'] == $reportWithGoals['module']
                    && $apiReportToUpdate['action'] == $reportWithGoals['action']
                    && empty($apiReportToUpdate['parameters'])) {
                    $apiReportToUpdate['metricsGoal'] = $goalMetrics;
                    $apiReportToUpdate['processedMetricsGoal'] = $goalProcessedMetrics;
                    break;
                }
            }
        }
    }

    private static function getAllReportsWithGoalMetrics()
    {
        $reportsWithGoals = array();

        $reports = new ReportsProvider();

        foreach ($reports->getAllReports() as $report) {
            if ($report->hasGoalMetrics()) {
                $reportsWithGoals[] = array(
                    'category' => $report->getCategoryId(),
                    'name'     => $report->getName(),
                    'module'   => $report->getModule(),
                    'action'   => $report->getAction(),
                    'parameters' => $report->getParameters()
                );
            }
        }

        $reportsWithGoals[] = array('category' => 'General_Visit',
            'name'     => Piwik::translate('Goals_VisitsUntilConv'),
            'module'   => 'Goals',
            'action'   => 'getVisitsUntilConversion',
            'viewDataTable' => 'table',
        );
        $reportsWithGoals[] = array('category' => 'General_Visit',
            'name'     => Piwik::translate('Goals_DaysToConv'),
            'module'   => 'Goals',
            'action'   => 'getDaysToConversion',
            'viewDataTable' => 'table',
        );

        /**
         * Triggered when gathering all reports that contain Goal metrics. The list of reports
         * will be displayed on the left column of the bottom of every _Goals_ page.
         *
         * If plugins define reports that contain goal metrics (such as **conversions** or **revenue**),
         * they can use this event to make sure their reports can be viewed on Goals pages.
         *
         * **Example**
         *
         *     public function getReportsWithGoalMetrics(&$reports)
         *     {
         *         $reports[] = array(
         *             'category' => Piwik::translate('MyPlugin_myReportCategory'),
         *             'name' => Piwik::translate('MyPlugin_myReportDimension'),
         *             'module' => 'MyPlugin',
         *             'action' => 'getMyReport'
         *         );
         *     }
         *
         * @param array &$reportsWithGoals The list of arrays describing reports that have Goal metrics.
         *                                 Each element of this array must be an array with the following
         *                                 properties:
         *
         *                                 - **category**: The report category. This should be a translated string.
         *                                 - **name**: The report's translated name.
         *                                 - **module**: The plugin the report is in, eg, `'UserCountry'`.
         *                                 - **action**: The API method of the report, eg, `'getCountry'`.
         * @ignore
         * @deprecated since 2.5.0
         */
        Piwik::postEvent('Goals.getReportsWithGoalMetrics', array(&$reportsWithGoals));

        return $reportsWithGoals;
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Goals/angularjs/common/directives/goal-page-link.js";
        $jsFiles[] = "plugins/Goals/angularjs/manage-goals/manage-goals.controller.js";
        $jsFiles[] = "plugins/Goals/angularjs/manage-goals/manage-goals.directive.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Goals/stylesheets/goals.css";
    }

    public function fetchGoalsFromDb(&$array, $idSite)
    {
        // add the 'goal' entry in the website array
        $array['goals'] = API::getInstance()->getGoals($idSite);
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Goals_AddGoal';
        $translationKeys[] = 'Goals_AddNewGoal';
        $translationKeys[] = 'Goals_UpdateGoal';
        $translationKeys[] = 'Goals_DeleteGoalConfirm';
        $translationKeys[] = 'Goals_UpdateGoal';
        $translationKeys[] = 'Goals_DeleteGoalConfirm';
        $translationKeys[] = 'Goals_Ecommerce';
        $translationKeys[] = 'Goals_Optional';
    }
}
