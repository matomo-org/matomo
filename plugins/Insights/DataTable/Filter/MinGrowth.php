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
    private $minGrowthPercent;
    private $growthColumn;

    public function __construct($table, $growthColumn, $minGrowthPercent)
    {
        $this->growthColumn = $growthColumn;
        $this->minGrowthPercent = $minGrowthPercent;
    }

    public function filter($table)
    {
        if (!$this->minGrowthPercent) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            $growthNumeric = $row->getColumn($this->growthColumn);

            if ($growthNumeric > $this->minGrowthPercent) {
                continue;
            } elseif ($growthNumeric < -$this->minGrowthPercent) {
                continue;
            }

            $table->deleteRow($key);
        }
    }
}