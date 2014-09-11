<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals;

use Piwik\DataAccess\LogAggregator;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Tracker\GoalManager;

class Archiver extends \Piwik\Plugin\Archiver
{
    const VISITS_UNTIL_RECORD_NAME = 'visits_until_conv';
    const DAYS_UNTIL_CONV_RECORD_NAME = 'days_until_conv';
    const ITEMS_SKU_RECORD_NAME = 'Goals_ItemsSku';
    const ITEMS_NAME_RECORD_NAME = 'Goals_ItemsName';
    const ITEMS_CATEGORY_RECORD_NAME = 'Goals_ItemsCategory';
    const SKU_FIELD = 'idaction_sku';
    const NAME_FIELD = 'idaction_name';
    const CATEGORY_FIELD = 'idaction_category';
    const CATEGORY2_FIELD = 'idaction_category2';
    const CATEGORY3_FIELD = 'idaction_category3';
    const CATEGORY4_FIELD = 'idaction_category4';
    const CATEGORY5_FIELD = 'idaction_category5';
    const NO_LABEL = ':';
    const LOG_CONVERSION_TABLE = 'log_conversion';
    const VISITS_COUNT_FIELD = 'visitor_count_visits';
    const DAYS_SINCE_FIRST_VISIT_FIELD = 'visitor_days_since_first';
    /**
     * This array stores the ranges to use when displaying the 'visits to conversion' report
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
     * This array stores the ranges to use when displaying the 'days to conversion' report
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
    protected $dimensionRecord = array(
        self::SKU_FIELD      => self::ITEMS_SKU_RECORD_NAME,
        self::NAME_FIELD     => self::ITEMS_NAME_RECORD_NAME,
        self::CATEGORY_FIELD => self::ITEMS_CATEGORY_RECORD_NAME
    );

    /**
     * Array containing one DataArray for each Ecommerce items dimension (name/sku/category abandoned carts and orders)
     * @var array
     */
    protected $itemReports = array();

    public function aggregateDayReport()
    {
        $this->aggregateGeneralGoalMetrics();
        $this->aggregateEcommerceItems();
    }

    protected function aggregateGeneralGoalMetrics()
    {
        $prefixes = array(
            self::VISITS_UNTIL_RECORD_NAME    => 'vcv',
            self::DAYS_UNTIL_CONV_RECORD_NAME => 'vdsf',
        );

        $selects = array();
        $selects = array_merge($selects, LogAggregator::getSelectsFromRangedColumn(
            self::VISITS_COUNT_FIELD, self::$visitCountRanges, self::LOG_CONVERSION_TABLE, $prefixes[self::VISITS_UNTIL_RECORD_NAME]
        ));
        $selects = array_merge($selects, LogAggregator::getSelectsFromRangedColumn(
            self::DAYS_SINCE_FIRST_VISIT_FIELD, self::$daysToConvRanges, self::LOG_CONVERSION_TABLE, $prefixes[self::DAYS_UNTIL_CONV_RECORD_NAME]
        ));

        $query = $this->getLogAggregator()->queryConversionsByDimension(array(), false, $selects);
        if ($query === false) {
            return;
        }

        $totalConversions = $totalRevenue = 0;
        $goals = new DataArray();
        $visitsToConversions = $daysToConversions = array();

        $conversionMetrics = $this->getLogAggregator()->getConversionsMetricFields();
        while ($row = $query->fetch()) {
            $idGoal = $row['idgoal'];
            unset($row['idgoal']);
            unset($row['label']);

            $values = array();
            foreach ($conversionMetrics as $field => $statement) {
                $values[$field] = $row[$field];
            }
            $goals->sumMetrics($idGoal, $values);

            if (empty($visitsToConversions[$idGoal])) {
                $visitsToConversions[$idGoal] = new DataTable();
            }
            $array = LogAggregator::makeArrayOneColumn($row, Metrics::INDEX_NB_CONVERSIONS, $prefixes[self::VISITS_UNTIL_RECORD_NAME]);
            $visitsToConversions[$idGoal]->addDataTable(DataTable::makeFromIndexedArray($array));

            if (empty($daysToConversions[$idGoal])) {
                $daysToConversions[$idGoal] = new DataTable();
            }
            $array = LogAggregator::makeArrayOneColumn($row, Metrics::INDEX_NB_CONVERSIONS, $prefixes[self::DAYS_UNTIL_CONV_RECORD_NAME]);
            $daysToConversions[$idGoal]->addDataTable(DataTable::makeFromIndexedArray($array));

            // We don't want to sum Abandoned cart metrics in the overall revenue/conversions/converted visits
            // since it is a "negative conversion"
            if ($idGoal != GoalManager::IDGOAL_CART) {
                $totalConversions += $row[Metrics::INDEX_GOAL_NB_CONVERSIONS];
                $totalRevenue += $row[Metrics::INDEX_GOAL_REVENUE];
            }
        }

        // Stats by goal, for all visitors
        $numericRecords = $this->getConversionsNumericMetrics($goals);
        $this->getProcessor()->insertNumericRecords($numericRecords);

        $this->insertReports(self::VISITS_UNTIL_RECORD_NAME, $visitsToConversions);
        $this->insertReports(self::DAYS_UNTIL_CONV_RECORD_NAME, $daysToConversions);

        // Stats for all goals
        $nbConvertedVisits = $this->getProcessor()->getNumberOfVisitsConverted();
        $metrics = array(
            self::getRecordName('conversion_rate')     => $this->getConversionRate($nbConvertedVisits),
            self::getRecordName('nb_conversions')      => $totalConversions,
            self::getRecordName('nb_visits_converted') => $nbConvertedVisits,
            self::getRecordName('revenue')             => $totalRevenue,
        );
        $this->getProcessor()->insertNumericRecords($metrics);
    }

