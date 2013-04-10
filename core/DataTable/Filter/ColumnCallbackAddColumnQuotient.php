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
 * Adds a new column that is a division of two columns of the current row.
 * Useful to process bounce rates, exit rates, average time on page, etc.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackAddColumnQuotient extends Piwik_DataTable_Filter
{
    protected $table;
    protected $columnValueToRead;
    protected $columnNameToAdd;
    protected $columnNameUsedAsDivisor;
    protected $totalValueUsedAsDivisor;
    protected $quotientPrecision;
    protected $shouldSkipRows;
    protected $getDivisorFromSummaryRow;

    /**
     * @param Piwik_DataTable $table
     * @param string $columnNameToAdd
     * @param string $columnValueToRead
     * @param number|string $divisorValueOrDivisorColumnName
     *                           if a numeric value is given, we use this value as the divisor to process the percentage.
     *                           if a string is given, this string is the column name's value used as the divisor.
     * @param int $quotientPrecision                 Division precision
     * @param bool|number $shouldSkipRows                    Whether rows w/o the column to read should be skipped.
     * @param bool $getDivisorFromSummaryRow          Whether to get the divisor from the summary row or the current row.
     */
    public function __construct($table, $columnNameToAdd, $columnValueToRead, $divisorValueOrDivisorColumnName, $quotientPrecision = 0, $shouldSkipRows = false, $getDivisorFromSummaryRow = false)
    {
        parent::__construct($table);
        $this->table = $table;
        $this->columnValueToRead = $columnValueToRead;
        $this->columnNameToAdd = $columnNameToAdd;
        if (is_numeric($divisorValueOrDivisorColumnName)) {
            $this->totalValueUsedAsDivisor = $divisorValueOrDivisorColumnName;
        } else {
            $this->columnNameUsedAsDivisor = $divisorValueOrDivisorColumnName;
        }
        $this->quotientPrecision = $quotientPrecision;
        $this->shouldSkipRows = $shouldSkipRows;
        $this->getDivisorFromSummaryRow = $getDivisorFromSummaryRow;
    }

    /**
     * Filters the given data table
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
            $existingValue = $row->getColumn($this->columnNameToAdd);
            if ($existingValue !== false) {
                continue;
            }

            $value = $this->getDividend($row);
            if ($value === false && $this->shouldSkipRows) {
                continue;
            }

            $divisor = $this->getDivisor($row);

            $formattedValue = $this->formatValue($value, $divisor);
            $row->addColumn($this->columnNameToAdd, $formattedValue);

            $this->filterSubTable($row);
        }
    }

    /**
     * Formats the given value
     *
     * @param number $value
     * @param number $divisor
     * @return float|int
     */
    protected function formatValue($value, $divisor)
    {
        $quotient = 0;
        if ($divisor > 0 && $value > 0) {
            $quotient = round($value / $divisor, $this->quotientPrecision);
        }
        return $quotient;
    }

    /**
     * Returns the dividend to use when calculating the new column value. Can
     * be overridden by descendent classes to customize behavior.
     *
     * @param Piwik_DataTable_Row $row  The row being modified.
     * @return int|float
     */
    protected function getDividend($row)
    {
        return $row->getColumn($this->columnValueToRead);
    }

    /**
     * Returns the divisor to use when calculating the new column value. Can
     * be overridden by descendent classes to customize behavior.
     *
     * @param Piwik_DataTable_Row $row  The row being modified.
     * @return int|float
     */
    protected function getDivisor($row)
    {
        if (!is_null($this->totalValueUsedAsDivisor)) {
            return $this->totalValueUsedAsDivisor;
        } else if ($this->getDivisorFromSummaryRow) {
            $summaryRow = $this->table->getRowFromId(Piwik_DataTable::ID_SUMMARY_ROW);
            return $summaryRow->getColumn($this->columnNameUsedAsDivisor);
        } else {
            return $row->getColumn($this->columnNameUsedAsDivisor);
        }
    }
}
