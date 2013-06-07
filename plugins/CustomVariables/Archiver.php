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

class Piwik_CustomVariables_Archiver extends Piwik_PluginsArchiver
{
    const LABEL_CUSTOM_VALUE_NOT_DEFINED = "Value not defined";
    const CUSTOM_VARIABLE_RECORD_NAME = 'CustomVariables_valueByName';
    protected $metricsByKey = array();
    protected $metricsByKeyAndValue = array();
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;
    protected $newEmptyRow;

    function __construct($processor)
    {
        parent::__construct($processor);
        $this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_custom_variables'];
        $this->maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_custom_variables'];
    }

    public function archiveDay()
    {
        for ($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            $this->aggregateCustomVariable($i);
        }

        $this->removeVisitsMetricsFromActionsAggregate();
        $this->getProcessor()->enrichMetricsWithConversions($this->metricsByKey);
        $this->getProcessor()->enrichPivotMetricsWithConversions($this->metricsByKeyAndValue);

        $table = $this->getProcessor()->getDataTableWithSubtablesFromArraysIndexedByLabel($this->metricsByKeyAndValue, $this->metricsByKey);
        $blob = $table->getSerialized(
            $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
            $columnToSort = Piwik_Archive::INDEX_NB_VISITS
        );

        $this->getProcessor()->insertBlobRecord(self::CUSTOM_VARIABLE_RECORD_NAME, $blob);
    }

    protected function aggregateCustomVariable($slot)
    {
        $keyField = "custom_var_k" . $slot;
        $valueField = "custom_var_v" . $slot;
        $where = "%s.$keyField != ''";
        $dimensions = array($keyField, $valueField);

        $query = $this->getProcessor()->queryVisitsByDimension($dimensions, $where);
        $this->aggregateFromVisits($query, $keyField, $valueField);

        $query = $this->getProcessor()->queryActionsByDimension($dimensions, $where);
        $this->aggregateFromActions($query, $keyField, $valueField);

        $query = $this->getProcessor()->queryConversionsByDimension($dimensions, $where);
        $this->aggregateFromConversions($query, $keyField, $valueField);
    }

    protected function aggregateFromVisits($query, $keyField, $valueField)
    {
        while ($row = $query->fetch()) {
            $value = $row[$valueField];
            $value = $this->cleanCustomVarValue($value);

            $key = $row[$keyField];
            $this->aggregateVisit($key, $value, $row);
        }
    }

    protected function cleanCustomVarValue($value)
    {
        if (strlen($value)) {
            return $value;
        }
        return self::LABEL_CUSTOM_VALUE_NOT_DEFINED;
    }

    protected function aggregateVisit($key, $value, $row)
    {
        if (!isset($this->metricsByKey[$key])) {
            $this->metricsByKey[$key] = $this->getProcessor()->makeEmptyRow();
        }
        if (!isset($this->metricsByKeyAndValue[$key][$value])) {
            $this->metricsByKeyAndValue[$key][$value] = $this->getProcessor()->makeEmptyRow();
        }

        $this->getProcessor()->sumMetrics($row, $this->metricsByKey[$key]);
        $this->getProcessor()->sumMetrics($row, $this->metricsByKeyAndValue[$key][$value]);
    }

    protected function aggregateFromActions($query, $keyField, $valueField)
    {
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $row[$valueField];
            $value = $this->cleanCustomVarValue($value);
            $this->aggregateAction($key, $value, $row);
        }
    }

    protected function aggregateAction($key, $value, $row)
    {
        $alreadyAggregated = $this->aggregateEcommerceCategories($key, $value, $row);
        if (!$alreadyAggregated) {
            $this->aggregateActionByKeyAndValue($key, $value, $row);
            $this->aggregateActionByKey($key, $row);
        }
    }

    /**
     * @return bool True if the $row metrics were already added to the ->metrics
     */
    protected function aggregateEcommerceCategories($key, $value, $row)
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
                    $this->aggregateActionByKeyAndValue($key, $category, $row);
                    $ecommerceCategoriesAggregated = true;
                    $count++;
                }
            }
        }
        return $ecommerceCategoriesAggregated;
    }

    protected function aggregateActionByKeyAndValue($key, $value, $row)
    {
        if (!isset($this->metricsByKeyAndValue[$key][$value])) {
            $this->metricsByKeyAndValue[$key][$value] = $this->getProcessor()->makeEmptyActionRow();
        }
        $this->getProcessor()->sumMetrics($row, $this->metricsByKeyAndValue[$key][$value], $onlyMetricsAvailableInActionsTable = true);

        if ($this->isReservedKey($key)) {
            // Price tracking on Ecommerce product/category pages:
            // the average is returned from the SQL query so the price is not "summed" like other metrics
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

    protected function aggregateActionByKey($key, $row)
    {
        if (!isset($this->metricsByKey[$key])) {
            $this->metricsByKey[$key] = $this->getProcessor()->makeEmptyActionRow();
        }
        $this->getProcessor()->sumMetrics($row, $this->metricsByKey[$key], $onlyMetricsAvailableInActionsTable = true);
    }

    protected function aggregateFromConversions($query, $keyField, $valueField)
    {
        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $this->cleanCustomVarValue($row[$valueField]);
            $idGoal = $row['idgoal'];
            $this->aggregateConversion($key, $value, $idGoal, $row);
        }
    }

    protected function aggregateConversion($key, $value, $idGoal, $row)
    {
        if (!isset($this->metricsByKey[$key][Piwik_Archive::INDEX_GOALS][$idGoal])) {
            $this->metricsByKey[$key][Piwik_Archive::INDEX_GOALS][$idGoal] = $this->getProcessor()->makeEmptyGoalRow($idGoal);
        }
        if (!isset($this->metricsByKeyAndValue[$key][$value][Piwik_Archive::INDEX_GOALS][$idGoal])) {
            $this->metricsByKeyAndValue[$key][$value][Piwik_Archive::INDEX_GOALS][$idGoal] = $this->getProcessor()->makeEmptyGoalRow($idGoal);
        }

        $this->getProcessor()->sumGoalMetrics($row, $this->metricsByKey[$key][Piwik_Archive::INDEX_GOALS][$idGoal]);
        $this->getProcessor()->sumGoalMetrics($row, $this->metricsByKeyAndValue[$key][$value][Piwik_Archive::INDEX_GOALS][$idGoal]);
    }

    protected function removeVisitsMetricsFromActionsAggregate()
    {
        $emptyActionRow = $this->getProcessor()->makeEmptyActionRow();

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

    public function archivePeriod()
    {
        $nameToCount = $this->getProcessor()->archiveDataTable(
            self::CUSTOM_VARIABLE_RECORD_NAME, null, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
            $columnToSort = Piwik_Archive::INDEX_NB_VISITS);
    }
}