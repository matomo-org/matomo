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
use Piwik\Plugins\Insights\DataTable\Filter\OrderBy;

/**
 * @group Insights
 * @group FilterOrderByTest
 * @group Unit
 * @group Core
 */
class FilterOrderByTest extends BaseUnitTest
{
    public function setUp(): void
    {
        $this->table = new DataTable();
    }

    public function testOrderByShouldListAllHighestPositiveValuesFirstThenAllNegativeValuesLowestFirst()
    {
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'pos1', 'growth' => 12)),
            array(Row::COLUMNS => array('label' => 'pos2', 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'neg1', 'growth' => -9)),
            array(Row::COLUMNS => array('label' => 'pos3', 'growth' => 10)),
            array(Row::COLUMNS => array('label' => 'neg2', 'growth' => -11)),
            array(Row::COLUMNS => array('label' => 'neg3', 'growth' => -13)),
            array(Row::COLUMNS => array('label' => 'pos4', 'growth' => 9)),
            array(Row::COLUMNS => array('label' => 'pos5', 'growth' => 10)),
            array(Row::COLUMNS => array('label' => 'pos6', 'growth' => 0)),
            array(Row::COLUMNS => array('label' => 'neg4', 'growth' => -7)),
            array(Row::COLUMNS => array('label' => 'neg5', 'growth' => -8))
        ));

        $this->applyOrderByFilter();

        $this->assertOrder(array('pos1', 'pos3', 'pos5', 'pos4', 'pos2', 'pos6', 'neg3', 'neg2', 'neg1', 'neg5', 'neg4'));
    }

    public function testOrderByShouldSortDependingOnNbVisitsIfColumnsHaveSameValue()
    {
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'pos1', 'nb_visits' => 40, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos2', 'nb_visits' => 55, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos3', 'nb_visits' => 35, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos4', 'nb_visits' => 60, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos5', 'nb_visits' => 7, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos6', 'nb_visits' => 35, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'neg1', 'nb_visits' => 33, 'growth' => -5)),
            array(Row::COLUMNS => array('label' => 'neg2', 'nb_visits' => 34, 'growth' => -5)),
            array(Row::COLUMNS => array('label' => 'neg3', 'nb_visits' => 99, 'growth' => -5)),
            array(Row::COLUMNS => array('label' => 'neg4', 'nb_visits' => 20, 'growth' => -5))
        ));

        $this->applyOrderByFilter();

        $this->assertOrder(array('pos4', 'pos2', 'pos1', 'pos3', 'pos6', 'pos5', 'neg3', 'neg2', 'neg1', 'neg4'));
    }

    public function testOrderByShouldSortDependingOnNbVisitsIfColumnsHaveSameValueAndNbVisitsIsNegative()
    {
        $this->table->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'pos1', 'nb_visits' => -40, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos2', 'nb_visits' => -55, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos3', 'nb_visits' => -35, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos4', 'nb_visits' => -60, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos5', 'nb_visits' => -7, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'pos6', 'nb_visits' => -35, 'growth' => 7)),
            array(Row::COLUMNS => array('label' => 'neg1', 'nb_visits' => -33, 'growth' => -5)),
            array(Row::COLUMNS => array('label' => 'neg2', 'nb_visits' => -34, 'growth' => -5)),
            array(Row::COLUMNS => array('label' => 'neg3', 'nb_visits' => -99, 'growth' => -5)),
            array(Row::COLUMNS => array('label' => 'neg4', 'nb_visits' => -20, 'growth' => -5))
        ));

        $this->applyOrderByFilter();

        $this->assertOrder(array('pos4', 'pos2', 'pos1', 'pos3', 'pos6', 'pos5', 'neg3', 'neg2', 'neg1', 'neg4'));
    }

    private function applyOrderByFilter()
    {
        $filter = new OrderBy($this->table, 'growth', 'nb_visits');
        $filter->filter($this->table);
    }
}
