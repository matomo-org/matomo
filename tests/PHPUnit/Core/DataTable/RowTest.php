<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable;
use Piwik\DataTable\Row;

class RowTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testDataTableAssociatedIsNegativeWhenSubDataTableInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    /**
     * @group Core
     */
    public function testDataTableAssociatedIsNegativeWhenSubDataTableAdded()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $testRow->addSubtable($this->getTestSubDataTable());
        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    /**
     * @group Core
     */
    public function testDataTableAssociatedIsNegativeWhenSubDataTableSetted()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $testRow->setSubtable($this->getTestSubDataTable());
        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    /**
     * @group Core
     */
    public function testIdSubDataTabledIsPositiveWhenSubDataTableInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->getIdSubDataTable() > 0);
    }


    /**
     * @group Core
     */
    public function testDataTableAssociatedIsPositiveOnSerializedRow()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();

        // testDataTableAssociatedIsPositiveOnSerializedRow is only valid as long as the Row is not modified after being unserialized
        $this->assertFalse(method_exists($testRow, '__wakeup'));

        $serializedTestRow = serialize($testRow);
        $unserializedTestRow = unserialize($serializedTestRow);

        $this->assertTrue($unserializedTestRow->c[Row::DATATABLE_ASSOCIATED] > 0);
    }

    /**
     * @group Core
     */
    public function testDataTableAssociatedIsNegativeAfterSerialize()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();

        serialize($testRow);

        $testRow->cleanPostSerialize();

        $this->assertTrue($testRow->c[Row::DATATABLE_ASSOCIATED] < 0);
    }

    /**
     * @group Core
     */
    public function testIsSubDataTableLoadedIsTrueWhenSubDataTableInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableLoaded();
        $this->assertTrue($testRow->isSubtableLoaded());
    }

    /**
     * @group Core
     */
    public function testIsSubDataTableLoadedIsFalseWhenSubDataTableNotInMemory()
    {
        $testRow = $this->getTestRowWithSubDataTableNotLoaded();
        $this->assertFalse($testRow->isSubtableLoaded());
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
