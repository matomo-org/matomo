<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugin\Manager;
use Piwik\Tracker\GoalManager;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Tracker\Action;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\RankingQuery;

class Archiver extends \Piwik\Plugin\Archiver
{
    const VISITS_UNTIL_RECORD_NAME = 'visits_until_conv';
    const DAYS_UNTIL_CONV_RECORD_NAME = 'days_until_conv';
    const ITEMS_SKU_RECORD_NAME = 'Goals_ItemsSku';
    const ITEMS_NAME_RECORD_NAME = 'Goals_ItemsName';
    const ITEMS_CATEGORY_RECORD_NAME = 'Goals_ItemsCategory';
    const PAGE_CONVERSIONS_URL_RECORD_NAME = 'Goal_page_conversions_url';
    const PAGE_CONVERSIONS_TITLES_RECORD_NAME = 'Goal_page_conversions_titles';
    const PAGE_CONVERSIONS_ENTRY_RECORD_NAME = 'Goal_page_conversions_entry';
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
    const SECONDS_SINCE_FIRST_VISIT_FIELD = 'visitor_seconds_since_first';

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
    protected $dimensionRecord = [
        self::SKU_FIELD      => self::ITEMS_SKU_RECORD_NAME,
        self::NAME_FIELD     => self::ITEMS_NAME_RECORD_NAME,
        self::CATEGORY_FIELD => self::ITEMS_CATEGORY_RECORD_NAME
    ];
    protected $actionMapping = [
        self::SKU_FIELD      => 'idaction_product_sku',
        self::NAME_FIELD     => 'idaction_product_name',
        self::CATEGORY_FIELD => 'idaction_product_cat',
        self::CATEGORY2_FIELD => 'idaction_product_cat2',
        self::CATEGORY3_FIELD => 'idaction_product_cat3',
        self::CATEGORY4_FIELD => 'idaction_product_cat4',
        self::CATEGORY5_FIELD => 'idaction_product_cat5',
    ];

    /**
     * Array containing one DataArray for each Ecommerce items dimension (name/sku/category abandoned carts and orders)
     * @var DataArray[][]
     */
    protected $itemReports = [];

    /**
     * @var int
     */
    private $productReportsMaximumRows;

    public function __construct(ArchiveProcessor $processor)
    {
        parent::__construct($processor);

        $general = Config::getInstance()->General;
        $this->productReportsMaximumRows = $general['datatable_archiving_maximum_rows_products'];
    }

