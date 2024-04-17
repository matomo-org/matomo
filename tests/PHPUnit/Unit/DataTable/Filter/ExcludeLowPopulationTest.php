<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Filter;

use Piwik\DataTable\Filter\ExcludeLowPopulation;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class ExcludeLowPopulationTest extends \PHPUnit\Framework\TestCase
{
    protected function getTestDataTable()
    {
        $table = new DataTable();
        $table->addRowsFromArray(
            array(
                 array(Row::COLUMNS => array('label' => 'zero', 'count' => 0)),
                 array(Row::COLUMNS => array('label' => 'one', 'count' => 1)),
                 array(Row::COLUMNS => array('label' => 'onedotfive', 'count' => 1.5)),
                 array(Row::COLUMNS => array('label' => 'ten', 'count' => 10)),
                 array(Row::COLUMNS => array('label' => 'ninety', 'count' => 90)),
                 array(Row::COLUMNS => array('label' => 'hundred', 'count' => 100)),
            )
        );
        return $table;
    }

    /**
     * @group Core
     **/
    public function testStandardTable()
    {
        $table = $this->getTestDataTable();
        $filter = new ExcludeLowPopulation($table, 'count', 1.1);
        $filter->filter($table);
        $this->assertEquals(4, $table->getRowsCount());
        $this->assertEquals(array(1.5, 10, 90, 100), $table->getColumn('count'));
    }

    /**
     * @group Core
     **/
    public function testFilterEqualOneDoesFilter()
    {
        $table = $this->getTestDataTable();
        $filter = new ExcludeLowPopulation($table, 'count', 1);
        $filter->filter($table);
        $this->assertEquals(5, $table->getRowsCount());
    }

    /**
     * @group Core
     **/
    public function testFilterEqualZeroDoesFilter()
    {
        $table = $this->getTestDataTable();
        $filter = new ExcludeLowPopulation($table, 'count', 0);
        $filter->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $this->assertEquals(array(10, 90, 100), $table->getColumn('count'));
    }

    /**
     * @group Core
    */
    public function testFilterSpecifyExcludeLowPopulationThresholdDoesFilter()
    {
        $table = $this->getTestDataTable();
        $filter = new ExcludeLowPopulation($table, 'count', 0, 0.4); //40%
        $filter->filter($table);
        $this->assertEquals(2, $table->getRowsCount());
        $this->assertEquals(array(90, 100), $table->getColumn('count'));
    }

    /**
     * Test to exclude low population filter
     *
     * @group Core
    */
    public function testFilterLowpop1()
    {

        $idcol = Row::COLUMNS;

        $table = new DataTable();
        $rows = array(
            array($idcol => array('label' => 'google', 'nb_visits' => 897)), //0
            array($idcol => array('label' => 'ask', 'nb_visits' => -152)), //1
            array($idcol => array('label' => 'piwik', 'nb_visits' => 1.5)), //2
            array($idcol => array('label' => 'piwik2', 'nb_visits' => 1.4)), //2
            array($idcol => array('label' => 'yahoo', 'nb_visits' => 154)), //3
            array($idcol => array('label' => 'amazon', 'nb_visits' => 30)), //4
            array($idcol => array('label' => '238949', 'nb_visits' => 0)), //5
            array($idcol => array('label' => 'Q*(%&*', 'nb_visits' => 1)), //6
            array($idcol => array('label' => 'Q*(%&*2', 'nb_visits' => -1.5)), //6
        );
        $table->addRowsFromArray($rows);

        $expectedtable = new DataTable();
        $rows = array(
            array($idcol => array('label' => 'google', 'nb_visits' => 897)), //0
            array($idcol => array('label' => 'piwik', 'nb_visits' => 1.5)), //2
            array($idcol => array('label' => 'piwik2', 'nb_visits' => 1.4)), //2
            array($idcol => array('label' => 'yahoo', 'nb_visits' => 154)), //3
            array($idcol => array('label' => 'amazon', 'nb_visits' => 30)), //4
        );
        $expectedtable->addRowsFromArray($rows);

        $filter = new ExcludeLowPopulation($table, 'nb_visits', 1.4);
        $filter->filter($table);

        $this->assertTrue(DataTable::isEqual($table, $expectedtable));
    }
}