    protected function getConversionsNumericMetrics(DataArray $goals)
    {
        $numericRecords = array();
        $goals = $goals->getDataArray();
        foreach ($goals as $idGoal => $array) {
            foreach ($array as $metricId => $value) {
                $metricName = Metrics::$mappingFromIdToNameGoal[$metricId];
                $recordName = self::getRecordName($metricName, $idGoal);
                $numericRecords[$recordName] = $value;
            }
            if (!empty($array[Metrics::INDEX_GOAL_NB_VISITS_CONVERTED])) {
                $conversion_rate = $this->getConversionRate($array[Metrics::INDEX_GOAL_NB_VISITS_CONVERTED]);
                $recordName = self::getRecordName('conversion_rate', $idGoal);
                $numericRecords[$recordName] = $conversion_rate;
            }
        }
        return $numericRecords;
    }

    /**
     * @param string $recordName 'nb_conversions'
     * @param int|bool $idGoal idGoal to return the metrics for, or false to return overall
     * @return string Archive record name
     */
    public static function getRecordName($recordName, $idGoal = false)
    {
        $idGoalStr = '';
        if ($idGoal !== false) {
            $idGoalStr = $idGoal . "_";
        }
        return 'Goal_' . $idGoalStr . $recordName;
    }

    protected function getConversionRate($count)
    {
        $visits = $this->getProcessor()->getNumberOfVisits();
        return round(100 * $count / $visits, GoalManager::REVENUE_PRECISION);
    }

    protected function insertReports($recordName, $visitsToConversions)
    {
        foreach ($visitsToConversions as $idGoal => $table) {
            $record = self::getRecordName($recordName, $idGoal);
            $this->getProcessor()->insertBlobRecord($record, $table->getSerialized());
        }
        $overviewTable = $this->getOverviewFromGoalTables($visitsToConversions);
        $this->getProcessor()->insertBlobRecord(self::getRecordName($recordName), $overviewTable->getSerialized());
    }

    protected function getOverviewFromGoalTables($tableByGoal)
    {
        $overview = new DataTable();
        foreach ($tableByGoal as $idGoal => $table) {
            if ($this->isStandardGoal($idGoal)) {
                $overview->addDataTable($table);
            }
        }
        return $overview;
    }

    protected function isStandardGoal($idGoal)
    {
        return !in_array($idGoal, $this->getEcommerceIdGoals());
    }

    protected function aggregateEcommerceItems()
    {
        $this->initItemReports();
        foreach ($this->getItemsDimensions() as $dimension) {
            $query = $this->getLogAggregator()->queryEcommerceItems($dimension);
            if ($query == false) {
                continue;
            }
            $this->aggregateFromEcommerceItems($query, $dimension);
        }
        $this->insertItemReports();
        return true;
    }

    protected function initItemReports()
    {
        foreach ($this->getEcommerceIdGoals() as $ecommerceType) {
            foreach ($this->dimensionRecord as $dimension => $record) {
                $this->itemReports[$dimension][$ecommerceType] = new DataArray();
            }
        }
    }

    protected function insertItemReports()
    {
        /** @var DataArray $array */
        foreach ($this->itemReports as $dimension => $itemAggregatesByType) {
            foreach ($itemAggregatesByType as $ecommerceType => $itemAggregate) {
                $recordName = $this->dimensionRecord[$dimension];
                if ($ecommerceType == GoalManager::IDGOAL_CART) {
                    $recordName = self::getItemRecordNameAbandonedCart($recordName);
                }
                $table = $itemAggregate->asDataTable();
                $this->getProcessor()->insertBlobRecord($recordName, $table->getSerialized());
            }
        }
    }

    protected function getItemsDimensions()
    {
        $dimensions = array_keys($this->dimensionRecord);
        foreach ($this->getItemExtraCategories() as $category) {
            $dimensions[] = $category;
        }
        return $dimensions;
    }

