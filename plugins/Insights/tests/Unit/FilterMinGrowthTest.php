<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Insights\DataTable\Filter\MinGrowth;

/**
 * @group Insights
 * @group FilterMinGrowthTest
 * @group Unit
 * @group Core
 */
class FilterMinGrowthTest extends BaseUnitTest
{
    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'pos1', 'growth' => 22)),
            array(Row::COLUMNS => array('label' => 'pos2', 'growth' => 14)),
            array(Row::COLUMNS => array('label' => 'neg1', 'growth' => -18)),
            array(Row::COLUMNS => array('label' => 'pos3', 'growth' => 20)),
            array(Row::COLUMNS => array('label' => 'neg2', 'growth' => -22)),
            array(Row::COLUMNS => array('label' => 'neg3', 'growth' => -25)),
            array(Row::COLUMNS => array('label' => 'pos4', 'growth' => 17)),
            array(Row::COLUMNS => array('label' => 'pos5', 'growth' => 20)),
            array(Row::COLUMNS => array('label' => 'pos6', 'growth' => 0)),
            array(Row::COLUMNS => array('label' => 'neg4', 'growth' => -15)),
            array(Row::COLUMNS => array('label' => 'neg5', 'growth' => -16))
        ));
    }

    public function testShouldNotRemoveAnyIfMinGrowthIsZero()
    {
        $rowsCountBefore = $this->table->getRowsCount();
        $this->assertGreaterThan(0, $rowsCountBefore);

        $this->applyMinGrowthFilter(0, 0);

        $this->assertSame($rowsCountBefore, $this->table->getRowsCount());
    }

    public function testShouldKeepAllRowsHavingHigherGrowth()
    {
        $this->applyMinGrowthFilter(15, -15);

        $this->assertOrder(array('pos1', 'neg1', 'pos3', 'neg2', 'neg3', 'pos4', 'pos5', 'neg4', 'neg5'));
    }

    public function testShouldKeepRowsIfTheyHaveGivenMinGrowth()
    {
        $this->applyMinGrowthFilter(22, -22);

        $this->assertOrder(array('pos1', 'neg2', 'neg3'));
    }

    public function testDifferentGrowth()
    {
        $this->applyMinGrowthFilter(22, -16);
        $this->assertOrder(array('pos1', 'neg1', 'neg2', 'neg3', 'neg5'));
    }

    public function testDifferentGrowth2()
    {
        $this->applyMinGrowthFilter(15, -24);
        $this->assertOrder(array('pos1', 'pos3', 'neg3', 'pos4', 'pos5'));
    }

    public function testShouldRemoveAllIfMinGrowthIsTooHigh()
    {
        $this->applyMinGrowthFilter(999, -999);

        $this->assertOrder(array());
    }

    private function applyMinGrowthFilter($minGrowthPercentPositive, $minGrowthPercentNegative)
    {
        $filter = new MinGrowth($this->table, 'growth', $minGrowthPercentPositive, $minGrowthPercentNegative);
        $filter->filter($this->table);
    }

}
