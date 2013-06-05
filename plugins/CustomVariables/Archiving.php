<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CustomVariables
 */

class Piwik_CustomVariables_Archiving
{
    const LABEL_CUSTOM_VALUE_NOT_DEFINED = "Value not defined";
    const BLOB_NAME = 'CustomVariables_valueByName';
    protected $metricsByKey = array();
    protected $metricsByKeyAndValue = array();
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;
    protected $newEmptyRow;

    function __construct()
    {
        //TODO FIX
        $this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_referers'];
        $this->maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referers'];
    }

    public function archiveDay(Piwik_ArchiveProcessing_Day $archiveProcessing)
    {
        $this->archiveDayAggregate($archiveProcessing);

        $table = $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->metricsByKeyAndValue, $this->metricsByKey);


        $blob = $table->getSerialized(
            $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
            $columnToSort = Piwik_Archive::INDEX_NB_VISITS
        );

        $archiveProcessing->insertBlobRecord(self::BLOB_NAME, $blob);
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @return void
     */
    protected function archiveDayAggregate(Piwik_ArchiveProcessing_Day $archiveProcessing)
    {
        for ($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            $this->aggregateCustomVariable($archiveProcessing, $i);
        }

        $this->removeVisitsMetricsFromActionsAggregate($archiveProcessing);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByKey);
        $archiveProcessing->enrichConversionsByLabelArrayHasTwoLevels($this->metricsByKeyAndValue);
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @param $slot
     */
    protected function aggregateCustomVariable(Piwik_ArchiveProcessing_Day $archiveProcessing, $slot)
    {
        $keyField = "custom_var_k" . $slot;
        $valueField = "custom_var_v" . $slot;
        $where = "%s.$keyField != ''";
        $dimensions = array($keyField, $valueField);

        $query = $archiveProcessing->queryVisitsByDimension($dimensions, $where);
        $this->aggregateFromVisits($archiveProcessing, $query, $keyField, $valueField);

        $query = $archiveProcessing->queryActionsByDimension($dimensions, $where);
        $this->aggregateFromActions($archiveProcessing, $query, $keyField, $valueField);

        $query = $archiveProcessing->queryConversionsByDimension($dimensions, $where);
        $this->aggregateFromConversions($archiveProcessing, $query, $keyField, $valueField);
    }

    protected function aggregateFromVisits(Piwik_ArchiveProcessing_Day $archiveProcessing, $query, $keyField, $valueField)
    {
        while ($row = $query->fetch()) {
            $value = $row[$valueField];
            $value = $this->cleanCustomVarValue($value);

            $key = $row[$keyField];
            if (!isset($this->metricsByKey[$key])) {
                $this->metricsByKey[$key] = $archiveProcessing->makeEmptyRow();
            }
            if (!isset($this->metricsByKeyAndValue[$key][$value])) {
                $this->metricsByKeyAndValue[$key][$value] = $archiveProcessing->makeEmptyRow();
            }

            $archiveProcessing->sumMetrics($row, $this->metricsByKey[$key]);
            $archiveProcessing->sumMetrics($row, $this->metricsByKeyAndValue[$key][$value]);
        }
    }

    protected function cleanCustomVarValue($value)
    {
        if (strlen($value)) {
            return $value;
        }
        return self::LABEL_CUSTOM_VALUE_NOT_DEFINED;
    }

