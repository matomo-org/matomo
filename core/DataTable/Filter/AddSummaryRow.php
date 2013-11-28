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
 * Add a summary row row to the table that is the sum of all other table
 * rows.
 *
 * **Basic usage example**
 * 
 *     $dataTable->filter('AddSummaryRow');
 * 
 *     // use a human readable label for the summary row (instead of '-1')
 *     $dataTable->filter('AddSummaryRow', array($labelSummaryRow = Piwik_Translate('General_Total')));
 * 
 * @package Piwik
 * @subpackage DataTable
 * @api
 */
class AddSummaryRow extends Filter
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
     * Executes the filter. See [AddSummaryRow](#).
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