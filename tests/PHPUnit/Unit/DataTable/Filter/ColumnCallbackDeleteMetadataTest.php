<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group ColumnCallbackDeleteMetadataTest
 * @group DataTable
 * @group Filter
 */
class ColumnCallbackDeleteMetadataTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'ColumnCallbackDeleteMetadata';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->addRowWithMetadata(array('test' => '1'));
        $this->addRowWithMetadata(array('test' => '2', 'other' => 'value'));
        $this->addRowWithMetadata(array('test' => '3'));
        $this->addRowWithMetadata(array('test' => '1', 'other' => 'value'));
        $this->addRowWithMetadata(array('test' => '4'));
    }

    private function buildRowWithMetadata($metadata)
    {
        $row = new Row(array(Row::COLUMNS => array('label' => 'val1')));
        foreach ($metadata as $name => $value) {
            $row->setMetadata($name, $value);
        }
        return $row;
    }

    private function addRowWithMetadata($metadata)
    {
        $row = $this->buildRowWithMetadata($metadata);
        $this->table->addRow($row);

        return $row;
    }

    public function test_filter_shouldRemoveAllMetadataEntriesHavingTheGivenName()
    {
        $this->table->filter($this->filter, array('test'));

        $metadata = $this->table->getRowsMetadata('test');
        $this->assertSame(array(false, false, false, false, false), $metadata);

        $metadata = $this->table->getRowsMetadata('other');
        $expected = array(false, 'value', false, 'value', false);
        $this->assertSame($expected, $metadata);
    }

    public function test_filter_shouldRemoveAllMetadataEntriesHavingTheGivenName_EvenIfOnlySomeRowsHaveThatMetadataName()
    {
        $this->table->filter($this->filter, array('other'));

        $metadata = $this->table->getRowsMetadata('other');
        $this->assertSame(array(false, false, false, false, false), $metadata);

        $metadata = $this->table->getRowsMetadata('test');
        $expected = array('1', '2', '3', '1', '4');
        $this->assertSame($expected, $metadata);
    }

    public function test_filter_shouldRemoveTheMetadataFromSubtables_IfOneIsSet()
    {
        $row   = $this->addRowWithMetadata(array('test' => '5', 'other' => 'value2'));
        $table = new DataTable();
        $table->addRow($this->buildRowWithMetadata(array('other' => 'value3')));
        $table->addRow($this->buildRowWithMetadata(array('test' => '6')));
        $table->addRow($this->buildRowWithMetadata(array('test' => '7', 'other' => 'value4')));
        $row->setSubtable($table);

        $this->table->filter($this->filter, array('other'));

        $this->assertFalse($row->getMetadata('other'));

        $metadata = $table->getRowsMetadata('other');
        $this->assertSame(array(false, false, false), $metadata);
    }
}
