<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;
use Piwik\DataTable\BaseFilter;

/**
 * Limits the number of positive and negative values. A value is considered as positive if the value of $columnToRead
 * is 0 or higher. A value is considered as negative in all other cases (< 0).
 */
class Limit extends BaseFilter
{
    private $limitPositive;
    private $limitNegative;
    private $columnToRead;

    public function __construct($table, $columnToRead, $limitPositiveValues, $limitNegativeValues)
    {
        $this->columnToRead  = $columnToRead;
        $this->limitPositive = (int) $limitPositiveValues;
        $this->limitNegative = (int) $limitNegativeValues;
    }

    public function filter($table)
    {
        $countIncreaser = 0;
        $countDecreaser = 0;

        foreach ($table->getRows() as $key => $row) {

            if ($row->getColumn($this->columnToRead) >= 0) {

                $countIncreaser++;

                if ($countIncreaser > $this->limitPositive) {
                    $table->deleteRow($key);
                }

            } else {
                $countDecreaser++;

                if ($countDecreaser > $this->limitNegative) {
                    $table->deleteRow($key);
                }

            }
        }
    }
}