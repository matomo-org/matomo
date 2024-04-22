<?php

namespace Piwik\Tests\Unit\DataTable;

use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class MapTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Manager::getInstance()->deleteAll();
    }

    public function testMultiFilter()
    {
        // first map
        $innerTable1 = new DataTable();
        $innerTable1->addRowsFromSimpleArray([
            ['label' => '2020-03-04.1', 'value' => 1],
        ]);
        $innerTable2 = new DataTable();
        $innerTable2->addRowsFromSimpleArray([
            ['label' => '2020-03-04.2', 'value' => 3],
        ]);
        $innerTable3 = new DataTable();
        $innerTable3->addRowsFromSimpleArray([
            ['label' => '2020-05-05.3', 'value' => 5],
        ]);
        $innerTable4 = new DataTable();
        $innerTable4->addRowsFromSimpleArray([
            ['label' => '2020-05-05.4', 'value' => 7],
        ]);

        $innerMap1 = new DataTable\Map();
        $innerMap1->addTable($innerTable1, '1');
        $innerMap1->addTable($innerTable2, '2');

        $innerMap2 = new DataTable\Map();
        $innerMap2->addTable($innerTable3, '3');
        $innerMap2->addTable($innerTable4, '4');

        $outerMap1 = new DataTable\Map();
        $outerMap1->addTable($innerMap1, '2020-03-04');
        $outerMap1->addTable($innerMap2, '2020-05-05');

        // second map
        $innerTable5 = new DataTable();
        $innerTable5->addRowsFromSimpleArray([
            ['label' => '2020-03-04.1', 'value' => 9],
        ]);
        $innerTable6 = new DataTable();
        $innerTable6->addRowsFromSimpleArray([
            ['label' => '2020-03-04.2', 'value' => 11],
        ]);
        $innerTable7 = new DataTable();
        $innerTable7->addRowsFromSimpleArray([
            ['label' => '2020-05-06.5', 'value' => 13],
        ]);
        $innerTable8 = new DataTable();
        $innerTable8->addRowsFromSimpleArray([
            ['label' => '2020-05-06.4', 'value' => 15],
        ]);

        $innerMap3 = new DataTable\Map();
        $innerMap3->addTable($innerTable5, '1');
        $innerMap3->addTable($innerTable6, '2');

        $innerMap4 = new DataTable\Map();
        $innerMap4->addTable($innerTable7, '5');
        $innerMap4->addTable($innerTable8, '4');

        $outerMap2 = new DataTable\Map();
        $outerMap2->addTable($innerMap3, '2020-03-04');
        $outerMap2->addTable($innerMap4, '2020-05-06');

        $visitedLabels = [];
        $result = $outerMap1->multiFilter([$outerMap2], function ($table1, $table2) use (&$visitedLabels) {
            $label1 = $table1->getFirstRow()->getColumn('label');
            $value1 = $table1->getFirstRow()->getColumn('value');

            $label2 = empty($table2) ? false : $table2->getFirstRow()->getColumn('label');
            $value2 = empty($table2) ? 0 : $table2->getFirstRow()->getColumn('value');

            $visitedLabels[] = [$label1, $label2];

            return $value1 + $value2;
        });

        $this->assertEquals([
            '2020-03-04' => ['1' => 10, '2' => 14],
            '2020-05-05' => ['3' => 5, '4' => 7],
        ], $result);
        $this->assertEquals([
            ['2020-03-04.1', '2020-03-04.1'],
            ['2020-03-04.2', '2020-03-04.2'],
            ['2020-05-05.3', false],
            ['2020-05-05.4', false],
        ], $visitedLabels);
    }

    private function createTestDataTable()
    {
        $result = new DataTable();

        $result->addRowsFromArray(array(
                                       array(Row::COLUMNS => array('label' => 'row1', 'col1' => 1)),
                                       array(Row::COLUMNS => array('label' => 'row2', 'col1' => 2))
                                  ));

        return $result;
    }

    private function createInstanceWithDataTables()
    {
        $dataTable = new DataTable\Map();

        $subDataTable1 = $this->createTestDataTable();
        $dataTable->addTable($subDataTable1, 'subDataTable1');

        $subDataTable2 = $this->createTestDataTable();
        $dataTable->addTable($subDataTable2, 'subDataTable2');

        return $dataTable;
    }

    private function createInstanceWithDataTableMaps()
    {
        $dataTable = new DataTable\Map();

        $subDataTableMap1 = $this->createInstanceWithDataTables();
        $dataTable->addTable($subDataTableMap1, 'subArray1');

        $subDataTableMap2 = $this->createInstanceWithDataTables();
        $dataTable->addTable($subDataTableMap2, 'subArray2');

        return $dataTable;
    }

    /**
     * Tests that Set::mergeChildren works when the DataTable\Map contains DataTables.
     * @group Core
     */
    public function testMergeChildrenDataTable()
    {
        $dataTable = $this->createInstanceWithDataTables();

        $result = $dataTable->mergeChildren();

        // check that the result is a DataTable w/ 4 rows
        $this->assertInstanceOf('\Piwik\DataTable', $result);
        $this->assertEquals(4, $result->getRowsCount());

        // check that the first two rows have 'subDataTable1' as the label
        $this->mergeChildrenCheckRow($result->getRowFromId(0), 'subDataTable1', 1);
        $this->mergeChildrenCheckRow($result->getRowFromId(1), 'subDataTable1', 2);

        // check that the last two rows have 'subDataTable2' as the label
        $this->mergeChildrenCheckRow($result->getRowFromId(2), 'subDataTable2', 1);
        $this->mergeChildrenCheckRow($result->getRowFromId(3), 'subDataTable2', 2);
    }

    private function mergeChildrenCheckRow(Row $row, $expectedLabel, $expectedColumnValue)
    {
        $this->assertEquals($expectedLabel, $row->getColumn('label'));
        $this->assertEquals($expectedColumnValue, $row->getColumn('col1'));
    }

    /**
     * Tests that Set::mergeChildren works when the DataTable\Map contains DataTable\Maps.
     * @group Core
     */
    public function testMergeChildrenDataTableMap()
    {
        $dataTable = $this->createInstanceWithDataTableMaps();

        $result = $dataTable->mergeChildren();

        // check that the result is a DataTable\Map w/ two DataTable children
        $this->assertInstanceOf('\Piwik\DataTable\Map', $result);
        $this->assertEquals(2, $result->getRowsCount());

        // check that the first sub-DataTable is a DataTable with 4 rows
        $subDataTable1 = $result->getTable('subDataTable1');
        $this->assertTrue($subDataTable1 instanceof DataTable);
        $this->assertEquals(4, $subDataTable1->getRowsCount());

        // check that the first two rows of the first sub-table have 'subArray1' as the label
        $this->mergeChildrenCheckRow($subDataTable1->getRowFromId(0), 'subArray1', 1);
        $this->mergeChildrenCheckRow($subDataTable1->getRowFromId(1), 'subArray1', 2);

        // check that the last two rows of the first sub-table have 'subArray2' as the label
        $this->mergeChildrenCheckRow($subDataTable1->getRowFromId(2), 'subArray2', 1);
        $this->mergeChildrenCheckRow($subDataTable1->getRowFromId(3), 'subArray2', 2);

        // check that the second sub-DataTable is a DataTable with 4 rows
        $subDataTable2 = $result->getTable('subDataTable2');
        $this->assertTrue($subDataTable2 instanceof DataTable);
        $this->assertEquals(4, $subDataTable2->getRowsCount());

        // check that the first two rows of the second sub-table have 'subArray1' as the label
        $this->mergeChildrenCheckRow($subDataTable2->getRowFromId(0), 'subArray1', 1);
        $this->mergeChildrenCheckRow($subDataTable2->getRowFromId(1), 'subArray1', 2);

        // check that the last two rows of the second sub-table have 'subArray2' as the label
        $this->mergeChildrenCheckRow($subDataTable2->getRowFromId(2), 'subArray2', 1);
        $this->mergeChildrenCheckRow($subDataTable2->getRowFromId(3), 'subArray2', 2);
    }
}
