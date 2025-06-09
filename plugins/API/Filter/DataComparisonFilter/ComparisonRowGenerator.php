<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\Filter\DataComparisonFilter;

use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Simple;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;

class ComparisonRowGenerator
{
    /**
     * @var bool
     */
    private $isRequestMultiplePeriod;

    /**
     * @var string
     */
    private $segmentNameForReport;

    /**
     * @var array
     */
    private $columnMappings;

    public function __construct($segmentNameForReport, $isRequestMultiplePeriod, $columnMappings)
    {
        $this->segmentNameForReport = $segmentNameForReport;
        $this->isRequestMultiplePeriod = $isRequestMultiplePeriod;
        $this->columnMappings = $columnMappings;
    }

    public function compareTables($compareMetadata, DataTableInterface $tables, ?DataTableInterface $compareTables = null)
    {
        if ($tables instanceof DataTable) {
            $this->compareTable($compareMetadata, $tables, $compareTables, $compareTables);
        } elseif ($tables instanceof DataTable\Map) {
            $childTablesArray = array_values($tables->getDataTables());
            $compareTablesArray = ($compareTables instanceof DataTable\Map) ? array_values($compareTables->getDataTables()) : [];

            $isDatePeriod = $tables->getKeyName() == 'date';

            foreach ($childTablesArray as $index => $childTable) {
                $compareChildTable = !empty($compareTablesArray[$index]) ? $compareTablesArray[$index] : null;
                $this->compareTables($compareMetadata, $childTable, $compareChildTable);
            }

            // in case one of the compared periods has more periods than the main one, we want to fill the result with empty datatables
            // so the comparison data is still present. this allows us to see that data in an evolution report.
            if ($isDatePeriod) {
                $lastTable = end($childTablesArray);

                /** @var Period $lastPeriod */
                $lastPeriod = $lastTable->getMetadata('period');
                $periodType = $lastPeriod->getLabel();

                for ($i = count($childTablesArray); $i < count($compareTablesArray); ++$i) {
                    $periodChangeCount = $i - count($childTablesArray) + 1;
                    $newPeriod = Period\Factory::build($periodType, $lastPeriod->getDateStart()->addPeriod($periodChangeCount, $periodType));

                    // create an empty table for the main request
                    $newTable = new DataTable();
                    $newTable->setAllTableMetadata($lastTable->getAllTableMetadata());
                    $newTable->setMetadata('period', $newPeriod);

                    if ($newPeriod->getLabel() === 'week' || $newPeriod->getLabel() === 'range') {
                        $periodLabel = $newPeriod->getRangeString();
                    } else {
                        $periodLabel = $newPeriod->getPrettyString();
                    }

                    $tables->addTable($newTable, $periodLabel);

                    // compare with the empty table
                    $compareTable = !empty($compareTablesArray[$i]) ? $compareTablesArray[$i] : null;
                    $this->compareTables($compareMetadata, $newTable, $compareTable);
                }
            }
        } else {
            throw new \Exception("Unexpected DataTable type: " . get_class($tables));
        }
    }

    private function compareTable($compareMetadata, DataTable $table, ?DataTable $rootCompareTable = null, ?DataTable $compareTable = null)
    {
        // if there are no rows in the table because the metrics are 0, add one so we can still set comparison values
        if ($table->getRowsCount() == 0) {
            $table->addRow(new DataTable\Row());
        }

        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');

            $compareRow = null;
            if ($compareTable instanceof Simple) {
                $compareRow = $compareTable->getFirstRow() ?: null;
            } elseif ($compareTable instanceof DataTable) {
                $compareRow = $compareTable->getRowFromLabel($label) ?: null;
            }

            $this->compareRow($table, $compareMetadata, $row, $compareRow, $rootCompareTable);
        }

        $totalsRow = $table->getTotalsRow();
        if (!empty($totalsRow)) {
            $compareRow = $compareTable ? $compareTable->getTotalsRow() : null;
            $this->compareRow($table, $compareMetadata, $totalsRow, $compareRow, $rootCompareTable);
        }

