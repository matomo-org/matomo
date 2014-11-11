<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\CoreHome\Metrics\EvolutionMetric;

/**
 * A {@link DataTable} filter that calculates the evolution of a metric and adds
 * it to each row as a percentage.
 *
 * **This filter cannot be used as an argument to {@link Piwik\DataTable::filter()}** since
 * it requires corresponding data from another DataTable. Instead,
 * you must manually perform a binary filter (see the **MultiSites** API for an
 * example).
 *
 * The evolution metric is calculated as:
 *
 *     ((currentValue - pastValue) / pastValue) * 100
 *
 * @api
 */
class CalculateEvolutionFilter extends ColumnCallbackAddColumnPercentage
{
    /**
     * @var EvolutionMetric
     */
    protected $evolutionMetric;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable being filtered.
     * @param DataTable $pastDataTable The DataTable containing data for the period in the past.
     * @param string $columnToAdd The column to add evolution data to, eg, `'visits_evolution'`.
     * @param string $columnToRead The column to use to calculate evolution data, eg, `'nb_visits'`.
     * @param int $quotientPrecision The precision to use when rounding the evolution value.
     */
    public function __construct($table, $pastDataTable, $columnToAdd, $columnToRead, $quotientPrecision = 0)
    {
        parent::__construct(
            $table, $columnToAdd, $columnToRead, $columnToRead, $quotientPrecision, $shouldSkipRows = true);

        $this->evolutionMetric = new EvolutionMetric($columnToRead, $pastDataTable, $columnToAdd, $quotientPrecision);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $evolutionName = $this->evolutionMetric->getName();
        foreach ($table->getRows() as $row) {
            $value = $this->evolutionMetric->compute($row);
            $row->addColumn($evolutionName, $value);
        }
    }

    /**
     * Calculates the evolution percentage for two arbitrary values.
     *
     * @param float|int $currentValue The current metric value.
     * @param float|int $pastValue The value of the metric in the past. We measure the % change
     *                                      from this value to $currentValue.
     * @param float|int $quotientPrecision The quotient precision to round to.
     * @param bool $appendPercentSign Whether to append a '%' sign to the end of the number or not.
     *
     * @return string The evolution percent, eg `'15%'`.
     */
    public static function calculate($currentValue, $pastValue, $quotientPrecision = 0, $appendPercentSign = true)
    {
        $number = self::getPercentageValue($currentValue - $pastValue, $pastValue, $quotientPrecision);
        if ($appendPercentSign) {
            $number = self::appendPercentSign($number);
        }

        return $number;
    }

    public static function appendPercentSign($number)
    {
        return $number . '%';
    }

    public static function prependPlusSignToNumber($number)
    {
        if ($number > 0) {
            $number = '+' . $number;
        }

        return $number;
    }

    /**
     * Returns an evolution percent based on a value & divisor.
     */
    private static function getPercentageValue($value, $divisor, $quotientPrecision)
    {
        if ($value == 0) {
            $evolution = 0;
        } elseif ($divisor == 0) {
            $evolution = 100;
        } else {
            $evolution = ($value / $divisor) * 100;
        }

        $evolution = round($evolution, $quotientPrecision);
        return $evolution;
    }
}