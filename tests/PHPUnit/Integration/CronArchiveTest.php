<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\API;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeLogger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API as SegmentAPI;

/**
 * @group Archiver
 * @group CronArchive
 */
class CronArchiveTest extends IntegrationTestCase
{
    public function test_getColumnNamesFromTable()
    {
        Fixture::createWebsite('2014-12-12 00:01:02');
        Fixture::createWebsite('2014-12-12 00:01:02');

        $ar = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');
        $ar->rememberToInvalidateArchivedReportsLater(1, Date::factory('2014-04-05'));
        $ar->rememberToInvalidateArchivedReportsLater(2, Date::factory('2014-04-05'));
        $ar->rememberToInvalidateArchivedReportsLater(2, Date::factory('2014-04-06'));

        $api = API::getInstance();

        $cronarchive = new TestCronArchive(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php');
        $cronarchive->setApiToInvalidateArchivedReport($api);
        $cronarchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain();

        /**
         * should look like this but the result is random
         *  array(
        array(array(1,2), '2014-04-05'),
        array(array(2), '2014-04-06')
        )
         */
        $invalidatedReports = $api->getInvalidatedReports();
        $this->assertCount(2, $invalidatedReports);
        sort($invalidatedReports[0][0]);
        sort($invalidatedReports[1][0]);
        usort($invalidatedReports, function ($a, $b) {
            return strcmp($a[1], $b[1]);
        });

        $this->assertSame(array(1,2), $invalidatedReports[0][0]);
        $this->assertSame('2014-04-05', $invalidatedReports[0][1]);

        $this->assertSame(array(2), $invalidatedReports[1][0]);
        $this->assertSame('2014-04-06', $invalidatedReports[1][1]);

    }

    public function test_setSegmentsToForceFromSegmentIds_CorrectlyGetsSegmentDefinitions_FromSegmentIds()
    {
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burb', 'actions>=3', 1, true, true);
        SegmentAPI::getInstance()->add('sub', 'actions>=4', 1, true, true);

        $cronarchive = new TestCronArchive(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php');
        $cronarchive->setSegmentsToForceFromSegmentIds(array(2, 4));

        $expectedSegments = array('actions>=2', 'actions>=4');
        $this->assertEquals($expectedSegments, array_values($cronarchive->segmentsToForce));
    }

    public function test_wasSegmentCreatedRecently()
    {
        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);

        $segments = new Model();
        $segments->updateSegment($id, array('ts_created' => Date::now()->subHour(30)->getDatetime()));

        $allSegments = $segments->getSegmentsToAutoArchive(1);

        $cronarchive = new TestCronArchive(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php');
        $this->assertTrue($cronarchive->wasSegmentChangedRecently('actions>=1', $allSegments));

        // created 30 hours ago...
        $this->assertFalse($cronarchive->wasSegmentChangedRecently('actions>=2', $allSegments));

        // not configured segment
        $this->assertFalse($cronarchive->wasSegmentChangedRecently('actions>=999', $allSegments));
    }

    public function test_skipSegmentsToday()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => serialize(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);

        $segments = new Model();
        $segments->updateSegment($id, array('ts_created' => Date::now()->subHour(30)->getDatetime()));

        $logger = new FakeLogger();

        $archiver = new CronArchive(null, $logger);
        $archiver->skipSegmentsToday = true;
        $archiver->shouldArchiveAllSites = true;
        $archiver->shouldArchiveAllPeriodsSince = true;
        $archiver->init();
        $archiver->run();

        self::assertStringContainsString('Will skip segments archiving for today unless they were created recently', $logger->output);
        self::assertStringContainsString('Segment "actions>=1" was created or changed recently and will therefore archive today', $logger->output);
        self::assertStringNotContainsString('Segment "actions>=2" was created recently', $logger->output);
    }

    public function test_output()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => serialize(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burr', 'actions>=4', 1, true, true);

        $logger = new FakeLogger();

        $archiver = new CronArchive(null, $logger);
        $archiver->shouldArchiveAllSites = true;
        $archiver->shouldArchiveAllPeriodsSince = true;
        $archiver->segmentsToForce = array('actions>=2;browserCode=FF', 'actions>=2');
        $archiver->init();
        $archiver->run();

        $expected = <<<LOG
---------------------------
INIT
Running Matomo 4.0.0-b1 as Super User
---------------------------
NOTES
- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Matomo UI > Settings > General Settings.
  See the doc at: https://matomo.org/docs/setup-auto-archiving/
- Async process archiving supported, using CliMulti.
- Reports for today will be processed at most every 900 seconds. You can change this value in Matomo UI > Settings > General Settings.
- Will process all 0 websites
- Limiting segment archiving to following segments:
  * actions>=2;browserCode=FF
  * actions>=2
---------------------------
START
Starting Matomo reports archiving...
Checking for queued invalidations...
  Today archive can be skipped due to no visits, skipping invalidation...
  Segment "actions>=2" was created or changed recently and will therefore archive today (for site ID = 1)
  Segment "actions>=4" was created or changed recently and will therefore archive today (for site ID = 1)
Done invalidating
Found no visits for site ID = 1, day (2020-03-27,2020-03-27), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-26,2020-03-26), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-25,2020-03-25), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-24,2020-03-24), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-23,2020-03-23), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-16,2020-03-16), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-09,2020-03-09), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-02,2020-03-02), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-01,2020-03-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-27,2020-03-27), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-26,2020-03-26), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-25,2020-03-25), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-24,2020-03-24), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-23,2020-03-23), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-16,2020-03-16), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-09,2020-03-09), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-02,2020-03-02), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-01,2020-03-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-23,2020-03-29), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-16,2020-03-22), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-09,2020-03-15), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-02,2020-03-08), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-23,2020-03-29), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-16,2020-03-22), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-09,2020-03-15), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-02,2020-03-08), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-03-01,2020-03-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-03-01,2020-03-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-02-01,2020-02-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-02-01,2020-02-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-02-24,2020-03-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-02-24,2020-03-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-02-01,2020-02-29), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-02-01,2020-02-29), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-01-01,2020-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-01-01,2020-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-01-27,2020-02-02), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-01-27,2020-02-02), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-01-01,2020-01-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-01-01,2020-01-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2020-01-01,2020-12-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2020-01-01,2020-12-31), site is using the tracker so skipping archiving...
No next invalidated archive.
Found no visits for site ID = 1, week (2019-12-30,2020-01-05), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2019-12-30,2020-01-05), site is using the tracker so skipping archiving...
No next invalidated archive.

