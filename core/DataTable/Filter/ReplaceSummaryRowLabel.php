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
use Piwik\DataTable\Manager;
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
        $rows = $table->getRows();
        foreach ($rows as $id => $row) {
            if ($row->getColumn('label') == DataTable::LABEL_SUMMARY_ROW
                || $id == DataTable::ID_SUMMARY_ROW
            ) {
                $row->setColumn('label', $this->newLabel);
                break;
            }
        }

        // recurse
        foreach ($rows as $row) {
            if ($row->isSubtableLoaded()) {
                $subTable = Manager::getInstance()->getTable($row->getIdSubDataTable());
                $this->filter($subTable);
            }
        }
    }
}
