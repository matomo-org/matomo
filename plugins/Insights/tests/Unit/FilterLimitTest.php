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
use Piwik\Plugins\Insights\DataTable\Filter\Limit;

/**
 * @group Insights
 * @group FilterLimitTest
 * @group Unit
 * @group Core
 */
class FilterLimitTest extends BaseUnitTest
{
    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'pos1', 'growth' => 12)),
            array(Row::COLUMNS => array('label' => 'pos2', 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'neg1', 'growth' => -9)),
            array(Row::COLUMNS => array('label' => 'pos3', 'growth' => 10)),
            array(Row::COLUMNS => array('label' => 'neg2', 'growth' => -11)),
            array(Row::COLUMNS => array('label' => 'neg3', 'growth' => -13)),
            array(Row::COLUMNS => array('label' => 'pos4', 'growth' => 9)),
            array(Row::COLUMNS => array('label' => 'pos5', 'growth' => 10)),
            array(Row::COLUMNS => array('label' => 'neg4', 'growth' => -7)),
            array(Row::COLUMNS => array('label' => 'neg5', 'growth' => -8))
        ));
    }

    public function testFilterAll()
    {
        $this->applyLimit($limitIncreaser = 0, $limitDecreaser = 0);

        $this->assertOrder(array());
    }

    public function testNoDescreaser()
    {
        $this->applyLimit($limitIncreaser = 4, $limitDecreaser = 0);

        $this->assertOrder(array('pos1', 'pos2', 'pos3', 'pos4'));
    }

    public function testNoIncreaser()
    {
        $this->applyLimit($limitIncreaser = 0, $limitDecreaser = 4);

        $this->assertOrder(array('neg1', 'neg2', 'neg3', 'neg4'));
    }

    public function testShouldKeepOrderOfRows()
    {
        $this->applyLimit($limitIncreaser = 3, $limitDecreaser = 2);

        $this->assertOrder(array('pos1', 'pos2', 'neg1', 'pos3', 'neg2'));
    }

    public function testShouldReturnAllRowsIfLimitIsHighEnough()
    {
        $this->applyLimit($limitIncreaser = 99, $limitDecreaser = 99);

        $this->assertOrder(array('pos1', 'pos2', 'neg1', 'pos3', 'neg2', 'neg3', 'pos4', 'pos5', 'neg4', 'neg5'));
    }

    public function testShouldReturnAllRowsIfNoLimitIsSet()
    {
        $this->applyLimit($limitIncreaser = -1, $limitDecreaser = -1);

        $this->assertOrder(array('pos1', 'pos2', 'neg1', 'pos3', 'neg2', 'neg3', 'pos4', 'pos5', 'neg4', 'neg5'));
    }

    public function testShouldReturnAllRowsIfNoLimitIsSetOnlyIncreaser()
    {
        $this->applyLimit($limitIncreaser = -1, $limitDecreaser = 2);

        $this->assertOrder(array('pos1', 'pos2', 'neg1', 'pos3', 'neg2', 'pos4', 'pos5'));
    }

    private function applyLimit($limitIncrease, $limitDecrease)
    {
        $filter = new Limit($this->table, 'growth', $limitIncrease, $limitDecrease);
        $filter->filter($this->table);
    }
}
