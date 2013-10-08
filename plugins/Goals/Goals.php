<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Goals
 */
namespace Piwik\Plugins\Goals;

use Piwik\ArchiveProcessor;
use Piwik\Common;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Tracker\GoalManager;
use Piwik\Translate;
use Piwik\WidgetsList;

/**
 *
 * @package Goals
 */
class Goals extends \Piwik\Plugin
{
    public function getInformation()
    {
        $suffix = Piwik_Translate('SitesManager_PiwikOffersEcommerceAnalytics',
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
        $dimensions = array();

        /**
         * This event is triggered to define available goal segments.
         * @matt
         */
        Piwik_PostEvent('Goals.getReportsWithGoalMetrics', array(&$dimensions));
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
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles'          => 'getJsFiles',
            'AssetManager.getStylesheetFiles'          => 'getStylesheetFiles',
            'Site.getSiteAttributes'                   => 'fetchGoalsFromDb',
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'API.getReportMetadata.end'                => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'Menu.Reporting.addItems'                  => 'addMenus',
            'SitesManager.deleteSite.end'              => 'deleteSiteGoals',
            'Goals.getReportsWithGoalMetrics'          => 'getActualReportsWithGoalMetrics',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
            'Translate.getClientSideTranslationKeys'   => 'getClientSideTranslationKeys'
        );
        return $hooks;
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
            'revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
        );