    protected function getItemExtraCategories()
    {
        return array(self::CATEGORY2_FIELD, self::CATEGORY3_FIELD, self::CATEGORY4_FIELD, self::CATEGORY5_FIELD);
    }

    protected function isItemExtraCategory($field)
    {
        return in_array($field, $this->getItemExtraCategories());
    }

    protected function aggregateFromEcommerceItems($query, $dimension)
    {
        while ($row = $query->fetch()) {
            $ecommerceType = $row['ecommerceType'];

            $label = $this->cleanupRowGetLabel($row, $dimension);
            if ($label === false) {
                continue;
            }

            // Aggregate extra categories in the Item categories array
            if ($this->isItemExtraCategory($dimension)) {
                $array = $this->itemReports[self::CATEGORY_FIELD][$ecommerceType];
            } else {
                $array = $this->itemReports[$dimension][$ecommerceType];
            }

            $this->roundColumnValues($row);
            $array->sumMetrics($label, $row);
        }
    }

    protected function cleanupRowGetLabel(&$row, $currentField)
    {
        $label = $row['label'];
        if (empty($label)) {
            // An empty additional category -> skip this iteration
            if ($this->isItemExtraCategory($currentField)) {
                return false;
            }
            $label = "Value not defined";
            // Product Name/Category not defined"
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables')) {
                $label = \Piwik\Plugins\CustomVariables\Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED;
            }
        }

        if ($row['ecommerceType'] == GoalManager::IDGOAL_CART) {
            // abandoned carts are the numner of visits with an abandoned cart
            $row[Metrics::INDEX_ECOMMERCE_ORDERS] = $row[Metrics::INDEX_NB_VISITS];
        }

        unset($row[Metrics::INDEX_NB_VISITS]);
        unset($row['label']);
        unset($row['labelIdAction']);
        unset($row['ecommerceType']);

        return $label;
    }

    protected function roundColumnValues(&$row)
    {
        $columnsToRound = array(
            Metrics::INDEX_ECOMMERCE_ITEM_REVENUE,
            Metrics::INDEX_ECOMMERCE_ITEM_QUANTITY,
            Metrics::INDEX_ECOMMERCE_ITEM_PRICE,
            Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED,
        );
        foreach ($columnsToRound as $column) {
            if (isset($row[$column])
                && $row[$column] == round($row[$column])
            ) {
                $row[$column] = round($row[$column]);
            }
        }
    }

    protected function getEcommerceIdGoals()
    {
        return array(GoalManager::IDGOAL_CART, GoalManager::IDGOAL_ORDER);
    }

    public static function getItemRecordNameAbandonedCart($recordName)
    {
        return $recordName . '_Cart';
    }

    /**
     * @internal param $this->getProcessor()
     */
    public function aggregateMultipleReports()
    {
        /*
         * Archive Ecommerce Items
         */
        $dataTableToSum = $this->dimensionRecord;
        foreach ($this->dimensionRecord as $recordName) {
            $dataTableToSum[] = self::getItemRecordNameAbandonedCart($recordName);
        }
        $this->getProcessor()->aggregateDataTableRecords($dataTableToSum);

        /*
         *  Archive General Goal metrics
         */
        $goalIdsToSum = GoalManager::getGoalIds($this->getProcessor()->getParams()->getSite()->getId());

        //Ecommerce
        $goalIdsToSum[] = GoalManager::IDGOAL_ORDER;
        $goalIdsToSum[] = GoalManager::IDGOAL_CART; //bug here if idgoal=1
        // Overall goal metrics
        $goalIdsToSum[] = false;

        $fieldsToSum = array();
        foreach ($goalIdsToSum as $goalId) {
            $metricsToSum = Goals::getGoalColumns($goalId);
            unset($metricsToSum[array_search('conversion_rate', $metricsToSum)]);
            foreach ($metricsToSum as $metricName) {
                $fieldsToSum[] = self::getRecordName($metricName, $goalId);
            }
        }
        $records = $this->getProcessor()->aggregateNumericMetrics($fieldsToSum);

        // also recording conversion_rate for each goal
        foreach ($goalIdsToSum as $goalId) {
            $nb_conversions = $records[self::getRecordName('nb_visits_converted', $goalId)];
            $conversion_rate = $this->getConversionRate($nb_conversions);
            $this->getProcessor()->insertNumericRecord(self::getRecordName('conversion_rate', $goalId), $conversion_rate);

            // sum up the visits to conversion data table & the days to conversion data table
            $this->getProcessor()->aggregateDataTableRecords(array(
                                                                  self::getRecordName(self::VISITS_UNTIL_RECORD_NAME, $goalId),
                                                                  self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME, $goalId)));
        }

        // sum up goal overview reports
        $this->getProcessor()->aggregateDataTableRecords(array(
                                                              self::getRecordName(self::VISITS_UNTIL_RECORD_NAME),
                                                              self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME)));
    }
}
