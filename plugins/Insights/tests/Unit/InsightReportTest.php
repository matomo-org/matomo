<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Unit;

use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Plugins\Insights\InsightReport;

/**
 * @group Insights
 * @group InsightReportTest
 * @group Unit
 * @group Core
 */
class InsightReportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InsightReport
     */
    private $insightReport;

    /**
     * @var DataTable
     */
    private $currentTable;

    /**
     * @var DataTable
     */
    private $pastTable;

    /**
     * val11   mov    5  170   +165   +3400%
     * val7    new    0  134   +134    +100%
     * val3    new    0   90    +90    +100%
     * val10   mov    0   89    +89    +100%
     * val2    new    0   70    +70    +100%
     * val1    mov  102  120    +18     +18%
     * val12   mov    5   14     +9    +180%
     * val5    new    0    0      0       0%
     * val109  dis    0    0      0       0%
     * val6    mov  180    0   -180    -100%
     * val107  dis  150    0   -150    -100%
     * val9    mov   72    7    -65     -90%
     * val8    mov  140  100    -40     -40%
     * val102  dis   29    0    -29    -100%
     * val4    mov  120   99    -21     -19%
     */

    // TODO use data providers
    public function setUp(): void
    {
        $this->currentTable = new DataTable();
        $this->currentTable->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', 'nb_visits' => 120)),
            array(Row::COLUMNS => array('label' => 'val2', 'nb_visits' => 70)),
            array(Row::COLUMNS => array('label' => 'val3', 'nb_visits' => 90)),
            array(Row::COLUMNS => array('label' => 'val4', 'nb_visits' => 99)),
            array(Row::COLUMNS => array('label' => 'val5', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val6', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val7', 'nb_visits' => 134)),
            array(Row::COLUMNS => array('label' => 'val8', 'nb_visits' => 100)),
            array(Row::COLUMNS => array('label' => 'val9', 'nb_visits' => 7)),
            array(Row::COLUMNS => array('label' => 'val10', 'nb_visits' => 89)),
            array(Row::COLUMNS => array('label' => 'val11', 'nb_visits' => 170)),
            array(Row::COLUMNS => array('label' => 'val12', 'nb_visits' => 14))
        ));

        $this->pastTable = new DataTable();
        $this->pastTable->addRowsFromArray(array(
            array(Row::COLUMNS => array('label' => 'val1', 'nb_visits' => 102)),
            array(Row::COLUMNS => array('label' => 'val102', 'nb_visits' => 29)),
            array(Row::COLUMNS => array('label' => 'val4', 'nb_visits' => 120)),
            array(Row::COLUMNS => array('label' => 'val6', 'nb_visits' => 180)),
            array(Row::COLUMNS => array('label' => 'val109', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val8', 'nb_visits' => 140)),
            array(Row::COLUMNS => array('label' => 'val9', 'nb_visits' => 72)),
            array(Row::COLUMNS => array('label' => 'val107', 'nb_visits' => 150)),
            array(Row::COLUMNS => array('label' => 'val10', 'nb_visits' => 0)),
            array(Row::COLUMNS => array('label' => 'val11', 'nb_visits' => 5)),
            array(Row::COLUMNS => array('label' => 'val12', 'nb_visits' => 5))
        ));

        $this->insightReport = new InsightReport();
    }

    /**
     * @dataProvider provideOrderTestData
     */
    public function test_generateInsight_Order($orderBy, $expectedOrder)
    {
        $report = $this->generateInsight(2, 2, 2, 17, -17, $orderBy);

        $this->assertOrder($report, $expectedOrder);
    }

    public function provideOrderTestData()
    {
        return array(
            array(InsightReport::ORDER_BY_IMPORTANCE, array('val6', 'val11', 'val107', 'val7', 'val3', 'val10', 'val2', 'val9', 'val8', 'val102', 'val4', 'val1', 'val12')),
            array(InsightReport::ORDER_BY_RELATIVE, array('val11', 'val12', 'val7', 'val3', 'val10', 'val2', 'val1', 'val6', 'val107', 'val102', 'val9', 'val8', 'val4')),
            array(InsightReport::ORDER_BY_ABSOLUTE, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102', 'val4')),
        );
    }

    public function test_generateInsight_Order_ShouldThrowException_IfInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported orderBy');

        $this->generateInsight(2, 2, 2, 17, -17, 'InvalidOrDeRbY');
    }

    /**
     * @dataProvider provideMinGrowthTestData
     */
    public function test_generateInsight_MinGrowth($minGrowthPositive, $minGrowthNegative, $expectedOrder)
    {
        $report = $this->generateInsight(2, 2, 2, $minGrowthPositive, $minGrowthNegative, InsightReport::ORDER_BY_ABSOLUTE);
        $this->assertOrder($report, $expectedOrder);
    }

    public function provideMinGrowthTestData()
    {
        return array(
            array(4000, -4000, array()),
            array(1000, -1000, array('val11')),
            array(120, -120, array('val11', 'val12')),
            array(80, -80, array('val11', 'val7', 'val3', 'val10', 'val2', 'val12', 'val6', 'val107', 'val9', 'val102')),
            array(19, -19, array('val11', 'val7', 'val3', 'val10', 'val2', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102')),
            array(17, -17, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102', 'val4')),
            array(17, -80, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12', 'val6', 'val107', 'val9', 'val102')),
            array(17, -4000, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12')),
            array(4000, -17, array('val6', 'val107', 'val9', 'val8', 'val102', 'val4')),
        );
    }

    /**
     * @dataProvider provideLimitTestData
     */
    public function test_generateInsight_Limit($limitIncrease, $limitDecrease, $expectedOrder)
    {
        $report = $this->generateInsight(2, 2, 2, 20, -20, InsightReport::ORDER_BY_ABSOLUTE, $limitIncrease, $limitDecrease);

        $this->assertOrder($report, $expectedOrder);
    }

    public function provideLimitTestData()
    {
        return array(
            array(1, 1, array('val11', 'val6')),
            array(1, 0, array('val11')), // only increase
            array(0, 1, array('val6')),  // only decrease
            array(0, 0, array()),  // neither increase nor decrease
        );
    }

    public function test_generateInsight_NoMovers()
    {
        $report = $this->generateInsight(-1, 2, 2, 20, -20);

        $this->assertOrder($report, array('val7', 'val3', 'val2', 'val107', 'val102'));
    }

    /**
     * @dataProvider provideMinImpactMoversTestData
     */
    public function test_generateInsight_MinImpactMovers($minMoversPercent, $expectedOrder)
    {
        $report = $this->generateInsight($minMoversPercent, -1, -1, 17, -17);

        $this->assertOrder($report, $expectedOrder);
    }

    public function provideMinImpactMoversTestData()
    {
        return array(
            array(2, array('val11', 'val10', 'val1', 'val12', 'val6', 'val9', 'val8', 'val4')),
            array(20, array('val11', 'val10', 'val6', 'val9', 'val8')),
            array(80, array('val11', 'val6')),
            array(10000, array())
        );
    }

    public function test_generateInsight_NoNew()
    {
        $report = $this->generateInsight(2, -1, 2, 17, -17);

        $this->assertOrder($report, array('val11', 'val10', 'val1', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102', 'val4'));
    }

    /**
     * @dataProvider provideMinImpactNewTestData
     */
    public function test_generateInsight_MinImpactNew($minNewPercent, $expectedOrder)
    {
        $report = $this->generateInsight(-1, $minNewPercent, -1, 17, -17);

        $this->assertOrder($report, $expectedOrder);
    }

    public function provideMinImpactNewTestData()
    {
        return array(
            array(2, array('val7', 'val3', 'val2')),
            array(22, array('val7', 'val3', 'val2')),
            array(36, array('val7', 'val3')),
            array(66, array('val7')),
            array(10000, array())
        );
    }

    public function test_generateInsight_NoDisappeared()
    {
        $report = $this->generateInsight(2, 2, -1, 17, -17);

        $this->assertOrder($report, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12', 'val6', 'val9', 'val8', 'val4'));
    }

    /**
     * @dataProvider provideMinImpactDisappearedData
     */
    public function test_generateInsight_MinDisappeared($minDisappearedPercent, $expectedOrder)
    {
        $report = $this->generateInsight(-1, -1, $minDisappearedPercent, 17, -17);
        $this->assertOrder($report, $expectedOrder);
    }

    public function provideMinImpactDisappearedData()
    {
        return array(
            array(2, array('val107', 'val102')),
            array(14, array('val107', 'val102')),
            array(15, array('val107')),
            array(75, array('val107')),
            array(76, array()),
            array(10000, array())
        );
    }

    public function test_generateInsights_ShouldSetCorrectMetadata()
    {
        $report = $this->generateInsight(2, 4, 8, 17, -21);
        $metadata = $report->getAllTableMetadata();

        $expectedMetadata = array(
            'reportName' => 'TestReport',
            'metricName' => 'Visits',
            'date'       => '2012-12-12',
            'lastDate'   => '2012-12-11',
            'period'     => 'day',
            'orderBy'    => 'absolute',
            'metric'     => 'nb_visits',
            'totalValue' => 200,
            'minChangeMovers' => 4,
            'minIncreaseNew'  => 8,
            'minDecreaseDisappeared' => 16,
            'minGrowthPercentPositive' => 17,
            'minGrowthPercentNegative' => -21,
            'minMoversPercent' => 2,
            'minNewPercent' => 4,
            'minDisappearedPercent' => 8,
        );

        self::assertIsArray($metadata['report']);
        $this->assertEquals('TestReport', $metadata['report']['name']);
        unset($metadata['report']);
        unset($metadata['totals']);

        $this->assertEquals($expectedMetadata, $metadata);
    }

    public function test_markMoversAndShakers()
    {
        $report = $this->generateInsight(2, 2, 2, 5, -5);
        $this->insightReport->markMoversAndShakers($report, $this->currentTable, $this->pastTable, 160, 100);

        // increase by 60% --> minGrowth 80%
        $movers = array('val11', 'val12', 'val7', 'val9', 'val3', 'val10', 'val2', 'val6', 'val107', 'val102');
        $nonMovers = array('val1', 'val8', 'val4');

        $this->assertMoversAndShakers($report, $movers, $nonMovers);
    }

    public function test_markMoversAndShakers_shouldAddMetadata()
    {
        $report = $this->generateInsight(2, 2, 2, 5, -5);
        $this->insightReport->markMoversAndShakers($report, $this->currentTable, $this->pastTable, 200, 100);

        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(100, $metadata['lastTotalValue']);
        $this->assertEquals(200, $metadata['totalValue']);
        $this->assertEquals(100, $metadata['evolutionDifference']);
        $this->assertEquals(100, $metadata['evolutionTotal']);
    }

    public function test_generateMoversAndShakers()
    {
        // increase by 60% --> minGrowth 80%
        $report = $this->generateMoverAndShaker(160, 100);
        $this->assertOrder($report, array('val11', 'val7', 'val3', 'val10', 'val2', 'val12', 'val6', 'val107', 'val9', 'val102'));

        // increase by 1600% --> minGrowth 1640%
        $report = $this->generateMoverAndShaker(1600, 100);
        $this->assertOrder($report, array('val11', 'val6', 'val107', 'val9'));
    }

    private function assertMoversAndShakers(DataTable $report, $movers, $nonMovers)
    {
        foreach ($movers as $mover) {
            $row = $report->getRowFromLabel($mover);
            if (!$row) {
                $this->fail("$mover is not a valid label");
                continue;
            }
            $this->assertTrue($row->getColumn('isMoverAndShaker'), "$mover is not a mover but should be");
        }

        foreach ($nonMovers as $nonMover) {
            $row = $report->getRowFromLabel($nonMover);
            if (!$row) {
                $this->fail("$nonMover is not a valid label");
                continue;
            }
            $this->assertFalse($row->getColumn('isMoverAndShaker'), "$nonMover is a mover but should be not");
        }
    }

    public function test_generateMoversAndShakers_Metadata()
    {
        $report   = $this->generateMoverAndShaker(150, 50);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(50, $metadata['lastTotalValue']);
        $this->assertEquals(150, $metadata['totalValue']);
        $this->assertEquals(100, $metadata['evolutionDifference']);
        $this->assertEquals(200, $metadata['evolutionTotal']);

        $report   = $this->generateMoverAndShaker(75, 50);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(50, $metadata['lastTotalValue']);
        $this->assertEquals(75, $metadata['totalValue']);
        $this->assertEquals(25, $metadata['evolutionDifference']);
        $this->assertEquals(50, $metadata['evolutionTotal']);

        $report   = $this->generateMoverAndShaker(25, 50);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(50, $metadata['lastTotalValue']);
        $this->assertEquals(25, $metadata['totalValue']);
        $this->assertEquals(-25, $metadata['evolutionDifference']);
        $this->assertEquals(-50, $metadata['evolutionTotal']);
    }

    public function test_generateMoversAndShakers_ParameterCalculation()
    {
        $report   = $this->generateMoverAndShaker(3000, 50); // evolution of 5900%
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(6380, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-70, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(1, $metadata['minMoversPercent']);
        $this->assertEquals(10, $metadata['minNewPercent']);
        $this->assertEquals(8, $metadata['minDisappearedPercent']);

        $report   = $this->generateMoverAndShaker(300, 100);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(240, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-70, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(1, $metadata['minMoversPercent']);
        $this->assertEquals(6, $metadata['minNewPercent']);
        $this->assertEquals(8, $metadata['minDisappearedPercent']);

        $report   = $this->generateMoverAndShaker(225, 150);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(70, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-70, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(1, $metadata['minMoversPercent']);
        $this->assertEquals(5, $metadata['minNewPercent']);
        $this->assertEquals(7, $metadata['minDisappearedPercent']);

        $report   = $this->generateMoverAndShaker(300, 600);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(70, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-70, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(1, $metadata['minMoversPercent']);
        $this->assertEquals(5, $metadata['minNewPercent']);
        $this->assertEquals(7, $metadata['minDisappearedPercent']);

        // make sure to force a change of at least 2 visits in all rows if total is soooo low
        $report   = $this->generateMoverAndShaker(25, 50);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(70, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-70, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(8, $metadata['minMoversPercent']);
        $this->assertEquals(8, $metadata['minNewPercent']);
        $this->assertEquals(8, $metadata['minDisappearedPercent']);

        // make sure to force a change of at least 2 visits
        $report   = $this->generateMoverAndShaker(75, 150);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(70, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-70, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(3, $metadata['minMoversPercent']);
        $this->assertEquals(5, $metadata['minNewPercent']);
        $this->assertEquals(7, $metadata['minDisappearedPercent']);

        // make sure no division by zero issue
        $report   = $this->generateMoverAndShaker(0, 150);
        $metadata = $report->getAllTableMetadata();

        $this->assertEquals(120, $metadata['minGrowthPercentPositive']);
        $this->assertEquals(-120, $metadata['minGrowthPercentNegative']);
        $this->assertEquals(1, $metadata['minMoversPercent']);
        $this->assertEquals(5, $metadata['minNewPercent']);
        $this->assertEquals(7, $metadata['minDisappearedPercent']);
    }

    private function generateMoverAndShaker($totalValue, $lastTotalValue, $orderBy = null, $limitIncreaser = 99, $limitDecreaser = 99)
    {
        if (is_null($orderBy)) {
            $orderBy = InsightReport::ORDER_BY_ABSOLUTE;
        }

        $reportMetadata = array('name' => 'TestReport',  'metrics' => array('nb_visits' => 'Visits'));

        $report = $this->insightReport->generateMoverAndShaker(
            $reportMetadata,
            'day',
            '2012-12-12',
            '2012-12-11',
            'nb_visits',
            $this->currentTable,
            $this->pastTable,
            $totalValue,
            $lastTotalValue,
            $orderBy,
            $limitIncreaser,
            $limitDecreaser
        );

        return $report;
    }

    private function generateInsight($minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercentPositive, $minGrowthPercentNegative, $orderBy = null, $limitIncreaser = 99, $limitDecreaser = 99)
    {
        if (is_null($orderBy)) {
            $orderBy = InsightReport::ORDER_BY_ABSOLUTE;
        }

        $reportMetadata = array('name' => 'TestReport',  'metrics' => array('nb_visits' => 'Visits'));

        $report = $this->insightReport->generateInsight(
            $reportMetadata,
            'day',
            '2012-12-12',
            '2012-12-11',
            'nb_visits',
            $this->currentTable,
            $this->pastTable,
            $totalValue = 200,
            $minMoversPercent,
            $minNewPercent,
            $minDisappearedPercent,
            $minGrowthPercentPositive,
            $minGrowthPercentNegative,
            $orderBy,
            $limitIncreaser,
            $limitDecreaser
        );

        return $report;
    }

    private function assertOrder(DataTable $table, $expectedOrder)
    {
        $this->assertEquals($expectedOrder, $table->getColumn('label'));
        $this->assertEquals(count($expectedOrder), $table->getRowsCount());
    }
}
