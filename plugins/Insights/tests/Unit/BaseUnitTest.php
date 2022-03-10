<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Unit;

use Piwik\DataTable;

/**
 * Abstract class because it avoids it being picked up as a test case
 * (which would trigger warning because it has no test)
 *
 * @group Insights
 * @group Unit
 * @group Core
 */
abstract class BaseUnitTest extends \PHPUnit\Framework\TestCase
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