        if ($compareTable) {
            $totals = $compareTable->getMetadata('totals');
            if (!empty($totals)) {
                $totals = $this->replaceIndexesInTotals($totals);
                $comparisonTotalsEntry = array_merge($compareMetadata, [
                    'totals' => $totals,
                ]);

                $allTotalsTables = $table->getMetadata('comparisonTotals') ?: [];
                $allTotalsTables[] = $comparisonTotalsEntry;
                $table->setMetadata('comparisonTotals', $allTotalsTables);
            }
        }
    }

    private function compareRow(DataTable $table, $compareMetadata, DataTable\Row $row, ?DataTable\Row $compareRow = null, ?DataTable $rootTable = null)
    {
        $comparisonDataTable = $row->getComparisons();
        if (empty($comparisonDataTable)) {
            $comparisonDataTable = new DataTable();
            $comparisonDataTable->setMetadata(
                DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME,
                $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME)
            );
            $row->setComparisons($comparisonDataTable);
        }

        $this->addIndividualChildPrettifiedMetadata($compareMetadata, $rootTable);

        $columns = [];
        if ($compareRow) {
            foreach ($compareRow as $name => $value) {
                if (
                    !is_numeric($value)
                    || $name == 'label'
                ) {
                    continue;
                }

                $columns[$name] = $value;
            }
        } else {
            foreach ($row as $name => $value) {
                if (
                    !is_numeric($value)
                    || $name == 'label'
                ) {
                    continue;
                }

                $columns[$name] = 0;
            }
        }

        $newRow = new DataTable\Row([
            DataTable\Row::COLUMNS => $columns,
            DataTable\Row::METADATA => $compareMetadata,
        ]);

        // set subtable
        $newRow->setMetadata('idsubdatatable', -1);
        if ($compareRow) {
            $subtableId = $compareRow->getMetadata('idsubdatatable_in_db') ?: $compareRow->getIdSubDataTable();
            if ($subtableId) {
                $newRow->setMetadata('idsubdatatable', $subtableId);
            }
        }

        // add segment metadatas
        if ($row->getMetadata('segment')) {
            $newSegment = $row->getMetadata('segment');
            if ($newRow->getMetadata('compareSegment')) {
                $newSegment = Segment::combine($newRow->getMetadata('compareSegment'), SegmentExpression::AND_DELIMITER, $newSegment);
            }
            $newRow->setMetadata('segment', $newSegment);
        } elseif (
            $this->segmentNameForReport
            && $row->getMetadata('segmentValue') !== false
        ) {
            $segmentValue = $row->getMetadata('segmentValue');
            $newRow->setMetadata('segment', sprintf('%s==%s', $this->segmentNameForReport, urlencode($segmentValue)));
        }

        $comparisonDataTable->addRow($newRow);

        // recurse on subtable if there
        $subtable = $row->getSubtable();
        $compareSubTable = $compareRow ? $compareRow->getSubtable() : null;

        if ($subtable && $compareSubTable) {
            $this->compareTable($compareMetadata, $subtable, $rootTable, $compareSubTable);
        }
    }

    private function addIndividualChildPrettifiedMetadata(array &$metadata, ?DataTable $parentTable = null)
    {
        if (
            $parentTable
            && $this->isRequestMultiplePeriod
        ) {
            /** @var Period $period */
            $period = $parentTable->getMetadata('period');
            if (empty($period)) {
                return;
            }

            $prettyPeriod = $period->getLocalizedLongString();
            $metadata['comparePeriodPretty'] = ucfirst($prettyPeriod);

            $metadata['comparePeriod'] = $period->getLabel();
            $metadata['compareDate'] = $period->getDateStart()->toString();
        }
    }

    private function replaceIndexesInTotals($totals)
    {
        foreach ($totals as $index => $value) {
            if (isset($this->columnMappings[$index])) {
                $name = $this->columnMappings[$index];
                $totals[$name] = $totals[$index];
                unset($totals[$index]);
            }
        }
        return $totals;
    }
}
