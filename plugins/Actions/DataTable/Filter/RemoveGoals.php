<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

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
        $table->filter(function (DataTable $dataTable) {

            foreach ($dataTable->getRowsWithoutSummaryRow() as $row) {

                $goals = $row->getMetadata('goals');
                if ($goals) {

                    $goalCols = ['nb_conversions', 'revenue', 'nb_conv_pages_before', 'nb_conversions_attrib',
                        'nb_conversions_page_rate', 'nb_conversions_page_uniq', 'revenue_attrib', 'revenue_entry',
                        'nb_conversions_entry_rate', 'revenue_per_entry', 'nb_conversions_entry'];
                    foreach ($goals as $goalId) {
                        foreach ($goalCols as $columnName) {
                            $row->deleteColumn('goal_'.$goalId.'_'.$columnName);
                        }
                    }
                    $row->deleteMetadata('goals');
                }

            }
        });
    }
}