<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals;

use Piwik\API\Request;
use Piwik\Columns\ComputedMetricFactory;
use Piwik\Columns\Dimension;
use Piwik\Columns\MetricsList;
use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\AverageOrderRevenue;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionEntryRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionPageRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenuePerEntry;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenuePerVisit;
use Piwik\Plugins\Goals\RecordBuilders\ProductRecord;
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
            if (
                $dimension['module'] === 'CustomVariables'
                || $dimension['action'] == 'getVisitInformationPerServerTime'
            ) {
                $group = 'VisitsSummary_VisitsSummary';
            }
            unset($dimension['category']);
            $dimensionsByGroup[$group][] = $dimension;
        }

        return $dimensionsByGroup;
    }

    public static function getGoalIdFromGoalColumn($columnName)
    {
        if (strpos($columnName, 'goal_') === 0) {
            $column = str_replace(array('goal_'), '', $columnName);
            return (int) $column;
        }
    }

    public static function makeGoalColumn($idGoal, $column, $forceInt = true)
    {
        if ($forceInt) { // in non-archiver code idGoal can be, eg, ecommerceOrder
            $idGoal = (int) $idGoal;
        }

        return 'goal_' . $idGoal . '_' . $column;
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
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Tracker.Cache.getSiteAttributes'        => 'fetchGoalsFromDb',
            'API.getReportMetadata.end'              => 'getReportMetadataEnd',
            'SitesManager.deleteSite.end'            => 'deleteSiteGoals',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Metrics.getDefaultMetricTranslations'   => 'addMetricTranslations',
            'Metrics.getDefaultMetricSemanticTypes'  => 'addMetricSemanticTypes',
            'Category.addSubcategories'              => 'addSubcategories',
            'Metric.addMetrics'                      => 'addMetrics',
            'Metric.addComputedMetrics'              => 'addComputedMetrics',
            'System.addSystemSummaryItems'           => 'addSystemSummaryItems',
            'Archiver.addRecordBuilders'             => 'addRecordBuilders',
        );
        return $hooks;
    }

    public function addRecordBuilders(array &$recordBuilders): void
    {
        $recordBuilders[] = new ProductRecord(ProductRecord::SKU_FIELD, ProductRecord::ITEMS_SKU_RECORD_NAME);
        $recordBuilders[] = new ProductRecord(ProductRecord::NAME_FIELD, ProductRecord::ITEMS_NAME_RECORD_NAME);
        $recordBuilders[] = new ProductRecord(ProductRecord::CATEGORY_FIELD, ProductRecord::ITEMS_CATEGORY_RECORD_NAME, [
            ProductRecord::CATEGORY2_FIELD,
            ProductRecord::CATEGORY3_FIELD,
            ProductRecord::CATEGORY4_FIELD,
            ProductRecord::CATEGORY5_FIELD,
        ]);
    }

    public function addSystemSummaryItems(&$systemSummary)
    {
        $goalModel = new Model();
        $numGoals = $goalModel->getActiveGoalCount();

        $systemSummary[] = new SystemSummary\Item($key = 'goals', Piwik::translate('Goals_NGoals', $numGoals), $value = null, array('module' => 'Goals', 'action' => 'manage'), $icon = 'icon-goal', $order = 7);
    }

    public function addComputedMetrics(MetricsList $list, ComputedMetricFactory $computedMetricFactory)
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);

        foreach ($goals as $goal) {
            $metric = $computedMetricFactory->createComputedMetric('goal_' .  $goal['idgoal'] . '_conversion', 'nb_uniq_visitors', ComputedMetric::AGGREGATION_RATE);
            $goalName = '"' . Piwik::translate('Goals_GoalX', $goal['name']) . '"';
            $metricName = Piwik::translate('Goals_ConversionRate', $goalName);
            $metric->setTranslatedName($metricName);
            $list->addMetric($metric);
        }
    }

    public function addMetrics(MetricsList $metricsList)
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);

        foreach ($goals as $goal) {
            $custom = new GoalDimension($goal, 'idgoal', 'Conversions goal "' . $goal['name'] . '" (ID ' . $goal['idgoal'] . ' )');
            $custom->setType(Dimension::TYPE_NUMBER);
            $custom->setSqlSegment('count(distinct log_conversion.idvisit, log_conversion.buster)');

            $metric = new ArchivedMetric($custom, ArchivedMetric::AGGREGATION_SUM);
            $metric->setQuery('count(distinct log_conversion.idvisit, log_conversion.buster)');
            $metric->setTranslatedName($custom->getName());
            $metric->setDocumentation('The number of times this goal was converted.');
            $metric->setCategory($custom->getCategoryId());
            $metric->setName('goal_' . $goal['idgoal'] . '_conversion');
            $metricsList->addMetric($metric);

            $custom = new GoalDimension($goal, 'revenue', 'Revenue goal "' . $goal['name'] . '" (ID ' . $goal['idgoal'] . ' )');
            $custom->setType(Dimension::TYPE_MONEY);
            $metric = new ArchivedMetric($custom, ArchivedMetric::AGGREGATION_SUM);
            $metric->setTranslatedName($custom->getName());
            $metric->setName('goal_' . $goal['idgoal'] . '_revenue');
            $metric->setDocumentation('The amount of revenue that was generated by converting this goal.');
            $metric->setCategory($custom->getCategoryId());
            $metricsList->addMetric($metric);

            $custom = new GoalDimension($goal, 'visitor_seconds_since_first', 'Days to conversion goal "' . $goal['name'] . '" (ID ' . $goal['idgoal'] . ' )');
            $custom->setType(Dimension::TYPE_NUMBER);
            $metric = new ArchivedMetric($custom, ArchivedMetric::AGGREGATION_SUM);
            $metric->setTranslatedName($custom->getName());
            $metric->setCategory($custom->getCategoryId());
            $metric->setDocumentation('The number of days it took a visitor to convert this goal.');
            $metric->setName('goal_' . $goal['idgoal'] . '_daystoconversion');
            $metric->setQuery('sum(floor(log_visit.visitor_seconds_since_first / 86400))');
            $metricsList->addMetric($metric);

            $custom = new GoalDimension($goal, 'visitor_count_visits', 'Visits to conversion goal "' . $goal['name'] . '" (ID ' . $goal['idgoal'] . ' )');
            $custom->setType(Dimension::TYPE_NUMBER);
            $metric = new ArchivedMetric($custom, ArchivedMetric::AGGREGATION_SUM);
            $metric->setTranslatedName($custom->getName());
            $metric->setCategory($custom->getCategoryId());
            $metric->setDocumentation('The number of visits it took a visitor to convert this goal.');
            $metric->setName('goal_' . $goal['idgoal'] . '_visitstoconversion');
            $metricsList->addMetric($metric);
        }
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

        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1', 'orderByName' => true], $default = []);

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

    public function addMetricSemanticTypes(array &$types): void
    {
        $goalMetricTypes = array(
            'orders'            => Dimension::TYPE_NUMBER,
            'ecommerce_revenue' => Dimension::TYPE_MONEY,
            'quantity'          => Dimension::TYPE_NUMBER,
            'revenue_subtotal'  => Dimension::TYPE_MONEY,
            'revenue_tax'       => Dimension::TYPE_MONEY,
            'revenue_shipping'  => Dimension::TYPE_MONEY,
            'revenue_discount'  => Dimension::TYPE_MONEY,
            'avg_order_revenue' => Dimension::TYPE_MONEY,
            'items'             => Dimension::TYPE_NUMBER,
        );

        $types = array_merge($types, $goalMetricTypes);
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

        // TODO: comment
        $idGoalPlaceholder = -99;

        $idSites = $info['parameters']['idSites'] ?? null;
        $idSite = $info['parameters']['idSite'] ?? null;

        if (empty($idSite) && !empty($idSites)) { // see API/API::getReportMetadata()
            if (is_array($idSites)) {
                $idSite = array_shift($idSites);
            } else {
                $idSite = $idSites;
            }
        }

        $allIdGoals = API::getInstance()->getGoals($idSite);
        $allIdGoals = array_keys($allIdGoals);

        $reportsWithGoals = self::getAllReportsWithGoalMetrics();

        foreach ($reports as $index => &$apiReportToUpdate) {
            $reportHasGoals = false;
            foreach ($reportsWithGoals as $reportWithGoals) {
                if (
                    $apiReportToUpdate['module'] == $reportWithGoals['module']
                    && $apiReportToUpdate['action'] == $reportWithGoals['action']
                    && empty($apiReportToUpdate['parameters'])
                ) {
                    $reportHasGoals = true;
                    break;
                }
            }

            if (!$reportHasGoals) {
                continue;
            }

            if (!is_array($apiReportToUpdate)) { // TODO: remove after done debugging
                throw new \Exception("found empty report at $index");
            }

            // collect extra metrics and processed metrics
            $extraProcessedMetrics = [
                new \Piwik\Plugins\Goals\Columns\Metrics\RevenuePerVisit($allIdGoals),
            ];

            $goalMetrics = [
                'nb_conversions' => [
                    'name' => Piwik::translate('Goals_ColumnConversions'),
                    'type' => Dimension::TYPE_NUMBER,
                    'aggregation' => Metric::AGGREGATION_TYPE_SUM,
                ],
                'revenue' => [
                    'name' => Piwik::translate('General_ColumnRevenue'),
                    'type' => Dimension::TYPE_MONEY,
                    'aggregation' => Metric::AGGREGATION_TYPE_SUM,
                ],
            ];

            $goalProcessedMetrics = [
                new RevenuePerVisit($idSite, $idGoalPlaceholder),
                new ConversionRate($idSite, $idGoalPlaceholder),
            ];

            // add special goal metrics for Actions page reports
            $reportApi = $reportWithGoals['module'] . '.' . $reportWithGoals['action'];
            $isPageReport = in_array($reportApi, AddColumnsProcessedMetricsGoal::ACTIONS_PAGE_REPORTS_WITH_GOAL_METRICS);
            $isEntryPageReport = in_array($reportApi, AddColumnsProcessedMetricsGoal::ACTIONS_ENTRY_PAGE_REPORTS_WITH_GOAL_METRICS);

            if ($isPageReport) {
                unset($goalMetrics['revenue']);

                $goalMetrics['nb_conversions_attrib'] = [
                    'name' => Piwik::translate('Goals_ColumnConversions'),
                    'type' => Dimension::TYPE_NUMBER,
                    'aggregation' => null,
                ];

                $goalMetrics['revenue_attrib'] = [
                    'name' => Piwik::translate('General_ColumnRevenue'),
                    'type' => Dimension::TYPE_MONEY,
                    'aggregation' => null,
                ];

                $goalProcessedMetrics[] = new ConversionPageRate($idSite, $idGoalPlaceholder);
            } elseif ($isEntryPageReport) {
                $goalMetrics['nb_conversions_entry'] = [
                    'name' => Piwik::translate('Goals_ColumnConversions'),
                    'type' => Dimension::TYPE_NUMBER,
                    'aggregation' => Metric::AGGREGATION_TYPE_SUM,
                ];

                $goalMetrics['revenue_entry'] = [
                    'name' => Piwik::translate('General_ColumnRevenue'),
                    'type' => Dimension::TYPE_MONEY,
                    'aggregation' => Metric::AGGREGATION_TYPE_SUM,
                ];

                $goalProcessedMetrics[] = new RevenuePerEntry($idSite, $idGoalPlaceholder);
                $goalProcessedMetrics[] = new ConversionEntryRate($idSite, $idGoalPlaceholder);
            }

            // add ecommerce metrics if idGoal is an ecommerce goal
            $idGoal = \Piwik\Request::fromRequest()->getParameter('idGoal', '');
            if (
                $idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER
                || $idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART
            ) {
                $goalMetrics['items'] = [
                    'name' => Piwik::translate('General_PurchasedProducts'),
                    'type' => Dimension::TYPE_NUMBER,
                    'aggregation' => Metric::AGGREGATION_TYPE_SUM,
                ];

                $extraProcessedMetrics[] = new AverageOrderRevenue($idSite, $idGoalPlaceholder);
            }

            // add the metrics to the report metadata
            foreach ($extraProcessedMetrics as $metric) {
                $name = $metric->getName();

                $apiReportToUpdate['processedMetrics'][$name] = trim($metric->getTranslatedName());
                $apiReportToUpdate['metricTypes'][$name] = $metric->getSemanticType() ?: 'unspecified';
                $apiReportToUpdate['processedMetricFormulas'][$name] = $metric->getFormula();
                $apiReportToUpdate['temporaryMetricSemanticTypes'] = array_merge(
                    $apiReportToUpdate['temporaryMetricSemanticTypes'] ?: [],
                    $metric->getExtraMetricSemanticTypes()
                );
                $apiReportToUpdate['temporaryMetricAggregationTypes'] = array_merge(
                    $apiReportToUpdate['temporaryMetricAggregationTypes'] ?: [],
                    $metric->getExtraMetricAggregationTypes()
                );
            }

            foreach ($goalMetrics as $metricId => $metricInfo) {
                if (!empty($metricInfo['name'])) {
                    $apiReportToUpdate['metricsGoal'][$metricId] = $metricInfo['name'];
                }

                if (!empty($metricInfo['type'])) {
                    $apiReportToUpdate['metricTypesGoal'][$metricId] = $metricInfo['type'];
                }

                if (!empty($metricInfo['aggregation'])) {
                    $apiReportToUpdate['metricAggregationTypesGoal'][$metricId] = $metricInfo['aggregation'];
                }
            }

            foreach ($goalProcessedMetrics as $metric) {
                $name = $metric->getName();
                $name = preg_replace('/^goal_.*?_/', '', $name);

                $formula = $metric->getFormula();
                $formula = str_replace('["idgoal=' . $idGoalPlaceholder . '"]', '["idgoal={idGoal}"]', $formula);
                $formula = str_replace('goal_' . $idGoalPlaceholder . '_', 'goal_{idGoal}_', $formula);

                $apiReportToUpdate['processedMetricsGoal'][$name] = ucfirst(trim($metric->getTranslatedName()));
                $apiReportToUpdate['metricTypesGoal'][$name] = $metric->getSemanticType() ?: 'unspecified';
                $apiReportToUpdate['processedMetricFormulasGoal'][$name] = $formula;
                $apiReportToUpdate['temporaryMetricSemanticTypesGoal'] = array_merge(
                    $apiReportToUpdate['temporaryMetricSemanticTypesGoal'] ?? [],
                    $metric->getExtraMetricSemanticTypes()
                );
                $apiReportToUpdate['temporaryMetricAggregationTypesGoal'] = array_merge(
                    $apiReportToUpdate['temporaryMetricAggregationTypesGoal'] ?? [],
                    $metric->getExtraMetricAggregationTypes()
                );
            }
        }
    }

    private static function getAllReportsWithGoalMetrics()
    {
        $reportsWithGoals = array();

        $reports = new ReportsProvider();

        foreach ($reports->getAllReports() as $report) {
            if ($report->hasGoalMetrics() && $report->isEnabled()) {
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

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Goals/stylesheets/goals.less";
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
        $translationKeys[] = 'Goals_Ecommerce';
        $translationKeys[] = 'Goals_Optional';
        $translationKeys[] = 'Goals_TimeInMinutes';
        $translationKeys[] = 'Goals_Pattern';
        $translationKeys[] = 'Goals_ClickToViewThisGoal';
        $translationKeys[] = 'Goals_ManageGoals';
        $translationKeys[] = 'Goals_GoalName';
        $translationKeys[] = 'Goals_GoalIsTriggeredWhen';
        $translationKeys[] = 'Goals_ThereIsNoGoalToManage';
        $translationKeys[] = 'Goals_ManuallyTriggeredUsingJavascriptFunction';
        $translationKeys[] = 'Goals_VisitUrl';
        $translationKeys[] = 'Goals_ClickOutlink';
        $translationKeys[] = 'Goals_SendEvent';
        $translationKeys[] = 'Goals_GoalIsTriggered';
        $translationKeys[] = 'Goals_WhereThe';
        $translationKeys[] = 'Goals_URL';
        $translationKeys[] = 'Goals_Contains';
        $translationKeys[] = 'Goals_IsExactly';
        $translationKeys[] = 'Goals_MatchesExpression';
        $translationKeys[] = 'Goals_AllowMultipleConversionsPerVisit';
        $translationKeys[] = 'Goals_HelpOneConversionPerVisit';
        $translationKeys[] = 'Goals_DefaultRevenueHelp';
        $translationKeys[] = 'Goals_DefaultRevenueLabel';
        $translationKeys[] = 'Goals_GoalRevenue';
        $translationKeys[] = 'Goals_Filename';
        $translationKeys[] = 'Goals_ExternalWebsiteUrl';
        $translationKeys[] = 'Goals_VisitDuration';
        $translationKeys[] = 'Goals_AtLeastMinutes';
        $translationKeys[] = 'Goals_VisitPageTitle';
        $translationKeys[] = 'Intl_NMinutes';
        $translationKeys[] = 'Goals_PageTitle';
        $translationKeys[] = 'Goals_UseEventValueAsRevenue';
        $translationKeys[] = 'Goals_EventValueAsRevenueHelp';
        $translationKeys[] = 'Goals_EventValueAsRevenueHelp2';
        $translationKeys[] = 'Events_EventCategory';
        $translationKeys[] = 'Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore';
        $translationKeys[] = 'Goals_LearnMoreAboutGoalTrackingDocumentation';
        $translationKeys[] = 'Goals_EcommerceReports';
        $translationKeys[] = 'SitesManager_WebsitesManagement';
        $translationKeys[] = 'Goals_CaseSensitive';
        $translationKeys[] = 'Goals_Download';
        $translationKeys[] = 'Events_EventAction';
        $translationKeys[] = 'Events_EventName';
        $translationKeys[] = 'Goals_YouCanEnableEcommerceReports';
        $translationKeys[] = 'Goals_CategoryTextGeneral_Actions';
        $translationKeys[] = 'General_ForExampleShort';
        $translationKeys[] = 'General_Id';
        $translationKeys[] = 'General_Description';
        $translationKeys[] = 'General_ColumnRevenue';
        $translationKeys[] = 'General_Edit';
        $translationKeys[] = 'General_Delete';
        $translationKeys[] = 'General_OperationGreaterThan';
        $translationKeys[] = 'General_Yes';
        $translationKeys[] = 'General_No';
        $translationKeys[] = 'General_OrCancel';
    }
}
