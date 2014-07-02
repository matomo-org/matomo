<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Site;
use Piwik\Tracker\GoalManager;
use Piwik\Translate;

/**
 *
 */
class Goals extends \Piwik\Plugin
{
    public function getInformation()
    {
        $suffix = Piwik::translate('SitesManager_PiwikOffersEcommerceAnalytics',
            array('<a href="http://piwik.org/docs/ecommerce-analytics/" target="_blank">', '</a>'));
        $info = parent::getInformation();
        $info['description'] .= ' ' . $suffix;
        return $info;
    }

    protected $ecommerceReports = array(
        array('Goals_ProductSKU', 'Goals', 'getItemsSku'),
        array('Goals_ProductName', 'Goals', 'getItemsName'),
        array('Goals_ProductCategory', 'Goals', 'getItemsCategory')
    );

    static public function getReportsWithGoalMetrics()
    {
        $dimensions = self::getAllReportsWithGoalMetrics();

        $dimensionsByGroup = array();
        foreach ($dimensions as $dimension) {
            $group = $dimension['category'];
            unset($dimension['category']);
            $dimensionsByGroup[$group][] = $dimension;
        }

        uksort($dimensionsByGroup, array('self', 'sortGoalDimensionsByModule'));
        return $dimensionsByGroup;
    }

    public function getEcommerceReports()
    {
        return $this->ecommerceReports;
    }

    public static function sortGoalDimensionsByModule($a, $b)
    {
        $order = array(
            Piwik::translate('Referrers_Referrers'),
            Piwik::translate('General_Visit'),
            Piwik::translate('VisitTime_ColumnServerTime'),
        );
        $orderA = array_search($a, $order);
        $orderB = array_search($b, $order);
        return $orderA > $orderB;
    }

    static public function getGoalColumns($idGoal)
    {
        $columns = array(
            'nb_conversions',
            'nb_visits_converted',
            'conversion_rate',
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
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Tracker.Cache.getSiteAttributes'        => 'fetchGoalsFromDb',
            'API.getReportMetadata.end'              => 'getReportMetadata',
            'API.getSegmentDimensionMetadata'        => 'getSegmentsMetadata',
            'SitesManager.deleteSite.end'            => 'deleteSiteGoals',
            'Goals.getReportsWithGoalMetrics'        => 'getActualReportsWithGoalMetrics',
            'ViewDataTable.configure'                => 'configureViewDataTable',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'ViewDataTable.addViewDataTable'         => 'getAvailableDataTableVisualizations'
        );
        return $hooks;
    }

    public function getAvailableDataTableVisualizations(&$visualizations)
    {
        $visualizations[] = 'Piwik\\Plugins\\Goals\\Visualizations\\Goals';
    }

    /**
     * Delete goals recorded for this site
     */
    function deleteSiteGoals($idSite)
    {
        Db::query("DELETE FROM " . Common::prefixTable('goal') . " WHERE idsite = ? ", array($idSite));
    }

    /**
     * Returns the Metadata for the Goals plugin API.
     * The API returns general Goal metrics: conv, conv rate and revenue globally
     * and for each goal.
     *
     * Also, this will update metadata of all other reports that have Goal segmentation
     */
    public function getReportMetadata(&$reports, $info)
    {
        $idSites = $info['idSites'];

        // Processed in AddColumnsProcessedMetricsGoal
        // These metrics will also be available for some reports, for each goal
        // Example: Conversion rate for Goal 2 for the keyword 'piwik'
        $goalProcessedMetrics = array(
            'revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit'),
        );

        $goalMetrics = array(
            'nb_conversions'      => Piwik::translate('Goals_ColumnConversions'),
            'nb_visits_converted' => Piwik::translate('General_ColumnVisitsWithConversions'),
            'conversion_rate'     => Piwik::translate('General_ColumnConversionRate'),
            'revenue'             => Piwik::translate('General_ColumnRevenue')
        );

