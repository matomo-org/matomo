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
 * @group AddSegmentByLabelTest
 * @group DataTable
 * @group Filter
 */
class AddSegmentByLabelTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'AddSegmentByLabel';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->addRow(array('label' => 'http://piwik.org/test'));
        $this->addRow(array('label' => ''));
        $this->addRow(array('label' => 'search test', 'other' => 'value'));
        $this->addRow(array('label' => 'keyword t/est'));
        $this->addRow(array('label' => false));
        $this->addRow(array('label' => 'play A movie', 'other' => 'value'));
        $this->addRow(array('nb_visits' => 15));
        $this->addRow(array('label' => 'Piwik'));
    }

    private function addRow($columns)
    {
        $this->table->addRow($this->buildRow($columns));
    }

    private function buildRow($columns)
    {
        return new Row(array(Row::COLUMNS => $columns));
    }

    public function testFilterIfOnlyOneSegmentGivenShouldCopyTheValuePlainIfOnlyOneSegmentIsGiven()
    {
        $segmentName = 'city';
        $segmentStart = $segmentName . '==';
        $this->table->filter($this->filter, array($segmentName));

        $segmentValues = $this->table->getRowsMetadata('segment');
        $expected = array(
            $segmentStart . 'http%3A%2F%2Fpiwik.org%2Ftest',
            false, // empty label we do not generate for this currently
            $segmentStart . 'search+test',
            $segmentStart . 'keyword+t%2Fest',
            false,
            $segmentStart . 'play+A+movie',
            false,
            $segmentStart . 'Piwik');
        $this->assertSame($expected, $segmentValues);
    }

    public function testFilterIfOnlyOneSegmentGivenShouldIgnoreASummaryRow()
    {
        $summaryRow = $this->buildRow(array('label' => 'mytest'));
        $this->table->addSummaryRow($summaryRow);

        $this->table->filter($this->filter, array('mysegment'));

        $this->assertFalse($summaryRow->getMetadata('segment'));
    }

    public function testFilterIfTwoSegmentsAreGivenShouldOnlyGenerateAFilterForLabelsHavingThatManyExplodedParts()
    {
        // must result in 2 exploded parts for city and region
        $this->table->filter($this->filter, array(array('city', 'region'), $delimiter = ' '));

        $segmentValues = $this->table->getRowsMetadata('segment');
        $expected = array(
            false,
            false,
            'city==search;region==test',
            'city==keyword;region==t%2Fest',
            false,
            false,
            false,
            false);
        $this->assertSame($expected, $segmentValues);
    }

    public function testFilterIfMultipleSegmentsAreGivenShouldOnlyGenerateAFilterForLabelsHavingThatManyExplodedParts()
    {
        // must result in 3 exploded parts city, region and country
        $this->table->filter($this->filter, array(array('city', 'region', 'country'), $delimiter = ' '));

        $segmentValues = $this->table->getRowsMetadata('segment');
        $expected = array(
            false,
            false,
            false,
            false,
            false,
            'city==play;region==A;country==movie',
            false,
            false);
        $this->assertSame($expected, $segmentValues);
    }

    public function testFilterIfMultipleSegmentsAreGivenIfShouldBePossibleToIgnorePartsByUsingAnEmptyStringAsSegmentName()
    {
        // must result in 3 exploded parts city, region and country
        $this->table->filter($this->filter, array(array('city', '', 'country'), $delimiter = ' '));

        $segmentValues = $this->table->getRowsMetadata('segment');
        $expected = array(
            false,
            false,
            false,
            false,
            false,
            'city==play;country==movie',
            false,
            false);
        $this->assertSame($expected, $segmentValues);
    }

    public function testFilterIfMultipleSegmentsAreGivenShouldIgnoreASummaryRow()
    {
        $summaryRow = $this->buildRow(array('label' => 'part1 part2'));
        $this->table->addSummaryRow($summaryRow);

        $this->table->filter($this->filter, array(array('seg1', 'seg2'), $delimiter = ' '));

        $this->assertFalse($summaryRow->getMetadata('segment'));
    }
}
