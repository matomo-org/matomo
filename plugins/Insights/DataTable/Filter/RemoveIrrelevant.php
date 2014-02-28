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

class RemoveIrrelevant extends DataTable\BaseFilter
{
    private $minRequiredValue;
    private $columnToRead;

    public function __construct($table, $columnToRead, $minRequiredValue)
    {
        $this->columnToRead = $columnToRead;
        $this->minRequiredValue = $minRequiredValue;
    }

    public function filter($table)
    {
        if (!$this->minRequiredValue) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            $value = $row->getColumn($this->columnToRead);

            if ($this->minRequiredValue > $value) {
                $table->deleteRow($key);
            }
        }
    }
}