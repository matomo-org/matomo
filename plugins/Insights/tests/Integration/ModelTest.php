<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Plugins\Insights\Model;
use Piwik\Plugins\Insights\tests\Fixtures\SomeVisitsDifferentPathsOnTwoDays;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Insights
 * @group ModelTest
 * @group Plugins
 */
class ModelTest extends SystemTestCase
{
    /**
     * @var SomeVisitsDifferentPathsOnTwoDays
     */
    public static $fixture = null;

    /**
     * @var Model
     */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = StaticContainer::getContainer()->make('Piwik\Plugins\Insights\Model');
    }

    public function test_requestReport_shouldReturnTheDataTableOfTheReport_AndContainReportTotals()
    {
        $idSite = self::$fixture->idSite;
        $date   = self::$fixture->date1;
        $metric = 'nb_visits';

        $table  = $this->model->requestReport($idSite, 'day', $date, 'Actions_getPageUrls', $metric, false);

        $this->assertEquals(5, $table->getRowsCount());

        $totals = $table->getMetadata('totals');
        $this->assertEquals(50, $totals[$metric]);
    }

    public function test_getReportByUniqueId_shouldReturnReport()
    {
        $report = $this->model->getReportByUniqueId(self::$fixture->idSite, 'Actions_getPageUrls');

        $this->assertEquals('Actions', $report['module']);
        $this->assertEquals('getPageUrls', $report['action']);
    }

    public function test_getLastDate_shouldReturnTheLastDateDependingOnPeriod()
    {
        $date = $this->model->getLastDate('2012-12-12', 'day', 1);
        $this->assertEquals('2012-12-11', $date);

        $date = $this->model->getLastDate('2012-12-12', 'week', 1);
        $this->assertEquals('2012-12-05', $date);

        $date = $this->model->getLastDate('2012-12-03', 'week', 1);
        $this->assertEquals('2012-11-26', $date);

        $date = $this->model->getLastDate('2012-12-02', 'week', 1);
        $this->assertEquals('2012-11-25', $date);

        $date = $this->model->getLastDate('2012-12-12', 'month', 1);
        $this->assertEquals('2012-11-12', $date);

        $date = $this->model->getLastDate('2012-12-01', 'month', 1);
        $this->assertEquals('2012-11-01', $date);
    }

    public function test_getLastDate_shouldReturnTheLastDateDependingOnComparedTo()
    {
        $date = $this->model->getLastDate('2012-12-12', 'day', 1);
        $this->assertEquals('2012-12-11', $date);

        $date = $this->model->getLastDate('2012-12-12', 'day', 2);
        $this->assertEquals('2012-12-10', $date);

        $date = $this->model->getLastDate('2012-12-12', 'day', 7);
        $this->assertEquals('2012-12-05', $date);
    }

    public function test_getMetricTotalValue_shouldReturnTheTotalValueFromMetadata()
    {
        $table = $this->getTableWithTotal('17');

        $total = $this->model->getMetricTotalValue($table, 'nb_visits');

        $this->assertEquals(17, $total);
        self::assertIsInt($total);
    }

    public function test_getMetricTotalValue_shouldReturnZeroIfMetricHasNoTotal()
    {
        $table = new DataTable();
        $table->setMetadata('totals', array('nb_visits' => '17'));

        $total = $this->model->getMetricTotalValue($table, 'unknown_metric');

        $this->assertEquals(0, $total);
    }

    public function test_getLastDate_shouldThrowExceptionIfNotPossibleToGetLastDate()
    {
        $this->expectException(\Exception::class);

        $this->model->getLastDate('last10', 'day', 1);
    }

    public function test_getTotalValue_shouldCalculateTotals()
    {
        $total = $this->model->getTotalValue(self::$fixture->idSite, 'day', self::$fixture->date1, 'nb_visits', false);
        $this->assertEquals(50, $total);

        $total = $this->model->getTotalValue(self::$fixture->idSite, 'day', self::$fixture->date2, 'nb_visits', false);
        $this->assertEquals(59, $total);
    }

    public function test_getTotalValue_shouldCalculateTotalsAndApplySegment()
    {
        $total = $this->model->getTotalValue(self::$fixture->idSite, 'day', self::$fixture->date1, 'nb_visits', 'resolution==1000x1001');
        $this->assertEquals(1, $total);
    }

    public function test_getTotalValue_shouldReturnZero_IfColumnDoesNotExist()
    {
        $total = $this->model->getTotalValue(self::$fixture->idSite, 'day', self::$fixture->date1, 'unknown_ColUmn', false);
        $this->assertEquals(0, $total);
    }

    public function test_getRelevantTotalValue_shouldReturnTotalValue_IfMetricTotalIsHighEnough()
    {
        $table = $this->getTableWithTotal(25);
        $total = $this->model->getRelevantTotalValue($table, 'nb_visits', 50);
        $this->assertEquals(50, $total);
    }

    public function test_getRelevantTotalValue_shouldReturnMetricTotal_IfMetricTotalIsHigherThanTotalValue()
    {
        $table = $this->getTableWithTotal(80);
        $total = $this->model->getRelevantTotalValue($table, 'nb_visits', 50);
        $this->assertEquals(80, $total);
    }

    public function test_getRelevantTotalValue_shouldReturnMetricTotal_IfMetricTotalIsTooLow()
    {
        $table = $this->getTableWithTotal(24);
        $total = $this->model->getRelevantTotalValue($table, 'nb_visits', 50);
        $this->assertEquals(24, $total);

        $table = $this->getTableWithTotal(0);
        $total = $this->model->getRelevantTotalValue($table, 'nb_visits', 50);
        $this->assertEquals(0, $total);
    }

    private function getTableWithTotal($total)
    {
        $table = new DataTable();
        $table->setMetadata('totals', array('nb_visits' => $total));
        return $table;
    }
}

ModelTest::$fixture = new SomeVisitsDifferentPathsOnTwoDays();
