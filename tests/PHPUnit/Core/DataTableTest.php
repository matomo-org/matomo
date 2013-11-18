<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Common;
use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Timer;

class DataTableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testApplyFilter()
    {
        $table = $this->_getDataTable1ForTest();
        $this->assertEquals(4, $table->getRowsCount());
        $table->filter('Limit', array(2, 2));
        $this->assertEquals(2, $table->getRowsCount());
        $table->filter('Limit', array(0, 1));
        $this->assertEquals(1, $table->getRowsCount());
    }

    protected function _getSimpleTestDataTable()
    {
        $table = new DataTable;
        $table->addRowsFromArray(
            array(
                 array(Row::COLUMNS => array('label' => 'ten', 'count' => 10)),
                 array(Row::COLUMNS => array('label' => 'ninety', 'count' => 90)),
                 array(Row::COLUMNS => array('label' => 'hundred', 'count' => 100)),
                 DataTable::ID_SUMMARY_ROW => array(Row::COLUMNS => array('label' => 'summary', 'count' => 200))
            )
        );
        return $table;
    }

    /**
     * @group Core
     */
    public function testRenameColumn()
    {
        $table = $this->_getSimpleTestDataTable();
        $this->assertEquals(array(10, 90, 100, 200), $table->getColumn('count'));
        $table->renameColumn('count', 'renamed');
        $this->assertEquals(array(false, false, false, false), $table->getColumn('count'));
        $this->assertEquals(array(10, 90, 100, 200), $table->getColumn('renamed'));
    }

    /**
     * @group Core
     */
    public function testDeleteColumn()
    {
        $table = $this->_getSimpleTestDataTable();
        $this->assertEquals(array(10, 90, 100, 200), $table->getColumn('count'));
        $table->deleteColumn('count');
        $this->assertEquals(array(false, false, false, false), $table->getColumn('count'));
    }

    /**
     * @group Core
     */
    public function testDeleteRow()
    {
        $table = $this->_getSimpleTestDataTable();

        // normal row
        $idToDelete = 1;
        $this->assertEquals(2, count($table->getRowFromId($idToDelete)->getColumns()));
        $table->deleteRow($idToDelete);
        $this->assertFalse($table->getRowFromId($idToDelete));

        // summary row special case
        $idToDelete = DataTable::ID_SUMMARY_ROW;
        $this->assertEquals(2, count($table->getRowFromId($idToDelete)->getColumns()));
        $table->deleteRow($idToDelete);
        $this->assertFalse($table->getRowFromId($idToDelete));
    }

    /**
     * @group Core
     */
    public function testGetLastRow()
    {
        $table = $this->_getSimpleTestDataTable();
        $rowsCount = $table->getRowsCount();

        $this->assertEquals($table->getLastRow(), $table->getRowFromId(DataTable::ID_SUMMARY_ROW));
        $table->deleteRow(DataTable::ID_SUMMARY_ROW);

        $this->assertEquals($table->getLastRow(), $table->getRowFromId($rowsCount - 2));
    }

    /**
     * @group Core
     */
    public function testGetRowFromIdSubDataTable()
    {
        $table1 = $this->_getDataTable1ForTest();
        $idTable1 = $table1->getId();
        $table2 = $this->_getDataTable2ForTest();
        $this->assertFalse($table2->getRowFromIdSubDataTable($idTable1));

        $table2->getFirstRow()->addSubtable($table1);
        $this->assertEquals($table2->getRowFromIdSubDataTable($idTable1), $table2->getFirstRow());

        $table3 = $this->_getDataTable1ForTest();
        $idTable3 = $table3->getId();
        $table2->getLastRow()->addSubtable($table3);
        $this->assertEquals($table2->getRowFromIdSubDataTable($idTable3), $table2->getLastRow());
    }

    /**
     * we test the count rows and the count rows recursive version
     * on a Simple array (1 level only)
     *
     * @group Core
     */
    public function testCountRowsSimple()
    {

        $table = new DataTable;
        $idcol = Row::COLUMNS;
        $rows = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => 'piwik')),
            array($idcol => array('label' => 'yahoo')),
            array($idcol => array('label' => 'amazon')),
            array($idcol => array('label' => '238975247578949')),
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')));

        $table->addRowsFromArray($rows);

        $this->assertEquals(count($rows), $table->getRowsCount());
        $this->assertEquals(count($rows), $table->getRowsCountRecursive());
    }

    /**
     * we test the count rows and the count rows recursive version
     * on a Complex array (rows with 2 and 3 levels only)
     *
     * the recursive count returns
     *         the sum of the number of rows of all the subtables
     *         + the number of rows in the parent table
     *
     * @group Core
     */
    public function testCountRowsComplex()
    {

        $idcol = Row::COLUMNS;
        $idsubtable = Row::DATATABLE_ASSOCIATED;

        // table to go in the SUB table of RoW1
        $tableSubOfSubOfRow1 = new DataTable;
        $rows1sub = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'google78')),
            array($idcol => array('label' => 'googlaegge')),
            array($idcol => array('label' => 'gogeoggle')),
            array($idcol => array('label' => 'goaegaegaogle')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
        );
        $tableSubOfSubOfRow1->addRowsFromArray($rows1sub);

        // table to go in row1
        $tableSubOfRow1 = new DataTable;
        $rows1 = array(
            array($idcol => array('label' => 'google'), $idsubtable => $tableSubOfSubOfRow1),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
        );
        $tableSubOfRow1->addRowsFromArray($rows1);

        // table to go in row2
        $tableSubOfRow2 = new DataTable;
        $rows2 = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
            array($idcol => array('label' => 'agaegaesk')),
            array($idcol => array('label' => '23g  8975247578949')),
        );
        $tableSubOfRow2->addRowsFromArray($rows2);

        // main parent table
        $table = new DataTable;
        $rows = array(
            array($idcol => array('label' => 'row1')),
            array($idcol      => array('label' => 'row2'),
                  $idsubtable => $tableSubOfRow1),
            array($idcol      => array('label' => 'row3'),
                  $idsubtable => $tableSubOfRow2),
        );
        $table->addRowsFromArray($rows);


        $this->assertEquals(count($rows), $table->getRowsCount());
        $countAllRows = count($rows) + count($rows1) + count($rows2) + count($rows1sub);
        $this->assertEquals($countAllRows, $table->getRowsCountRecursive());
    }

    /**
     * Simple test of the DataTable_Row
     *
     * @group Core
     */
    public function testRow()
    {
        $columns = array('test_column' => 145,
                         092582495     => new Timer,
                         'super'       => array('this column has an array value, amazing'));
        $metadata = array('logo'  => 'piwik.png',
                          'super' => array('this column has an array value, amazing'));
        $arrayRow = array(
            Row::COLUMNS  => $columns,
            Row::METADATA => $metadata,
            'fake useless key'            => 38959,
            43905724897                   => 'value');
        $row = new Row($arrayRow);

        $this->assertEquals($columns, $row->getColumns());
        $this->assertEquals($metadata, $row->getMetadata());
        $this->assertNull($row->getIdSubDataTable());

    }

    /**
     * Simple test of the DataTable_Row
     *
     * @group Core
     */
    public function testSumRow()
    {
        $columns = array('test_int'          => 145,
                         'test_float'        => 145.5,
                         'test_float3'       => 1.5,
                         'test_stringint'    => "145",
                         "test"              => 'string fake',
                         'integerArrayToSum' => array(1 => 1, 2 => 10.0, 3 => array(1 => 2, 2 => 3)),
        );
        $metadata = array('logo'  => 'piwik.png',
                          'super' => array('this column has an array value, amazing'));
        $arrayRow = array(
            Row::COLUMNS  => $columns,
            Row::METADATA => $metadata,
            'fake useless key'            => 38959,
            43905724897                   => 'value');
        $row1 = new Row($arrayRow);

        $columns2 = array('test_int'          => 5,
                          'test_float'        => 4.5,
                          'test_float2'       => 14.5,
                          'test_stringint'    => "5",
                          0925824             => 'toto',
                          'integerArrayToSum' => array(1 => 5, 2 => 5.5, 3 => array(2 => 4)),
        );
        $finalRow = new Row(array(Row::COLUMNS => $columns2));
        $finalRow->sumRow($row1);
        $columnsWanted = array('test_int'          => 150,
                               'test_float'        => 150.0,
                               'test_float2'       => 14.5,
                               'test_float3'       => 1.5,
                               'test_stringint'    => 150, //add also strings!!
                               'test'              => 'string fake',
                               'integerArrayToSum' => array(1 => 6, 2 => 15.5, 3 => array(1 => 2, 2 => 7)),
                               0925824             => 'toto',
        );

        // Also testing that metadata is copied over
        $rowWanted = new Row(array(Row::COLUMNS => $columnsWanted, Row::METADATA => $metadata));
        $this->assertTrue(Row::isEqual($rowWanted, $finalRow));


        // testing that, 'sumRow' does not result in extra unwanted attributes being serialized
        $expectedRow = 'O:19:"Piwik\DataTable\Row":1:{s:1:"c";a:3:{i:0;a:8:{s:8:"test_int";i:150;s:10:"test_float";d:150;s:11:"test_float2";d:14.5;s:14:"test_stringint";i:150;i:0;s:4:"toto";s:17:"integerArrayToSum";a:3:{i:1;i:6;i:2;d:15.5;i:3;a:2:{i:2;i:7;i:1;i:2;}}s:11:"test_float3";d:1.5;s:4:"test";s:11:"string fake";}i:1;a:2:{s:4:"logo";s:9:"piwik.png";s:5:"super";a:1:{i:0;s:39:"this column has an array value, amazing";}}i:3;N;}}';
        $this->assertEquals($expectedRow, serialize($finalRow));

        // Testing sumRow with disabled metadata sum
        $rowWanted = new Row(array(Row::COLUMNS => $columnsWanted)); // no metadata
        $finalRow = new Row(array(Row::COLUMNS => $columns2));
        $finalRow->sumRow($row1, $enableCopyMetadata = false);
        $this->assertTrue(Row::isEqual($rowWanted, $finalRow));
    }

    /**
     * @group Core
     */
    public function test_unserializeWorks_WhenDataTableFormatPriorPiwik2()
    {
        $serializedDatatable = '';
        // Prior Piwik 2.0, we didn't use namespaces. Some
        require PIWIK_INCLUDE_PATH . "/tests/resources/pre-Piwik2-DataTable-archived.php";
        require_once PIWIK_INCLUDE_PATH . "/core/DataTable/Bridges.php";

        $this->assertTrue(strlen($serializedDatatable) > 1000);

        $table = unserialize($serializedDatatable);
        $this->assertTrue($table[0] instanceof \Piwik\DataTable\Row);
    }

    /**
     * Test that adding two string column values results in an exception.
     *
     * @group Core
     * 
     * @expectedException Exception
     */
    public function testSumRow_stringException()
    {
        $columns = array(
            'super' => array('this column has an array string that will be 0 when algorithm sums the value'),
        );
        $row1 = new Row(array(Row::COLUMNS => $columns));

        $columns2 = array(
            'super' => array('this column has geagaean array value, amazing'),
        );
        $row2 = new Row(array(Row::COLUMNS => $columns2));

        $row2->sumRow($row1);
        $this->fail("sumRow did not throw when adding two string columns.");
    }

    /**
     * Test serialize with an infinite recursion (a row linked to a table in the parent hierarchy)
     * After 100 recursion must throw an exception
     *
     * @group Core
     * 
     * @expectedException Exception
     */
    public function testSerializeWithInfiniteRecursion()
    {
        $table = new DataTable;
        $table->addRowFromArray(array(Row::COLUMNS              => array('visits' => 245, 'visitors' => 245),
                                      Row::DATATABLE_ASSOCIATED => $table,));

        $table->getSerialized();
    }


    /**
     * Test queing filters
     *
     * @group Core
     */
    public function testFilterQueueSortString()
    {

        $idcol = Row::COLUMNS;

        $table = new DataTable;
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'tsk')), //1
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //2
        );
        $table->addRowsFromArray($rows);

        $expectedtable = new DataTable;
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //2
            array($idcol => array('label' => 'tsk')), //1
        );
        $expectedtable->addRowsFromArray($rows);

        $expectedtableReverse = new DataTable;
        $expectedtableReverse->addRowsFromArray(array_reverse($rows));

        $tableCopy = clone $table;
        $this->assertTrue(DataTable::isEqual($tableCopy, $table));

        // queue the filter and check the table didnt change
        $table->queueFilter("Sort", array('label', 'asc'));
        $this->assertTrue(DataTable::isEqual($tableCopy, $table));

        // apply filter and check the table is sorted
        $table->applyQueuedFilters();
        $this->assertTrue(DataTable::isEqual($expectedtable, $table));

        // apply one more filter check it hasnt changed
        $table->queueFilter("Sort", array('label', 'desc'));
        $this->assertTrue(DataTable::isEqual($expectedtable, $table));

        // now apply the second sort and check it is correctly sorted
        $table->applyQueuedFilters();
        $this->assertTrue(DataTable::isEqual($expectedtableReverse, $table));

        // do one more time to make sure it doesnt change
        $table->applyQueuedFilters();
        $this->assertTrue(DataTable::isEqual($expectedtableReverse, $table));
    }

    /**
     * General tests that tries to test the normal behaviour of DataTable
     *
     * We create some tables, add rows, some of the rows link to sub tables
     *
     * Then we serialize everything, and we check that the unserialize give the same object back
     *
     * @group Core
     */
    public function testGeneral()
    {
        /*
         * create some fake tables to make sure that the serialized array of the first TABLE
         * does not take in consideration those tables
         */
        $useless1 = new DataTable;
        $useless1->addRowFromArray(array(Row::COLUMNS => array(13,),));
        /*
         * end fake tables
         */

        /*
         * MAIN TABLE
         */
        $table = new DataTable;
        $subtable = new DataTable;
        $idtable = $table->getId();
        $idsubtable = $subtable->getId();

        /*
         * create some fake tables to make sure that the serialized array of the first TABLE
         * does not take in consideration those tables
         * -> we check that the DataTable_Manager is not impacting DataTable 
         */
        $useless2 = new DataTable;
        $useless1->addRowFromArray(array(Row::COLUMNS => array(8487,),));
        $useless3 = new DataTable;
        $useless3->addRowFromArray(array(Row::COLUMNS => array(8487,),));
        /*
         * end fake tables
         */

        $row = array(Row::COLUMNS  => array(0 => 1554, 1 => 42, 2 => 657, 3 => 155744,),
                     Row::METADATA => array('logo' => 'test.png'));
        $row = new Row($row);

        $table->addRow($row);
        $table->addRowFromArray(array(Row::COLUMNS  => array(0 => 1554, 1 => 42,),
                                      Row::METADATA => array('url' => 'piwik.org')));

        $table->addRowFromArray(array(Row::COLUMNS              => array(0 => 787877888787,),
                                      Row::METADATA             => array('url' => 'OUPLA ADDED'),
                                      Row::DATATABLE_ASSOCIATED => $subtable));

        /*
         * SUB TABLE
         */


        $row = array(Row::COLUMNS  => array(0 => 1554,),
                     Row::METADATA => array('searchengine' => 'google'),
        );
        $subtable->addRowFromArray($row);

        $row = array(Row::COLUMNS  => array(0 => 84894,),
                     Row::METADATA => array('searchengine' => 'yahoo'),
        );
        $subtable->addRowFromArray($row);
        $row = array(Row::COLUMNS  => array(0 => 4898978989,),
                     Row::METADATA => array('searchengine' => 'ask'),
        );
        $subtable->addRowFromArray($row);


        /*
         * SUB SUB TABLE
         */
        $subsubtable = new DataTable;
        $subsubtable->addRowFromArray(array(Row::COLUMNS  => array(245),
                                            Row::METADATA => array('yes' => 'subsubmetadata1'),)
        );

        $subsubtable->addRowFromArray(array(Row::COLUMNS  => array(13,),
                                            Row::METADATA => array('yes' => 'subsubmetadata2'),)
        );

        $row = array(Row::COLUMNS              => array(0 => 666666666666666,),
                     Row::METADATA             => array('url' => 'NEW ROW ADDED'),
                     Row::DATATABLE_ASSOCIATED => $subsubtable);

        $subtable->addRowFromArray($row);

        $idsubsubtable = $subsubtable->getId();

        $serialized = ($table->getSerialized());

        $this->assertEquals(array_keys($serialized), array($idsubsubtable, $idsubtable, 0));

        // In the next test we compare an unserialized datatable with its original instance.
        // The unserialized datatable rows will have positive DATATABLE_ASSOCIATED ids.
        // Positive DATATABLE_ASSOCIATED ids mean that the associated sub-datatables are not loaded in memory.
        // In this case, this is NOT true: we know that the sub-datatable is loaded in memory.
        // HOWEVER, because of datatable id conflicts happening in the datatable manager, it is not yet
        // possible to know, after unserializing a datatable, if its sub-datatables are loaded in memory.
        $expectedTableRows = array();
        foreach ($table->getRows() as $currentRow) {
            $expectedTableRow = clone $currentRow;

            $currentRowAssociatedDatatableId = $currentRow->c[Row::DATATABLE_ASSOCIATED];
            if ($currentRowAssociatedDatatableId != null) {
                // making DATATABLE_ASSOCIATED ids positive
                $expectedTableRow->c[Row::DATATABLE_ASSOCIATED] = -1 * $currentRowAssociatedDatatableId;
            }

            $expectedTableRows[] = $expectedTableRow;
        }

        $tableAfter = new DataTable;
        $tableAfter->addRowsFromSerializedArray($serialized[0]);

        $this->assertEquals($expectedTableRows, $tableAfter->getRows());

        $subsubtableAfter = new DataTable;
        $subsubtableAfter->addRowsFromSerializedArray($serialized[$idsubsubtable]);
        $this->assertEquals($subsubtable->getRows(), $subsubtableAfter->getRows());
        $this->assertEquals($subsubtable->getRows(), DataTable::fromSerializedArray($serialized[$idsubsubtable])->getRows());
        $this->assertTrue($subsubtable->getRowsCount() > 0);

        $this->assertEquals($table, Manager::getInstance()->getTable($idtable));
        $this->assertEquals($subsubtable, Manager::getInstance()->getTable($idsubsubtable));
    }

    /**
     * for all datatable->addDatatable tests we check that
     * - row uniqueness is based on the label + presence of the SUBTABLE id
     *         => the label is the criteria used to match 2 rows in 2 datatable
     * - no metadata are lost in the first datatable rows that have been changed
     * - when a subtable
     */


    /**
     * add an empty datatable to a normal datatable
     *
     * @group Core
     */
    public function testAddSimpleNoRowTable2()
    {
        $table = $this->_getDataTable1ForTest();
        $tableEmpty = new DataTable;
        $tableAfter = clone $table;
        $tableAfter->addDataTable($tableEmpty);
        $this->assertTrue(DataTable::isEqual($table, $tableAfter));
    }

    /**
     * add a normal datatable to an empty datatable
     *
     * @group Core
     */
    public function testAddSimpleNoRowTable1()
    {
        $table = $this->_getDataTable1ForTest();
        $tableEmpty = new DataTable;
        $tableEmpty->addDataTable($table);
        $this->assertTrue(DataTable::isEqual($tableEmpty, $table));
    }

    /**
     * add to the datatable another datatable// they don't have any row in common
     *
     * @group Core
     */
    public function testAddSimpleNoCommonRow()
    {
        $table1 = $this->_getDataTable1ForTest();
        $table2 = $this->_getDataTable2ForTest();

        $table1->addDataTable($table2);

        $rowsExpected = array_merge($this->_getRowsDataTable1ForTest(), $this->_getRowsDataTable2ForTest());
        $tableExpected = new DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table1, $tableExpected));
    }

    /**
     * add 2 datatable with some common rows
     *
     * @group Core
     */
    public function testAddSimpleSomeCommonRow()
    {

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
        );
        $table = new DataTable;
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'test', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 111)),
            array($idcol => array('label' => ' google ', 'visits' => 5)),
            array($idcol => array('label' => '123', 'visits' => 2)),
        );
        $table2 = new DataTable;
        $table2->addRowsFromArray($rows2);

        $table->addDataTable($table2);

        $rowsExpected = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 113)),
            array($idcol => array('label' => '123', 'visits' => 4)),
            array($idcol => array('label' => 'test', 'visits' => 1)),
            array($idcol => array('label' => ' google ', 'visits' => 5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
        );
        $tableExpected = new DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table, $tableExpected));
    }

    /**
     * add 2 datatable with only common rows
     *
     * @group Core
     */
    public function testAddSimpleAllCommonRow()
    {
        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
        );
        $table = new DataTable;
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'google', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => 0)),
            array($idcol => array('label' => '123', 'visits' => 1.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 8))
        );
        $table2 = new DataTable;
        $table2->addRowsFromArray($rows2);

        $table->addDataTable($table2);

        $rowsExpected = array(
            array($idcol => array('label' => 'google', 'visits' => 0)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 3.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 15))
        );
        $tableExpected = new DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table, $tableExpected));
    }

    /**
     * test add 2 different tables to the same table
     *
     * @group Core
     */
    public function testAddDataTable2times()
    {

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 0)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 1))
        );
        $table = new DataTable;
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'google2', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => 100)),
            array($idcol => array('label' => '123456', 'visits' => 1.5)),
        );
        $table2 = new DataTable;
        $table2->addRowsFromArray($rows2);

        $rows3 = array(
            array($idcol => array('label' => 'google2', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => -10)),
            array($idcol => array('label' => '123ab', 'visits' => 1.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 3))
        );
        $table3 = new DataTable;
        $table3->addRowsFromArray($rows3);

        // add the 2 tables
        $table->addDataTable($table2);
        $table->addDataTable($table3);

        $rowsExpected = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 90)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            array($idcol => array('label' => 'google2', 'visits' => -2)),
            array($idcol => array('label' => '123456', 'visits' => 1.5)),
            array($idcol => array('label' => '123ab', 'visits' => 1.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 4))
        );
        $tableExpected = new DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table, $tableExpected));
    }


    /**
     * @group Core
     */
    public function testUnrelatedDataTableNotDestructed()
    {
        $mockedDataTable = $this->getMock('\Piwik\DataTable', array('__destruct'));
        $mockedDataTable->expects($this->never())->method('__destruct');

        $rowBeingDestructed = new Row();

        // we simulate the fact that the value of Row::DATATABLE_ASSOCIATED retrieved
        // from the database is in conflict with one of the Manager managed table identifiers.
        // This is a rare but legitimate case as identifiers are not thoroughly synchronized
        // when the expanded parameter is false.
        $rowBeingDestructed->c[Row::DATATABLE_ASSOCIATED] = $mockedDataTable->getId();

        Common::destroy($rowBeingDestructed);
    }

    /**
     * @group Core
     */
    public function testGetSerializedCallsCleanPostSerialize()
    {
        $mockedDataTableRow = $this->getMock('\Piwik\DataTable\Row', array('cleanPostSerialize'));
        $mockedDataTableRow->expects($this->once())->method('cleanPostSerialize');

        $dataTableBeingSerialized = new DataTable();
        $dataTableBeingSerialized->addRow($mockedDataTableRow);

        $dataTableBeingSerialized->getSerialized();
    }

    /**
     * @group Core
     */
    public function testSubDataTableIsDestructed()
    {
        $mockedDataTable = $this->getMock('\Piwik\DataTable', array('__destruct'));
        $mockedDataTable->expects($this->once())->method('__destruct');

        $rowBeingDestructed = new Row();
        $rowBeingDestructed->setSubtable($mockedDataTable);

        Common::destroy($rowBeingDestructed);
    }

    protected function _getDataTable1ForTest()
    {
        $rows = $this->_getRowsDataTable1ForTest();
        $table = new DataTable;
        $table->addRowsFromArray($rows);
        return $table;
    }

    protected function _getDataTable2ForTest()
    {
        $rows = $this->_getRowsDataTable2ForTest();
        $table = new DataTable;
        $table->addRowsFromArray($rows);
        return $table;
    }

    protected function _getRowsDataTable1ForTest()
    {
        $rows = array(
            array(Row::COLUMNS => array('label' => 'google', 'visits' => 1)),
            array(Row::COLUMNS => array('label' => 'ask', 'visits' => 2)),
            array(Row::COLUMNS => array('label' => '123', 'visits' => 2)),
            DataTable::ID_SUMMARY_ROW => array(Row::COLUMNS => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 4))

        );
        return $rows;
    }

    protected function _getRowsDataTable2ForTest()
    {
        $rows = array(
            array(Row::COLUMNS => array('label' => 'test', 'visits' => 1)),
            array(Row::COLUMNS => array('label' => ' google ', 'visits' => 3)),
            array(Row::COLUMNS => array('label' => '123a', 'visits' => 2)),
        );
        return $rows;
    }

}