        $conversionReportMetrics = array(
            'nb_conversions' => Piwik::translate('Goals_ColumnConversions')
        );

        // General Goal metrics: conversions, conv rate, revenue
        $goalsCategory = Piwik::translate('Goals_Goals');
        $reports[] = array(
            'category'         => $goalsCategory,
            'name'             => Piwik::translate('Goals_Goals'),
            'module'           => 'Goals',
            'action'           => 'get',
            'metrics'          => $goalMetrics,
            'processedMetrics' => array(),
            'order'            => 1
        );

        // If only one website is selected, we add the Goal metrics
        if (count($idSites) == 1) {
            $idSite = reset($idSites);
            $goals = API::getInstance()->getGoals($idSite);

            // Add overall visits to conversion report
            $reports[] = array(
                'category'          => $goalsCategory,
                'name'              => Piwik::translate('Goals_VisitsUntilConv'),
                'module'            => 'Goals',
                'action'            => 'getVisitsUntilConversion',
                'dimension'         => Piwik::translate('Goals_VisitsUntilConv'),
                'constantRowsCount' => true,
                'parameters'        => array(),
                'metrics'           => $conversionReportMetrics,
                'order'             => 5
            );

            // Add overall days to conversion report
            $reports[] = array(
                'category'          => $goalsCategory,
                'name'              => Piwik::translate('Goals_DaysToConv'),
                'module'            => 'Goals',
                'action'            => 'getDaysToConversion',
                'dimension'         => Piwik::translate('Goals_DaysToConv'),
                'constantRowsCount' => true,
                'parameters'        => array(),
                'metrics'           => $conversionReportMetrics,
                'order'             => 10
            );

            foreach ($goals as $goal) {
                // Add the general Goal metrics: ie. total Goal conversions,
                // Goal conv rate or Goal total revenue.
                // This API call requires a custom parameter
                $goal['name'] = Common::sanitizeInputValue($goal['name']);
                $reports[] = array(
                    'category'         => $goalsCategory,
                    'name'             => Piwik::translate('Goals_GoalX', $goal['name']),
                    'module'           => 'Goals',
                    'action'           => 'get',
                    'parameters'       => array('idGoal' => $goal['idgoal']),
                    'metrics'          => $goalMetrics,
                    'processedMetrics' => false,
                    'order'            => 50 + $goal['idgoal'] * 3
                );

                // Add visits to conversion report
                $reports[] = array(
                    'category'          => $goalsCategory,
                    'name'              => $goal['name'] . ' - ' . Piwik::translate('Goals_VisitsUntilConv'),
                    'module'            => 'Goals',
                    'action'            => 'getVisitsUntilConversion',
                    'dimension'         => Piwik::translate('Goals_VisitsUntilConv'),
                    'constantRowsCount' => true,
                    'parameters'        => array('idGoal' => $goal['idgoal']),
                    'metrics'           => $conversionReportMetrics,
                    'order'             => 51 + $goal['idgoal'] * 3
                );

                // Add days to conversion report
                $reports[] = array(
                    'category'          => $goalsCategory,
                    'name'              => $goal['name'] . ' - ' . Piwik::translate('Goals_DaysToConv'),
                    'module'            => 'Goals',
                    'action'            => 'getDaysToConversion',
                    'dimension'         => Piwik::translate('Goals_DaysToConv'),
                    'constantRowsCount' => true,
                    'parameters'        => array('idGoal' => $goal['idgoal']),
                    'metrics'           => $conversionReportMetrics,
                    'order'             => 52 + $goal['idgoal'] * 3
                );
            }

            $site = new Site($idSite);
            if ($site->isEcommerceEnabled()) {
                $category = Piwik::translate('Goals_Ecommerce');
                $ecommerceMetrics = array_merge($goalMetrics, array(
                                                                   'revenue_subtotal'  => Piwik::translate('General_Subtotal'),
                                                                   'revenue_tax'       => Piwik::translate('General_Tax'),
                                                                   'revenue_shipping'  => Piwik::translate('General_Shipping'),
                                                                   'revenue_discount'  => Piwik::translate('General_Discount'),
                                                                   'items'             => Piwik::translate('General_PurchasedProducts'),
                                                                   'avg_order_revenue' => Piwik::translate('General_AverageOrderValue')
                                                              ));
                $ecommerceMetrics['nb_conversions'] = Piwik::translate('General_EcommerceOrders');

                // General Ecommerce metrics
                $reports[] = array(
                    'category'         => $category,
                    'name'             => Piwik::translate('General_EcommerceOrders'),
                    'module'           => 'Goals',
                    'action'           => 'get',
                    'parameters'       => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER),
                    'metrics'          => $ecommerceMetrics,
                    'processedMetrics' => false,
                    'order'            => 10
                );
                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik::translate('General_EcommerceOrders') . ' - ' . Piwik::translate('Goals_VisitsUntilConv'),
                    'module'            => 'Goals',
                    'action'            => 'getVisitsUntilConversion',
                    'dimension'         => Piwik::translate('Goals_VisitsUntilConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER),
                    'order'             => 11
                );
                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik::translate('General_EcommerceOrders') . ' - ' . Piwik::translate('Goals_DaysToConv'),
                    'module'            => 'Goals',
                    'action'            => 'getDaysToConversion',
                    'dimension'         => Piwik::translate('Goals_DaysToConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER),
                    'order'             => 12
                );

                // Abandoned cart general metrics
                $abandonedCartMetrics = $goalMetrics;
                $abandonedCartMetrics['nb_conversions'] = Piwik::translate('General_AbandonedCarts');
                $abandonedCartMetrics['revenue'] = Piwik::translate('Goals_LeftInCart', Piwik::translate('General_ColumnRevenue'));
                $abandonedCartMetrics['items'] = Piwik::translate('Goals_LeftInCart', Piwik::translate('Goals_Products'));
                unset($abandonedCartMetrics['nb_visits_converted']);

                // Abandoned Cart metrics
                $reports[] = array(
                    'category'         => $category,
                    'name'             => Piwik::translate('General_AbandonedCarts'),
                    'module'           => 'Goals',
                    'action'           => 'get',
                    'parameters'       => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART),
                    'metrics'          => $abandonedCartMetrics,
                    'processedMetrics' => false,
                    'order'            => 15
                );

                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik::translate('General_AbandonedCarts') . ' - ' . Piwik::translate('Goals_VisitsUntilConv'),
                    'module'            => 'Goals',
                    'action'            => 'getVisitsUntilConversion',
                    'dimension'         => Piwik::translate('Goals_VisitsUntilConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART),
                    'order'             => 20
                );
                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik::translate('General_AbandonedCarts') . ' - ' . Piwik::translate('Goals_DaysToConv'),
                    'module'            => 'Goals',
                    'action'            => 'getDaysToConversion',
                    'dimension'         => Piwik::translate('Goals_DaysToConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART),
                    'order'             => 25
                );

