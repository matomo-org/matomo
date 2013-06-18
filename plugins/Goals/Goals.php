<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 *
 * @package Piwik_Goals
 */
class Piwik_Goals extends Piwik_Plugin
{
    protected $ecommerceReports = array(
        array('Goals_ProductSKU', 'Goals', 'getItemsSku'),
        array('Goals_ProductName', 'Goals', 'getItemsName'),
        array('Goals_ProductCategory', 'Goals', 'getItemsCategory')
    );

    static public function getReportsWithGoalMetrics()
    {
        $dimensions = array();
        Piwik_PostEvent('Goals.getReportsWithGoalMetrics', $dimensions);
        $dimensionsByGroup = array();
        foreach ($dimensions as $dimension) {
            $group = $dimension['category'];
            unset($dimension['category']);
            $dimensionsByGroup[$group][] = $dimension;
        }
        return $dimensionsByGroup;
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
        if ($idGoal === Piwik_Tracker_GoalManager::IDGOAL_ORDER) {
            $columns = array_merge($columns, array(
                                                  'revenue_subtotal',
                                                  'revenue_tax',
                                                  'revenue_shipping',
                                                  'revenue_discount',
                                             ));
        }
        // Abandoned carts & orders
        if ($idGoal <= Piwik_Tracker_GoalManager::IDGOAL_ORDER) {
            $columns[] = 'items';
        }
        return $columns;
    }

    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('Goals_PluginDescription') . ' ' . Piwik_Translate('SitesManager_PiwikOffersEcommerceAnalytics', array('<a href="http://piwik.org/docs/ecommerce-analytics/" target="_blank">', '</a>')),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
            'TrackerPlugin'   => true, // this plugin must be loaded during the stats logging
        );
        return $info;
    }

    function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJsFiles'          => 'getJsFiles',
            'AssetManager.getCssFiles'         => 'getCssFiles',
            'Common.fetchWebsiteAttributes'    => 'fetchGoalsFromDb',
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'API.getReportMetadata.end'        => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenus',
            'SitesManager.deleteSite'          => 'deleteSiteGoals',
            'Goals.getReportsWithGoalMetrics'  => 'getActualReportsWithGoalMetrics',
        );
        return $hooks;
    }

    /**
     * Delete goals recorded for this site
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function deleteSiteGoals($notification)
    {
        $idSite = & $notification->getNotificationObject();
        Piwik_Query("DELETE FROM " . Piwik_Common::prefixTable('goal') . " WHERE idsite = ? ", array($idSite));
    }

    /**
     * Returns the Metadata for the Goals plugin API.
     * The API returns general Goal metrics: conv, conv rate and revenue globally
     * and for each goal.
     *
     * Also, this will update metadata of all other reports that have Goal segmentation
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $info = $notification->getNotificationInfo();
        $idSites = $info['idSites'];
        $reports = & $notification->getNotificationObject();

        // Processed in AddColumnsProcessedMetricsGoal
        // These metrics will also be available for some reports, for each goal
        // Example: Conversion rate for Goal 2 for the keyword 'piwik'
        $goalProcessedMetrics = array(
            'revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
        );

        $goalMetrics = array(
            'nb_conversions'      => Piwik_Translate('Goals_ColumnConversions'),
            'nb_visits_converted' => Piwik_Translate('General_ColumnVisitsWithConversions'),
            'conversion_rate'     => Piwik_Translate('General_ColumnConversionRate'),
            'revenue'             => Piwik_Translate('Goals_ColumnRevenue')
        );

        $conversionReportMetrics = array(
            'nb_conversions' => Piwik_Translate('Goals_ColumnConversions')
        );

        // General Goal metrics: conversions, conv rate, revenue
        $goalsCategory = Piwik_Translate('Goals_Goals');
        $reports[] = array(
            'category'         => $goalsCategory,
            'name'             => Piwik_Translate('Goals_Goals'),
            'module'           => 'Goals',
            'action'           => 'get',
            'metrics'          => $goalMetrics,
            'processedMetrics' => array(),
            'order'            => 1
        );

        // If only one website is selected, we add the Goal metrics
        if (count($idSites) == 1) {
            $idSite = reset($idSites);
            $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);

            // Add overall visits to conversion report
            $reports[] = array(
                'category'          => $goalsCategory,
                'name'              => Piwik_Translate('Goals_VisitsUntilConv'),
                'module'            => 'Goals',
                'action'            => 'getVisitsUntilConversion',
                'dimension'         => Piwik_Translate('Goals_VisitsUntilConv'),
                'constantRowsCount' => true,
                'parameters'        => array(),
                'metrics'           => $conversionReportMetrics,
                'order'             => 5
            );

            // Add overall days to conversion report
            $reports[] = array(
                'category'          => $goalsCategory,
                'name'              => Piwik_Translate('Goals_DaysToConv'),
                'module'            => 'Goals',
                'action'            => 'getDaysToConversion',
                'dimension'         => Piwik_Translate('Goals_DaysToConv'),
                'constantRowsCount' => true,
                'parameters'        => array(),
                'metrics'           => $conversionReportMetrics,
                'order'             => 10
            );

            foreach ($goals as $goal) {
                // Add the general Goal metrics: ie. total Goal conversions,
                // Goal conv rate or Goal total revenue.
                // This API call requires a custom parameter
                $reports[] = array(
                    'category'         => $goalsCategory,
                    'name'             => Piwik_Translate('Goals_GoalX', $goal['name']),
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
                    'name'              => $goal['name'] . ' - ' . Piwik_Translate('Goals_VisitsUntilConv'),
                    'module'            => 'Goals',
                    'action'            => 'getVisitsUntilConversion',
                    'dimension'         => Piwik_Translate('Goals_VisitsUntilConv'),
                    'constantRowsCount' => true,
                    'parameters'        => array('idGoal' => $goal['idgoal']),
                    'metrics'           => $conversionReportMetrics,
                    'order'             => 51 + $goal['idgoal'] * 3
                );

                // Add days to conversion report
                $reports[] = array(
                    'category'          => $goalsCategory,
                    'name'              => $goal['name'] . ' - ' . Piwik_Translate('Goals_DaysToConv'),
                    'module'            => 'Goals',
                    'action'            => 'getDaysToConversion',
                    'dimension'         => Piwik_Translate('Goals_DaysToConv'),
                    'constantRowsCount' => true,
                    'parameters'        => array('idGoal' => $goal['idgoal']),
                    'metrics'           => $conversionReportMetrics,
                    'order'             => 52 + $goal['idgoal'] * 3
                );
            }

            $site = new Piwik_Site($idSite);
            if ($site->isEcommerceEnabled()) {
                $category = Piwik_Translate('Goals_Ecommerce');
                $ecommerceMetrics = array_merge($goalMetrics, array(
                                                                   'revenue_subtotal'  => Piwik_Translate('General_Subtotal'),
                                                                   'revenue_tax'       => Piwik_Translate('General_Tax'),
                                                                   'revenue_shipping'  => Piwik_Translate('General_Shipping'),
                                                                   'revenue_discount'  => Piwik_Translate('General_Discount'),
                                                                   'items'             => Piwik_Translate('General_PurchasedProducts'),
                                                                   'avg_order_revenue' => Piwik_Translate('General_AverageOrderValue')
                                                              ));
                $ecommerceMetrics['nb_conversions'] = Piwik_Translate('General_EcommerceOrders');

                // General Ecommerce metrics
                $reports[] = array(
                    'category'         => $category,
                    'name'             => Piwik_Translate('General_EcommerceOrders'),
                    'module'           => 'Goals',
                    'action'           => 'get',
                    'parameters'       => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER),
                    'metrics'          => $ecommerceMetrics,
                    'processedMetrics' => false,
                    'order'            => 10
                );
                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik_Translate('General_EcommerceOrders') . ' - ' . Piwik_Translate('Goals_VisitsUntilConv'),
                    'module'            => 'Goals',
                    'action'            => 'getVisitsUntilConversion',
                    'dimension'         => Piwik_Translate('Goals_VisitsUntilConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER),
                    'order'             => 11
                );
                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik_Translate('General_EcommerceOrders') . ' - ' . Piwik_Translate('Goals_DaysToConv'),
                    'module'            => 'Goals',
                    'action'            => 'getDaysToConversion',
                    'dimension'         => Piwik_Translate('Goals_DaysToConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER),
                    'order'             => 12
                );

                // Abandoned cart general metrics
                $abandonedCartMetrics = $goalMetrics;
                $abandonedCartMetrics['nb_conversions'] = Piwik_Translate('General_AbandonedCarts');
                $abandonedCartMetrics['revenue'] = Piwik_Translate('Goals_LeftInCart', Piwik_Translate('Goals_ColumnRevenue'));
                $abandonedCartMetrics['items'] = Piwik_Translate('Goals_LeftInCart', Piwik_Translate('Goals_Products'));
                unset($abandonedCartMetrics['nb_visits_converted']);

                // Abandoned Cart metrics
                $reports[] = array(
                    'category'         => $category,
                    'name'             => Piwik_Translate('General_AbandonedCarts'),
                    'module'           => 'Goals',
                    'action'           => 'get',
                    'parameters'       => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART),
                    'metrics'          => $abandonedCartMetrics,
                    'processedMetrics' => false,
                    'order'            => 15
                );

                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik_Translate('General_AbandonedCarts') . ' - ' . Piwik_Translate('Goals_VisitsUntilConv'),
                    'module'            => 'Goals',
                    'action'            => 'getVisitsUntilConversion',
                    'dimension'         => Piwik_Translate('Goals_VisitsUntilConv'),
                    'constantRowsCount' => true,
                    'metrics'           => $conversionReportMetrics,
                    'parameters'        => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART),
                    'order'             => 20
                );
                $reports[] = array(
                    'category'          => $category,
                    'name'              => Piwik_Translate('General_AbandonedCarts') . ' - ' . Piwik_Translate('Goals_DaysToConv'),
                    'module'            => 'Goals',
                    'action'            => 'getDaysToConversion',
                    'dimension'         => Piwik_Translate('Goals_DaysToConv'),
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
                        'name'             => Piwik_Translate($ecommerceReport[0]),
                        'module'           => 'Goals',
                        'action'           => $ecommerceReport[2],
                        'dimension'        => Piwik_Translate($ecommerceReport[0]),
                        'metrics'          => $productColumns,
                        'processedMetrics' => false,
                        'order'            => 30 + $i
                    );
                }
            }
        }

        unset($goalMetrics['nb_visits_converted']);

        /*
         * Add the metricsGoal and processedMetricsGoal entry
         * to all reports that have Goal segmentation
         */
        $reportsWithGoals = array();
        Piwik_PostEvent('Goals.getReportsWithGoalMetrics', $reportsWithGoals);
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

    static public function getProductReportColumns()
    {
        return array(
            'revenue'         => Piwik_Translate('General_ProductRevenue'),
            'quantity'        => Piwik_Translate('General_Quantity'),
            'orders'          => Piwik_Translate('General_UniquePurchases'),
            'avg_price'       => Piwik_Translate('General_AveragePrice'),
            'avg_quantity'    => Piwik_Translate('General_AverageQuantity'),
            'nb_visits'       => Piwik_Translate('General_ColumnNbVisits'),
            'conversion_rate' => Piwik_Translate('General_ProductConversionRate'),
        );
    }

    /**
     * This function executes when the 'Goals.getReportsWithGoalMetrics' event fires. It
     * adds the 'visits to conversion' report metadata to the list of goal reports so
     * this report will be displayed.
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getActualReportsWithGoalMetrics($notification)
    {
        $dimensions =& $notification->getNotificationObject();
        $dimensions = array_merge($dimensions, array(
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('Goals_VisitsUntilConv'),
                                                          'module'   => 'Goals',
                                                          'action'   => 'getVisitsUntilConversion'
                                                    ),
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('Goals_DaysToConv'),
                                                          'module'   => 'Goals',
                                                          'action'   => 'getDaysToConversion'
                                                    )
                                               ));
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => 'General_VisitConvertedGoalId',
            'segment'        => 'visitConvertedGoalId',
            'sqlSegment'     => 'log_conversion.idgoal',
            'acceptedValues' => '1, 2, 3, etc.',
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = "plugins/Goals/templates/GoalForm.js";
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();
        $cssFiles[] = "plugins/Goals/templates/goals.css";
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function fetchGoalsFromDb($notification)
    {
        $idsite = $notification->getNotificationInfo();

        // add the 'goal' entry in the website array
        $array =& $notification->getNotificationObject();
        $array['goals'] = Piwik_Goals_API::getInstance()->getGoals($idsite);
    }

    function addWidgets()
    {
        $idSite = Piwik_Common::getRequestVar('idSite', null, 'int');

        // Ecommerce widgets
        $site = new Piwik_Site($idSite);
        if ($site->isEcommerceEnabled()) {
            Piwik_AddWidget('Goals_Ecommerce', 'Goals_EcommerceOverview', 'Goals', 'widgetGoalReport', array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
            Piwik_AddWidget('Goals_Ecommerce', 'Goals_EcommerceLog', 'Goals', 'getEcommerceLog');
            foreach ($this->ecommerceReports as $widget) {
                Piwik_AddWidget('Goals_Ecommerce', $widget[0], $widget[1], $widget[2]);
            }
        }

        // Goals widgets
        Piwik_AddWidget('Goals_Goals', 'Goals_GoalsOverview', 'Goals', 'widgetGoalsOverview');
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                Piwik_AddWidget('Goals_Goals', Piwik_Common::sanitizeInputValue($goal['name']), 'Goals', 'widgetGoalReport', array('idGoal' => $goal['idgoal']));
            }
        }
    }

    function addMenus()
    {
        $idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $mainGoalMenu = $this->getGoalCategoryName($idSite);
        $site = new Piwik_Site($idSite);
        if (count($goals) == 0) {
            Piwik_AddMenu($mainGoalMenu, '', array(
                                                  'module' => 'Goals',
                                                  'action' => ($site->isEcommerceEnabled() ? 'ecommerceReport' : 'addNewGoal'),
                                                  'idGoal' => ($site->isEcommerceEnabled() ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER : null)),
                true,
                25);
            if ($site->isEcommerceEnabled()) {
                Piwik_AddMenu($mainGoalMenu, 'Goals_Ecommerce', array('module' => 'Goals', 'action' => 'ecommerceReport', 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER), true, 1);
            }
            Piwik_AddMenu($mainGoalMenu, 'Goals_AddNewGoal', array('module' => 'Goals', 'action' => 'addNewGoal'));
        } else {
            Piwik_AddMenu($mainGoalMenu, '', array(
                                                  'module' => 'Goals',
                                                  'action' => ($site->isEcommerceEnabled() ? 'ecommerceReport' : 'index'),
                                                  'idGoal' => ($site->isEcommerceEnabled() ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER : null)),
                true,
                25);

            if ($site->isEcommerceEnabled()) {
                Piwik_AddMenu($mainGoalMenu, 'Goals_Ecommerce', array('module' => 'Goals', 'action' => 'ecommerceReport', 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER), true, 1);
            }
            Piwik_AddMenu($mainGoalMenu, 'Goals_GoalsOverview', array('module' => 'Goals', 'action' => 'index'), true, 2);
            foreach ($goals as $goal) {
                Piwik_AddMenu($mainGoalMenu, str_replace('%', '%%', Piwik_TranslationWriter::clean($goal['name'])), array('module' => 'Goals', 'action' => 'goalReport', 'idGoal' => $goal['idgoal']));
            }
        }
    }

    protected function getGoalCategoryName($idSite)
    {
        $site = new Piwik_Site($idSite);
        return $site->isEcommerceEnabled() ? 'Goals_EcommerceAndGoalsMenu' : 'Goals_Goals';
    }

    /**
     * Hooks on the Daily archiving.
     * Will process Goal stats overall and for each Goal.
     * Also processes the New VS Returning visitors conversion stats.
     *
     * @param Piwik_Event_Notification $notification
     * @return void
     */
    function archiveDay($notification)
    {
        /**
         * @var Piwik_ArchiveProcessor_Day
         */
        $archiveProcessor = $notification->getNotificationObject();

        $archiving = new Piwik_Goals_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * Hooks on Period archiving.
     * Sums up Goal conversions stats, and processes overall conversion rate
     *
     * @param Piwik_Event_Notification $notification
     * @return void
     */
    function archivePeriod($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();

        $archiving = new Piwik_Goals_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }
}
