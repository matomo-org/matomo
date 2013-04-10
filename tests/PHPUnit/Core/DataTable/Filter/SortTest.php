<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Filter_SortTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Sort
     */
    public function testNormalSortDescending()
    {
        $table = new Piwik_DataTable();
        $table->addRowsFromArray(array(
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'count' => 100)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nintendo', 'count' => 0)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'count' => 10)
                                      )));
        $filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'desc');
        $filter->filter($table);
        $expectedOrder = array('ask', 'yahoo', 'nintendo');
        $this->assertEquals($expectedOrder, $table->getColumn('label'));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Sort
     */
    public function testNormalSortAscending()
    {
        $table = new Piwik_DataTable();
        $table->addRowsFromArray(array(
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'count' => 100.5)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nintendo', 'count' => 0.5)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'count' => 10.5)
                                      )));
        $filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'asc');
        $filter->filter($table);
        $expectedOrder = array('nintendo', 'yahoo', 'ask');
        $this->assertEquals($expectedOrder, $table->getColumn('label'));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Sort
     */
    public function testMissingColumnValuesShouldAppearLastAfterSortAsc()
    {
        $table = new Piwik_DataTable();
        $table->addRowsFromArray(array(
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nintendo', 'count' => 1)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nocolumn')),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nocolumnbis')),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'count' => 2)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'amazing')),
                                      Piwik_DataTable::ID_SUMMARY_ROW => array(Piwik_DataTable_Row::COLUMNS => array('label' => 'summary', 'count' => 10)
                                      )));
        $filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'asc');
        $filter->filter($table);
        $expectedOrder = array('nintendo', 'ask', 'amazing', 'nocolumnbis', 'nocolumn', 'summary');
        $this->assertEquals($expectedOrder, $table->getColumn('label'));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Sort
     */
    public function testMissingColumnValuesShouldAppearLastAfterSortDesc()
    {
        $table = new Piwik_DataTable();
        $table->addRowsFromArray(array(
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nintendo', 'count' => 1)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'count' => 2)),
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'amazing')),
                                      Piwik_DataTable::ID_SUMMARY_ROW => array(Piwik_DataTable_Row::COLUMNS => array('label' => 'summary', 'count' => 10)
                                      )));
        $filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'desc');
        $filter->filter($table);
        $expectedOrder = array('ask', 'nintendo', 'amazing', 'summary');
        $this->assertEquals($expectedOrder, $table->getColumn('label'));
    }

    /**
     * Test to sort by label
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Sort
     */
    public function testFilterSortString()
    {
        $idcol = Piwik_DataTable_Row::COLUMNS;
        $table = new Piwik_DataTable();
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'ask')), //1
            array($idcol => array('label' => 'piwik')), //2
            array($idcol => array('label' => 'yahoo')), //3
            array($idcol => array('label' => 'amazon')), //4
            array($idcol => array('label' => '238975247578949')), //5
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')) //6
        );
        $table->addRowsFromArray($rows);
        $expectedtable = new Piwik_DataTable();
        $rows = array(
            array($idcol => array('label' => '238975247578949')), //5
            array($idcol => array('label' => 'amazon')), //4
            array($idcol => array('label' => 'ask')), //1
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'piwik')), //2
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //6
            array($idcol => array('label' => 'yahoo')) //3
        );
        $expectedtable->addRowsFromArray($rows);
        $expectedtableReverse = new Piwik_DataTable();
        $expectedtableReverse->addRowsFromArray(array_reverse($rows));

        $filter = new Piwik_DataTable_Filter_Sort($table, 'label', 'asc');
        $filter->filter($table);
        $this->assertTrue(Piwik_DataTable::isEqual($expectedtable, $table));

        $filter = new Piwik_DataTable_Filter_Sort($table, 'label', 'desc');
        $filter->filter($table);
        $this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtableReverse));
    }

    /**
     * Test to sort by visit
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_Sort
     */
    public function testFilterSortNumeric()
    {
        $idcol = Piwik_DataTable_Row::COLUMNS;
        $table = new Piwik_DataTable();
        $rows = array(
            array($idcol => array('label' => 'google', 'nb_visits' => 897)), //0
            array($idcol => array('label' => 'ask', 'nb_visits' => -152)), //1
            array($idcol => array('label' => 'piwik', 'nb_visits' => 1.5)), //2
            array($idcol => array('label' => 'yahoo', 'nb_visits' => 154)), //3
            array($idcol => array('label' => 'amazon', 'nb_visits' => 30)), //4
            array($idcol => array('label' => '238949', 'nb_visits' => 0)), //5
            array($idcol => array('label' => 'Q*(%&*', 'nb_visits' => 1)) //6
        );
        $table->addRowsFromArray($rows);
        $expectedtable = new Piwik_DataTable();
        $rows = array(
            array($idcol => array('label' => 'ask', 'nb_visits' => -152)), //1
            array($idcol => array('label' => '238949', 'nb_visits' => 0)), //5
            array($idcol => array('label' => 'Q*(%&*', 'nb_visits' => 1)), //6
            array($idcol => array('label' => 'piwik', 'nb_visits' => 1.5)), //2
            array($idcol => array('label' => 'amazon', 'nb_visits' => 30)), //4
            array($idcol => array('label' => 'yahoo', 'nb_visits' => 154)), //3
            array($idcol => array('label' => 'google', 'nb_visits' => 897)) //0
        );
        $expectedtable->addRowsFromArray($rows);
        $expectedtableReverse = new Piwik_DataTable();
        $expectedtableReverse->addRowsFromArray(array_reverse($rows));

        $filter = new Piwik_DataTable_Filter_Sort($table, 'nb_visits', 'asc');
        $filter->filter($table);
        $this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtable));

        $filter = new Piwik_DataTable_Filter_Sort($table, 'nb_visits', 'desc');
        $filter->filter($table);
        $this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtableReverse));
    }
}
