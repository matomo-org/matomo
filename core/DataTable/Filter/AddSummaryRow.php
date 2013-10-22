<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\Filter;
use Piwik\DataTable;
use Piwik\DataTable\Row\DataTableSummaryRow;

/**
 * Add a new row to the table containing a summary
 * of the rows from StartRowToSummarize to EndRowToSummarize.
 * It then deletes the rows from StartRowToSummarize to EndRowToSummarize.
 * The new row created has a label = 'other'
 *
 * This filter is useful to build a more compact view of a table,
 * keeping the first records unchanged.
 *
 * For example we use this for the pie chart, to build the last pie part
 * which is the sum of all the remaining data after the top 5 data.
 * This row is assigned a label of 'Others'.
 *
 * @package Piwik
 * @subpackage DataTable
 */
class AddSummaryRow extends Filter
{
    /**
     * Creates a new filter and set all required parameters
     *
     * @param DataTable $table
     * @param int $startRowToSummarize
     * @param int $labelSummaryRow
     * @param null $columnToSortByBeforeTruncating
     * @param bool $deleteRows
     */
    public function __construct($table, $labelSummaryRow = DataTable::LABEL_SUMMARY_ROW)
    {
        parent::__construct($table);
        $this->labelSummaryRow = $labelSummaryRow;
    }

    /**
     * Adds a summary row to the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $row = new DataTableSummaryRow($table);
        $row->setColumn('label', $this->labelSummaryRow);
        $table->addSummaryRow($row);
    }
}