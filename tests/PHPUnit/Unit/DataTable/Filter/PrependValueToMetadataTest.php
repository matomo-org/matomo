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
 * @group PrependValueToMetadataTest
 * @group DataTable
 * @group Filter
 */
class PrependValueToMetadataTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'PrependValueToMetadata';

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
        $this->addRowWithMetadata(array('test' => '4', 'other' => ''));
    }

    private function addRowWithMetadata($metadata)
    {
        $row = new Row(array(Row::COLUMNS => array('label' => 'val1')));
        foreach ($metadata as $name => $value) {
            $row->setMetadata($name, $value);
        }
        $this->table->addRow($row);
    }

    public function testFilterShouldNotFailIfColumnOrValueIsNotSetOrDoesNotMatch()
    {
        $this->table->filter($this->filter, array('test', ''));
        $this->table->filter($this->filter, array('', 'test'));
        $this->table->filter($this->filter, array('', ''));
        $this->table->filter($this->filter, array('anyrandomcolumns', 'test'));

        // verify not modified
        $metadata = $this->table->getRowsMetadata('test');
        $this->assertSame(array('1', '2', '3', '1', '4'), $metadata);

        $metadata = $this->table->getRowsMetadata('other');
        $this->assertSame(array(false, 'value', false, 'value', ''), $metadata);
    }

    public function testFilterShouldPrependValueToMetadataNameIfPossible()
    {
        $this->table->filter($this->filter, array('test', 'piwik_'));

        $metadata = $this->table->getRowsMetadata('test');
        $this->assertSame(array('piwik_1', 'piwik_2', 'piwik_3', 'piwik_1', 'piwik_4'), $metadata);

        // those should still be the same
        $metadata = $this->table->getRowsMetadata('other');
        $this->assertSame(array(false, 'value', false, 'value', ''), $metadata);
    }

    public function testFilterShouldOnlyPrependIfAMetadataNameIsSet()
    {
        $this->table->filter($this->filter, array('other', 'prependme'));

        $metadata = $this->table->getRowsMetadata('other');
        $this->assertSame(array(false, 'prependmevalue', false, 'prependmevalue', 'prependme'), $metadata);

        // should still be the same
        $metadata = $this->table->getRowsMetadata('test');
        $this->assertSame(array('1', '2', '3', '1', '4'), $metadata);
    }
}
