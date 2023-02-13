<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * Replaces the label of the summary row with a supplied label.
 *
 * This filter is only used to prettify the summary row label and so it should
 * always be queued on a {@link DataTable}.
 *
 * This filter always recurses. In other words, this filter will always apply itself to
 * all subtables in the given {@link DataTable}'s table hierarchy.
 *
 * **Basic example**
 *
 *     $dataTable->queueFilter('ReplaceSummaryRowLabel', array(Piwik::translate('General_Others')));
 *
 * @api
 */
class ReplaceSummaryRowLabel extends BaseFilter
{

    /**
     * @var string|null
     */
    private $newLabel;

    /**
     * Constructor.
     *
     * @param DataTable $table The table that will eventually be filtered.
     * @param string|null $newLabel The new label for summary row. If null, defaults to
     *                              `Piwik::translate('General_Others')`.
     */
    public function __construct($table, $newLabel = null)
    {
        parent::__construct($table);
        if (is_null($newLabel)) {
            $newLabel = Piwik::translate('General_Others');
        }
        $this->newLabel = $newLabel;
    }

    /**
     * See {@link ReplaceSummaryRowLabel}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $row = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
        if ($row) {
            $row->setColumn('label', $this->newLabel);
        }

        // recurse
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $subTable = $row->getSubtable();
            if ($subTable) {
                $this->filter($subTable);
            }
        }
        
       
        $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
        if (!empty($summaryRow)) {
            $subTable = $summaryRow->getSubtable();
            if ($subTable) {
                $this->filter($subTable);
            }
        }
    }
}
