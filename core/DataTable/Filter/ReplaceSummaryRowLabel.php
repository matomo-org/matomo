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

/**
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ReplaceSummaryRowLabel extends Piwik_DataTable_Filter
{
    /**
     * @param Piwik_DataTable $table
     * @param string|null $newLabel  new label for summary row
     */
    public function __construct($table, $newLabel = null)
    {
        parent::__construct($table);
        if (is_null($newLabel)) {
            $newLabel = Piwik_Translate('General_Others');
        }
        $this->newLabel = $newLabel;
    }

    /**
     * Updates the summary row label
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        $rows = $table->getRows();
        foreach ($rows as $row) {
            if ($row->getColumn('label') == Piwik_DataTable::LABEL_SUMMARY_ROW) {
                $row->setColumn('label', $this->newLabel);
                break;
            }
        }

        // recurse
        foreach ($rows as $row) {
            if ($row->isSubtableLoaded()) {
                $subTable = Piwik_DataTable_Manager::getInstance()->getTable($row->getIdSubDataTable());
                $this->filter($subTable);
            }
        }
    }
}