    protected function aggregateFromActions(Piwik_ArchiveProcessing_Day $archiveProcessing, $query, $keyField, $valueField)
    {
        $keys = array();
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $row[$valueField];
            $value = $this->cleanCustomVarValue($value);

            $alreadyAggregated = $this->aggregateEcommerceCategories($archiveProcessing, $key, $value, $row);
            if (!$alreadyAggregated) {
                $this->aggregateAction($archiveProcessing, $key, $value, $row);

                if (!isset($this->metricsByKey[$key])) {
                    $this->metricsByKey[$key] = $archiveProcessing->makeEmptyActionRow();
                }
                $archiveProcessing->sumMetrics($row, $this->metricsByKey[$key], $onlyMetricsAvailableInActionsTable = true);
            }
        }
    }

    /**
     * @return bool True if the $row metrics were already added to the ->metrics
     */
    protected function aggregateEcommerceCategories(Piwik_ArchiveProcessing_Day $archiveProcessing, $key, $value, $row)
    {
        $ecommerceCategoriesAggregated = false;
        if ($key == '_pkc'
            && $value[0] == '[' && $value[1] == '"'
        ) {
            // In case categories were truncated, try closing the array
            if (substr($value, -2) != '"]') {
                $value .= '"]';
            }
            $decoded = @Piwik_Common::json_decode($value);
            if (is_array($decoded)) {
                $count = 0;
                foreach ($decoded as $category) {
                    if (empty($category)
                        || $count >= Piwik_Tracker_GoalManager::MAXIMUM_PRODUCT_CATEGORIES
                    ) {
                        continue;
                    }
                    $this->aggregateAction($archiveProcessing, $key, $category, $row);
                    $ecommerceCategoriesAggregated = true;
                    $count++;
                }
            }
        }
        return $ecommerceCategoriesAggregated;
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @param $key
     * @param $value
     * @param $row
     */
    protected function aggregateAction(Piwik_ArchiveProcessing_Day $archiveProcessing, $key, $value, $row)
    {
        if (!isset($this->metricsByKeyAndValue[$key][$value])) {
            $this->metricsByKeyAndValue[$key][$value] = $archiveProcessing->makeEmptyActionRow();
        }
        $archiveProcessing->sumMetrics($row, $this->metricsByKeyAndValue[$key][$value], $onlyMetricsAvailableInActionsTable = true);

        if ($this->isReservedKey($key)) {
            // Price tracking on Ecommerce product/category pages:
            // The the AVG is returned from the SQL query so the price is not summed
            $index = Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED;
            if (!empty($row[$index])) {
                $this->metricsByKeyAndValue[$key][$value][$index] = (float)$row[$index];
            }
        }
    }

    protected static function isReservedKey($key)
    {
        return in_array($key, Piwik_CustomVariables_API::getReservedCustomVariableKeys());
    }

    protected function aggregateFromConversions(Piwik_ArchiveProcessing_Day $archiveProcessing, $query, $keyField, $valueField)
    {
        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $this->cleanCustomVarValue($row[$valueField]);
            $idGoal = $row['idgoal'];

            if (!isset($this->metricsByKey[$key][Piwik_Archive::INDEX_GOALS][$idGoal])) {
                $this->metricsByKey[$key][Piwik_Archive::INDEX_GOALS][$idGoal] = $archiveProcessing->makeEmptyGoalRow($idGoal);
            }
            if (!isset($this->metricsByKeyAndValue[$key][$value][Piwik_Archive::INDEX_GOALS][$idGoal])) {
                $this->metricsByKeyAndValue[$key][$value][Piwik_Archive::INDEX_GOALS][$idGoal] = $archiveProcessing->makeEmptyGoalRow($idGoal);
            }

            $archiveProcessing->sumGoalMetrics($row, $this->metricsByKey[$key][Piwik_Archive::INDEX_GOALS][$idGoal]);
            $archiveProcessing->sumGoalMetrics($row, $this->metricsByKeyAndValue[$key][$value][Piwik_Archive::INDEX_GOALS][$idGoal]);
        }
    }

    protected function removeVisitsMetricsFromActionsAggregate(Piwik_ArchiveProcessing_Day $archiveProcessing)
    {
        $emptyActionRow = $archiveProcessing->makeEmptyActionRow();

        foreach ($this->metricsByKey as $key => &$row) {
            $isActionRowAggregate = (count($row) == count($emptyActionRow));
            if (!self::isReservedKey($key)
                && $isActionRowAggregate
            ) {
                unset($row[Piwik_Archive::INDEX_NB_UNIQ_VISITORS]);
                unset($row[Piwik_Archive::INDEX_NB_VISITS]);
            }
        }
    }

    public function archivePeriod($archiveProcessing)
    {
        $nameToCount = $archiveProcessing->archiveDataTable(
            self::BLOB_NAME, null, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
            $columnToSort = Piwik_Archive::INDEX_NB_VISITS);
    }

}