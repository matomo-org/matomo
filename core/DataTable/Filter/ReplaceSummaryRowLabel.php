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
use Piwik\DataTable\Manager;
use Piwik\Piwik;

/**
 *
 * @package Piwik
 * @subpackage DataTable
 */
class ReplaceSummaryRowLabel extends Filter
{
    /**
     * @param DataTable $table
     * @param string|null $newLabel new label for summary row
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
     * Updates the summary row label
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $rows = $table->getRows();
        foreach ($rows as $row) {
            if ($row->getColumn('label') == DataTable::LABEL_SUMMARY_ROW) {
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
