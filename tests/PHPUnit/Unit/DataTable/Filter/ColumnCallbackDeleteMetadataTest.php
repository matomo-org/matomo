<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

/**
 * @group ColumnCallbackDeleteMetadataTest
 * @group DataTable
 * @group Filter
 * @group Unit
 * @group Core
 */
class ColumnCallbackDeleteMetadataTest extends UnitTestCase
{
    private $filter = 'ColumnCallbackDeleteMetadata';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp()
    {
        $this->table = new DataTable();
        $this->addRowWithMetadata(array('test' => '1'));
        $this->addRowWithMetadata(array('test' => '2', 'other' => 'value'));
        $this->addRowWithMetadata(array('test' => '3'));
        $this->addRowWithMetadata(array('test' => '1', 'other' => 'value'));
        $this->addRowWithMetadata(array('test' => '4'));
    }

    private function addRowWithMetadata($metadata)
    {
        $row = new Row(array(Row::COLUMNS => array('label' => 'val1')));
        foreach ($metadata as $name => $value) {
            $row->setMetadata($name, $value);
        }
        $this->table->addRow($row);
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
}
