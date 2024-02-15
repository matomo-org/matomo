<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group PrependSegmentTest
 * @group DataTable
 * @group Filter
 */
class PrependSegmentTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'PrependSegment';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->addRowWithMetadata(array('test' => '1'));
        $this->addRowWithMetadata(array('test' => '2', 'segment' => 'country=NZ'));
        $this->addRowWithMetadata(array('test' => '3'));
        $this->addRowWithMetadata(array('test' => '1', 'segment' => 'country=AU'));
        $this->addRowWithMetadata(array('test' => '4', 'segment' => ''));
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
        $prepend = 'city=test;';
        $this->table->filter($this->filter, array($prepend));

        $metadata = $this->table->getRowsMetadata('segment');
        $this->assertSame(
            array(
            false,
            $prepend . 'country=NZ',
            false,
            $prepend . 'country=AU',
            $prepend),
            $metadata
        );

        // should be still the same
        $metadata = $this->table->getRowsMetadata('test');
        $this->assertSame(array('1', '2', '3', '1', '4'), $metadata);
    }
}
