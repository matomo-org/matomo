<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group Core
 */
class RowTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Row
     */
    private $row;

    public function setUp()
    {
        $this->row = new Row();
    }

    public function testDataTableAssociatedIsNegativeWhenSubDataTableInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    public function testDataTableAssociatedIsNegativeWhenSubDataTableAdded()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $testRow->addSubtable($this->getTestSubDataTable());
        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    public function testDataTableAssociatedIsNegativeWhenSubDataTableSetted()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $testRow->setSubtable($this->getTestSubDataTable());
        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    public function testIdSubDataTabledIsPositiveWhenSubDataTableInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->getIdSubDataTable() > 0);
    }

    public function testDataTableAssociatedIsPositiveOnSerializedRow()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();

        // testDataTableAssociatedIsPositiveOnSerializedRow is only valid as long as the Row is not modified after being unserialized
        $this->assertFalse(method_exists($testRow, '__wakeup'));

        $serializedTestRow = serialize($testRow);
        $unserializedTestRow = unserialize($serializedTestRow);

        $this->assertTrue($unserializedTestRow->c[Row::DATATABLE_ASSOCIATED] > 0);
    }

    public function testDataTableAssociatedIsNegativeAfterSerialize()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();

        serialize($testRow);

        $testRow->cleanPostSerialize();

        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
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

    public function test_getColumn_shouldReturnRawScalarValue()
    {
        $this->assertColumnSavesValue(5, 'testInteger', 5);
        $this->assertColumnSavesValue(5.444, 'testFloat', 5.444);
        $this->assertColumnSavesValue('MyString', 'testString', 'MyString');
        $this->assertColumnSavesValue(array(array(1 => '5')), 'testArray', array(array(1 => '5')));
    }

    public function test_getColumn_shouldResolveACallable()
    {
        $this->assertColumnSavesValue(6, 'testClosure', function () {
            return 6;
        });

        $this->assertColumnSavesValue(7, 'testCallable', array($this, 'callbackReturnScalar'));
    }

    public function test_getColumn_shouldPassRowToCallable()
    {
        $callbackRow = null;

        $this->row->addColumn('testClosure', function (Row $row) use (&$callbackRow) {
            $callbackRow = $row;
            return $row;
        });

        $returnedRow = $this->row->getColumn('testClosure');
        $this->assertNotEmpty($callbackRow);
        $this->assertSame($returnedRow, $callbackRow);
    }

    public function test_getColumn_shouldReturnFalseIfValueIsNull()
    {
        $this->assertColumnSavesValue(false, 'testScalar', null);
        $this->assertColumnSavesValue(false, 'testClosure', function () {
            return null;
        });
    }

    public function test_getColumns_shouldNotCallAnyCallableForSecurity()
    {
        $this->assertColumnSavesValue('print_r', 'testScalar', 'print_r');
        $this->assertColumnSavesValue(array('print_r'), 'testScalar', array('print_r'));
        $this->assertColumnSavesValue(array(null, 'print_r'), 'testScalar', array(null, 'print_r'));

        $this->assertColumnSavesValue('phpinfo', 'testScalar', 'phpinfo');
        $this->assertColumnSavesValue(array('phpinfo'), 'testScalar', array('phpinfo'));
        $this->assertColumnSavesValue(array(null, 'phpinfo'), 'testScalar', array(null, 'phpinfo'));
    }

    public function test_getColumns_shouldReturnAllColumns()
    {
        $this->row->setColumns(array(
            'nb_visits' => 4,
            'label'     => 'Test',
            'closure'   => function () { return 5; },
            'callable'  => array($this, 'callbackReturnScalar'),
            'goals'     => array(1 => array())
        ));

        $expected = array(
            'nb_visits' => 4,
            'label'     => 'Test',
            'closure'   => 5,
            'callable'  => 7,
            'goals'     => array(1 => array())
        );

        $this->assertEquals($expected, $this->row->getColumns());
    }

    public function test_getColumns_shouldNotConvertNullValuesToFalse()
    {
        $this->row->setColumns(array(
            'nb_visits' => null,
            'label'     => 'Test',
            'closure'   => function () { return null; },
            'boolean'   => false
        ));

        $expected = array(
            'nb_visits' => null,
            'label'     => 'Test',
            'closure'   => null,
            'boolean'   => false
        );

        $this->assertSame($expected, $this->row->getColumns());
    }

    public function callbackReturnScalar(Row $row)
    {
        return 7;
    }

    public function test_SumRow_shouldIgnoreCallableValues_AndNotRaiseAnyException()
    {
        $columns = array(
            'nb_visits' => 5,
            'label'     => 'Test',
            'closure'   => function () { return 7; },
        );

        $this->row->setColumns($columns);

        $secondRow = new Row(array(Row::COLUMNS => $columns));

        $this->row->sumRow($secondRow);

        $this->assertEquals(10, $this->row->getColumn('nb_visits'));
        $this->assertEquals(7, $this->row->getColumn('closure'));
    }

    private function assertColumnSavesValue($expectedValue, $columnName, $valueToSet)
    {
        $this->row->setColumn($columnName, $valueToSet);
        $this->assertSame($expectedValue, $this->row->getColumn($columnName));
    }

    protected function getTestRowWithSubDataTableLoaded()
    {
        $testSubDataTable = $this->getTestSubDataTable();

        $testRow = new Row(
            array(
                 Row::DATATABLE_ASSOCIATED => $testSubDataTable
            )
        );

        return $testRow;
    }

    protected function getTestSubDataTable()
    {
        return new DataTable();
    }

    protected function getTestRowWithSubDataTableNotLoaded()
    {
        $testRow = new Row(
            array(
                 Row::DATATABLE_ASSOCIATED => 50
            )
        );

        return $testRow;
    }
}
