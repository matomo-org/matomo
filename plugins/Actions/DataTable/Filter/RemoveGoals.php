<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\API\Request;

class RemoveGoals extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $site = $table->getMetadata('site');
        if ($site) {
            $goals = Request::processRequest('Goals.getGoals', ['idSite' => $site->getId(), 'filter_limit' => '-1'], $default = []);
            if ($goals) {
                $this->removeGoalsColumns($table, $goals);
            }
        }
    }

    private function removeGoalsColumns(DataTable $datatable, array $goals)
    {
        $goalCols = ['nb_conversions', 'revenue', 'nb_conv_pages_before', 'nb_conversions_attrib',
                         'nb_conversions_page_rate', 'nb_conversions_page_uniq', 'revenue_attrib', 'revenue_entry',
                         'nb_conversions_entry_rate', 'revenue_per_entry', 'nb_conversions_entry'];

        foreach ($datatable->getRowsWithoutSummaryRow() as $row) {

            foreach ($goals as $goalId => $goal) {
                foreach ($goalCols as $columnName) {
                    $row->deleteColumn('goal_'.$goalId.'_'.$columnName);
                }
            }
            $row->deleteMetadata('goals');

            $st = $row->getSubtable();
            if ($st) {
                $this->removeGoalsColumns($st, $goals);
            }

        }
    }


}