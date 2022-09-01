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
use Piwik\Plugins\DevicePlugins\Reports\GetPlugin;
use Piwik\Plugins\UserCountry\Reports\GetCity;
use Piwik\Plugins\VisitsSummary\Reports\Get;

/**
 * @group AddSegmentBySegmentValueTest
 * @group DataTable
 * @group Filter
 * @group Core
 */
class AddSegmentBySegmentValueTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'AddSegmentBySegmentValue';

    /**
     * @var DataTable
     */
    private $table;

    private $report;

    public function setUp(): void
    {
        parent::setUp();

        $this->report = new GetCity();
        $this->table = new DataTable();
        $this->addRowWithMetadata(array('test' => '1'));
        $this->addRowWithMetadata(array('test' => '2', 'segmentValue' => 'teeest'));
        $this->addRowWithMetadata(array('test' => '3', 'segmentValue' => 'existing', 'segment' => 'city==mytest'));
        $this->addRowWithMetadata(array('test' => '1', 'segmentValue' => 'test/test2.r'));
        $this->addRowWithMetadata(array('test' => '4'));
    }

    private function addRowWithMetadata($metadata)
    {
        $row = new Row(array(Row::COLUMNS => array('label' => 'val1')));
        foreach ($metadata as $name => $value) {
            $row->setMetadata($name, $value);
        }
        $this->table->addRow($row);

        return $row;
    }

    public function test_filter_shouldGenerateASegmentIfSegmentValueIsPresent()
    {
        $segmentValue = 'existing';
        $expectedSegment = 'city==existing';
        $this->assertSegmentForSegmentValueAndReport($this->report, $segmentValue, $expectedSegment);
    }

    public function test_filter_shouldUrlEncodeTheValue()
    {
        $segmentValue = 'existing täs/ts';
        $expectedSegment = 'city==existing+t%C3%A4s%2Fts';
        $this->assertSegmentForSegmentValueAndReport($this->report, $segmentValue, $expectedSegment);
    }

    public function test_filter_shouldNotOverwriteAnExistingSegmentValue()
    {
        $row = $this->addRowWithMetadata(array('segmentValue' => 'existing', 'segment' => 'city==mytest'));

        $this->table->filter($this->filter, array($this->report));

        $this->assertSegment('city==mytest', $row);
    }

    public function test_filter_shouldNotGenerateASegment_IfReportHasNoDimension()
    {
        $report = new Get(); // VisitsSummary.get has no dimension
        $this->assertNull($report->getDimension());

        $this->assertSegmentForSegmentValueAndReport($report, $segmentValue = 'existing', false);
    }

    public function test_filter_shouldNotGenerateASegment_IfDimensionHasNoSegmentFilter()
    {
        // plugin report currently has a dimensions but no segments, we have to use another report if later we add segments
        $report = new GetPlugin();
        $this->assertEmpty($report->getDimension()->getSegments());

        $this->assertSegmentForSegmentValueAndReport($report, $segmentValue = 'existing', false);
    }

    public function test_filter_shouldNotFail_IfNoReportGiven()
    {
        $this->assertSegmentForSegmentValueAndReport($report = null, $segmentValue = 'existing', false);
    }

    public function test_filter_shouldNotFail_IfDataTableHasNoRows()
    {
        $table = new DataTable();
        $table->filter($this->filter, array($this->report));
        $this->assertSame(0, $table->getRowsCount());
    }

    private function assertSegmentForSegmentValueAndReport($report, $segmentValue, $expectedSegment)
    {
        $row = $this->addRowWithMetadata(array('segmentValue' => $segmentValue));

        $this->table->filter($this->filter, array($report));

        $this->assertSegment($expectedSegment, $row);
    }

    private function assertSegment($expected, Row $row)
    {
        $segment = $row->getMetadata('segment');
        $this->assertSame($expected, $segment);
    }
}
