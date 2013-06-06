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

class Piwik_Goals_Archiving
{
    const VISITS_UNTIL_RECORD_NAME = 'visits_until_conv';
    const DAYS_UNTIL_CONV_RECORD_NAME = 'days_until_conv';
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
    protected $dimensions = array(
        'idaction_sku'      => 'Goals_ItemsSku',
        'idaction_name'     => 'Goals_ItemsName',
        'idaction_category' => 'Goals_ItemsCategory'
    );

    /**
     * @param $archiveProcessing
     */
    public function archiveDay($archiveProcessing)
    {
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
        $overall = $archiveProcessing->makeEmptyGoalRow($idGoal = 1);
        while ($row = $query->fetch()) {
            $idgoal = $row['idgoal'];

            if (!isset($goals[$idgoal])) {
                $goals[$idgoal] = $archiveProcessing->makeEmptyGoalRow($idgoal);

                $visitsToConvReport[$idgoal] = new Piwik_DataTable();
                $daysToConvReport[$idgoal] = new Piwik_DataTable();
            }
            $archiveProcessing->sumGoalMetrics($row, $goals[$idgoal]);

            // We don't want to sum Abandoned cart metrics in the overall revenue/conversions/converted visits
            // since it is a "negative conversion"
            if ($idgoal != Piwik_Tracker_GoalManager::IDGOAL_CART) {
                $archiveProcessing->sumGoalMetrics($row, $overall);
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
                        $label = Piwik_CustomVariables_Archiving::LABEL_CUSTOM_VALUE_NOT_DEFINED;
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

    /**
     * @param $archiveProcessing
     */
    public function archivePeriod($archiveProcessing)
    {
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

    static public function getItemRecordNameAbandonedCart($recordName)
    {
        return $recordName . '_Cart';
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

    private function getConversionRate($count, $archiveProcessing)
    {
        $visits = $archiveProcessing->getNumberOfVisits();
        return round(100 * $count / $visits, Piwik_Tracker_GoalManager::REVENUE_PRECISION);
    }

}