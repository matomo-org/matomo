<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API\DataTable;

use Piwik\DataTable\Row;
use Piwik\DataTable;

class MergeDataTables
{

    /**
     * Merge the columns of two data tables.
     * Manipulates the first table.
     *
     * @param DataTable|DataTable\Map $table1 The table to eventually filter.
     * @param DataTable|DataTable\Map $table2 Whether to delete rows with no visits or not.
     */
    public function mergeDataTables($table1, $table2)
    {
        // handle table arrays
        if ($table1 instanceof DataTable\Map && $table2 instanceof DataTable\Map) {
            $subTables2 = $table2->getDataTables();
            foreach ($table1->getDataTables() as $index => $subTable1) {
                if (!array_key_exists($index, $subTables2)) {
                    // occurs when archiving starts on dayN and continues into dayN+1, see https://github.com/piwik/piwik/issues/5168#issuecomment-50959925
                    continue;
                }
                $subTable2 = $subTables2[$index];
                $this->mergeDataTables($subTable1, $subTable2);
            }
            return;
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

}