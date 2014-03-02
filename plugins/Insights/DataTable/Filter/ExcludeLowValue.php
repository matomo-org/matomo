<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable;

class ExcludeLowValue extends DataTable\BaseFilter
{
    private $minimumValue;
    private $columnToRead;

    public function __construct($table, $columnToRead, $minimumValue)
    {
        $this->columnToRead = $columnToRead;
        $this->minimumValue = $minimumValue;
    }

    public function filter($table)
    {
        if (!$this->minimumValue) {
            return;
        }

        $minimumValue = $this->minimumValue;
        $isValueLowPopulation = function ($value) use ($minimumValue) {
            return $value < $minimumValue;
        };

        $table->filter('ColumnCallbackDeleteRow', array($this->columnToRead, $isValueLowPopulation));
    }
}