LOG;
        $this->assertStringMatchesFormat($expected, $logger->output);
    }

    public function test_shouldNotStopProcessingWhenOneSiteIsInvalid()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => serialize(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');

        $logger = new FakeLogger();

        $archiver = new CronArchive(null, $logger);
        $archiver->shouldArchiveSpecifiedSites = array(99999, 1);
        $archiver->init();
        $archiver->run();

        $expected = <<<LOG
- Will process 2 websites (--force-idsites)
---------------------------
START
Starting Matomo reports archiving...
Checking for queued invalidations...
  Today archive can be skipped due to no visits, skipping invalidation...
Done invalidating
No next invalidated archive.
LOG;

        self::assertStringContainsString($expected, $logger->output);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\CliMulti' => \DI\object('Piwik\Tests\Framework\Mock\FakeCliMulti')
        );
    }

    private function insertArchiveData($archiveRows)
    {
        foreach ($archiveRows as $row) {
            $table = ArchiveTableCreator::getNumericTable(Date::factory($row['date1']));

            $tsArchived = isset($row['ts_archived']) ? $row['ts_archived'] : Date::now()->getDatetime();
            Db::query("INSERT INTO `$table` (idarchive, idsite, period, date1, date2, `name`, `value`, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$row['idarchive'], $row['idsite'], $row['period'], $row['date1'], $row['date2'], $row['name'], $row['value'], $tsArchived]);
        }
    }
}

class TestCronArchive extends CronArchive
{
    protected function checkPiwikUrlIsValid()
    {
    }

    protected function initPiwikHost($piwikUrl = false)
    {
    }

    public function wasSegmentChangedRecently($definition, $allSegments)
    {
        return parent::wasSegmentChangedRecently($definition, $allSegments);
    }
}
