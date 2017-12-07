<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugin\ReportsProvider;

/**
 * This class is responsible for flattening data tables.
 *
 * It loads subtables and combines them into a single table by concatenating the labels.
 * This manipulator is triggered by using flat=1 in the API request.
 */
class Flattener extends DataTableManipulator
{

    private $includeAggregateRows = false;

    /**
     * If the flattener is used after calling this method, aggregate rows will
     * be included in the result. This can be useful when they contain data that
     * the leafs don't have (e.g. conversion stats in some cases).
     */
    public function includeAggregateRows()
    {
        $this->includeAggregateRows = true;
    }

    /**
     * Separator for building recursive labels (or paths)
     * @var string
     */
    public $recursiveLabelSeparator = '';

    /**
     * @param  DataTable $dataTable
     * @param string $recursiveLabelSeparator
     * @return DataTable|DataTable\Map
     */
    public function flatten($dataTable, $recursiveLabelSeparator)
    {
        $this->recursiveLabelSeparator = $recursiveLabelSeparator;

        return $this->manipulate($dataTable);
    }

    /**
     * Template method called from self::manipulate.
     * Flatten each data table.
     *
     * @param DataTable $dataTable
     * @return DataTable
     */
    protected function manipulateDataTable($dataTable)
    {
        $newDataTable = $dataTable->getEmptyClone($keepFilters = true);
        if ($dataTable->getTotalsRow()) {
            $newDataTable->setTotalsRow($dataTable->getTotalsRow());
        }

        // this recursive filter will be applied to subtables
        $dataTable->filter('ReplaceSummaryRowLabel');
        $dataTable->filter('ReplaceColumnNames');

        $report        = ReportsProvider::factory($this->apiModule, $this->apiMethod);
        if (!empty($report)) {
            $dimension = $report->getDimension();
        }

        $dimensionName = !empty($dimension) ? str_replace('.', '_', $dimension->getId()) : 'label1';

        $this->flattenDataTableInto($dataTable, $newDataTable, $level = 1, $dimensionName);

        return $newDataTable;
    }

    /**
     * @param $dataTable DataTable
     * @param $newDataTable
     * @param $dimensionName
     */
    protected function flattenDataTableInto($dataTable, $newDataTable, $level, $dimensionName, $prefix = '', $logo = false)
    {
        foreach ($dataTable->getRows() as $rowId => $row) {
            $this->flattenRow($row, $rowId, $newDataTable, $level, $dimensionName, $prefix, $logo);
        }
    }

    /**
     * @param Row $row
     * @param DataTable $dataTable
     * @param string $labelPrefix
     * @param string $dimensionName
     * @param bool $parentLogo
     */
    private function flattenRow
    (Row $row, $rowId, DataTable $dataTable, $level, $dimensionName,
                                $labelPrefix = '', $parentLogo = false)
    {
        $dimensions = $dataTable->getMetadata('dimensions');

        if (empty($dimensions)) {
            $dimensions = [];
        }

        if (!in_array($dimensionName, $dimensions)) {
            $dimensions[] = $dimensionName;
        }

        $dataTable->setMetadata('dimensions', $dimensions);

        $origLabel = $label = $row->getColumn('label');

        if ($label !== false) {
            $label = trim($label);

            if ($this->recursiveLabelSeparator == '/') {
                if (substr($label, 0, 1) == '/' && substr($labelPrefix, -1) == '/') {
                    $label = substr($label, 1);
                } elseif ($rowId === DataTable::ID_SUMMARY_ROW && $labelPrefix && $label != DataTable::LABEL_SUMMARY_ROW) {
                    $label = ' - ' . $label;
                }
            }

            $origLabel = $label;

            $label = $labelPrefix . $label;
            $row->setColumn('label', $label);

            if ($row->getMetadata($dimensionName)) {
                $origLabel = $row->getMetadata($dimensionName) . $this->recursiveLabelSeparator . $origLabel;
            }

            $row->setMetadata($dimensionName, $origLabel);
        }

        $logo = $row->getMetadata('logo');
        if ($logo === false && $parentLogo !== false) {
            $logo = $parentLogo;
            $row->setMetadata('logo', $logo);
        }

        /** @var DataTable $subTable */
        $subTable = $row->getSubtable();

        if ($subTable) {
            $subTable->applyQueuedFilters();
            $row->deleteMetadata('idsubdatatable_in_db');
        } else {
            $subTable = $this->loadSubtable($dataTable, $row);
        }

        $row->removeSubtable();

        if ($subTable === null) {
            if ($this->includeAggregateRows) {
                $row->setMetadata('is_aggregate', 0);
            }
            $dataTable->addRow($row);
        } else {
            if ($this->includeAggregateRows) {
                $row->setMetadata('is_aggregate', 1);
                $dataTable->addRow($row);
            }
            $prefix = $label . $this->recursiveLabelSeparator;

            $report        = ReportsProvider::factory($this->apiModule, $this->apiMethod);
            if (!empty($report)) {
                $subDimension = $report->getSubtableDimension();
            }

            if ($level === 2) {
                $subDimension = $report->getThirdLeveltableDimension();
            }

            if (empty($subDimension)) {
                $report           = ReportsProvider::factory($this->apiModule, $this->getApiMethodForSubtable($this->request));
                $subDimension     = $report->getDimension();
            }

            $subDimensionName = $subDimension ? str_replace('.', '_', $subDimension->getId()) : 'label' . (substr_count($prefix, $this->recursiveLabelSeparator) + 1);

            if ($origLabel !== false) {
                foreach ($subTable->getRows() as $subRow) {
                    foreach ($row->getMetadata() as $name => $value) {
                        if ($subRow->getMetadata($name) === false) {
                            $subRow->setMetadata($name, $value);
                        }
                    }

                    $subRow->setMetadata($dimensionName, $origLabel);
                }
            }

            $this->flattenDataTableInto($subTable, $dataTable, $level + 1, $subDimensionName, $prefix, $logo);
        }
    }

    /**
     * Remove the flat parameter from the subtable request
     *
     * @param array $request
     * @return array
     */
    protected function manipulateSubtableRequest($request)
    {
        unset($request['flat']);

        return $request;
    }

}
