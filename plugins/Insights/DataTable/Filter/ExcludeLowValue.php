<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable;

/**
 * Removes all rows whose value is too low.
 */
class ExcludeLowValue extends DataTable\BaseFilter
{
    private $minimumValue;
    private $columnToRead;
    private $columnToCheckToBeTrue;

    /**
     * @param DataTable $table
     * @param string $columnToRead
     * @param int    $minimumValue
     * @param string $columnToCheckToBeTrue  if set, we will delete a row only if this column evaluates to true. If
     *                                       column does not evaluate to true we will not delete the row even if
     *                                       the value is lower than the minimumValue.
     */
    public function __construct($table, $columnToRead, $minimumValue, $columnToCheckToBeTrue = '')
    {
        $this->columnToRead = $columnToRead;
        $this->minimumValue = $minimumValue;
        $this->columnToCheckToBeTrue = $columnToCheckToBeTrue;
    }

    public function filter($table)
    {
        if (!$this->minimumValue) {
            return;
        }

        foreach ($table->getRows() as $key => $row) {

            if ($this->columnToCheckToBeTrue && !$row->getColumn($this->columnToCheckToBeTrue)) {
                continue;
            }

            $value = $row->getColumn($this->columnToRead);

            if ($this->minimumValue > abs($value)) {
                $table->deleteRow($key);
            }
        }
    }
}