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

use Piwik\DataTable;
use Piwik\DataTable\Filter;

/**
 * Delete all rows that have a $columnToFilter value less than the $minimumValue
 *
 * For example we delete from the countries report table all countries that have less than 3 visits.
 * It is very useful to exclude noise from the reports.
 * You can obviously apply this filter on a percentaged column, eg. remove all countries with the column 'percent_visits' less than 0.05
 *
 * @package Piwik
 * @subpackage DataTable
 */
class ExcludeLowPopulation extends Filter
{
    const MINIMUM_SIGNIFICANT_PERCENTAGE_THRESHOLD = 0.02;

    /**
     * The minimum value to enforce in a datatable for a specified column. Rows found with
     * a value less than this are removed.
     *
     * @var number
     */
    private $minimumValue;

    /**
     * Constructor
     *
     * @param DataTable $table
     * @param string $columnToFilter column to filter
     * @param number|\Closure $minimumValue minimum value
     * @param bool $minimumPercentageThreshold
     */
    public function __construct($table, $columnToFilter, $minimumValue, $minimumPercentageThreshold = false)
    {
        parent::__construct($table);
        $this->columnToFilter = $columnToFilter;

        if ($minimumValue == 0) {
            if ($minimumPercentageThreshold === false) {
                $minimumPercentageThreshold = self::MINIMUM_SIGNIFICANT_PERCENTAGE_THRESHOLD;
            }
            $allValues = $table->getColumn($this->columnToFilter);
            $sumValues = array_sum($allValues);
            $minimumValue = $sumValues * $minimumPercentageThreshold;
        }

        $this->minimumValue = $minimumValue;
    }

    /**
     * Executes filter and removes all rows below the defined minimum
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $minimumValue = $this->minimumValue;
        $isValueHighPopulation = function ($value) use ($minimumValue) {
            return $value >= $minimumValue;
        };

        $table->filter('ColumnCallbackDeleteRow', array($this->columnToFilter, $isValueHighPopulation));
    }
}
