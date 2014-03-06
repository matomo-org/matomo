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
    private $minPositiveGrowth;
    private $minNegativeGrowth;
    private $columnToRead;

    public function __construct($table, $columnToRead, $minPositiveGrowth, $minNegativeGrowth)
    {
        $this->columnToRead = $columnToRead;
        $this->minPositiveGrowth = $minPositiveGrowth;
        $this->minNegativeGrowth = $minNegativeGrowth;
    }

    public function filter($table)
    {
        if (!$this->minPositiveGrowth && !$this->minNegativeGrowth) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            $growthNumeric = $row->getColumn($this->columnToRead);

            if ($growthNumeric >= $this->minPositiveGrowth && $growthNumeric >= 0) {
                continue;
            } elseif ($growthNumeric <= $this->minNegativeGrowth && $growthNumeric < 0) {
                continue;
            }

            $table->deleteRow($key);
        }
    }
}