        $goalMetrics = array(
            'nb_conversions'      => Piwik_Translate('Goals_ColumnConversions'),
            'nb_visits_converted' => Piwik_Translate('General_ColumnVisitsWithConversions'),
            'conversion_rate'     => Piwik_Translate('General_ColumnConversionRate'),
            'revenue'             => Piwik_Translate('General_ColumnRevenue')
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
            $goals = API::getInstance()->getGoals($idSite);

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

            $site = new Site($idSite);
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
                $abandonedCartMetrics['revenue'] = Piwik_Translate('Goals_LeftInCart', Piwik_Translate('General_ColumnRevenue'));
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

        $reportsWithGoals = array();

        /*
         * Add the metricsGoal and processedMetricsGoal entry
         * to all reports that have Goal segmentation
         *
         * @matt is this same event as triggered above? Do we need two names? Currently they appear twice in the
         * generated documentation. Maybe we can create a private/protected function to trigger this event to have it
         * defined only once?
         */
        Piwik_PostEvent('Goals.getReportsWithGoalMetrics', array(&$reportsWithGoals));
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
     */
    public function getActualReportsWithGoalMetrics(&$dimensions)
    {
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

    public function getSegmentsMetadata(&$segments)
    {
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
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

    public function addWidgets()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        // Ecommerce widgets
        $site = new Site($idSite);
        if ($site->isEcommerceEnabled()) {
            WidgetsList::add('Goals_Ecommerce', 'Goals_EcommerceOverview', 'Goals', 'widgetGoalReport', array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
            WidgetsList::add('Goals_Ecommerce', 'Goals_EcommerceLog', 'Goals', 'getEcommerceLog');
            foreach ($this->ecommerceReports as $widget) {
                WidgetsList::add('Goals_Ecommerce', $widget[0], $widget[1], $widget[2]);
            }
        }

        // Goals widgets
        WidgetsList::add('Goals_Goals', 'Goals_GoalsOverview', 'Goals', 'widgetGoalsOverview');
        $goals = API::getInstance()->getGoals($idSite);
        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                WidgetsList::add('Goals_Goals', Common::sanitizeInputValue($goal['name']), 'Goals', 'widgetGoalReport', array('idGoal' => $goal['idgoal']));
            }
        }
    }

    function addMenus()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals = API::getInstance()->getGoals($idSite);
        $mainGoalMenu = $this->getGoalCategoryName($idSite);
        $site = new Site($idSite);
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
                Piwik_AddMenu($mainGoalMenu, str_replace('%', '%%', Translate::clean($goal['name'])), array('module' => 'Goals', 'action' => 'goalReport', 'idGoal' => $goal['idgoal']));
            }
        }
    }

    protected function getGoalCategoryName($idSite)
    {
        $site = new Site($idSite);
        return $site->isEcommerceEnabled() ? 'Goals_EcommerceAndGoalsMenu' : 'Goals_Goals';
    }

    /**
     * Hooks on the Daily archiving.
     * Will process Goal stats overall and for each Goal.
     * Also processes the New VS Returning visitors conversion stats.
     */
    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * Hooks on Period archiving.
     * Sums up Goal conversions stats, and processes overall conversion rate
     */
    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['Goals.getItemsSku'] = $this->getDisplayPropertiesForGetItemsSku();
        $properties['Goals.getItemsName'] = $this->getDisplayPropertiesForGetItemsName();
        $properties['Goals.getItemsCategory'] = $this->getDisplayPropertiesForGetItemsCategory();
        $properties['Goals.getVisitsUntilConversion'] = $this->getDisplayPropertiesForGetVisitsUntilConversion();
        $properties['Goals.getDaysToConversion'] = $this->getDisplayPropertiesForGetDaysToConversion();
    }

    private function getDisplayPropertiesForGetItemsSku()
    {
        return $this->getDisplayPropertiesForItemsReport(Piwik_Translate('Goals_ProductSKU'));
    }

    private function getDisplayPropertiesForGetItemsName()
    {
        return $this->getDisplayPropertiesForItemsReport(Piwik_Translate('Goals_ProductName'));
    }

    private function getDisplayPropertiesForGetItemsCategory()
    {
        return $this->getDisplayPropertiesForItemsReport(Piwik_Translate('Goals_ProductCategory'));
    }

    private function getDisplayPropertiesForGetVisitsUntilConversion()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'show_table_all_columns'      => false,
            'columns_to_display'          => array('label', 'nb_conversions'),
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'translations'                => array(
                'label'          => Piwik_Translate('Goals_VisitsUntilConv'),
                'nb_conversions' => Piwik_Translate('Goals_ColumnConversions'),
            ),
            'filter_limit'                => count(Archiver::$visitCountRanges),
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_all_views_icons'        => false
        );
    }

    private function getDisplayPropertiesForGetDaysToConversion()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'show_table_all_columns'      => false,
            'columns_to_display'          => array('label', 'nb_conversions'),
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'translations'                => array(
                'label'          => Piwik_Translate('Goals_DaysToConv'),
                'nb_conversions' => Piwik_Translate('Goals_ColumnConversions'),
            ),
            'filter_limit'                => count(Archiver::$daysToConvRanges),
            'show_all_views_icons'        => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
        );
    }

    private function getDisplayPropertiesForItemsReport($label)
    {
        $idSite = Common::getRequestVar('idSite');

        $moneyColumns = array('revenue', 'avg_price');
        $prettifyMoneyColumns = array(
            'ColumnCallbackReplace', array($moneyColumns, '\Piwik\MetricsFormatter::getPrettyMoney', array($idSite)));

        $result = array(
            'show_ecommerce'              => true,
            'show_all_views_icons'        => false,
            'show_table'                  => false,
            'show_exclude_low_population' => false,
            'show_table_all_columns'      => false,
            'filter_limit'                => 10,
            'translations'                => array('label' => $label),
            'filter_sort_column'          => 'revenue',
            'filter_sort_order'           => 'desc',
            'filters'                     => array($prettifyMoneyColumns)
        );

        // set columns/translations which differ based on viewDataTable TODO: shouldn't have to do this check... amount of reports should be dynamic, but metadata should be static
        $columns = Goals::getProductReportColumns();

        $abandonedCart = Common::getRequestVar('viewDataTable', 'ecommerceOrder', 'string') == 'ecommerceAbandonedCart';
        if ($abandonedCart) {
            $columns['abandoned_carts'] = Piwik_Translate('General_AbandonedCarts');
            $columns['revenue'] = Piwik_Translate('Goals_LeftInCart', Piwik_Translate('General_ProductRevenue'));
            $columns['quantity'] = Piwik_Translate('Goals_LeftInCart', Piwik_Translate('General_Quantity'));
            $columns['avg_quantity'] = Piwik_Translate('Goals_LeftInCart', Piwik_Translate('General_AverageQuantity'));
            unset($columns['orders']);
            unset($columns['conversion_rate']);

            $result['request_parameters_to_modify'] = array('abandonedCarts' => '1');
        }

        $result['translations'] = array_merge(array('label' => $label), $columns);
        $result['columns_to_display'] = array_keys($result['translations']);

        // set metrics documentation in normal ecommerce report
        if (!$abandonedCart) {
            $result['metrics_documentation'] = array(
                'revenue'         => Piwik_Translate('Goals_ColumnRevenueDocumentation',
                    Piwik_Translate('Goals_DocumentationRevenueGeneratedByProductSales')),
                'quantity'        => Piwik_Translate('Goals_ColumnQuantityDocumentation', $label),
                'orders'          => Piwik_Translate('Goals_ColumnOrdersDocumentation', $label),
                'avg_price'       => Piwik_Translate('Goals_ColumnAveragePriceDocumentation', $label),
                'avg_quantity'    => Piwik_Translate('Goals_ColumnAverageQuantityDocumentation', $label),
                'nb_visits'       => Piwik_Translate('Goals_ColumnVisitsProductDocumentation', $label),
                'conversion_rate' => Piwik_Translate('Goals_ColumnConversionRateProductDocumentation', $label),
            );
        }

        $result['custom_parameters']['viewDataTable'] =
            $abandonedCart ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART : Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;

        return $result;
    }


    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Goals_AddGoal';
        $translationKeys[] = 'Goals_UpdateGoal';
        $translationKeys[] = 'Goals_DeleteGoalConfirm';
    }
}
