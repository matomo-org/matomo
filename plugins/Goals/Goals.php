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
    const VISITS_UNTIL_RECORD_NAME = 'visits_until_conv';
    const DAYS_UNTIL_CONV_RECORD_NAME = 'days_until_conv';

    /**
     * This array stores the ranges to use when displaying the 'visits to conversion'
     * report.
     */
    public static $visitCountRanges = array(
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 6),
        array(7, 7),
        array(8, 8),
        array(9, 14),
        array(15, 25),
        array(26, 50),
        array(51, 100),
        array(100)
    );

    /**
     * This array stores the ranges to use when displaying the 'days to conversion'
     * report.
     */
    public static $daysToConvRanges = array(
        array(0, 0),
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 6),
        array(7, 7),
        array(8, 14),
        array(15, 30),
        array(31, 60),
        array(61, 120),
        array(121, 364),
        array(364)
    );

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
                    'parameters'       => array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER),
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
                    'parameters'        => array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER),
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
                    'parameters'        => array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER),
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
                    'parameters'       => array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_CART),
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
                    'parameters'        => array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_CART),
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
                    'parameters'        => array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_CART),
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

    protected $ecommerceReports = array(
        array('Goals_ProductSKU', 'Goals', 'getItemsSku'),
        array('Goals_ProductName', 'Goals', 'getItemsName'),
        array('Goals_ProductCategory', 'Goals', 'getItemsCategory')
    );

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
            Piwik_AddWidget('Goals_Ecommerce', 'Goals_EcommerceOverview', 'Goals', 'widgetGoalReport', array('idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER));
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

    protected function getGoalCategoryName($idSite)
    {
        $site = new Piwik_Site($idSite);
        return $site->isEcommerceEnabled() ? 'Goals_EcommerceAndGoalsMenu' : 'Goals_Goals';
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
                                                  'idGoal' => ($site->isEcommerceEnabled() ? Piwik_Archive::LABEL_ECOMMERCE_ORDER : null)),
                true,
                25);
            if ($site->isEcommerceEnabled()) {
                Piwik_AddMenu($mainGoalMenu, 'Goals_Ecommerce', array('module' => 'Goals', 'action' => 'ecommerceReport', 'idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER), true, 1);
            }
            Piwik_AddMenu($mainGoalMenu, 'Goals_AddNewGoal', array('module' => 'Goals', 'action' => 'addNewGoal'));
        } else {
            Piwik_AddMenu($mainGoalMenu, '', array(
                                                  'module' => 'Goals',
                                                  'action' => ($site->isEcommerceEnabled() ? 'ecommerceReport' : 'index'),
                                                  'idGoal' => ($site->isEcommerceEnabled() ? Piwik_Archive::LABEL_ECOMMERCE_ORDER : null)),
                true,
                25);

            if ($site->isEcommerceEnabled()) {
                Piwik_AddMenu($mainGoalMenu, 'Goals_Ecommerce', array('module' => 'Goals', 'action' => 'ecommerceReport', 'idGoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER), true, 1);
            }
            Piwik_AddMenu($mainGoalMenu, 'Goals_GoalsOverview', array('module' => 'Goals', 'action' => 'index'), true, 2);
            foreach ($goals as $goal) {
                Piwik_AddMenu($mainGoalMenu, str_replace('%', '%%', Piwik_TranslationWriter::clean($goal['name'])), array('module' => 'Goals', 'action' => 'goalReport', 'idGoal' => $goal['idgoal']));
            }
        }
    }

    /**
     * @param string $recordName 'nb_conversions'
     * @param int|bool $idGoal idGoal to return the metrics for, or false to return overall
     * @return string Archive record name
     */
    static public function getRecordName($recordName, $idGoal = false)
    {
        $idGoalStr = '';
        if ($idGoal !== false) {
            $idGoalStr = $idGoal . "_";
        }
        return 'Goal_' . $idGoalStr . $recordName;
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
        /**
         * @var Piwik_ArchiveProcessing
         */
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        /*
         * Archive Ecommerce Items
         */
        if ($this->shouldArchiveEcommerceItems($archiveProcessing)) {
            $dataTableToSum = $this->dimensions;
            foreach ($this->dimensions as $recordName) {
                $dataTableToSum[] = self::getItemRecordNameAbandonedCart($recordName);
            }
            $archiveProcessing->archiveDataTable($dataTableToSum);
        }

        /*
         *  Archive General Goal metrics
         */
        $goalIdsToSum = Piwik_Tracker_GoalManager::getGoalIds($archiveProcessing->idsite);

        //Ecommerce
        $goalIdsToSum[] = Piwik_Tracker_GoalManager::IDGOAL_ORDER;
        $goalIdsToSum[] = Piwik_Tracker_GoalManager::IDGOAL_CART; //bug here if idgoal=1
        // Overall goal metrics
        $goalIdsToSum[] = false;

        $fieldsToSum = array();
        foreach ($goalIdsToSum as $goalId) {
            $metricsToSum = Piwik_Goals::getGoalColumns($goalId);
            unset($metricsToSum[array_search('conversion_rate', $metricsToSum)]);
            foreach ($metricsToSum as $metricName) {
                $fieldsToSum[] = self::getRecordName($metricName, $goalId);
            }
        }
        $records = $archiveProcessing->archiveNumericValuesSum($fieldsToSum);

        // also recording conversion_rate for each goal
        foreach ($goalIdsToSum as $goalId) {
            $nb_conversions = $records[self::getRecordName('nb_visits_converted', $goalId)];
            $conversion_rate = $this->getConversionRate($nb_conversions, $archiveProcessing);
            $archiveProcessing->insertNumericRecord(self::getRecordName('conversion_rate', $goalId), $conversion_rate);

            // sum up the visits to conversion data table & the days to conversion data table
            $archiveProcessing->archiveDataTable(array(
                                                      self::getRecordName(self::VISITS_UNTIL_RECORD_NAME, $goalId),
                                                      self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME, $goalId)));
        }

        // sum up goal overview reports
        $archiveProcessing->archiveDataTable(array(
                                                  self::getRecordName(self::VISITS_UNTIL_RECORD_NAME),
                                                  self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME)));
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
         * @var Piwik_ArchiveProcessing_Day
         */
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $this->archiveGeneralGoalMetrics($archiveProcessing);
        $this->archiveEcommerceItems($archiveProcessing);
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     */
    function archiveGeneralGoalMetrics($archiveProcessing)
    {
        // extra aggregate selects for the visits to conversion report
        $visitToConvExtraCols = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visitor_count_visits', self::$visitCountRanges, 'log_conversion', 'vcv');

        // extra aggregate selects for the days to conversion report
        $daysToConvExtraCols = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visitor_days_since_first', self::$daysToConvRanges, 'log_conversion', 'vdsf');

        $query = $archiveProcessing->queryConversionsByDimension(
            array(), '', array_merge($visitToConvExtraCols, $daysToConvExtraCols));

        if ($query === false) {
            return;
        }

        $goals = array();
        $visitsToConvReport = array();
        $daysToConvReport = array();

        // Get a standard empty goal row
        $overall = $archiveProcessing->getNewGoalRow($idGoal = 1);
        while ($row = $query->fetch()) {
            $idgoal = $row['idgoal'];

            if (!isset($goals[$idgoal])) {
                $goals[$idgoal] = $archiveProcessing->getNewGoalRow($idgoal);

                $visitsToConvReport[$idgoal] = new Piwik_DataTable();
                $daysToConvReport[$idgoal] = new Piwik_DataTable();
            }
            $archiveProcessing->updateGoalStats($row, $goals[$idgoal]);

            // We don't want to sum Abandoned cart metrics in the overall revenue/conversions/converted visits
            // since it is a "negative conversion"
            if ($idgoal != Piwik_Tracker_GoalManager::IDGOAL_CART) {
                $archiveProcessing->updateGoalStats($row, $overall);
            }

            // map the goal + visit number of a visitor with the # of conversions that happened on that visit
            $table = $archiveProcessing->getSimpleDataTableFromRow($row, Piwik_Archive::INDEX_NB_CONVERSIONS, 'vcv');
            $visitsToConvReport[$idgoal]->addDataTable($table);

            // map the goal + day number of a visit with the # of conversion that happened on that day
            $table = $archiveProcessing->getSimpleDataTableFromRow($row, Piwik_Archive::INDEX_NB_CONVERSIONS, 'vdsf');
            $daysToConvReport[$idgoal]->addDataTable($table);
        }

        // these data tables hold reports for every goal of a site
        $visitsToConvOverview = new Piwik_DataTable();
        $daysToConvOverview = new Piwik_DataTable();

        // Stats by goal, for all visitors
        foreach ($goals as $idgoal => $values) {
            foreach ($values as $metricId => $value) {
                $metricName = Piwik_Archive::$mappingFromIdToNameGoal[$metricId];
                $recordName = self::getRecordName($metricName, $idgoal);
                $archiveProcessing->insertNumericRecord($recordName, $value);
            }
            $conversion_rate = $this->getConversionRate($values[Piwik_Archive::INDEX_GOAL_NB_VISITS_CONVERTED], $archiveProcessing);
            $recordName = self::getRecordName('conversion_rate', $idgoal);
            $archiveProcessing->insertNumericRecord($recordName, $conversion_rate);

            // if the goal is not a special goal (like ecommerce) add it to the overview report
            if ($idgoal !== Piwik_Tracker_GoalManager::IDGOAL_CART &&
                $idgoal !== Piwik_Tracker_GoalManager::IDGOAL_ORDER
            ) {
                $visitsToConvOverview->addDataTable($visitsToConvReport[$idgoal]);
                $daysToConvOverview->addDataTable($daysToConvReport[$idgoal]);
            }

            // visit count until conversion stats
            $archiveProcessing->insertBlobRecord(
                self::getRecordName(self::VISITS_UNTIL_RECORD_NAME, $idgoal),
                $visitsToConvReport[$idgoal]->getSerialized());

            // day count until conversion stats
            $archiveProcessing->insertBlobRecord(
                self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME, $idgoal),
                $daysToConvReport[$idgoal]->getSerialized());
        }

        // archive overview reports
        $archiveProcessing->insertBlobRecord(
            self::getRecordName(self::VISITS_UNTIL_RECORD_NAME), $visitsToConvOverview->getSerialized());
        $archiveProcessing->insertBlobRecord(
            self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME), $daysToConvOverview->getSerialized());

        // Stats for all goals
        $totalAllGoals = array(
            self::getRecordName('conversion_rate')     => $this->getConversionRate($archiveProcessing->getNumberOfVisitsConverted(), $archiveProcessing),
            self::getRecordName('nb_conversions')      => $overall[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS],
            self::getRecordName('nb_visits_converted') => $archiveProcessing->getNumberOfVisitsConverted(),
            self::getRecordName('revenue')             => $overall[Piwik_Archive::INDEX_GOAL_REVENUE],
        );
        foreach ($totalAllGoals as $recordName => $value) {
            $archiveProcessing->insertNumericRecord($recordName, $value);
        }
    }

    protected $dimensions = array(
        'idaction_sku'      => 'Goals_ItemsSku',
        'idaction_name'     => 'Goals_ItemsName',
        'idaction_category' => 'Goals_ItemsCategory'
    );

    protected function shouldArchiveEcommerceItems($archiveProcessing)
    {
        // Per item doesn't support segment
        // Also, when querying Goal metrics for visitorType==returning, we wouldnt want to trigger an extra request
        // event if it did support segment
        // (if this is implented, we should have shouldProcessReportsForPlugin() support partial archiving based on which metric is requested)
        if (!$archiveProcessing->getSegment()->isEmpty()) {
            return false;
        }
        return true;
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     */
    function archiveEcommerceItems($archiveProcessing)
    {
        if (!$this->shouldArchiveEcommerceItems($archiveProcessing)) {
            return false;
        }
        $items = array();

        $dimensionsToQuery = $this->dimensions;
        $dimensionsToQuery['idaction_category2'] = 'AdditionalCategory';
        $dimensionsToQuery['idaction_category3'] = 'AdditionalCategory';
        $dimensionsToQuery['idaction_category4'] = 'AdditionalCategory';
        $dimensionsToQuery['idaction_category5'] = 'AdditionalCategory';

        foreach ($dimensionsToQuery as $dimension => $recordName) {
            $query = $archiveProcessing->queryEcommerceItems($dimension);
            if ($query == false) {
                continue;
            }

            while ($row = $query->fetch()) {
                $label = $row['label'];
                $ecommerceType = $row['ecommerceType'];

                if (empty($label)) {
                    // idaction==0 case:
                    // If we are querying any optional category, we do not include idaction=0
                    // Otherwise we over-report in the Product Categories report
                    if ($recordName == 'AdditionalCategory') {
                        continue;
                    }
                    // Product Name/Category not defined"
                    if (class_exists('Piwik_CustomVariables')) {
                        $label = Piwik_CustomVariables::LABEL_CUSTOM_VALUE_NOT_DEFINED;
                    } else {
                        $label = "Value not defined";
                    }
                }
                // For carts, idorder = 0. To count abandoned carts, we must count visits with an abandoned cart
                if ($ecommerceType == Piwik_Tracker_GoalManager::IDGOAL_CART) {
                    $row[Piwik_Archive::INDEX_ECOMMERCE_ORDERS] = $row[Piwik_Archive::INDEX_NB_VISITS];
                }
                unset($row[Piwik_Archive::INDEX_NB_VISITS]);
                unset($row['label']);
                unset($row['ecommerceType']);

                $columnsToRound = array(
                    Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE,
                    Piwik_Archive::INDEX_ECOMMERCE_ITEM_QUANTITY,
                    Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE,
                    Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED,
                );
                foreach ($columnsToRound as $column) {
                    if (isset($row[$column])
                        && $row[$column] == round($row[$column])
                    ) {
                        $row[$column] = round($row[$column]);
                    }
                }
                $items[$dimension][$ecommerceType][$label] = $row;
            }
        }

        foreach ($this->dimensions as $dimension => $recordName) {
            foreach (array(Piwik_Tracker_GoalManager::IDGOAL_CART, Piwik_Tracker_GoalManager::IDGOAL_ORDER) as $ecommerceType) {
                if (!isset($items[$dimension][$ecommerceType])) {
                    continue;
                }
                $recordNameInsert = $recordName;
                if ($ecommerceType == Piwik_Tracker_GoalManager::IDGOAL_CART) {
                    $recordNameInsert = self::getItemRecordNameAbandonedCart($recordName);
                }
                $table = $archiveProcessing->getDataTableFromArray($items[$dimension][$ecommerceType]);

                // For "category" report, we aggregate all 5 category queries into one datatable
                if ($dimension == 'idaction_category') {
                    foreach (array('idaction_category2', 'idaction_category3', 'idaction_category4', 'idaction_category5') as $categoryToSum) {
                        if (!empty($items[$categoryToSum][$ecommerceType])) {
                            $tableToSum = $archiveProcessing->getDataTableFromArray($items[$categoryToSum][$ecommerceType]);
                            $table->addDataTable($tableToSum);
                        }
                    }
                }
                $archiveProcessing->insertBlobRecord($recordNameInsert, $table->getSerialized());
            }
        }
    }

    static public function getItemRecordNameAbandonedCart($recordName)
    {
        return $recordName . '_Cart';
    }

    function getConversionRate($count, $archiveProcessing)
    {
        $visits = $archiveProcessing->getNumberOfVisits();
        return round(100 * $count / $visits, Piwik_Tracker_GoalManager::REVENUE_PRECISION);
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
}
