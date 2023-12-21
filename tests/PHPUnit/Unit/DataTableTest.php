<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Common;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\RankingQuery;
use Piwik\Timer;

/**
 * @group DataTableTest
 * @group DataTable
 * @group Core
 */
class DataTableTest extends \PHPUnit\Framework\TestCase
{
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
        $table = new DataTable();
        $table->addRowsFromArray(
            array(
                 array(Row::COLUMNS => array('label' => 'ten', 'count' => 10)),
                 array(Row::COLUMNS => array('label' => 'ninety', 'count' => 90)),
                 array(Row::COLUMNS => array('label' => 'hundred', 'count' => 100)),
                 DataTable::ID_SUMMARY_ROW => array(Row::COLUMNS => array('label' => 'summary', 'count' => 200))
            )
        );
        $table->setTotalsRow(new Row(array(Row::COLUMNS => array('label' => 'Total', 'count' => 200))));
        return $table;
    }

    protected function _getSimpleTestDataTable2()
    {
        $table = new DataTable();
        $table->addRowsFromArray(
            array(
                array(Row::COLUMNS => array('label' => 'ten', 'count' => 10)),
                array(Row::COLUMNS => array('label' => 'ninety', 'count' => 20)),
                array(Row::COLUMNS => array('label' => 'hundred', 'count' => 30)),
                DataTable::ID_SUMMARY_ROW => array(Row::COLUMNS => array('label' => 'summary', 'count' => 60))
            )
        );
        $table->setTotalsRow(new Row(array(Row::COLUMNS => array('label' => 'Total', 'count' => 60))));
        return $table;
    }

    public function testMultiFilter()
    {
        $table = $this->_getSimpleTestDataTable();
        $table2 = $this->_getSimpleTestDataTable2();

        $result = $table->multiFilter([$table2], function ($thisTable, $otherTable) {
            $thisTable->addDataTable($otherTable);
            return 5;
        });

        $tableExpected = new DataTable();
        $tableExpected->addRowsFromArray(
            [
                array(Row::COLUMNS => array('label' => 'ten', 'count' => 20)),
                array(Row::COLUMNS => array('label' => 'ninety', 'count' => 110)),
                array(Row::COLUMNS => array('label' => 'hundred', 'count' => 130)),
                DataTable::ID_SUMMARY_ROW => array(Row::COLUMNS => array('label' => 'summary', 'count' => 260))
            ]
        );

        $this->assertEquals(5, $result);
        $this->assertEquals($tableExpected->getRows(), $table->getRows());
    }

    public function testRenameColumn()
    {
        $table = $this->_getSimpleTestDataTable();
        $this->assertEquals(array(10, 90, 100, 200), $table->getColumn('count'));
        $this->assertEquals(200, $table->getTotalsRow()->getColumn('count'));
        $table->renameColumn('count', 'renamed');
        $this->assertEquals(array(false, false, false, false), $table->getColumn('count'));
        $this->assertEquals(array(10, 90, 100, 200), $table->getColumn('renamed'));
        $this->assertEquals(200, $table->getTotalsRow()->getColumn('renamed'));
    }

    public function testDeleteColumn()
    {
        $table = $this->_getSimpleTestDataTable();
        $this->assertEquals(array(10, 90, 100, 200), $table->getColumn('count'));
        $table->deleteColumn('count');
        $this->assertEquals(array(false, false, false, false), $table->getColumn('count'));
        $this->assertEquals(false, $table->getTotalsRow()->getColumn('count'));
    }

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

    public function testGetLastRow()
    {
        $table = $this->_getSimpleTestDataTable();
        $rowsCount = $table->getRowsCount();

        $this->assertEquals($table->getLastRow(), $table->getRowFromId(DataTable::ID_SUMMARY_ROW));
        $table->deleteRow(DataTable::ID_SUMMARY_ROW);

        $this->assertEquals($table->getLastRow(), $table->getRowFromId($rowsCount - 2));
    }

    public function testGetRowFromIdSubDataTable()
    {
        $table1 = $this->_getDataTable1ForTest();
        $idTable1 = $table1->getId();
        $table2 = $this->_getDataTable2ForTest();
        $this->assertFalse($table2->getRowFromIdSubDataTable($idTable1));

        $table2->getFirstRow()->setSubtable($table1);
        $this->assertEquals($table2->getRowFromIdSubDataTable($idTable1), $table2->getFirstRow());

        $table3 = $this->_getDataTable1ForTest();
        $idTable3 = $table3->getId();
        $table2->getLastRow()->setSubtable($table3);
        $this->assertEquals($table2->getRowFromIdSubDataTable($idTable3), $table2->getLastRow());
    }

    public function test_rebuildIndex()
    {
        $labels = array(0 => 'abc', 1 => 'def', 2 => 'ghi', 3 => 'jkl', 4 => 'mno');
        $table = new DataTable();

        $rows = array();
        foreach ($labels as $label) {
            $row = new Row(array(Row::COLUMNS => array('label' => $label)));
            $table->addRow($row);
            $rows[] = $row;
        }

        foreach ($labels as $label) {
            $rowVerify1 = $table->getRowFromLabel($label);
            $this->assertSame($label, $rowVerify1->getColumn('label'));
        }

        $table->setRows(array($rows[2], $rows[3], $rows[4]));
        $table->rebuildIndex();// rebuildindex would be called anyway but we force rebuilding the index just to make sure

        // verify still accessible
        $rowVerify1 = $table->getRowFromLabel('ghi');
        $this->assertSame('ghi', $rowVerify1->getColumn('label'));

        // verify no longer accessible
        $rowVerify3 = $table->getRowFromLabel('abc');
        $this->assertFalse($rowVerify3);
    }

    public function test_clone_shouldIncreasesTableId()
    {
        $table = new DataTable();
        $rows = array(
            array(Row::COLUMNS => array('label' => 'google')),
        );
        $table->addRowsFromArray($rows);

        $table2 = clone $table;

        $this->assertSame($table2->getId(), $table->getId() + 1);
    }

    /**
     * we test the count rows and the count rows recursive version
     * on a Simple array (1 level only)
     */
    public function testCountRowsSimple()
    {

        $table = new DataTable();
        $idcol = Row::COLUMNS;
        $rows = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => 'piwik')),
            array($idcol => array('label' => 'yahoo')),
            array($idcol => array('label' => 'amazon')),
            array($idcol => array('label' => '238975247578949')),
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))'))
        );

        $table->addRowsFromArray($rows);

        $this->assertEquals(7, $table->getRowsCount());
        $this->assertEquals(7, $table->getRowsCountRecursive());
    }

    /**
     * we test the count rows and the count rows recursive version
     * on a Complex array (rows with 2 and 3 levels only)
     *
     * the recursive count returns
     *         the sum of the number of rows of all the subtables
     *         + the number of rows in the parent table
     */
    public function testCountRowsComplex()
    {
        $idcol = Row::COLUMNS;
        $idsubtable = Row::DATATABLE_ASSOCIATED;

        // table to go in the SUB table of RoW1
        $tableSubOfSubOfRow1 = new DataTable();
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
        $tableSubOfRow1 = new DataTable();
        $rows1 = array(
            array($idcol => array('label' => 'google'), $idsubtable => $tableSubOfSubOfRow1),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
        );
        $tableSubOfRow1->addRowsFromArray($rows1);

        // table to go in row2
        $tableSubOfRow2 = new DataTable();
        $rows2 = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => '238975247578949')),
            array($idcol => array('label' => 'agaegaesk')),
            array($idcol => array('label' => '23g  8975247578949')),
        );
        $tableSubOfRow2->addRowsFromArray($rows2);

        // main parent table
        $table = new DataTable();
        $rows = array(
            array($idcol => array('label' => 'row1')),
            array($idcol      => array('label' => 'row2'),
                  $idsubtable => $tableSubOfRow1),
            array($idcol      => array('label' => 'row3'),
                  $idsubtable => $tableSubOfRow2),
        );
        $table->addRowsFromArray($rows);

        $this->assertEquals(3, $table->getRowsCount());
        $this->assertEquals(18, $table->getRowsCountRecursive());
    }

    /**
     * Simple test of the DataTable_Row
     */
    public function testRow()
    {
        $columns = array('test_column' => 145,
                         92582495     => new Timer(),
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
            '43905724897'                   => 'value');
        $row1 = new Row($arrayRow);

        $columns2 = array('test_int'          => 5,
                          'test_float'        => 4.5,
                          'test_float2'       => 14.5,
                          'test_stringint'    => "5",
                          925824             => 'toto',
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
                               925824             => 'toto',
        );

        // Also testing that metadata is copied over
        $rowWanted = new Row(array(Row::COLUMNS => $columnsWanted, Row::METADATA => $metadata));
        $this->assertTrue(Row::isEqual($rowWanted, $finalRow));

        // testing that, 'sumRow' does not result in extra unwanted attributes being serialized

        $expectedRow = 'a:3:{i:0;a:8:{s:8:"test_int";i:150;s:10:"test_float";d:150;s:11:"test_float2";d:14.5;s:14:"test_stringint";i:150;i:925824;s:4:"toto";s:17:"integerArrayToSum";a:3:{i:1;i:6;i:2;d:15.5;i:3;a:2:{i:2;i:7;i:1;i:2;}}s:11:"test_float3";d:1.5;s:4:"test";s:11:"string fake";}i:1;a:2:{s:4:"logo";s:9:"piwik.png";s:5:"super";a:1:{i:0;s:39:"this column has an array value, amazing";}}i:3;N;}';
        $this->assertEquals($expectedRow, serialize($finalRow->export()));

        // Testing sumRow with disabled metadata sum
        $rowWanted = new Row(array(Row::COLUMNS => $columnsWanted)); // no metadata
        $finalRow = new Row(array(Row::COLUMNS => $columns2));
        $finalRow->sumRow($row1, $enableCopyMetadata = false);
        $this->assertTrue(Row::isEqual($rowWanted, $finalRow));
    }

    /**
     * @dataProvider unserializeTestsDataProvider
     */
    public function test_unserializeWorks_WithAllDataTableFormats($indexToRead, $label, $column2, $subtable)
    {
        $serializedDatatable = array();
        // Prior Piwik 2.13, we serialized the actual Row or DataTableSummaryRow instances, afterwards only arrays
        require PIWIK_INCLUDE_PATH . "/tests/resources/DataTables-archived-different-formats.php";
        require_once PIWIK_INCLUDE_PATH . "/core/DataTable/Bridges.php";

        $table = $serializedDatatable[$indexToRead];
        $this->assertTrue(strlen($table) > 1000);

        $table = DataTable::fromSerializedArray($table);
        $row1  = $table->getFirstRow();
        $this->assertTrue($row1 instanceof \Piwik\DataTable\Row);
        $this->assertFalse($row1 instanceof \Piwik\DataTable\Row\DataTableSummaryRow); // we convert summary rows to Row instances

        $this->assertEquals($label, $row1->getColumn('label'));
        $this->assertEquals($column2, $row1->getColumn(2));
        $this->assertEquals($subtable, $row1->getIdSubDataTable());
    }

    public function testSumRowMetadata_CustomAggregationOperation()
    {
        $metadata1 = array('mytest' => 'value1');
        $metadata2 = array('mytest' => 'value2');

        $self = $this;
        $row1 = new Row(array(Row::COLUMNS => array('test_int' => 145), Row::METADATA => $metadata1));
        $finalRow = new Row(array(Row::COLUMNS => array('test_int' => 5), Row::METADATA => $metadata2));
        $finalRow->sumRowMetadata($row1, array('mytest' => function ($thisValue, $otherValue, $thisRow, $otherRow) use ($self, $row1, $finalRow) {
            $self->assertEquals('value2', $thisValue);
            $self->assertEquals('value1', $otherValue);
            $self->assertSame($thisRow, $finalRow);
            $self->assertSame($otherRow, $row1);

            if (!is_array($thisValue)) {
                $thisValue = array($thisValue);
            }

            $thisValue[] = $otherValue;
            return $thisValue;
        }));

        $this->assertEquals(array('value2', 'value1'), $finalRow->getMetadata('mytest'));
    }

    public function testSumRow_CustomAggregationOperation()
    {
        $columns = array('test_int' => 145, 'test_float' => 145.5);

        $row1 = new Row(array(Row::COLUMNS => $columns));

        $columns2 = array('test_int' => 5);
        $finalRow = new Row(array(Row::COLUMNS => $columns2));


        $self = $this;

        $finalRow->sumRow($row1, $copyMetadata = true, $operation = array('test_int' => function ($thisValue, $otherValue, $thisRow, $otherRow) use ($self, $row1, $finalRow) {
            $self->assertEquals(5, $thisValue);
            $self->assertEquals(145, $otherValue);
            $self->assertSame($thisRow, $finalRow);
            $self->assertSame($otherRow, $row1);

            if (!is_array($thisValue)) {
                $thisValue = array($thisValue);
            }

            $thisValue[] = $otherValue;
            return $thisValue;
        }));

        $this->assertEquals(array(5, 145), $finalRow->getColumn('test_int'));
    }

    public function testSumRow_ShouldThrowExceptionIfInvalidOperationIsGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown operation \'foobarinvalid\'');

        $row1 = new Row(array(Row::COLUMNS => array('test_int' => 145)));
        $finalRow = new Row(array(Row::COLUMNS => array('test_int' => 5)));
        $finalRow->sumRow($row1, $copyMetadata = true, $operation = array('test_int' => 'fooBarInvalid'));

        $this->assertEquals(array(5, 145), $finalRow->getColumn('test_int'));
    }

    public function unserializeTestsDataProvider()
    {
        return array(
            array($index = 0, $label = 'piwik.org', $column2 = 10509, $idSubtable = 1581), // pre Piwik 2.0 (without namespaces, Piwik_DataTable_Row)
            array($index = 1, $label = 'piwikactions.org', $column2 = 10508, $idSubtable = 1581), // pre Piwik 2.0 Actions (without namespaces, Piwik_DataTable_Row_DataTableSummary)
            array($index = 2, $label = 'start', $column2 = 89, $idSubtable = 2260), // >= Piwik 2.0 < Piwik 2.13 Actions (DataTableSummaryRow)
            array($index = 3, $label = 'Ask',   $column2 = 11, $idSubtable = 3335), // >= Piwik 2.0 < Piwik 2.13 Referrers (Row)
            array($index = 4, $label = 'MyLabel Test',   $column2 = 447, $idSubtable = 1), // >= Piwik 2.0 < Piwik 2.13 Referrers (Row)
        );
    }

    /**
     * Test that adding two string column values results in an exception.
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
        $this->assertTrue($noException = true);
    }

    /**
     * Test serialize with an infinite recursion (a row linked to a table in the parent hierarchy)
     * After 100 recursion must throw an exception
     */
    public function testSerializeWithInfiniteRecursion()
    {
        $this->expectException(\Exception::class);

        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array('visits' => 245, 'visitors' => 245),
                                      Row::DATATABLE_ASSOCIATED => $table));

        $table->getSerialized();
    }

    public function test_getSerialized_SerializesSubtablesOfSummaryRows()
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'dimval1', 'visits' => 245)));

        $summaryRow = new Row([Row::COLUMNS => ['label' => 'others', 'visits' => 500]]);

        $summaryRowSubtable = new DataTable();
        $summaryRowSubtable->addRow(new Row([Row::COLUMNS => ['label' => 'subtabledimension', 'visits' => 100]]));
        $summaryRow->setSubtable($summaryRowSubtable);

        $table->addSummaryRow($summaryRow);

        $results = $table->getSerialized();

        $this->assertCount(2, $results);
        $this->assertStringContainsString('dimval1', $results[0]);
        $this->assertStringContainsString('subtabledimension', $results[1]);

        $tableUnserialized = DataTable::fromSerializedArray($results[0]);
        $this->assertEquals(1, $tableUnserialized->getSummaryRow()->getIdSubDataTable());

        $expectedResults = [
            'a:2:{i:0;a:3:{i:0;a:2:{s:5:"label";s:7:"dimval1";s:6:"visits";i:245;}i:1;a:0:{}i:3;N;}i:-1;a:3:{i:0;a:2:{s:5:"label";s:6:"others";s:6:"visits";i:500;}i:1;a:0:{}i:3;i:1;}}',
            'a:1:{i:0;a:3:{i:0;a:2:{s:5:"label";s:17:"subtabledimension";s:6:"visits";i:100;}i:1;a:0:{}i:3;N;}}',
        ];
        $this->assertEquals($expectedResults, $results);
    }

    /**
     * Test queing filters
     */
    public function testFilterQueueSortString()
    {
        $idcol = Row::COLUMNS;

        $table = new DataTable();
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'tsk')), //1
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //2
        );
        $table->addRowsFromArray($rows);

        $expectedtable = new DataTable();
        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')), //2
            array($idcol => array('label' => 'tsk')), //1
        );
        $expectedtable->addRowsFromArray($rows);

        $expectedtableReverse = new DataTable();
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
     */
    public function testGeneral()
    {
        /*
         * create some fake tables to make sure that the serialized array of the first TABLE
         * does not take in consideration those tables
         */
        $useless1 = $this->createDataTable(array(array(13,)));
        /*
         * end fake tables
         */

        /*
         * MAIN TABLE
         */
        $table = new DataTable();
        $subtable = new DataTable();
        $idtable = $table->getId();

        /*
         * create some fake tables to make sure that the serialized array of the first TABLE
         * does not take in consideration those tables
         * -> we check that the DataTable_Manager is not impacting DataTable
         */
        $useless1->addRowFromArray(array(Row::COLUMNS => array(8487,),));
        $useless3 = $this->createDataTable(array(array(8487)));
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
        $subsubtable = new DataTable();
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

        $serialized = $table->getSerialized();

        $this->assertEquals(array_keys($serialized), array(2, 1, 0)); // subtableIds are now consecutive

        // In the next test we compare an unserialized datatable with its original instance.
        // The unserialized datatable rows will have positive DATATABLE_ASSOCIATED ids.
        // Positive DATATABLE_ASSOCIATED ids mean that the associated sub-datatables are not loaded in memory.
        // In this case, this is NOT true: we know that the sub-datatable is loaded in memory.
        // HOWEVER, because of datatable id conflicts happening in the datatable manager, it is not yet
        // possible to know, after unserializing a datatable, if its sub-datatables are loaded in memory.
        $expectedTableRows = array();
        $i = 0;
        foreach ($table->getRows() as $currentRow) {
            $expectedTableRow = clone $currentRow;

            $currentRowAssociatedDatatableId = $currentRow->subtableId;
            if ($currentRowAssociatedDatatableId != null) {
                $expectedTableRow->setNonLoadedSubtableId(++$i); // subtableIds are consecutive
            }

            $expectedTableRows[] = $expectedTableRow;
        }

        $tableAfter = new DataTable();
        $tableAfter->addRowsFromSerializedArray($serialized[0]);

        $this->assertEquals($expectedTableRows, $tableAfter->getRows());

        $subsubtableAfter = new DataTable();
        $subsubtableAfter->addRowsFromSerializedArray($serialized[$consecutiveSubtableId = 2]);
        $this->assertEquals($subsubtable->getRows(), $subsubtableAfter->getRows());
        $this->assertEquals($subsubtable->getRows(), DataTable::fromSerializedArray($serialized[$consecutiveSubtableId = 2])->getRows());
        $this->assertTrue($subsubtable->getRowsCount() > 0);

        $this->assertEquals($table, Manager::getInstance()->getTable($idtable));
        $this->assertEquals($subsubtable, Manager::getInstance()->getTable($idsubsubtable));
    }

    public function test_getSerialized_shouldCreateConsecutiveSubtableIds()
    {
        $numRowsInRoot = 10;
        $numRowsInSubtables = 5;

        $rootTable = new DataTable();
        $this->addManyRows($rootTable, 100);

        foreach ($rootTable->getRows() as $row) {
            $subtable = new DataTable();
            $this->addManyRows($subtable, 100);
            $row->setSubtable($subtable);

            foreach ($subtable->getRows() as $subRow) {
                $subRow->setSubtable(new DataTable());
            }
        }

        // we want to make sure the tables have high ids but we will ignore them and just give them Ids starting from 0
        $recentId = Manager::getInstance()->getMostRecentTableId();
        $this->assertGreaterThanOrEqual(5000, $recentId);

        $tables = $rootTable->getSerialized($numRowsInRoot, $numRowsInSubtables);

        // make sure subtableIds are consecutive. Why "-1"? Because if we want 10 rows, there will be 9 subtables + 1 summary row which won't have a subtable
        $sumSubTables = ($numRowsInRoot - 1) + (($numRowsInRoot - 1) * ($numRowsInSubtables - 1));
        $subtableIds  = array_keys($tables);
        sort($subtableIds);
        $this->assertEquals(range(0, $sumSubTables), $subtableIds);

        // make sure the rows subtableId were updated as well.
        foreach ($tables as $index => $serializedRows) {
            $rows = unserialize($serializedRows);
            $this->assertTrue(is_array($rows));

            if (0 === $index) {
                // root table, make sure correct amount of rows are in subtables
                $this->assertCount($numRowsInRoot, $rows);
            }

            foreach ($rows as $row) {
                $this->assertTrue(is_array($row));

                $subtableId = $row[Row::DATATABLE_ASSOCIATED];

                if ($row[Row::COLUMNS]['label'] === DataTable::LABEL_SUMMARY_ROW
                    || $row[Row::COLUMNS]['label'] === DataTable::LABEL_ARCHIVED_METADATA_ROW
                ) {
                    $this->assertNull($subtableId);
                } else {

                    $this->assertLessThanOrEqual($sumSubTables, $subtableId); // make sure row was actually updated
                    $this->assertGreaterThanOrEqual(0, $subtableId);
                    $subrows = unserialize($tables[$subtableId]);

                    // this way we make sure the rows point to the correct subtable. only 2nd level rows have actually
                    // subtables. All 3rd level datatables do not have a row see table creation further above
                    if ($index === 0) {
                        $this->assertCount($numRowsInSubtables, $subrows);
                    } else {
                        $this->assertCount(0, $subrows);
                    }
                }
            }
        }
    }

    public function test_getSerialized_shouldExportOnlyTheSerializedArrayOfAllTableRows()
    {
        $rootTable = new DataTable();
        $this->addManyRows($rootTable, 2);

        foreach ($rootTable->getRows() as $row) {
            $subtable = new DataTable();
            $this->addManyRows($subtable, 2);
            $row->setSubtable($subtable);
        }

        $tables = $rootTable->getSerialized();

        // we also make sure it actually handles the subtableIds correct etc
        $this->assertEquals(array(
            0 => 'a:2:{i:0;a:3:{i:0;a:1:{s:5:"label";s:6:"label0";}i:1;a:0:{}i:3;i:1;}i:1;a:3:{i:0;a:1:{s:5:"label";s:6:"label1";}i:1;a:0:{}i:3;i:2;}}',
            1 => 'a:2:{i:0;a:3:{i:0;a:1:{s:5:"label";s:6:"label0";}i:1;a:0:{}i:3;N;}i:1;a:3:{i:0;a:1:{s:5:"label";s:6:"label1";}i:1;a:0:{}i:3;N;}}',
            2 => 'a:2:{i:0;a:3:{i:0;a:1:{s:5:"label";s:6:"label0";}i:1;a:0:{}i:3;N;}i:1;a:3:{i:0;a:1:{s:5:"label";s:6:"label1";}i:1;a:0:{}i:3;N;}}',
        ), $tables);
    }

    public function test_serializationOfDataTableMetadata()
    {
        $table = new DataTable();
        $table->addRow(new Row([
            Row::COLUMNS => ['label' => 'abc', 'nb_visits' => 5],
        ]));
        $table->setAllTableMetadata([
            'str' => 'str value',
            'int' => 5,
            'float' => 3.65,
            'bool' => true,
            'object' => Date::today(),
        ]);

        $serialized = $table->getSerialized();

        $newTable = DataTable::fromSerializedArray(reset($serialized));

        $this->assertEquals([
            new Row([
                Row::COLUMNS => ['label' => 'abc', 'nb_visits' => 5],
            ]),
        ], $newTable->getRows());

        $this->assertEquals([
            'str' => 'str value',
            'int' => 5,
            'float' => 3.65,
            'bool' => true,
        ], $newTable->getAllTableMetadata());
    }

    private function addManyRows(DataTable $table, $numRows)
    {
        for ($i = 0; $i < $numRows; $i++) {
            $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'label' . $i)));
        }
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
     */
    public function testAddSimpleNoRowTable2()
    {
        $table = $this->_getDataTable1ForTest();
        $tableEmpty = new DataTable();
        $tableAfter = clone $table;
        $tableAfter->addDataTable($tableEmpty);
        $this->assertTrue(DataTable::isEqual($table, $tableAfter));
    }

    /**
     * add a normal datatable to an empty datatable
     */
    public function testAddSimpleNoRowTable1()
    {
        $table = $this->_getDataTable1ForTest();
        $tableEmpty = new DataTable();
        $tableEmpty->addDataTable($table);
        $this->assertTrue(DataTable::isEqual($tableEmpty, $table));
    }

    /**
     * add to the datatable another datatable// they don't have any row in common
     */
    public function testAddSimpleNoCommonRow()
    {
        $table1 = $this->_getDataTable1ForTest();
        $table2 = $this->_getDataTable2ForTest();

        $table1->addDataTable($table2);

        $rowsExpected = array_merge($this->_getRowsDataTable1ForTest(), $this->_getRowsDataTable2ForTest());
        $tableExpected = new DataTable();
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table1, $tableExpected));
    }

    /**
     * add 2 datatable with some common rows
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
        $table = new DataTable();
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'test', 'visits' => 1)),
            array($idcol => array('label' => 'ask', 'visits' => 111)),
            array($idcol => array('label' => ' google ', 'visits' => 5)),
            array($idcol => array('label' => '123', 'visits' => 2)),
        );
        $table2 = new DataTable();
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
        $tableExpected = new DataTable();
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table, $tableExpected));
    }

    /**
     * add 2 datatable with only common rows
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
        $table = new DataTable();
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'google', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => 0)),
            array($idcol => array('label' => '123', 'visits' => 1.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 8))
        );
        $table2 = new DataTable();
        $table2->addRowsFromArray($rows2);

        $table->addDataTable($table2);

        $rowsExpected = array(
            array($idcol => array('label' => 'google', 'visits' => 0)),
            array($idcol => array('label' => 'ask', 'visits' => 2)),
            array($idcol => array('label' => '123', 'visits' => 3.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 15))
        );
        $tableExpected = new DataTable();
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table, $tableExpected));
    }

    /**
     * test add 2 different tables to the same table
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
        $table = new DataTable();
        $table->addRowsFromArray($rows);

        $rows2 = array(
            array($idcol => array('label' => 'google2', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => 100)),
            array($idcol => array('label' => '123456', 'visits' => 1.5)),
        );
        $table2 = new DataTable();
        $table2->addRowsFromArray($rows2);

        $rows3 = array(
            array($idcol => array('label' => 'google2', 'visits' => -1)),
            array($idcol => array('label' => 'ask', 'visits' => -10)),
            array($idcol => array('label' => '123ab', 'visits' => 1.5)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => DataTable::LABEL_SUMMARY_ROW, 'visits' => 3))
        );
        $table3 = new DataTable();
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
        $tableExpected = new DataTable();
        $tableExpected->addRowsFromArray($rowsExpected);

        $this->assertTrue(DataTable::isEqual($table, $tableExpected));
    }

    public function test_addDataTable_whenThereIsNoSummaryRowInOneTable_andASummaryRowInTheOtherTable()
    {
        $table1 = new DataTable();
        $table1->addRowsFromSimpleArray([
            ['label' => 'a', 'value' => 5],
        ]);
        $table1->addSummaryRow(new Row([
            Row::COLUMNS => [
                'label' => DataTable::LABEL_SUMMARY_ROW,
                'value' => 15,
            ],
        ]));

        $table2 = new DataTable();
        $table2->addRowsFromSimpleArray([
            ['label' => 'a', 'value' => 10],
            ['label' => -1, 'value' => 30],
        ]);
        $table2->addSummaryRow(new Row([
            Row::COLUMNS => [
                'label' => DataTable::LABEL_SUMMARY_ROW,
                'value' => 5,
            ],
        ]));

        $table1->addDataTable($table2);

        $expectedRows = [
            0 => ['label' => 'a', 'value' => 15],
            1 => ['label' => -1, 'value' => 30],
            DataTable::ID_SUMMARY_ROW => ['label' => -1, 'value' => 20],
        ];

        $actualRows = $table1->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    public function test_addDataTable_whenThereIsASummaryRow_andRowWithNegativeOneLabel()
    {
        $table1 = new DataTable();
        $table1->addRowsFromSimpleArray([
            ['label' => 'a', 'value' => 5],
            ['label' => '-1', 'value' => 20],
        ]);
        $table1->addSummaryRow(new Row([
            Row::COLUMNS => [
                'label' => DataTable::LABEL_SUMMARY_ROW,
                'value' => 15,
            ],
        ]));

        $table2 = new DataTable();
        $table2->addRowsFromSimpleArray([
            ['label' => 'a', 'value' => 10],
            ['label' => -1, 'value' => 30],
        ]);
        $table2->addSummaryRow(new Row([
            Row::COLUMNS => [
                'label' => DataTable::LABEL_SUMMARY_ROW,
                'value' => 5,
            ],
        ]));

        $table1->addDataTable($table2);

        $expectedRows = [
            0 => ['label' => 'a', 'value' => 15],
            1 => ['label' => -1, 'value' => 50],
            DataTable::ID_SUMMARY_ROW => ['label' => -1, 'value' => 20],
        ];

        $actualRows = $table1->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    public function testUnrelatedDataTableNotDestructed()
    {
        $mockedDataTable = $this->createPartialMock('\Piwik\DataTable', array('__destruct'));
        $mockedDataTable->expects($this->never())->method('__destruct');

        $rowBeingDestructed = new Row();

        // we simulate the fact that the value of Row::DATATABLE_ASSOCIATED retrieved
        // from the database is in conflict with one of the Manager managed table identifiers.
        // This is a rare but legitimate case as identifiers are not thoroughly synchronized
        // when the expanded parameter is false.
        $rowBeingDestructed->subtableId = $mockedDataTable->getId();

        Common::destroy($rowBeingDestructed);
    }

    /**
     * @group Core
     */
    public function test_disableFilter_DoesActuallyDisableAFilter()
    {
        $dataTable = DataTable::makeFromSimpleArray(array_fill(0, 100, array()));
        $this->assertSame(100, $dataTable->getRowsCount());

        $dataTable2 = clone $dataTable;

        // verify here the filter is applied
        $dataTable->filter('Limit', array(10, 10));
        $this->assertSame(10, $dataTable->getRowsCount());

        // verify here the filter is not applied as it is disabled
        $dataTable2->disableFilter('Limit');
        $dataTable2->filter('Limit', array(10, 10));
        $this->assertSame(100, $dataTable2->getRowsCount());

        // passing a whole className is expected to work. This way we also make sure not all filters are disabled
        // and it only blocks the given one
        $dataTable2->filter('Piwik\DataTable\Filter\Limit', array(10, 10));
        $this->assertSame(10, $dataTable2->getRowsCount());
    }

    /**
     * @group Core
     */
    public function testSubDataTableIsDestructed()
    {
        $mockedDataTable = $this->getMockBuilder('\Piwik\DataTable')
            ->onlyMethods(['__destruct'])
            ->getMock();
        $mockedDataTable->expects($this->once())->method('__destruct');

        $rowBeingDestructed = new Row();
        $rowBeingDestructed->setSubtable($mockedDataTable);

        Common::destroy($rowBeingDestructed);
    }

    public function test_serializeFails_onSubTableNotFound()
    {
        // create a simple table with a subtable
        $table1 = $this->_getDataTable1ForTest();
        $table2 = $this->_getDataTable2ForTest();
        $table2->getFirstRow()->setSubtable($table1);
        $idSubtable = 1; // subtableIds are consecutive, we cannot use $table->getId()

        /* Check it looks good:
        $renderer = DataTable\Renderer::factory('xml');
        $renderer->setTable($table2);
        $renderer->setRenderSubTables(true);
        echo $renderer->render();
        */

        // test serialize:
        // - subtable is serialized as expected
        $serializedStrings = $table2->getSerialized();

        // both the main table and the sub table are serialized
        $this->assertEquals(sizeof($serializedStrings), 2);

        // the serialized string references the id subtable
        $unserialized = unserialize($serializedStrings[0]);
        $this->assertSame($idSubtable, $unserialized[0][3], "not found the id sub table in the serialized, not expected");

        // KABOOM, we delete the subtable, reproducing a "random data issue"
        Manager::getInstance()->deleteTable($table1->getId());

        // Now we will serialize this "broken datatable" and check it works.

        // - it does not throw an exception
        $serializedStrings = $table2->getSerialized();

        // - the serialized table does NOT contain the sub table
        $this->assertEquals(sizeof($serializedStrings), 1); // main table only is serialized
        $unserialized = unserialize($serializedStrings[0]);

        // - the serialized string does NOT contain the id subtable (the row was cleaned up as expected)
        $this->assertNull($unserialized[0][3], "found the id sub table in the serialized, not expected");
    }

    public function testMergeSubtablesKeepsMetadata()
    {
        $dataTable = $this->_getDataTable1ForTest();
        $dataTable->setMetadata('additionalMetadata', 'test');
        $dataTable = $dataTable->mergeSubtables();
        $this->assertEquals('test', $dataTable->getMetadata('additionalMetadata'));
    }

    public function test_sumRowWithLabel_addsNewRowIfTableDoesNotHaveRowWithSameLabel()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 30],
        ]);

        $dataTable->sumRowWithLabel('four', ['nb_visits' => 40]);

        $expectedRows = [
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 30],
            ['label' => 'four', 'nb_visits' => 40],
        ];

        $actualRows = $dataTable->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    public function test_sumRowWithLabel_sumsWithExistingRowIfTableDoesHaveRowWithSameLabel()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 30],
        ]);

        $dataTable->sumRowWithLabel('three', ['nb_visits' => 40]);

        $expectedRows = [
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 70],
        ];

        $actualRows = $dataTable->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    public function test_sumRowWithLabel_usesSummaryRowIfLabelIsSpecialRankingQueryLabel()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 30],
        ]);
        $dataTable->addSummaryRow(new Row([Row::COLUMNS => ['label' => DataTable::LABEL_SUMMARY_ROW, 'nb_visits' => 60]]));

        $dataTable->sumRowWithLabel(RankingQuery::LABEL_SUMMARY_ROW, ['nb_visits' => 50]);

        $expectedRows = [
            0 => ['label' => 'one', 'nb_visits' => 10],
            1 => ['label' => 'two', 'nb_visits' => 20],
            2 => ['label' => 'three', 'nb_visits' => 30],
            -1 => ['label' => DataTable::LABEL_SUMMARY_ROW, 'nb_visits' => 110],
        ];

        $actualRows = $dataTable->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    public function test_sumRowWithLabel_defaultsNullLabelToEmptyString()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 30],
        ]);

        $dataTable->sumRowWithLabel(null, ['nb_visits' => 40]);

        $expectedRows = [
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => 'three', 'nb_visits' => 30],
            ['label' => '', 'nb_visits' => 40],
        ];

        $actualRows = $dataTable->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    public function test_sumRowWithLabel_usesCustomAggregationOpsIfSupplied()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => '', 'nb_visits' => 30],
        ]);

        $dataTable->sumRowWithLabel(null, ['nb_visits' => 40], ['nb_visits' => 'max']);

        $expectedRows = [
            ['label' => 'one', 'nb_visits' => 10],
            ['label' => 'two', 'nb_visits' => 20],
            ['label' => '', 'nb_visits' => 40],
        ];

        $actualRows = $dataTable->getRows();
        $actualRows = array_map(function (Row $r) {
            return $r->getColumns();
        }, $actualRows);

        $this->assertEquals($expectedRows, $actualRows);
    }

    private function createDataTable($rows)
    {
        $useless1 = new DataTable();
        foreach ($rows as $row) {
            $useless1->addRowFromArray(array(Row::COLUMNS => $row));
        }

        return $useless1;
    }

    protected function _getDataTable1ForTest()
    {
        $rows = $this->_getRowsDataTable1ForTest();
        $table = new DataTable();
        $table->addRowsFromArray($rows);
        return $table;
    }

    protected function _getDataTable2ForTest()
    {
        $rows = $this->_getRowsDataTable2ForTest();
        $table = new DataTable();
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
