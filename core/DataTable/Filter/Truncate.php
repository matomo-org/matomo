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

/**
 * @package Piwik
 * @subpackage DataTable
 */
class Truncate extends Filter
{
    /**
     * @param DataTable $table
     * @param int $truncateAfter
     */
    public function __construct($table, $truncateAfter)
    {
        parent::__construct($table);
        $this->truncateAfter = $truncateAfter;
    }

    /**
     * Truncates the table after X rows and adds a summary row
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $table->filter('AddSummaryRow', array($this->truncateAfter));
        $table->filter('ReplaceSummaryRowLabel');

        foreach ($table->getRows() as $row) {
            if ($row->isSubtableLoaded()) {
                $idSubTable = $row->getIdSubDataTable();
                $subTable = Manager::getInstance()->getTable($idSubTable);
                $subTable->filter('Truncate', array($this->truncateAfter));
            }
        }
    }
}
