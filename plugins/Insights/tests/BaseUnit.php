<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group Insights
 * @group Unit
 * @group Core
 */
class BaseUnit extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataTable
     */
    protected $table;

    protected function assertOrder($expectedOrder)
    {
        $this->assertEquals($expectedOrder, $this->table->getColumn('label'));
        $this->assertEquals(count($expectedOrder), $this->table->getRowsCount());
    }

    protected function assertColumnValues($rowsWithValues)
    {
        $index = 0;
        foreach ($this->table->getRows() as $row) {
            $rowToCheck = $rowsWithValues[$index];
            foreach ($rowToCheck as $columnToCheck => $expectedValue) {
                $actualValue = $row->getColumn($columnToCheck);
                $this->assertEquals($expectedValue, $actualValue, "$columnToCheck in row $index does not match assumed $actualValue is $expectedValue");
            }
            $index++;
        }

        $this->assertEquals(count($rowsWithValues), $this->table->getRowsCount());
    }

}
