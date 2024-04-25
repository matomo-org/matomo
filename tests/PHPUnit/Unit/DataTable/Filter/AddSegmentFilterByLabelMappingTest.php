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
 * @group AddSegmentByLabelMappingTest
 * @group DataTable
 * @group Filter
 */
class AddSegmentByLabelMappingTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'AddSegmentByLabelMapping';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = new DataTable();
        $this->addRow(array('label' => 1));
        $this->addRow(array('label' => ''));
        $this->addRow(array('label' => 3));
        $this->addRow(array('label' => '4'));
        $this->addRow(array('label' => 'play A movie', 'other' => 'value'));
        $this->addRow(array('label' => 'Piwik'));
    }

    private function getEmptyMapping()
    {
        return array();
    }

    private function getMapping()
    {
        return array(
            1 => 'Core',
            2 => 'plugins',
            3 => 'pluginstests'
        );
    }

    private function addRow($columns)
    {
        $this->table->addRow($this->buildRow($columns));
    }

    private function buildRow($columns)
    {
        return new Row(array(Row::COLUMNS => $columns));
    }

    public function testFilterShouldNotFailIfMappingIsEmpty()
    {
        $this->table->filter($this->filter, array('segmentName', $this->getEmptyMapping()));

        $metadata = $this->table->getRowsMetadata('segment');
        $this->assertSame(array(false, false, false, false, false, false), $metadata);
    }

    public function testFilterShouldMapOnlyValuesThatExistInMapping()
    {
        $this->table->filter($this->filter, array('segmentName', $this->getMapping()));

        $metadata = $this->table->getRowsMetadata('segment');
        $expected = array('segmentName==Core', false, 'segmentName==pluginstests', false, false, false);
        $this->assertSame($expected, $metadata);
    }

    public function testFilterShouldUrlEncodeValues()
    {
        $mapping = array(
            1 => 'Core tests',
            3 => 'plugins tästs'
        );
        $this->table->filter($this->filter, array('segmentName', $mapping));

        $metadata = $this->table->getRowsMetadata('segment');
        $expected = array('segmentName==Core+tests', false, 'segmentName==plugins+t%C3%A4sts', false, false, false);
        $this->assertSame($expected, $metadata);
    }
}
