<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Piwik;

class CalculateConversionPageRate extends BaseFilter
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

        $goalTotals = [];

        // Find all conversions for each goal in the table and store in an array
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            if (isset($row['goals'])) {
                foreach ($row['goals'] as $goalIdString => $metrics) {
                    if (isset($row['goals'][$goalIdString]['nb_conversions_attrib'])) {
                        if (isset($goalTotals[$goalIdString])) {
                            $goalTotals[$goalIdString] += $row['goals'][$goalIdString]['nb_conversions_attrib'];
                        } else {
                            $goalTotals[$goalIdString] = $row['goals'][$goalIdString]['nb_conversions_attrib'];
                        }
                    }
                }
            }
        }

        // Walk the rows and populate the nb_conversions_page_rate with nb_conversions_page_uniq / $goalTotals[goal id]
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            if (isset($row['goals'])) {
                foreach ($row['goals'] as $goalIdString => $metrics) {
                    $goalId = substr($goalIdString,7);
                    if (isset($row['goals'][$goalIdString]['nb_conversions_page_uniq'])) {

                        $rate = Piwik::getQuotientSafe(
                                $row['goals'][$goalIdString]['nb_conversions_page_uniq'],
                                $goalTotals[$goalIdString],
                                3
                            );
                        $row['goals'][$goalIdString]['nb_conversions_page_rate'] = $rate;

                        // This filter runs after the goal values have been copied from numeric named columns in the subtable
                        // to labelled columns on the row, so we need to update the goal_x_nb_conversions_page_rate column too
                        $row['goal_'.$goalId.'_nb_conversions_page_rate'] = $rate;

                    }
                }
            }
        }

    }

}