    public function aggregateDayReport()
    {
        $this->aggregateGeneralGoalMetrics();

        if (Manager::getInstance()->isPluginActivated('Ecommerce')) {
            $this->aggregateEcommerceItems();
        }

        $this->getProcessor()->processDependentArchive('Goals', VisitFrequencyAPI::NEW_VISITOR_SEGMENT);
        $this->getProcessor()->processDependentArchive('Goals', VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT);
        $this->aggregatePageGoalsDayReports();
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
            'FLOOR(log_conversion.' . self::SECONDS_SINCE_FIRST_VISIT_FIELD . ' / 86400)', self::$daysToConvRanges, self::LOG_CONVERSION_TABLE, $prefixes[self::DAYS_UNTIL_CONV_RECORD_NAME]
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
            if ($query !== false) {
                $this->aggregateFromEcommerceItems($query, $dimension);
            }

            $query = $this->queryItemViewsForDimension($dimension);
            if ($query !== false) {
                $this->aggregateFromEcommerceViews($query, $dimension);
            }
        }
        $this->insertItemReports();
        return true;
    }

    protected function queryItemViewsForDimension($dimension)
    {
        $column = $this->actionMapping[$dimension];
        $where  = "log_link_visit_action.$column is not null";

        return $this->getLogAggregator()->queryActionsByDimension(
            ['label' => 'log_action1.name'],
            $where,
            ['AVG(log_link_visit_action.product_price) AS `avg_price_viewed`'],
            false,
            null,
            [$column]
        );
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
        foreach ($this->itemReports as $dimension => $itemAggregatesByType) {
            foreach ($itemAggregatesByType as $ecommerceType => $itemAggregate) {
                $recordName = $this->dimensionRecord[$dimension];
                if ($ecommerceType == GoalManager::IDGOAL_CART) {
                    $recordName = self::getItemRecordNameAbandonedCart($recordName);
                }
                $table = $itemAggregate->asDataTable();
                $blobData = $table->getSerialized($this->productReportsMaximumRows, $this->productReportsMaximumRows,
                    Metrics::INDEX_ECOMMERCE_ITEM_REVENUE);
                $this->getProcessor()->insertBlobRecord($recordName, $blobData);

                Common::destroy($table);
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

    protected function aggregateFromEcommerceViews($query, $dimension)
    {
        while ($row = $query->fetch()) {

            $label = $this->getRowLabel($row, $dimension);
            if ($label === false) {
                continue; // ignore empty additional categories
            }

            // Aggregate extra categories in the Item categories array
            if ($this->isItemExtraCategory($dimension)) {
                $array = $this->itemReports[self::CATEGORY_FIELD];
            } else {
                $array = $this->itemReports[$dimension];
            }

            unset($row['label']);
            $row['avg_price_viewed'] = round($row['avg_price_viewed'], GoalManager::REVENUE_PRECISION);

            // add views to all types
            foreach ($array as $ecommerceType => $dataArray) {
                $dataArray->sumMetrics($label, $row);
            }
        }
    }

    protected function cleanupRowGetLabel(&$row, $currentField)
    {
        $label = $this->getRowLabel($row, $currentField);

        if (isset($row['ecommerceType']) && $row['ecommerceType'] == GoalManager::IDGOAL_CART) {
            // abandoned carts are the number of visits with an abandoned cart
            $row[Metrics::INDEX_ECOMMERCE_ORDERS] = $row[Metrics::INDEX_NB_VISITS];
        }

        unset($row[Metrics::INDEX_NB_VISITS]);
        unset($row['label']);
        unset($row['labelIdAction']);
        unset($row['ecommerceType']);

        return $label;
    }

    protected function getRowLabel(&$row, $currentField)
    {
        $label = $row['label'];
        if (empty($label)) {
            // An empty additional category -> skip this iteration
            if ($this->isItemExtraCategory($currentField)) {
                return false;
            }
            $label = "Value not defined";
        }
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
        $columnsAggregationOperation = null;

        $this->getProcessor()->aggregateDataTableRecords($dataTableToSum,
            $maximumRowsInDataTableLevelZero = $this->productReportsMaximumRows,
            $maximumRowsInSubDataTable = $this->productReportsMaximumRows,
            $columnToSortByBeforeTruncation = Metrics::INDEX_ECOMMERCE_ITEM_REVENUE,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array());

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
            foreach ($metricsToSum as $metricName) {
                $fieldsToSum[] = self::getRecordName($metricName, $goalId);
            }
        }
        $this->getProcessor()->aggregateNumericMetrics($fieldsToSum);

        $columnsAggregationOperation = null;

        foreach ($goalIdsToSum as $goalId) {
            // sum up the visits to conversion data table & the days to conversion data table
            $this->getProcessor()->aggregateDataTableRecords(
                array(self::getRecordName(self::VISITS_UNTIL_RECORD_NAME, $goalId),
                      self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME, $goalId)),
                $maximumRowsInDataTableLevelZero = null,
                $maximumRowsInSubDataTable = null,
                $columnToSortByBeforeTruncation = null,
                $columnsAggregationOperation,
                $columnsToRenameAfterAggregation = null,
                $countRowsRecursive = array());
        }

        $columnsAggregationOperation = null;
        // sum up goal overview reports
        $this->getProcessor()->aggregateDataTableRecords(
                array(self::getRecordName(self::VISITS_UNTIL_RECORD_NAME),
                      self::getRecordName(self::DAYS_UNTIL_CONV_RECORD_NAME)),
                $maximumRowsInDataTableLevelZero = null,
                $maximumRowsInSubDataTable = null,
                $columnToSortByBeforeTruncation = null,
                $columnsAggregationOperation,
                $columnsToRenameAfterAggregation = null,
                $countRowsRecursive = array());

        $this->getProcessor()->processDependentArchive('Goals', VisitFrequencyAPI::NEW_VISITOR_SEGMENT);
        $this->getProcessor()->processDependentArchive('Goals', VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT);
        $this->aggregatePageGoalsMultipleReports();
    }

    /**
     * @var DataArray[]
     */
    protected $arrays = array();

    public function aggregatePageGoalsDayReports()
    {

        $maximumRowsInDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_events'];
        $maximumRowsInSubDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_events'];

        // Generate page visits data tables and add goal conversions fields
        $this->aggregatePageConversions('idaction_url', self::PAGE_CONVERSIONS_URL_RECORD_NAME, Action::TYPE_PAGE_URL);
        $this->aggregatePageConversions('idaction_name', self::PAGE_CONVERSIONS_TITLES_RECORD_NAME, Action::TYPE_PAGE_TITLE);
        $this->aggregateEntryConversions();

        // Enrich the metrics
        foreach ($this->arrays as $dataArray) {
            $dataArray->enrichMetricsWithConversions();
        }

        // Write the blobs
        foreach ($this->arrays as $recordName => $dataArray) {
            $dataTable = $dataArray->asDataTable();
            if ($recordName == self::PAGE_CONVERSIONS_ENTRY_RECORD_NAME) {
                $columnToSortByBeforeTruncation = Metrics::INDEX_PAGE_ENTRY_NB_VISITS;
            } else {
                $columnToSortByBeforeTruncation = Metrics::INDEX_NB_UNIQ_VISITORS;
            }

            $blob = $dataTable->getSerialized(
                $maximumRowsInDataTable,
                $maximumRowsInSubDataTable,
                $columnToSortByBeforeTruncation);
            $this->getProcessor()->insertBlobRecord($recordName, $blob);
        }

    }

    /**
     * Populate a datatable with page conversions for either page URLs or page titles
     *
     * @param string $linkField   'idaction_url' or 'idaction_name'
     * @param string $recordName
     * @param int $actionType
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function aggregatePageConversions(string $linkField, string $recordName, int $actionType)
    {

        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();

        $metricsConfig = array(
            PiwikMetrics::INDEX_NB_UNIQ_VISITORS => array(
                'aggregation' => false,
                'query' => "count(distinct log_link_visit_action.idvisitor)",
            ),
        );

        $select = "log_action.name,
                log_action.type,
                log_action.idaction,
                log_action.url_prefix
                ";

        $select = $this->addMetricsToSelect($select, $metricsConfig);

        $from = array(
            "log_link_visit_action",
            array(
                "table" => "log_action",
                "joinOn" => "log_link_visit_action.%s = log_action.idaction"
            )
        );

        $where  = $this->getLogAggregator()->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.%s IS NOT NULL AND log_link_visit_action.idaction_event_category IS NULL";

        $actionTypesWhere = "log_action.type IN (" . implode(", ", [$actionType]) . ")";
        $where .= " AND $actionTypesWhere";

        $groupBy = "log_link_visit_action.idvisit";
        $orderBy = "`" . PiwikMetrics::INDEX_NB_UNIQ_VISITORS . "` DESC, name ASC";

        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(array('idaction', 'name'));
            $rankingQuery->addColumn('url_prefix');

            $this->addMetricsToRankingQuery($rankingQuery, $metricsConfig);

            $rankingQuery->partitionResultIntoMultipleGroups('type', (array)[$actionType]);
        }

        $this->pageGoalsGetVisits($select, $from, $where, $groupBy, $orderBy, $linkField, $rankingQuery);

        // We now have a data array of pages with the unique visit count
        // Next need to perform a separate query to get the goal conversion metrics and add them to the data array

        $query = $this->getLogAggregator()->queryConversionsByPageView($linkField);

        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $this->getDataArray($recordName)->sumMetricsGoalsPages($row[$linkField], $row, true);
        }

    }

    /**
     * Populate a datatable with entry page conversions
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function aggregateEntryConversions()
    {

        $select = "count(distinct log_visit.idvisitor) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS . "`,
                count(*) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS . "`,
                log_action.idaction,
                log_action.name,
                log_action.type,
                log_action.url_prefix
                ";

        $from = array(
            "log_visit",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_visit.visit_entry_idaction_url = log_action.idaction"
                )
        );

        $where  = $this->getLogAggregator()->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= " AND log_visit.%s > 0";

        $groupBy = "log_visit.%s";

        $orderBy = "`" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS . "` DESC";

        $rankingQuery = false;
        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(array('idaction', 'name'));
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS);
            $rankingQuery->addColumn(array(PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS), 'sum');
        }

        $this->pageGoalsGetVisits($select, $from, $where, $groupBy, $orderBy, 'visit_entry_idaction_url', $rankingQuery);

        $query = $this->getLogAggregator()->queryConversionsByEntryPageView();

        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $this->getDataArray(self::PAGE_CONVERSIONS_ENTRY_RECORD_NAME)->sumMetricsGoalsPages($row['idaction_url'], $row, false);
        }
    }

    protected function pageGoalsGetVisits($select, $from, $where, $groupBy, $orderBy, $sprintfField, RankingQuery $rankingQuery = null)
    {
        $select = sprintf($select, $sprintfField);

        // get query with segmentation
        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // replace the rest of the %s
        $querySql = str_replace("%s", $sprintfField, $query['sql']);

        // apply ranking query
        if ($rankingQuery) {
            $querySql = $rankingQuery->generateRankingQuery($querySql);
        }

        $dataTable = new DataTable();
        $dataTable->setMaximumAllowedRows(ArchivingHelper::$maximumRowsInDataTableLevelZero);

        // get result
        $resultSet = $this->getLogAggregator()->getDb()->query($querySql, $query['bind']);

        if ($resultSet === false) {
            return;
        }

        while ($row = $resultSet->fetch()) {

            if ($sprintfField == 'idaction_url') {
                $this->aggregateRow($row, self::PAGE_CONVERSIONS_URL_RECORD_NAME);
            } else if ($sprintfField == 'idaction_name') {
                $this->aggregateRow($row, self::PAGE_CONVERSIONS_TITLES_RECORD_NAME);
            } else if ($sprintfField == 'visit_entry_idaction_url') {
                $this->aggregateRow($row, self::PAGE_CONVERSIONS_ENTRY_RECORD_NAME);
            }
        }

    }

    /**
     * @param string $name
     * @return DataArray
     */
    protected function getDataArray($name)
    {
        if (empty($this->arrays[$name])) {
            $this->arrays[$name] = new DataArray();
        }
        return $this->arrays[$name];
    }

    protected function aggregateRow($row, $recordName)
    {
        $dataArray = $this->getDataArray($recordName);
        $mainLabel = $row['name'];
        unset($row['name']);
        $dataArray->sumMetrics($mainLabel, $row);
    }

    private function addMetricsToSelect($select, $metricsConfig)
    {
        if (!empty($metricsConfig)) {
            foreach ($metricsConfig as $metric => $config) {
                $select .= ', ' . $config['query'] . " as `" . $metric . "`";
            }
        }

        return $select;
    }

    private function addMetricsToRankingQuery(RankingQuery $rankingQuery, $metricsConfig)
    {
        foreach ($metricsConfig as $metric => $config) {
            if (!empty($config['aggregation'])) {
                $rankingQuery->addColumn($metric, $config['aggregation']);
            } else {
                $rankingQuery->addColumn($metric);
            }
        }
    }


    public function aggregatePageGoalsMultipleReports()
    {

        $columnsAggregationOperation = null;
        $this->getProcessor()->aggregateDataTableRecords(
            [
                self::PAGE_CONVERSIONS_URL_RECORD_NAME,
                self::PAGE_CONVERSIONS_TITLES_RECORD_NAME,
                self::PAGE_CONVERSIONS_ENTRY_RECORD_NAME,
            ],
            $this->maximumRows,
            $maximumRowsInSubDataTable = null,
            $columnToSortByBeforeTruncation = null,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array()
        );


    }

}
