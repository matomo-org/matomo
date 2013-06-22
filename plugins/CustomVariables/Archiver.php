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

    /**
     * @var Piwik_DataArray
     */
    protected $dataArray;
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
        $this->dataArray = new Piwik_DataArray();

        for ($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            $this->aggregateCustomVariable($i);
        }

        $this->removeVisitsMetricsFromActionsAggregate();
        $this->dataArray->enrichMetricsWithConversions();
        $table = $this->getProcessor()->getDataTableFromDataArray($this->dataArray);
        $blob = $table->getSerialized(
            $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
            $columnToSort = Piwik_Metrics::INDEX_NB_VISITS
        );

        $this->getProcessor()->insertBlobRecord(self::CUSTOM_VARIABLE_RECORD_NAME, $blob);
    }

    protected function aggregateCustomVariable($slot)
    {
        $keyField = "custom_var_k" . $slot;
        $valueField = "custom_var_v" . $slot;
        $where = "%s.$keyField != ''";
        $dimensions = array($keyField, $valueField);

        $query = $this->getLogAggregator()->queryVisitsByDimension($dimensions, $where);
        $this->aggregateFromVisits($query, $keyField, $valueField);

        // IF we query Custom Variables scope "page" either: Product SKU, Product Name,
        // then we also query the "Product page view" price which was possibly recorded.
        $additionalSelects = false;
        // FIXMEA
        if (in_array($slot, array(3,4,5))) {
            $additionalSelects = array( $this->getSelectAveragePrice() );
        }
        $query = $this->getLogAggregator()->queryActionsByDimension($dimensions, $where, $additionalSelects);
        $this->aggregateFromActions($query, $keyField, $valueField);

        $query = $this->getLogAggregator()->queryConversionsByDimension($dimensions, $where);
        $this->aggregateFromConversions($query, $keyField, $valueField);
    }

    protected function getSelectAveragePrice()
    {
        return Piwik_DataAccess_LogAggregator::getSqlRevenue("AVG(log_link_visit_action.custom_var_v2)")
            . " as `" . Piwik_Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED . "`";
    }

    protected function aggregateFromVisits($query, $keyField, $valueField)
    {
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $this->cleanCustomVarValue($row[$valueField]);

            $this->dataArray->sumMetricsVisits($key, $row);
            $this->dataArray->sumMetricsVisitsPivot($key, $value, $row);
        }
    }

    protected function cleanCustomVarValue($value)
    {
        if (strlen($value)) {
            return $value;
        }
        return self::LABEL_CUSTOM_VALUE_NOT_DEFINED;
    }


    protected function aggregateFromActions($query, $keyField, $valueField)
    {
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $this->cleanCustomVarValue($row[$valueField]);

            $alreadyAggregated = $this->aggregateEcommerceCategories($key, $value, $row);
            if (!$alreadyAggregated) {
                $this->aggregateActionByKeyAndValue($key, $value, $row);
                $this->dataArray->sumMetricsActions($key, $row);
            }
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
        $this->dataArray->sumMetricsActionsPivot($key, $value, $row);

        if ($this->isReservedKey($key)) {
            // Price tracking on Ecommerce product/category pages:
            // the average is returned from the SQL query so the price is not "summed" like other metrics
            $index = Piwik_Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED;
            if (!empty($row[$index])) {
                $this->dataArray->setRowColumnPivot($key, $value, $index, (float)$row[$index]);
            }
        }
    }

    protected static function isReservedKey($key)
    {
        return in_array($key, Piwik_CustomVariables_API::getReservedCustomVariableKeys());
    }


    protected function aggregateFromConversions($query, $keyField, $valueField)
    {
        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $key = $row[$keyField];
            $value = $this->cleanCustomVarValue($row[$valueField]);
            $this->dataArray->sumMetricsGoals($key, $row);
            $this->dataArray->sumMetricsGoalsPivot($key, $value, $row);
        }
    }

    protected function removeVisitsMetricsFromActionsAggregate()
    {
        $dataArray = &$this->dataArray->getDataArray();
        foreach ($dataArray as $key => &$row) {
            if (!self::isReservedKey($key)
                && Piwik_DataArray::isRowActions($row)
            ) {
                unset($row[Piwik_Metrics::INDEX_NB_UNIQ_VISITORS]);
                unset($row[Piwik_Metrics::INDEX_NB_VISITS]);
            }
        }
    }

    public function archivePeriod()
    {
        $nameToCount = $this->getProcessor()->aggregateDataTableReports(
            self::CUSTOM_VARIABLE_RECORD_NAME, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable,
            $columnToSort = Piwik_Metrics::INDEX_NB_VISITS);
    }
}