                // Product reports metadata
                $productColumns = self::getProductReportColumns();
                foreach ($this->ecommerceReports as $i => $ecommerceReport) {
                    $reports[] = array(
                        'category'         => $category,
                        'name'             => Piwik::translate($ecommerceReport[0]),
                        'module'           => 'Goals',
                        'action'           => $ecommerceReport[2],
                        'dimension'        => Piwik::translate($ecommerceReport[0]),
                        'metrics'          => $productColumns,
                        'processedMetrics' => false,
                        'order'            => 30 + $i
                    );
                }
            }
        }

        unset($goalMetrics['nb_visits_converted']);

        $reportsWithGoals = self::getAllReportsWithGoalMetrics();

        foreach ($reportsWithGoals as $reportWithGoals) {
            // Select this report from the API metadata array
            // and add the Goal metrics to it
            foreach ($reports as &$apiReportToUpdate) {
                if ($apiReportToUpdate['module'] == $reportWithGoals['module']
                    && $apiReportToUpdate['action'] == $reportWithGoals['action']
                ) {
                    $apiReportToUpdate['metricsGoal'] = $goalMetrics;
                    $apiReportToUpdate['processedMetricsGoal'] = $goalProcessedMetrics;
                    break;
                }
            }
        }
    }

    static private function getAllReportsWithGoalMetrics()
    {
        $reportsWithGoals = array();

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
         */
        Piwik::postEvent('Goals.getReportsWithGoalMetrics', array(&$reportsWithGoals));

        return $reportsWithGoals;
    }

    static public function getProductReportColumns()
    {
        return array(
            'revenue'         => Piwik::translate('General_ProductRevenue'),
            'quantity'        => Piwik::translate('General_Quantity'),
            'orders'          => Piwik::translate('General_UniquePurchases'),
            'avg_price'       => Piwik::translate('General_AveragePrice'),
            'avg_quantity'    => Piwik::translate('General_AverageQuantity'),
            'nb_visits'       => Piwik::translate('General_ColumnNbVisits'),
            'conversion_rate' => Piwik::translate('General_ProductConversionRate'),
        );
    }

    /**
     * This function executes when the 'Goals.getReportsWithGoalMetrics' event fires. It
     * adds the 'visits to conversion' report metadata to the list of goal reports so
     * this report will be displayed.
     */
    public function getActualReportsWithGoalMetrics(&$dimensions)
    {
        $reportWithGoalMetrics = array(
            array('category' => Piwik::translate('General_Visit'),
                  'name'     => Piwik::translate('Goals_VisitsUntilConv'),
                  'module'   => 'Goals',
                  'action'   => 'getVisitsUntilConversion',
                  'viewDataTable' => 'table',
            ),
            array('category' => Piwik::translate('General_Visit'),
                  'name'     => Piwik::translate('Goals_DaysToConv'),
                  'module'   => 'Goals',
                  'action'   => 'getDaysToConversion',
                  'viewDataTable' => 'table',
            )
        );
        $dimensions = array_merge($dimensions, $reportWithGoalMetrics);
    }

    public function getSegmentsMetadata(&$segments)
    {
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => 'General_VisitConvertedGoalId',
            'segment'        => 'visitConvertedGoalId',
            'sqlSegment'     => 'log_conversion.idgoal',
            'acceptedValues' => '1, 2, 3, etc.',
        );
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Goals/javascripts/goalsForm.js";
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

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'Goals.getItemsSku':
                $this->configureViewForGetItemsSku($view);
                break;
            case 'Goals.getItemsName':
                $this->configureViewForGetItemsName($view);
                break;
            case 'Goals.getItemsCategory':
                $this->configureViewForGetItemsCategory($view);
                break;
            case 'Goals.getVisitsUntilConversion':
                $this->configureViewForGetVisitsUntilConversion($view);
                break;
            case 'Goals.getDaysToConversion':
                $this->configureViewForGetDaysToConversion($view);
                break;
        }
    }

    private function configureViewForGetItemsSku(ViewDataTable $view)
    {
        return $this->configureViewForItemsReport($view, Piwik::translate('Goals_ProductSKU'));
    }

    private function configureViewForGetItemsName(ViewDataTable $view)
    {
        return $this->configureViewForItemsReport($view, Piwik::translate('Goals_ProductName'));
    }

    private function configureViewForGetItemsCategory(ViewDataTable $view)
    {
        return $this->configureViewForItemsReport($view, Piwik::translate('Goals_ProductCategory'));
    }

    private function configureViewForGetVisitsUntilConversion(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display      = array('label', 'nb_conversions');
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_all_views_icons  = false;

        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit       = count(Archiver::$visitCountRanges);

        $view->config->addTranslations(array(
            'label'          => Piwik::translate('Goals_VisitsUntilConv'),
            'nb_conversions' => Piwik::translate('Goals_ColumnConversions'),
        ));
    }

    private function configureViewForGetDaysToConversion(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns  = false;
        $view->config->show_all_views_icons  = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->columns_to_display      = array('label', 'nb_conversions');

        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit       = count(Archiver::$daysToConvRanges);

        $view->config->addTranslations(array(
            'label'          => Piwik::translate('Goals_DaysToConv'),
            'nb_conversions' => Piwik::translate('Goals_ColumnConversions'),
        ));
    }

    private function configureViewForItemsReport(ViewDataTable $view, $label)
    {
        $idSite = Common::getRequestVar('idSite');

        $moneyColumns = array('revenue', 'avg_price');
        $prettifyMoneyColumns = array(
            'ColumnCallbackReplace', array($moneyColumns, '\Piwik\MetricsFormatter::getPrettyMoney', array($idSite)));

        $view->config->show_ecommerce = true;
        $view->config->show_table     = false;
        $view->config->show_all_views_icons      = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns      = false;
        $view->config->addTranslation('label', $label);
        $view->config->filters[] = $prettifyMoneyColumns;

        $view->requestConfig->filter_limit       = 10;
        $view->requestConfig->filter_sort_column = 'revenue';
        $view->requestConfig->filter_sort_order  = 'desc';

        // set columns/translations which differ based on viewDataTable TODO: shouldn't have to do this check... amount of reports should be dynamic, but metadata should be static
        $columns = Goals::getProductReportColumns();

        $abandonedCart = Common::getRequestVar('viewDataTable', 'ecommerceOrder', 'string') == 'ecommerceAbandonedCart';
        if ($abandonedCart) {
            $columns['abandoned_carts'] = Piwik::translate('General_AbandonedCarts');
            $columns['revenue'] = Piwik::translate('Goals_LeftInCart', Piwik::translate('General_ProductRevenue'));
            $columns['quantity'] = Piwik::translate('Goals_LeftInCart', Piwik::translate('General_Quantity'));
            $columns['avg_quantity'] = Piwik::translate('Goals_LeftInCart', Piwik::translate('General_AverageQuantity'));
            unset($columns['orders']);
            unset($columns['conversion_rate']);

            $view->requestConfig->request_parameters_to_modify['abandonedCarts'] = '1';
        }

        $translations = array_merge(array('label' => $label), $columns);

        $view->config->addTranslations($translations);
        $view->config->columns_to_display = array_keys($translations);

        // set metrics documentation in normal ecommerce report
        if (!$abandonedCart) {
            $view->config->metrics_documentation = array(
                'revenue'         => Piwik::translate('Goals_ColumnRevenueDocumentation',
                    Piwik::translate('Goals_DocumentationRevenueGeneratedByProductSales')),
                'quantity'        => Piwik::translate('Goals_ColumnQuantityDocumentation', $label),
                'orders'          => Piwik::translate('Goals_ColumnOrdersDocumentation', $label),
                'avg_price'       => Piwik::translate('Goals_ColumnAveragePriceDocumentation', $label),
                'avg_quantity'    => Piwik::translate('Goals_ColumnAverageQuantityDocumentation', $label),
                'nb_visits'       => Piwik::translate('Goals_ColumnVisitsProductDocumentation', $label),
                'conversion_rate' => Piwik::translate('Goals_ColumnConversionRateProductDocumentation', $label),
            );
        }

        $view->config->custom_parameters['viewDataTable'] =
            $abandonedCart ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART : Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;
    }


    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Goals_AddGoal';
        $translationKeys[] = 'Goals_UpdateGoal';
        $translationKeys[] = 'Goals_DeleteGoalConfirm';
        $translationKeys[] = 'Goals_UpdateGoal';
        $translationKeys[] = 'Goals_DeleteGoalConfirm';
        $translationKeys[] = 'Goals_Ecommerce';
        $translationKeys[] = 'Goals_Optional';
    }
}
