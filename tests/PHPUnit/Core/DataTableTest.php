<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group DataTable
     */
    public function testApplyFilter()
    {
        $table = $this->_getDataTable1ForTest();
        $this->assertEquals(4, $table->getRowsCount());
        $table->filter('Limit', array(2, 2));
        $this->assertEquals(2, $table->getRowsCount());
        $table->filter('Piwik_DataTable_Filter_Limit', array(0, 1));
        $this->assertEquals(1, $table->getRowsCount());
    }

    protected function _getSimpleTestDataTable()
    {
        $table = new Piwik_DataTable;
        $table->addRowsFromArray(
            array(
                 array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ten', 'count' => 10)),
                 array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ninety', 'count' => 90)),
                 array(Piwik_DataTable_Row::COLUMNS => array('label' => 'hundred', 'count' => 100)),
                 Piwik_DataTable::ID_SUMMARY_ROW => array(Piwik_DataTable_Row::COLUMNS => array('label' => 'summary', 'count' => 200))
            )
        );
        return $table;
    }

    /**
     * @group Core
     * @group DataTable
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
     * @group DataTable
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
     * @group DataTable
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
        $idToDelete = Piwik_DataTable::ID_SUMMARY_ROW;
        $this->assertEquals(2, count($table->getRowFromId($idToDelete)->getColumns()));
        $table->deleteRow($idToDelete);
        $this->assertFalse($table->getRowFromId($idToDelete));
    }

    /**
     * @group Core
     * @group DataTable
     */
    public function testGetLastRow()
    {
        $table = $this->_getSimpleTestDataTable();
        $rowsCount = $table->getRowsCount();

        $this->assertEquals($table->getLastRow(), $table->getRowFromId(Piwik_DataTable::ID_SUMMARY_ROW));
        $table->deleteRow(Piwik_DataTable::ID_SUMMARY_ROW);

        $this->assertEquals($table->getLastRow(), $table->getRowFromId($rowsCount - 2));
    }

    /**
     * @group Core
     * @group DataTable
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
     * @group DataTable
     */
    public function testCountRowsSimple()
    {

        $table = new Piwik_DataTable;
        $idcol = Piwik_DataTable_Row::COLUMNS;
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
     * @group DataTable
     */
    public function testCountRowsComplex()
    {

        $idcol = Piwik_DataTable_Row::COLUMNS;
        $idsubtable = Piwik_DataTable_Row::DATATABLE_ASSOCIATED;

        // table to go in the SUB table of RoW1
        $tableSubOfSubOfRow1 = new Piwik_DataTable;
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
        $tableSubOfRow1 = new Piwik_DataTable;
        $rows1 = array(
            array($idcol => array('label' => 'google'), $idsubtable => $tableSubOfSubOfRow1),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
        );
        $tableSubOfRow1->addRowsFromArray($rows1);

        // table to go in row2
        $tableSubOfRow2 = new Piwik_DataTable;
        $rows2 = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
            array($idcol => array('label' => 'agaegaesk')),
            array($idcol => array('label' => '23g  8975247578949')),
        );
        $tableSubOfRow2->addRowsFromArray($rows2);

        // main parent table
        $table = new Piwik_DataTable;
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
     * @group DataTable
     */
    public function testRow()
    {
        $columns = array('test_column' => 145,
                         092582495     => new Piwik_Timer,
                         'super'       => array('this column has an array value, amazing'));
        $metadata = array('logo'  => 'piwik.png',
                          'super' => array('this column has an array value, amazing'));
        $arrayRow = array(
            Piwik_DataTable_Row::COLUMNS  => $columns,
            Piwik_DataTable_Row::METADATA => $metadata,
            'fake useless key'            => 38959,
            43905724897                   => 'value');
        $row = new Piwik_DataTable_Row($arrayRow);

        $this->assertEquals($columns, $row->getColumns());
        $this->assertEquals($metadata, $row->getMetadata());
        $this->assertNull($row->getIdSubDataTable());

    }

    /**
     * Simple test of the DataTable_Row
     *
     * @group Core
     * @group DataTable
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
            Piwik_DataTable_Row::COLUMNS  => $columns,
            Piwik_DataTable_Row::METADATA => $metadata,
            'fake useless key'            => 38959,
            43905724897                   => 'value');
        $row1 = new Piwik_DataTable_Row($arrayRow);

        $columns2 = array('test_int'          => 5,
                          'test_float'        => 4.5,
                          'test_float2'       => 14.5,
                          'test_stringint'    => "5",
                          0925824             => 'toto',
                          'integerArrayToSum' => array(1 => 5, 2 => 5.5, 3 => array(2 => 4)),
        );
        $finalRow = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $columns2));
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
        $rowWanted = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $columnsWanted, Piwik_DataTable_Row::METADATA => $metadata));
        $this->assertTrue(Piwik_DataTable_Row::isEqual($rowWanted, $finalRow));


        // testing that, 'sumRow' does not result in extra unwanted attributes being serialized
        $expectedRow = 'O:19:"Piwik_DataTable_Row":1:{s:1:"c";a:3:{i:0;a:8:{s:8:"test_int";i:150;s:10:"test_float";d:150;s:11:"test_float2";d:14.5;s:14:"test_stringint";i:150;i:0;s:4:"toto";s:17:"integerArrayToSum";a:3:{i:1;i:6;i:2;d:15.5;i:3;a:2:{i:2;i:7;i:1;i:2;}}s:11:"test_float3";d:1.5;s:4:"test";s:11:"string fake";}i:1;a:2:{s:4:"logo";s:9:"piwik.png";s:5:"super";a:1:{i:0;s:39:"this column has an array value, amazing";}}i:3;N;}}';
        $this->assertEquals(serialize($finalRow), $expectedRow);

        // Testing sumRow with disabled metadata sum
        $rowWanted = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $columnsWanted)); // no metadata
        $finalRow = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $columns2));
        $finalRow->sumRow($row1, $enableCopyMetadata = false);
        $this->assertTrue(Piwik_DataTable_Row::isEqual($rowWanted, $finalRow));
    }

    /**
     * Test that adding two string column values results in an exception.
     *
     * @group Core
     * @group DataTable
     */
    public function testSumRow_stringException()
    {
        $columns = array(
            'super' => array('this column has an array string that will be 0 when algorithm sums the value'),
        );
        $row1 = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $columns));

        $columns2 = array(
            'super' => array('this column has geagaean array value, amazing'),
        );
        $row2 = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => $columns2));

        // TODO: when phpunit 3.7 is released, can do check w/ "@expectedException Exception"
        try {
            $row2->sumRow($row1);
            $this->fail("sumRow did not throw when adding two string columns.");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * Test serialize with an infinite recursion (a row linked to a table in the parent hierarchy)
     * After 100 recursion must throw an exception
     *
     * @group Core
     * @group DataTable
     */
    public function testSerializeWithInfiniteRecursion()
    {
        try {
            $table = new Piwik_DataTable;
            $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS              => array('visits' => 245, 'visitors' => 245),
                                          Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $table,));

            $table->getSerialized();
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }


    /**
     * Test queing filters
     *
     * @group Core
     * @group DataTable
     */
    public function testFilterQueueSortString()
    {

        $idcol = Piwik_DataTable_Row::COLUMNS;

        $table = new Piwik_DataTable;
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'tsk')), //1
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //2
        );
        $table->addRowsFromArray($rows);

        $expectedtable = new Piwik_DataTable;
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //2
            array($idcol => array('label' => 'tsk')), //1
        );
        $expectedtable->addRowsFromArray($rows);

        $expectedtableReverse = new Piwik_DataTable;
        $expectedtableReverse->addRowsFromArray(array_reverse($rows));

        $tableCopy = clone $table;
        $this->assertTrue(Piwik_DataTable::isEqual($tableCopy, $table));

        // queue the filter and check the table didnt change
        $table->queueFilter("Sort", array('label', 'asc'));
        $this->assertTrue(Piwik_DataTable::isEqual($tableCopy, $table));

        // apply filter and check the table is sorted
        $table->applyQueuedFilters();
        $this->assertTrue(Piwik_DataTable::isEqual($expectedtable, $table));

        // apply one more filter check it hasnt changed
        $table->queueFilter("Sort", array('label', 'desc'));
        $this->assertTrue(Piwik_DataTable::isEqual($expectedtable, $table));

        // now apply the second sort and check it is correctly sorted
        $table->applyQueuedFilters();
        $this->assertTrue(Piwik_DataTable::isEqual($expectedtableReverse, $table));

        // do one more time to make sure it doesnt change
        $table->applyQueuedFilters();
        $this->assertTrue(Piwik_DataTable::isEqual($expectedtableReverse, $table));
    }

    /**
     * General tests that tries to test the normal behaviour of DataTable
     *
     * We create some tables, add rows, some of the rows link to sub tables
     *
     * Then we serialize everything, and we check that the unserialize give the same object back
     *
     * @group Core
     * @group DataTable
     */
    public function testGeneral()
    {
        /*
         * create some fake tables to make sure that the serialized array of the first TABLE
         * does not take in consideration those tables
         */
        $useless1 = new Piwik_DataTable;
        $useless1->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array(13,),));
        /*
         * end fake tables
         */

        /*
         * MAIN TABLE
         */
        $table = new Piwik_DataTable;
        $subtable = new Piwik_DataTable;
        $idtable = $table->getId();
        $idsubtable = $subtable->getId();

        /*
         * create some fake tables to make sure that the serialized array of the first TABLE
         * does not take in consideration those tables
         * -> we check that the DataTable_Manager is not impacting DataTable 
         */
        $useless2 = new Piwik_DataTable;
        $useless1->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array(8487,),));
        $useless3 = new Piwik_DataTable;
        $useless3->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array(8487,),));
        /*
         * end fake tables
         */

        $row = array(Piwik_DataTable_Row::COLUMNS  => array(0 => 1554, 1 => 42, 2 => 657, 3 => 155744,),
                     Piwik_DataTable_Row::METADATA => array('logo' => 'test.png'));
        $row = new Piwik_DataTable_Row($row);

        $table->addRow($row);
        $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS  => array(0 => 1554, 1 => 42,),
                                      Piwik_DataTable_Row::METADATA => array('url' => 'piwik.org')));

        $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS              => array(0 => 787877888787,),
                                      Piwik_DataTable_Row::METADATA             => array('url' => 'OUPLA ADDED'),
                                      Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable));

        /*
         * SUB TABLE
         */


        $row = array(Piwik_DataTable_Row::COLUMNS  => array(0 => 1554,),
                     Piwik_DataTable_Row::METADATA => array('searchengine' => 'google'),
        );
        $subtable->addRowFromArray($row);

        $row = array(Piwik_DataTable_Row::COLUMNS  => array(0 => 84894,),
                     Piwik_DataTable_Row::METADATA => array('searchengine' => 'yahoo'),
        );
        $subtable->addRowFromArray($row);
        $row = array(Piwik_DataTable_Row::COLUMNS  => array(0 => 4898978989,),
                     Piwik_DataTable_Row::METADATA => array('searchengine' => 'ask'),
        );
        $subtable->addRowFromArray($row);


        /*
         * SUB SUB TABLE
         */
        $subsubtable = new Piwik_DataTable;
        $subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS  => array(245),
                                            Piwik_DataTable_Row::METADATA => array('yes' => 'subsubmetadata1'),)
        );

        $subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS  => array(13,),
                                            Piwik_DataTable_Row::METADATA => array('yes' => 'subsubmetadata2'),)
        );

        $row = array(Piwik_DataTable_Row::COLUMNS              => array(0 => 666666666666666,),
                     Piwik_DataTable_Row::METADATA             => array('url' => 'NEW ROW ADDED'),
                     Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subsubtable);

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

            $currentRowAssociatedDatatableId = $currentRow->c[Piwik_DataTable_Row::DATATABLE_ASSOCIATED];
            if ($currentRowAssociatedDatatableId != null) {
                // making DATATABLE_ASSOCIATED ids positive
                $expectedTableRow->c[Piwik_DataTable_Row::DATATABLE_ASSOCIATED] = -1 * $currentRowAssociatedDatatableId;
            }

            $expectedTableRows[] = $expectedTableRow;
        }

        $tableAfter = new Piwik_DataTable;
        $tableAfter->addRowsFromSerializedArray($serialized[0]);

        $this->assertEquals($expectedTableRows, $tableAfter->getRows());

        $subsubtableAfter = new Piwik_DataTable;
        $subsubtableAfter->addRowsFromSerializedArray($serialized[$idsubsubtable]);
        $this->assertEquals($subsubtable->getRows(), $subsubtableAfter->getRows());

        $this->assertEquals($table, Piwik_DataTable_Manager::getInstance()->getTable($idtable));
        $this->assertEquals($subsubtable, Piwik_DataTable_Manager::getInstance()->getTable($idsubsubtable));
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
     * @group DataTable
     */
    public function testAddSimpleNoRowTable2()
    {
        $table = $this->_getDataTable1ForTest();
        $tableEmpty = new Piwik_DataTable;
        $tableAfter = clone $table;
        $tableAfter->addDataTable($tableEmpty);
        $this->assertTrue(Piwik_DataTable::isEqual($table, $tableAfter));
    }

    /**
     * add a normal datatable to an empty datatable
     *
     * @group Core
     * @group DataTable
     */
    public function testAddSimpleNoRowTable1()
    {
        $table = $this->_getDataTable1ForTest();
        $tableEmpty = new Piwik_DataTable;
        $tableEmpty->addDataTable($table);
        $this->assertTrue(Piwik_DataTable::isEqual($tableEmpty, $table));
    }

    /**
     * add to the datatable another datatable// they don't have any row in common
     *
     * @group Core
     * @group DataTable
     */
    public function testAddSimpleNoCommonRow()
    {
        $table1 = $this->_getDataTable1ForTest();
        $table2 = $this->_getDataTable2ForTest();

        $table1->addDataTable($table2);

        $rowsExpected = array_merge($this->_getRowsDataTable1ForTest(), $this->_getRowsDataTable2ForTest());
        $tableExpected = new Piwik_DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(Piwik_DataTable::isEqual($table1, $tableExpected));
    }

    /**
     * add 2 datatable with some common rows
     *
     * @group Core
     * @group DataTable
     */
    public function testAddSimpleSomeCommonRow()
    {

        $idcol = Piwik_DataTable_Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
        );
        $table = new Piwik_DataTable;
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'test', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 111)),
            array($idcol => array('label' => ' google ', 'visits' => 5)),
            array($idcol => array('label' => '123', 'visits' => 2)),
        );
        $table2 = new Piwik_DataTable;
        $table2->addRowsFromArray($rows2);

        $table->addDataTable($table2);

        $rowsExpected = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 113)),
            array($idcol => array('label' => '123', 'visits' => 4)),
            array($idcol => array('label' => 'test', 'visits' => 1)),
            array($idcol => array('label' => ' google ', 'visits' => 5)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
        );
        $tableExpected = new Piwik_DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(Piwik_DataTable::isEqual($table, $tableExpected));
    }

    /**
     * add 2 datatable with only common rows
     *
     * @group Core
     * @group DataTable
     */
    public function testAddSimpleAllCommonRow()
    {
        $idcol = Piwik_DataTable_Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
        );
        $table = new Piwik_DataTable;
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'google', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => 0)),
            array($idcol => array('label' => '123', 'visits' => 1.5)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 8))
        );
        $table2 = new Piwik_DataTable;
        $table2->addRowsFromArray($rows2);

        $table->addDataTable($table2);

        $rowsExpected = array(
            array($idcol => array('label' => 'google', 'visits' => 0)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 3.5)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 15))
        );
        $tableExpected = new Piwik_DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(Piwik_DataTable::isEqual($table, $tableExpected));
    }

    /**
     * test add 2 different tables to the same table
     *
     * @group Core
     * @group DataTable
     */
    public function testAddDataTable2times()
    {

        $idcol = Piwik_DataTable_Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 0)),
            array($idcol => array('label' => '123', 'visits' => 2)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 1))
        );
        $table = new Piwik_DataTable;
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'google2', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => 100)),
            array($idcol => array('label' => '123456', 'visits' => 1.5)),
        );
        $table2 = new Piwik_DataTable;
        $table2->addRowsFromArray($rows2);

        $rows3 = array(
            array($idcol => array('label' => 'google2', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => -10)),
            array($idcol => array('label' => '123ab', 'visits' => 1.5)),
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 3))
        );
        $table3 = new Piwik_DataTable;
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
            Piwik_DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 4))
        );
        $tableExpected = new Piwik_DataTable;
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(Piwik_DataTable::isEqual($table, $tableExpected));
    }


    /**
     * @group Core
     * @group DataTable
     */
    public function testUnrelatedDataTableNotDestructed()
    {
        $mockedDataTable = $this->getMock('Piwik_DataTable', array('__destruct'));
        $mockedDataTable->expects($this->never())->method('__destruct');

        $rowBeingDestructed = new Piwik_DataTable_Row();

        // we simulate the fact that the value of Piwik_DataTable_Row::DATATABLE_ASSOCIATED retrieved
        // from the database is in conflict with one of the Piwik_DataTable_Manager managed table identifiers.
        // This is a rare but legitimate case as identifiers are not thoroughly synchronized
        // when the expanded parameter is false.
        $rowBeingDestructed->c[Piwik_DataTable_Row::DATATABLE_ASSOCIATED] = $mockedDataTable->getId();

        destroy($rowBeingDestructed);
    }

    /**
     * @group Core
     * @group DataTable
     */
    public function testGetSerializedCallsCleanPostSerialize()
    {
        $mockedDataTableRow = $this->getMock('Piwik_DataTable_Row', array('cleanPostSerialize'));
        $mockedDataTableRow->expects($this->once())->method('cleanPostSerialize');

        $dataTableBeingSerialized = new Piwik_DataTable();
        $dataTableBeingSerialized->addRow($mockedDataTableRow);

        $dataTableBeingSerialized->getSerialized();
    }

    /**
     * @group Core
     * @group DataTable
     */
    public function testSubDataTableIsDestructed()
    {
        $mockedDataTable = $this->getMock('Piwik_DataTable', array('__destruct'));
        $mockedDataTable->expects($this->once())->method('__destruct');

        $rowBeingDestructed = new Piwik_DataTable_Row();
        $rowBeingDestructed->setSubtable($mockedDataTable);

        destroy($rowBeingDestructed);
    }

    protected function _getDataTable1ForTest()
    {
        $rows = $this->_getRowsDataTable1ForTest();
        $table = new Piwik_DataTable;
        $table->addRowsFromArray($rows);
        return $table;
    }

    protected function _getDataTable2ForTest()
    {
        $rows = $this->_getRowsDataTable2ForTest();
        $table = new Piwik_DataTable;
        $table->addRowsFromArray($rows);
        return $table;
    }

    protected function _getRowsDataTable1ForTest()
    {
        $rows = array(
            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'google', 'visits' => 1)),
            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'visits' => 2)),
            array(Piwik_DataTable_Row::COLUMNS => array('label' => '123', 'visits' => 2)),
            Piwik_DataTable::ID_SUMMARY_ROW => array(Piwik_DataTable_Row::COLUMNS => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 4))

        );
        return $rows;
    }

    protected function _getRowsDataTable2ForTest()
    {
        $rows = array(
            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'test', 'visits' => 1)),
            array(Piwik_DataTable_Row::COLUMNS => array('label' => ' google ', 'visits' => 3)),
            array(Piwik_DataTable_Row::COLUMNS => array('label' => '123a', 'visits' => 2)),
        );
        return $rows;
    }

}
