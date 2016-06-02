<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\DataTable\Row\DataTableSummaryRow;

/**
 * Adds a summary row to {@link DataTable}s that contains the sum of all other table rows.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddSummaryRow');
 *
 *     // use a human readable label for the summary row (instead of '-1')
 *     $dataTable->filter('AddSummaryRow', array($labelSummaryRow = Piwik::translate('General_Total')));
 *
 * @api
 */
class AddSummaryRow extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The table that will be filtered.
     * @param int $labelSummaryRow The value of the label column for the new row.
     */
    public function __construct($table, $labelSummaryRow = DataTable::LABEL_SUMMARY_ROW)
    {
        parent::__construct($table);
        $this->labelSummaryRow = $labelSummaryRow;
    }

    /**
     * Executes the filter. See {@link AddSummaryRow}.
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
