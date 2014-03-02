<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;

class MinGrowth extends BaseFilter
{
    private $minValue;
    private $columnToRead;

    public function __construct($table, $columnToRead, $minValue)
    {
        $this->columnToRead = $columnToRead;
        $this->minValue = abs($minValue);
    }

    public function filter($table)
    {
        if (!$this->minValue) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            $growthNumeric = $row->getColumn($this->columnToRead);

            if ($growthNumeric >= $this->minValue) {
                continue;
            } elseif ($growthNumeric <= -$this->minValue) {
                continue;
            }

            $table->deleteRow($key);
        }
    }
}