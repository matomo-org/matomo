<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\Insights\InsightReport;

/**
 * @group Insights
 * @group InsightReportTest
 * @group Unit
 * @group Core
 */
class InsightReportTest extends \PHPUnit_Framework_TestCase
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
    public function setUp()
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

    public function testImportanceOrder()
    {
        $report = $this->generateInsight(2, 2, 2, 17, InsightReport::ORDER_BY_IMPORTANCE, 99, 99);
        $this->assertOrder($report, array('val6', 'val11', 'val107', 'val7', 'val3', 'val10', 'val2', 'val9', 'val8', 'val102', 'val4', 'val1', 'val12'));
    }

    public function testRelativeOrder()
    {
        $report = $this->generateInsight(2, 2, 2, 17, InsightReport::ORDER_BY_RELATIVE, 99, 99);
        $this->assertOrder($report, array('val11', 'val12', 'val7', 'val3', 'val10', 'val2', 'val1', 'val6', 'val107', 'val102', 'val9', 'val8', 'val4'));
    }

    public function testMinGrowth()
    {
        $report = $this->generateInsight(2, 2, 2, 4000, 'absolute', 99, 99);
        $this->assertOrder($report, array());

        $report = $this->generateInsight(2, 2, 2, 1000, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11'));

        $report = $this->generateInsight(2, 2, 2, 120, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val12'));

        $report = $this->generateInsight(2, 2, 2, 80, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val7', 'val3', 'val10', 'val2', 'val12', 'val6', 'val107', 'val9', 'val102'));

        $report = $this->generateInsight(2, 2, 2, 19, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val7', 'val3', 'val10', 'val2', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102'));

        $report = $this->generateInsight(2, 2, 2, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102', 'val4'));
    }

    public function testLimit()
    {
        $report = $this->generateInsight(2, 2, 2, 20, 'absolute', 1, 1);

        $this->assertOrder($report, array('val11', 'val6'));
    }

    public function testLimitOnlyIncrease()
    {
        $report = $this->generateInsight(2, 2, 2, 20, 'absolute', 1, 0);

        $this->assertOrder($report, array('val11'));
    }

    public function testLimitOnlyDecrease()
    {
        $report = $this->generateInsight(2, 2, 2, 20, 'absolute', 0, 1);

        $this->assertOrder($report, array('val6'));
    }

    public function testLimitOnlyNone()
    {
        $report = $this->generateInsight(2, 2, 2, 20, 'absolute', 0, 0);

        $this->assertOrder($report, array());
    }

    public function testNoMovers()
    {
        $report = $this->generateInsight(-1, 2, 2, 20, 'absolute', 99, 99);

        $this->assertOrder($report, array('val7', 'val3', 'val2', 'val107', 'val102'));
    }

    public function testMinImpactMovers()
    {
        $report = $this->generateInsight(2, -1, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val10', 'val1', 'val12', 'val6', 'val9', 'val8', 'val4'));

        $report = $this->generateInsight(20, -1, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val10', 'val6', 'val9', 'val8'));

        $report = $this->generateInsight(80, -1, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val6'));

        $report = $this->generateInsight(10000, -1, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array());
    }

    public function testNoNew()
    {
        $report = $this->generateInsight(2, -1, 2, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val10', 'val1', 'val12', 'val6', 'val107', 'val9', 'val8', 'val102', 'val4'));
    }

    public function testMinImpactNew()
    {
        $report = $this->generateInsight(-1, 2, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val7', 'val3', 'val2'));

        $report = $this->generateInsight(-1, 22, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val7', 'val3', 'val2'));

        $report = $this->generateInsight(-1, 36, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val7', 'val3'));

        $report = $this->generateInsight(-1, 66, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val7'));

        $report = $this->generateInsight(-1, 10000, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array());
    }

    public function testNoDisappeared()
    {
        $report = $this->generateInsight(2, 2, -1, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val11', 'val7', 'val3', 'val10', 'val2', 'val1', 'val12', 'val6', 'val9', 'val8', 'val4'));
    }

    public function testMinDisappeared()
    {
        $report = $this->generateInsight(-1, -1, 2, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val107', 'val102'));

        $report = $this->generateInsight(-1, -1, 14, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val107', 'val102'));

        $report = $this->generateInsight(-1, -1, 15, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val107'));

        $report = $this->generateInsight(-1, -1, 75, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array('val107'));

        $report = $this->generateInsight(-1, -1, 76, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array());

        $report = $this->generateInsight(-1, -1, 10000, 17, 'absolute', 99, 99);
        $this->assertOrder($report, array());
    }

    private function generateInsight($minVisitsMoversPercent, $minVisitsNewPercent, $minVisitsDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser)
    {
        $reportMetadata = array('name' => 'TestReport',  'metrics' => array('nb_visits' => 'Visits'));
        $report = $this->insightReport->generateInsight(
            $reportMetadata, 'day', 'today', 'yesterday', 'nb_visits', $this->currentTable, $this->pastTable, $totalValue = 200,
            $minVisitsMoversPercent, $minVisitsNewPercent, $minVisitsDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser);

        return $report;
    }

    private function assertOrder(DataTable $table, $expectedOrder)
    {
        $this->assertEquals($expectedOrder, $table->getColumn('label'));
        $this->assertEquals(count($expectedOrder), $table->getRowsCount());
    }
}
