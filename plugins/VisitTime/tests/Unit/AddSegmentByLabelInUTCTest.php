<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitTime\tests\Unit;

use Piwik\DataTable\Row;
use Piwik\DataTable;

/**
 * @group VisitTime
 * @group AddSegmentByLabelInUTCTest
 * @group Plugins
 */
class AddSegmentByLabelInUTCTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'Piwik\Plugins\VisitTime\DataTable\Filter\AddSegmentByLabelInUTC';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        $this->table = new DataTable();
        $this->addRow(array('label' => '0'));
        $this->addRow(array('label' => '1'));
        $this->addRow(array('label' => '2'));
        $this->addRow(array('label' => '12'));
        $this->addRow(array('label' => '13'));
        $this->addRow(array('label' => '14'));
        $this->addRow(array('label' => '20'));
    }

    private function addRow($columns)
    {
        $this->table->addRow($this->buildRow($columns));
    }

    private function buildRow($columns)
    {
        return new Row(array(Row::COLUMNS => $columns));
    }

    public function test_filter_shouldNotChangeHoursIfTimezoneIsUTCAlready()
    {
        $this->table->filter($this->filter, array('UTC', 'day', 'today'));

        $this->assertSegmentValues(array('0', '1', '2', '12', '13', '14', '20'));
    }

    public function test_filter_shouldConvertHoursFromTimezoneIntoUTC_Minus1()
    {
        $this->table->filter($this->filter, array('UTC-1', 'day', 'today'));
        $this->assertSegmentValues(array('1', '2', '3', '13', '14', '15', '21'));
    }

    public function test_filter_shouldConvertHoursFromTimezoneIntoUTC_Plus1()
    {
        $this->table->filter($this->filter, array('UTC+1', 'day', 'today'));
        $this->assertSegmentValuesInUTCplus1();
    }

    public function test_filter_shouldHandleRangePeriod_Plus1()
    {
        $this->table->filter($this->filter, array('UTC+1', 'range', '2015-02-02,2015-02-14'));
        $this->assertSegmentValuesInUTCplus1();
    }

    public function test_filter_shouldHandleRangePeriodWithLast7_Plus1()
    {
        $this->table->filter($this->filter, array('UTC+1', 'range', 'last7'));
        $this->assertSegmentValuesInUTCplus1();
    }

    public function test_filter_shouldHandleDayPeriodWithRange_Plus1()
    {
        $this->table->filter($this->filter, array('UTC+1', 'day', '2015-02-02,2015-02-14'));
        $this->assertSegmentValuesInUTCplus1();
    }

    public function test_filter_shouldHandleWeekWithLast7_Plus1()
    {
        $this->table->filter($this->filter, array('UTC+1', 'day', 'last7'));
        $this->assertSegmentValuesInUTCplus1();
    }

    public function test_filter_shouldIgnoreSummaryRow()
    {
        $row = $this->buildRow(array('label' => 'other'));
        $this->table->addSummaryRow($row);
        $this->table->filter($this->filter, array('UTC', 'day', 'today'));

        $this->assertFalse($row->getMetadata('segmentValue'));
    }

    private function assertSegmentValuesInUTCplus1()
    {
        $this->assertSegmentValues(array('23', '0', '1', '11', '12', '13', '19'));
    }

    private function assertSegmentValues($expectedSegmentValues)
    {
        $segmentValues = $this->table->getRowsMetadata('segmentValue');
        $this->assertSame($expectedSegmentValues, $segmentValues);
    }
}
