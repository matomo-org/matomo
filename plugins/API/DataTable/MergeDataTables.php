<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API\DataTable;

use Piwik\DataTable\Row;
use Piwik\DataTable;

class MergeDataTables
{
    /**
     * @var bool
     */
    private $copyExtraProcessedMetrics;

    public function __construct(bool $copyExtraProcessedMetrics = false)
    {
        $this->copyExtraProcessedMetrics = $copyExtraProcessedMetrics;
    }

    /**
     * Merge the columns of two data tables. Only takes into consideration the first row of each table.
     * Manipulates the first table.
     *
     * @param DataTable|DataTable\Map $table1 The table to eventually filter.
     * @param DataTable|DataTable\Map $table2 Whether to delete rows with no visits or not.
     */
    public function mergeDataTables($table1, $table2)
    {
        // handle table arrays
        if ($table1 instanceof DataTable\Map && $table2 instanceof DataTable\Map) {
            $subTables1 = $table1->getDataTables();
            foreach ($table2->getDataTables() as $index => $subTable2) {
                if (!array_key_exists($index, $subTables1)) {
                    $subTable1 = $this->makeNewDataTable($subTable2);
                    $table1->addTable($subTable1, $index);
                } else {
                    $subTable1 = $subTables1[$index];
                }
                $this->mergeDataTables($subTable1, $subTable2);
            }
            return;
        }

        if ($this->copyExtraProcessedMetrics) {
            $extraProcessedMetricsTable1 = $table1->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];
            $extraProcessedMetricsTable2 = $table2->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];
            $table1->setMetadata(
                DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME,
                array_merge($extraProcessedMetricsTable1, $extraProcessedMetricsTable2)
            );
        }

        $firstRow2 = $table2->getFirstRow();
        if (!($firstRow2 instanceof Row)) {
            return;
        }

        $firstRow1 = $table1->getFirstRow();
        if (empty($firstRow1)) {
            $firstRow1 = $table1->addRow(new Row());
        }

        foreach ($firstRow2->getColumns() as $metric => $value) {
            $firstRow1->setColumn($metric, $value);
        }
    }

    private function makeNewDataTable(DataTable\DataTableInterface $subTable2)
    {
        if ($subTable2 instanceof DataTable\Map) {
            $result = new DataTable\Map();
            $result->setKeyName($subTable2->getKeyName());
            return $result;
        } else if ($subTable2 instanceof DataTable\Simple) {
            $result = new DataTable\Simple();
            $result->setAllTableMetadata($subTable2->getAllTableMetadata());
            return $result;
        } else if ($subTable2 instanceof DataTable) {
            $result = new DataTable();
            $result->setAllTableMetadata($subTable2->getAllTableMetadata());
            return $result;
        } else {
            throw new \Exception("Unknown datatable type: " . get_class($subTable2));
        }
    }

}