<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\PrivacyManager\tests\System;

use Piwik\API\Request;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReportsPurger;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;

/**
 * @group PrivacyManager
 * @group Core
 * @group PurgeDataTest
 */
class PurgeDataTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass()
    {

    }
    public static function tearDownBeforeClass()
    {

    }

    public function setUp()
    {
        parent::setUpBeforeClass();
    }

    public function tearDown()
    {
        parent::tearDownAfterClass();
    }

    public function test_purgeData_keepAllExceptDay()
    {
        $this->assertHasOneDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');

        $deleteReportsOlderThan = 1;
        $keepBasicMetrics       = true;
        $reportPeriodsToKeep    = array(2,3,4,5);
        $this->purgeData($deleteReportsOlderThan, $reportPeriodsToKeep, $keepBasicMetrics);

        $this->assertHasNoDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');
    }

    public function test_purgeData_keepOnlyDay()
    {
        $this->assertHasOneDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');

        $deleteReportsOlderThan = 1;
        $keepBasicMetrics       = true;
        $reportPeriodsToKeep    = array(1);
        $this->purgeData($deleteReportsOlderThan, $reportPeriodsToKeep, $keepBasicMetrics);

        $this->assertNumVisits(2, 'day');
        $this->assertNumVisits(2, 'week');
        $this->assertHasOneDownload('day');
        $this->assertHasNoDownload('week');
        $this->assertHasNoDownload('month');
        $this->assertHasNoDownload('year');
    }

    public function test_purgeData_shouldNotPurgeAnything_IfDeleteReportsOlderThanIsFarBackInThePast()
    {
        $this->assertHasOneDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');

        $deleteReportsOlderThan = 1000;
        $keepBasicMetrics       = true;
        $reportPeriodsToKeep    = array(1,2,3,4,5);
        $this->purgeData($deleteReportsOlderThan, $reportPeriodsToKeep, $keepBasicMetrics);

        $this->assertHasOneDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');
    }

    public function test_purgeData_shouldPurgeAllPeriodsExceptBasicMetrics_IfNoPeriodToKeepIsGiven()
    {
        $this->assertHasOneDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');

        $deleteReportsOlderThan = 1;
        $keepBasicMetrics       = true;
        $reportPeriodsToKeep    = array();
        $this->purgeData($deleteReportsOlderThan, $reportPeriodsToKeep, $keepBasicMetrics);

        $this->assertNumVisits(2, 'day');
        $this->assertNumVisits(2, 'week');
        $this->assertNumVisits(2, 'month');
        $this->assertNumVisits(2, 'year');
        $this->assertHasNoDownload('day');
        $this->assertHasNoDownload('week');
        $this->assertHasNoDownload('month');
        $this->assertHasNoDownload('year');
    }

    public function test_purgeData_shouldPurgeEverything_IfNoPeriodToKeepIsGivenAndBasicMetricsNotKept()
    {
        $this->assertHasOneDownload('day');
        $this->assertHasOneDownload('week');
        $this->assertHasOneDownload('month');
        $this->assertHasOneDownload('year');

        $deleteReportsOlderThan = 1;
        $keepBasicMetrics       = false;
        $reportPeriodsToKeep    = array();
        $this->purgeData($deleteReportsOlderThan, $reportPeriodsToKeep, $keepBasicMetrics);

        $this->assertNumVisits(0, 'day');
        $this->assertNumVisits(0, 'week');
        $this->assertNumVisits(0, 'month');
        $this->assertNumVisits(0, 'year');
        $this->assertHasNoDownload('day');
        $this->assertHasNoDownload('week');
        $this->assertHasNoDownload('month');
        $this->assertHasNoDownload('year');
    }

    private function assertNumVisits($expectedNumVisits, $period)
    {
        $url = 'method=VisitsSummary.getVisits'
             . '&idSite=' . self::$fixture->idSite
             . '&date=' . self::$fixture->dateTime
             . '&period='. $period
             . '&format=original';
        $api   = new Request($url);
        $table = $api->process();
        $this->assertEquals($expectedNumVisits, $table->getFirstRow()->getColumn('nb_visits'));
    }

    private function assertHasOneDownload($period)
    {
        $api   = new Request($this->getDownloadApiRequestUrl($period));
        $table = $api->process();
        $this->assertEquals(1, $table->getRowsCount(), $period . ' should have one download but has not');
    }

    private function assertHasNoDownload($period)
    {
        $api   = new Request($this->getDownloadApiRequestUrl($period));
        $table = $api->process();
        $this->assertEquals(0, $table->getRowsCount(), $period . ' should not have a download but has one');
    }

    private function getDownloadApiRequestUrl($period)
    {
        return 'method=Actions.getDownloads'
             . '&idSite=' . self::$fixture->idSite
             . '&date=' . self::$fixture->dateTime
             . '&period='. $period
             . '&format=original';
    }

    private function purgeData($deleteReportsOlderThan, $reportPeriodsToKeep, $keepBasicMetrics)
    {
        $metricsToKeep           = PrivacyManager::getAllMetricsToKeep();
        $maxRowsToDeletePerQuery = 100000;
        $keepSegmentReports      = false;

        $purger = new ReportsPurger($deleteReportsOlderThan, $keepBasicMetrics, $reportPeriodsToKeep,
                                    $keepSegmentReports, $metricsToKeep, $maxRowsToDeletePerQuery);
        $purger->purgeData();
    }
}

PurgeDataTest::$fixture = new OneVisitorTwoVisits();
