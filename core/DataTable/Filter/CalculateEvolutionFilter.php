<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\NumberFormatter;
use Piwik\Site;

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
 * @deprecated since v2.10.0
 */
class CalculateEvolutionFilter extends ColumnCallbackAddColumnPercentage
{
    /**
     * The the DataTable that contains past data.
     *
     * @var DataTable
     */
    protected $pastDataTable;

    /**
     * Tells if column being added is the revenue evolution column.
     */
    protected $isRevenueEvolution = null;

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

        $this->pastDataTable = $pastDataTable;

        $this->isRevenueEvolution = $columnToAdd == 'revenue_evolution';
    }

    /**
     * Returns the difference between the column in the specific row and its
     * sister column in the past DataTable.
     *
     * @param Row $row
     * @return int|float
     */
    protected function getDividend($row)
    {
        $currentValue = $row->getColumn($this->columnValueToRead);

        // if the site this is for doesn't support ecommerce & this is for the revenue_evolution column,
        // we don't add the new column
        if ($currentValue === false
            && $this->isRevenueEvolution
            && !Site::isEcommerceEnabledFor($row->getColumn('label'))
        ) {
            return false;
        }

        $pastRow = $this->getPastRowFromCurrent($row);
        if ($pastRow) {
            $pastValue = $pastRow->getColumn($this->columnValueToRead);
        } else {
            $pastValue = 0;
        }

        return $currentValue - $pastValue;
    }

    /**
     * Returns the value of the column in $row's sister row in the past
     * DataTable.
     *
     * @param Row $row
     * @return int|float
     */
    protected function getDivisor($row)
    {
        $pastRow = $this->getPastRowFromCurrent($row);
        if (!$pastRow) {
            return 0;
        }

        return $pastRow->getColumn($this->columnNameUsedAsDivisor);
    }

    /**
     * Calculates and formats a quotient based on a divisor and dividend.
     *
     * Unlike ColumnCallbackAddColumnPercentage's,
     * version of this method, this method will return 100% if the past
     * value of a metric is 0, and the current value is not 0. For a
     * value representative of an evolution, this makes sense.
     *
     * @param int|float $value The dividend.
     * @param int|float $divisor
     * @return string
     */
    protected function formatValue($value, $divisor)
    {
        $value = self::getPercentageValue($value, $divisor, $this->quotientPrecision);
        $value = self::appendPercentSign($value);

        $value = Common::forceDotAsSeparatorForDecimalPoint($value);

        return $value;
    }

    /**
     * Utility function. Returns the current row in the past DataTable.
     *
     * @param Row $row The row in the 'current' DataTable.
     * @return bool|Row
     */
    protected function getPastRowFromCurrent($row)
    {
        return $this->pastDataTable->getRowFromLabel($row->getColumn('label'));
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
            return NumberFormatter::getInstance()->formatPercent($number, $quotientPrecision);
        }

        return NumberFormatter::getInstance()->format($number, $quotientPrecision);
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
