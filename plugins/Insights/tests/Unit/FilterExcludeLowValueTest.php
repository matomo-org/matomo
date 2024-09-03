<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue;

/**
 * @group Insights
 * @group FilterExcludeLowValueTest
 * @group Unit
 * @group Core
 */
class FilterExcludeLowValueTest extends BaseUnitTest
{
    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', 'growth' => 22, 'isFooBar' => false)),
            array(Row::COLUMNS => array('label' => 'val2', 'growth' => 14, 'isFooBar' => true)),
            array(Row::COLUMNS => array('label' => 'val3', 'growth' => 18, 'isFooBar' => false)),
            array(Row::COLUMNS => array('label' => 'val4', 'growth' => 20, 'isFooBar' => true)),
            array(Row::COLUMNS => array('label' => 'val5', 'growth' => 22, 'isFooBar' => true)),
            array(Row::COLUMNS => array('label' => 'val6', 'growth' => 25, 'isFooBar' => true)),
            array(Row::COLUMNS => array('label' => 'val7', 'growth' => 17, 'isFooBar' => false)),
            array(Row::COLUMNS => array('label' => 'val8', 'growth' => 20, 'isFooBar' => false)),
            array(Row::COLUMNS => array('label' => 'val9', 'growth' => 0, 'isFooBar' => false)),
            array(Row::COLUMNS => array('label' => 'val10', 'growth' => 15, 'isFooBar' => false)),
            array(Row::COLUMNS => array('label' => 'val11', 'growth' => 16, 'isFooBar' => true))
        ));
    }

    public function testShouldNotRemoveAnyIfMinValueIsZero()
    {
        $rowsCountBefore = $this->table->getRowsCount();
        $this->assertGreaterThan(0, $rowsCountBefore);

        $this->excludeLowValues(0);

        $this->assertSame($rowsCountBefore, $this->table->getRowsCount());
    }

    public function testShouldKeepAllRowsHavingHigherGrowth()
    {
        $this->excludeLowValues(15);

        $this->assertOrder(array('val1', 'val3', 'val4', 'val5', 'val6', 'val7', 'val8', 'val10', 'val11'));
    }

    public function testShouldKeepRowsIfTheyHaveGivenMinGrowth()
    {
        $this->excludeLowValues(22);

        $this->assertOrder(array('val1', 'val5', 'val6'));
    }

    public function testShouldRemoveAllIfMinValueIsTooHigh()
    {
        $this->excludeLowValues(99);

        $this->assertOrder(array());
    }

    public function testShouldRemoveValuesOnlyIfColumnToCheckIsTrue()
    {
        $this->excludeLowValues(21, 'isFooBar');

        $this->assertOrder(array('val1', 'val3', 'val5', 'val6', 'val7', 'val8', 'val9', 'val10'));
    }

    private function excludeLowValues($minimumValue, $columnToCheck = null)
    {
        $filter = new ExcludeLowValue($this->table, 'growth', $minimumValue, $columnToCheck);
        $filter->filter($this->table);
    }
}
