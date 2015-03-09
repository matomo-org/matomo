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
        // apply filters now since subtables have their filters applied before generic filters. if we don't do this
        // now, we'll try to apply filters to rows that have already been manipulated. this results in errors like
        // 'column ... already exists'.
        $keepFilters = true;
        if (Common::getRequestVar('disable_queued_filters', 0, 'int', $this->request) == 0) {
            $dataTable->applyQueuedFilters();
            $keepFilters = false;
        }

        $newDataTable = $dataTable->getEmptyClone($keepFilters);
        foreach ($dataTable->getRows() as $rowId => $row) {
            $this->flattenRow($row, $rowId, $newDataTable);
        }

        return $newDataTable;
    }

    /**
     * @param Row $row
     * @param DataTable $dataTable
     * @param string $labelPrefix
     * @param bool $parentLogo
     */
    private function flattenRow(Row $row, $rowId, DataTable $dataTable,
                                $labelPrefix = '', $parentLogo = false)
    {
        $label = $row->getColumn('label');
        if ($label !== false) {
            $label = trim($label);

            if ($this->recursiveLabelSeparator == '/') {
                if (substr($label, 0, 1) == '/') {
                    $label = substr($label, 1);
                } elseif ($rowId === DataTable::ID_SUMMARY_ROW && $labelPrefix && $label != DataTable::LABEL_SUMMARY_ROW) {
                    $label = ' - ' . $label;
                }
            }

            $label = $labelPrefix . $label;
            $row->setColumn('label', $label);
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
            foreach ($subTable->getRows() as $rowId => $row) {
                $this->flattenRow($row, $rowId, $dataTable, $prefix, $logo);
            }
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
