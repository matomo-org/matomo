<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

/**
 * A row will be deleted if a positive value of $columnToRead is lower than the $minPositiveValue or if the negative
 * value of $columnToRead is higher than the $minNegativeValue.
 * That means a row will be deleted if the value is between $minNegativeValue and $minPositiveValue.
 */
class MinGrowth extends BaseFilter
{
    private $minPositiveValue;
    private $minNegativeValue;
    private $columnToRead;

    public function __construct($table, $columnToRead, $minPositiveValue, $minNegativeValue)
    {
        $this->columnToRead = $columnToRead;
        $this->minPositiveValue = $minPositiveValue;
        $this->minNegativeValue = $minNegativeValue;
    }

    public function filter($table)
    {
        if (!$this->minPositiveValue && !$this->minNegativeValue) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            $growthNumeric = $row->getColumn($this->columnToRead);

            if ($growthNumeric >= $this->minPositiveValue && $growthNumeric >= 0) {
                continue;
            } elseif ($growthNumeric <= $this->minNegativeValue && $growthNumeric < 0) {
                continue;
            }

            $table->deleteRow($key);
        }
    }
}