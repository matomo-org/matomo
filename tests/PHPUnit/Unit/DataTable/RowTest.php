<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class RowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Row
     */
    private $row;

    public function setUp(): void
    {
        $this->row = new Row();
    }

    public function testIsSubtableLoadedReturnsTrueIfDataTableAssociatedIsLoaded()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->isSubtableLoaded());
        $this->assertGreaterThanOrEqual(1, $testRow->getIdSubDataTable());
    }

    public function testIsSubtableLoadedReturnsTrueWhenSubDataTableSetted()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $this->assertFalse($testRow->isSubtableLoaded()); // verify not already loaded
        $this->assertEquals(50, $testRow->getIdSubDataTable());

        $testRow->setSubtable($this->getTestSubDataTable());
        $this->assertTrue($testRow->isSubtableLoaded());
        $this->assertGreaterThanOrEqual(1, $testRow->getIdSubDataTable());
    }

    public function testGetIdSubDataTableShouldBeNullIfNoSubtableIsSet()
    {
        $testRow = $this->getTestRowWithNoSubDataTable();
        $this->assertEquals(null, $testRow->getIdSubDataTable());
    }

    public function testRemoveSubtableShouldRemoveASetSubtable()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->isSubtableLoaded());

        $testRow->removeSubtable();

        $this->assertFalse($testRow->isSubtableLoaded());
        $this->assertEquals(null, $testRow->getIdSubDataTable());
    }

    public function testDestructShouldRemoveASetSubtable()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->isSubtableLoaded());

        $testRow->__destruct();

        $this->assertFalse($testRow->isSubtableLoaded());
        $this->assertEquals(null, $testRow->getIdSubDataTable());
    }

    public function testCanBeClonedShouldRemoveASetSubtable()
    {
        $testRow = $this->getTestRowWithNoSubDataTable();
        $testRow->setColumn('label', 'test');

        $testRow2 = clone $testRow;

        $this->assertNotSame($testRow2, $testRow);
        $this->assertEquals('test', $testRow2->getColumn('label'));
        $this->assertEquals('test', $testRow->getColumn('label'));

        $testRow->setColumn('label', 'different');

        // only row 2 changes
        $this->assertEquals('test', $testRow2->getColumn('label'));
        $this->assertEquals('different', $testRow->getColumn('label'));
    }

    public function testExportShouldExportColumnsMetadataAndSubtableId()
    {
        $columns = array('label' => 'test', 'nb_visits' => 5);

        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $testRow->setColumns($columns);
        $testRow->setMetadata('test1', 'val1');
        $testRow->setMetadata('url', 'http://piwik.org');
        $export = $testRow->export();

        $expected = array(
            Row::COLUMNS => $columns,
            Row::METADATA => array('test1' => 'val1', 'url' => 'http://piwik.org')
        );

        // we cannot really test for exact match since the subtableId might change when other tests are changed
        $this->assertGreaterThan(1, $export[Row::DATATABLE_ASSOCIATED]);
        unset($export[Row::DATATABLE_ASSOCIATED]);

        $this->assertSame($expected, $export);
    }

    public function testIsSubtableLoadedShouldReturnFalseWhenRestoringAnExportedRow()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();

        // serialize and unserialize is not needed for this test case, the export is the important part.
        // we still do it, to have it more "realistic"
        $serializedTestRow   = serialize($testRow->export());
        $unserializedTestRow = unserialize($serializedTestRow);

        /** @var Row $unserializedTestRow */
        $row = new Row($unserializedTestRow);

        $this->assertTrue($row->getIdSubDataTable() > 0);
        $this->assertFalse($row->isSubtableLoaded());
    }

    public function testIsSubDataTableLoadedIsTrueWhenSubDataTableInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->isSubtableLoaded());
    }

    public function testIsSubDataTableLoadedIsFalseWhenSubDataTableNotInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $this->assertFalse($testRow->isSubtableLoaded());
    }

    public function testGetMetadataSetMetadataShouldReturnRawScalarValue()
    {
        $this->assertMetadataSavesValue(5, 'testInteger', 5);
        $this->assertMetadataSavesValue(5.444, 'testFloat', 5.444);
        $this->assertMetadataSavesValue('MyString', 'testString', 'MyString');
        $this->assertMetadataSavesValue(array(array(1 => '5')), 'testArray', array(array(1 => '5')));
    }

    public function testGetMetadataShouldReturnFalseIfMetadataKeyDoesNotExists()
    {
        $this->assertFalse($this->row->getMetadata('anyKey_thatDoesNotExist'));
    }

    public function testGetMetadataShouldReturnEmptyArrayIfNoParticularOneIsRequestedAndNoneAreSet()
    {
        $this->assertEquals(array(), $this->row->getMetadata());
    }

    public function testGetMetadataShouldReturnAllMetadataValuesIfNoParticularOneIsRequested()
    {
        $this->row->setMetadata('url', 'http://piwik.org');
        $this->row->setMetadata('segmentValue', 'test==piwik');

        $this->assertEquals(array(
            'url' => 'http://piwik.org',
            'segmentValue' => 'test==piwik'
        ), $this->row->getMetadata());
    }

    public function testDeleteMetadataShouldReturnDeleteAllValuesWhenNoSpecificOneIsRequestedToBeDeleted()
    {
        $this->row->setMetadata('url', 'http://piwik.org');
        $this->row->setMetadata('segmentValue', 'test==piwik');

        $this->assertNotEmpty($this->row->getMetadata()); // make sure it is actually set

        $this->row->deleteMetadata();

        $this->assertSame(array(), $this->row->getMetadata());
    }

    public function testDeleteMetadataShouldOnlyDeleteARequestedMetadataEntryWhileKeepingOthersUntouched()
    {
        $this->row->setMetadata('url', 'http://piwik.org');
        $this->row->setMetadata('segmentValue', 'test==piwik');

        $this->assertTrue($this->row->deleteMetadata('url'));

        $this->assertFalse($this->row->getMetadata('url'));
        $this->assertEquals('test==piwik', $this->row->getMetadata('segmentValue'));
    }

    public function testDeleteMetadataShouldReturnFalseAndKeepOtherEntriesUntouchedIfMetadataNameDidNotExist()
    {
        $this->row->setMetadata('segmentValue', 'test==piwik');

        $this->assertFalse($this->row->deleteMetadata('url'));

        $this->assertEquals('test==piwik', $this->row->getMetadata('segmentValue'));
    }

    public function testGetColumnShouldReturnRawScalarValue()
    {
        $this->assertColumnSavesValue(5, 'testInteger', 5);
        $this->assertColumnSavesValue(5.444, 'testFloat', 5.444);
        $this->assertColumnSavesValue('MyString', 'testString', 'MyString');
        $this->assertColumnSavesValue(array(array(1 => '5')), 'testArray', array(array(1 => '5')));
    }

    public function testGetColumnShouldReturnFalseIfValueIsNull()
    {
        $this->assertColumnSavesValue(false, 'testScalar', null);
    }

    public function testGetColumnsShouldNotCallAnyCallableForSecurity()
    {
        $this->assertColumnSavesValue('print_r', 'testScalar', 'print_r');
        $this->assertColumnSavesValue(array('print_r'), 'testScalar', array('print_r'));
        $this->assertColumnSavesValue(array(null, 'print_r'), 'testScalar', array(null, 'print_r'));

        $this->assertColumnSavesValue('phpinfo', 'testScalar', 'phpinfo');
        $this->assertColumnSavesValue(array('phpinfo'), 'testScalar', array('phpinfo'));
        $this->assertColumnSavesValue(array(null, 'phpinfo'), 'testScalar', array(null, 'phpinfo'));
    }

    public function testGetColumnsSetColumnsShouldReturnAllColumns()
    {
        $this->row->setColumns(array(
            'nb_visits' => 4,
            'label'     => 'Test',
            'goals'     => array(1 => array())
        ));

        $expected = array(
            'nb_visits' => 4,
            'label'     => 'Test',
            'goals'     => array(1 => array())
        );

        $this->assertEquals($expected, $this->row->getColumns());
        $this->assertEquals('Test', $this->row->getColumn('label'));
        $this->assertEquals(4, $this->row->getColumn('nb_visits'));
    }

    public function testDeleteColumnShouldOnlyDeleteARequestedColumnEntryWhileKeepingOthersUntouched()
    {
        $this->row->setColumn('label', 'http://piwik.org');
        $this->row->setColumn('nb_visits', '1');

        $this->assertTrue($this->row->deleteColumn('nb_visits'));
        $this->assertFalse($this->row->hasColumn('nb_visits'));  // verify
        $this->assertFalse($this->row->getMetadata('nb_visits')); // verify

        $this->assertEquals('http://piwik.org', $this->row->getColumn('label')); // make sure not deleted
    }

    public function testDeleteColumnShouldReturnFalseAndKeepOtherEntriesUntouchedIfColumnNameDidNotExist()
    {
        $this->row->setColumn('label', 'http://piwik.org');

        $this->assertFalse($this->row->deleteColumn('nb_visits'));
        $this->assertFalse($this->row->hasColumn('nb_visits'));

        $this->assertEquals('http://piwik.org', $this->row->getColumn('label'));
    }

    public function testDeleteColumnShouldReturnAColumnValueThatIsNull()
    {
        $this->row->setColumn('label', null);

        $this->assertTrue($this->row->hasColumn('label'));
        $this->assertTrue($this->row->deleteColumn('label'));
        $this->assertFalse($this->row->hasColumn('label'));
    }

    public function testRenameColumnShouldReturnAColumnOnlyIfAValueIsSetForThatColumn()
    {
        $this->row->setColumn('nb_visits', 10);

        $this->row->renameColumn('nb_visits', 'nb_hits');

        $this->assertFalse($this->row->hasColumn('nb_visits'));
        $this->assertTrue($this->row->hasColumn('nb_hits'));
        $this->assertEquals(10, $this->row->getColumn('nb_hits'));
    }

    public function testRenameColumnShouldNotReturnAColumnIfValueIsNotSetButRemoveColumn()
    {
        $this->row->setColumn('nb_visits', null);

        $this->row->renameColumn('nb_visits', 'nb_hits');

        $this->assertFalse($this->row->hasColumn('nb_visits'));
        $this->assertFalse($this->row->hasColumn('nb_hits'));
    }

    public function testRenameColumnShouldDoNothingIfGivenColumnDoesNotExist()
    {
        $this->row->setColumn('nb_visits', 11);

        $this->row->renameColumn('nb_hits', 'nb_pageviews');

        $this->assertFalse($this->row->hasColumn('nb_hits'));
        $this->assertFalse($this->row->hasColumn('nb_pageviews'));
        $this->assertEquals(11, $this->row->getColumn('nb_visits'));
    }

    public function testGetSubtableShouldReturnSubtableIfLoaded()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $subTable = $this->getTestSubDataTable();
        $testRow->setSubtable($subTable);

        $this->assertSame($subTable, $testRow->getSubtable());
    }

    public function testGetSubtableShouldReturnFalseIfSubtableExistsButIsNotLoaded()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();

        $this->assertFalse($testRow->getSubtable());
    }

    public function testGetSubtableShouldReturnFalseIfHasNoSubtableAtAll()
    {
        $testRow = $this->getTestRowWithNoSubDataTable();

        $this->assertFalse($testRow->getSubtable());
    }

    public function testSumSubTableWhenSubTableAlreadyExistsOverwriteExistingSubtable()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $this->assertFalse($testRow->isSubtableLoaded());

        $subTable = $this->getTestSubDataTable();
        $testRow->setSubtable($subTable);
        $this->assertTrue($testRow->isSubtableLoaded());

        $testRow->sumSubtable($subTable);

        $this->assertTrue(DataTable::isEqual($testRow->getSubtable(), $subTable));
    }

    public function testHasColumn()
    {
        $this->row->setColumns(array('test1' => 'yes', 'test2' => false, 'test3' => 5, 'test4' => array()));

        $this->assertFalse($this->row->hasColumn('test')); // does not exist
        $this->assertTrue($this->row->hasColumn('test1'));
        $this->assertTrue($this->row->hasColumn('test2')); // even if value is false it still exists
        $this->assertTrue($this->row->hasColumn('test3'));
        $this->assertTrue($this->row->hasColumn('test4'));
    }

    public function testHasColumnShouldReturnTrueEvenIfColumnValueIsNull()
    {
        $this->assertFalse($this->row->hasColumn('test'));
        $this->row->setColumn('test', null);
        $this->assertTrue($this->row->hasColumn('test'));
    }

    public function testSumRowMetadataShouldSumMetadataAccordingToAggregationOperations()
    {
        $this->row->setColumn('nb_visits', 10);
        $this->row->setMetadata('my_sum', 5);
        $this->row->setMetadata('my_max', 4);
        $this->row->setMetadata('my_array', array(array('test' => 1, 'value' => 1), array('test' => 2, 'value' => 2)));


        $row = $this->getTestRowWithNoSubDataTable();
        $row->setColumn('nb_visits', 15);
        $row->setMetadata('my_sum', 7);
        $row->setMetadata('my_max', 2);
        $row->setMetadata('my_array', array(array('test' => 3, 'value' => 3), array('test' => 2, 'value' => 2)));


        $aggregations = array(
            'nosuchcolumn' => 'max', // this metadata name does not exist and should be ignored
            'my_sum' => 'sum',
            'my_max' => 'max',
            'my_array' => 'uniquearraymerge'
        );
        $this->row->sumRowMetadata($row, $aggregations);

        $metadata = $this->row->getMetadata();
        $expected = array(
            'my_sum' => 12,
            'my_max' => 4,
            'my_array' => array(array('test' => 1, 'value' => 1), array('test' => 2, 'value' => 2), array('test' => 3, 'value' => 3))
        );
        $this->assertSame($expected, $metadata);
    }

    public function testSumRowMetadataUniquearraymergeShouldUseArrayFromOtherRowIfNoMetadataForThisRowSpecified()
    {
        $row = $this->getTestRowWithNoSubDataTable();
        $arrayValue = array(array('test' => 3, 'value' => 3), array('test' => 2, 'value' => 2));
        $row->setMetadata('my_array', $arrayValue);

        $aggregations = array('my_array' => 'uniquearraymerge');

        $this->row->sumRowMetadata($row, $aggregations);

        $this->assertSame(array('my_array' => $arrayValue), $this->row->getMetadata());
    }

    public function testSumRowMetadataUniquearraymergeShouldUseArrayFromThisRowIfNoMetadataForOtherRowSpecified()
    {
        $row = $this->getTestRowWithNoSubDataTable();

        $arrayValue = array(array('test' => 3, 'value' => 3), array('test' => 2, 'value' => 2));
        $this->row->setMetadata('my_array', $arrayValue);

        $aggregations = array('my_array' => 'uniquearraymerge');

        $this->row->sumRowMetadata($row, $aggregations);

        $this->assertSame(array('my_array' => $arrayValue), $this->row->getMetadata());
    }

    public function testSumRowThrowsIfAddingUnsupportedTypes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Trying to sum unsupported operands for column mycol in row with label = row1: array + integer');

        $row1 = new Row();
        $row1->addColumn('label', 'row1');
        $row1->addColumn('mycol', ['a']);

        $row2 = new Row();
        $row2->addColumn('label', 'row2');
        $row2->addColumn('mycol', 45);

        $row1->sumRow($row2);
    }

    private function assertColumnSavesValue($expectedValue, $columnName, $valueToSet)
    {
        $this->row->setColumn($columnName, $valueToSet);
        $this->assertSame($expectedValue, $this->row->getColumn($columnName));
    }

    private function assertMetadataSavesValue($expectedValue, $metadataName, $valueToSet)
    {
        $this->row->setMetadata($metadataName, $valueToSet);
        $this->assertSame($expectedValue, $this->row->getMetadata($metadataName));
    }

    protected function getTestRowWithSubDataTableLoaded()
    {
        $testSubDataTable = $this->getTestSubDataTable();

        $testRow = new Row(array(
             Row::DATATABLE_ASSOCIATED => $testSubDataTable
        ));

        return $testRow;
    }

    protected function getTestRowWithNoSubDataTable()
    {
        return new Row(array());
    }

    protected function getTestSubDataTable()
    {
        return new DataTable();
    }

    protected function getTestRowWithSubDataTableNotLoaded()
    {
        $testRow = new Row(array(
             Row::DATATABLE_ASSOCIATED => 50
        ));

        return $testRow;
    }
}
