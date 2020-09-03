<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\API;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeLogger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API as SegmentAPI;
use Piwik\Version;
use Psr\Log\NullLogger;

/**
 * @group Archiver
 * @group CronArchive
 */
class CronArchiveTest extends IntegrationTestCase
{
    public function test_isThereExistingValidPeriod_returnsTrueIfPeriodHasToday_AndExistingArchiveIsNewEnough()
    {
        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('week', '2020-04-05'), new Segment('', [1]));

        $tsArchived = Date::now()->subSeconds(100)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-30'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1,2, '2020-03-30', '2020-04-05', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $actual =$archiver->isThereExistingValidPeriod($params, $isYesterday = false);
        $this->assertTrue($actual);
    }

    public function test_isThereExistingValidPeriod_returnsTrueIfPeriodHasToday_AndExistingArchiveIsNewEnoughAndInvalidated()
    {
        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('week', '2020-04-05'), new Segment('', [1]));

        $tsArchived = Date::now()->subSeconds(100)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-30'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1,2, '2020-03-30', '2020-04-05', 'done', ArchiveWriter::DONE_INVALIDATED, $tsArchived
        ]);

        $actual =$archiver->isThereExistingValidPeriod($params, $isYesterday = false);
        $this->assertTrue($actual);
    }

    public function test_isThereExistingValidPeriod_returnsTrueIfPeriodDoesNotHaveToday_AndExistingArchiveIsOk()
    {
        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('day', '2020-03-05'), new Segment('', [1]));

        $tsArchived = Date::now()->subDay(1)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-05'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, 1, '2020-03-05', '2020-03-05', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $actual =$archiver->isThereExistingValidPeriod($params, $isYesterday = false);
        $this->assertTrue($actual);
    }

    public function test_isThereExistingValidPeriod_returnsFalseIfDayHasChangedAndDateIsYesterday()
    {
        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('day', '2020-04-04'), new Segment('', [1]));

        $tsArchived = Date::now()->subDay(1)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-04-04'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, 1, '2020-04-04', '2020-04-04', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $actual =$archiver->isThereExistingValidPeriod($params, $isYesterday = true);
        $this->assertFalse($actual);
    }

    public function test_isThereExistingValidPeriod_returnsTrueIfDayHasNotChangedAndDateIsYesterday()
    {
        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05 06:23:40');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('day', '2020-04-04'), new Segment('', [1]));

        $tsArchived = Date::now()->subSeconds(1500)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-04-04'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, 1, '2020-04-04', '2020-04-04', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $actual = $archiver->isThereExistingValidPeriod($params, $isYesterday = true);
        $this->assertTrue($actual);
    }

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
            '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
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
            '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');
        SegmentAPI::getInstance()->add('foo', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burr', 'actions>=4', 1, true, true);

        $tracker = Fixture::getTracker(1, '2019-12-12 02:03:00');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        $tracker->setForceVisitDateTime('2019-12-11 03:04:05');
        $tracker->setUrl('http://someurl.com/2');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg2'));

        $tracker->setForceVisitDateTime('2019-12-10 03:04:05');
        $tracker->setUrl('http://someurl.com/3');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg3'));

        $tracker->setForceVisitDateTime('2019-12-02 03:04:05');
        $tracker->setUrl('http://someurl.com/4');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg4'));

        $logger = new FakeLogger();

        $archiver = new CronArchive(null, $logger);

        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setSegmentsToForce(['actions>=2;browserCode=FF', 'actions>=2']);
        $archiver->setArchiveFilter($archiveFilter);

        $archiver->init();
        $archiver->run();

        $version = Version::VERSION;
        $expected = <<<LOG
---------------------------
INIT
Running Matomo $version as Super User
---------------------------
NOTES
- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Matomo UI > Settings > General Settings.
  See the doc at: https://matomo.org/docs/setup-auto-archiving/
- Async process archiving supported, using CliMulti.
- Reports for today will be processed at most every 900 seconds. You can change this value in Matomo UI > Settings > General Settings.
- Limiting segment archiving to following segments:
  * actions>=2;browserCode=FF
  * actions>=2
---------------------------
START
Starting Matomo reports archiving...
Checking for queued invalidations...
  Will invalidate archived reports for 2019-12-12 for following websites ids: 1
  Will invalidate archived reports for 2019-12-11 for following websites ids: 1
  Will invalidate archived reports for 2019-12-10 for following websites ids: 1
  Will invalidate archived reports for 2019-12-02 for following websites ids: 1
  Today archive can be skipped due to no visits for idSite = 1, skipping invalidation...
  Yesterday archive can be skipped due to no visits for idSite = 1, skipping invalidation...
  Segment "actions>=2" was created or changed recently and will therefore archive today (for site ID = 1)
  Segment "actions>=4" was created or changed recently and will therefore archive today (for site ID = 1)
Done invalidating
Start processing archives for site 1.
Found invalidated archive we can skip (no visits): [idinvalidation = 43, idsite = 1, period = day(2020-02-03 - 2020-02-03), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 73, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 73, idsite = 1, period = day(2020-02-03 - 2020-02-03), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 44, idsite = 1, period = week(2020-02-03 - 2020-02-09), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 74, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 74, idsite = 1, period = week(2020-02-03 - 2020-02-09), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 42, idsite = 1, period = day(2020-02-02 - 2020-02-02), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 72, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 72, idsite = 1, period = day(2020-02-02 - 2020-02-02), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 40, idsite = 1, period = day(2020-02-01 - 2020-02-01), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 70, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 70, idsite = 1, period = day(2020-02-01 - 2020-02-01), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 41, idsite = 1, period = month(2020-02-01 - 2020-02-29), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 71, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 71, idsite = 1, period = month(2020-02-01 - 2020-02-29), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 36, idsite = 1, period = week(2020-01-27 - 2020-02-02), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 66, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 66, idsite = 1, period = week(2020-01-27 - 2020-02-02), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 33, idsite = 1, period = day(2020-01-01 - 2020-01-01), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 63, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 63, idsite = 1, period = day(2020-01-01 - 2020-01-01), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 34, idsite = 1, period = month(2020-01-01 - 2020-01-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 64, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 64, idsite = 1, period = month(2020-01-01 - 2020-01-31), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 35, idsite = 1, period = year(2020-01-01 - 2020-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 65, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 65, idsite = 1, period = year(2020-01-01 - 2020-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 28, idsite = 1, period = day(2019-12-31 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 58, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 58, idsite = 1, period = day(2019-12-31 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 26, idsite = 1, period = day(2019-12-30 - 2019-12-30), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 56, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 56, idsite = 1, period = day(2019-12-30 - 2019-12-30), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 27, idsite = 1, period = week(2019-12-30 - 2020-01-05), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 57, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 57, idsite = 1, period = week(2019-12-30 - 2020-01-05), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 24, idsite = 1, period = day(2019-12-23 - 2019-12-23), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 54, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 54, idsite = 1, period = day(2019-12-23 - 2019-12-23), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 25, idsite = 1, period = week(2019-12-23 - 2019-12-29), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 55, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 55, idsite = 1, period = week(2019-12-23 - 2019-12-29), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 22, idsite = 1, period = day(2019-12-16 - 2019-12-16), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 52, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 52, idsite = 1, period = day(2019-12-16 - 2019-12-16), name = done49a9440bd6dba4b8850035e09d043c67]
Found invalidated archive we can skip (no visits): [idinvalidation = 23, idsite = 1, period = week(2019-12-16 - 2019-12-22), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 53, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 53, idsite = 1, period = week(2019-12-16 - 2019-12-22), name = done49a9440bd6dba4b8850035e09d043c67]
Skipping invalidated archive 1, segment '' is not in --force-idsegments: [idinvalidation = 1, idsite = 1, period = day(2019-12-12 - 2019-12-12), name = done]
Skipping invalidated archive 5, segment '' is not in --force-idsegments: [idinvalidation = 5, idsite = 1, period = day(2019-12-11 - 2019-12-11), name = done]
Skipping invalidated archive 9, segment '' is not in --force-idsegments: [idinvalidation = 9, idsite = 1, period = day(2019-12-10 - 2019-12-10), name = done]
Found invalidated archive we can skip (no visits): [idinvalidation = 20, idsite = 1, period = day(2019-12-09 - 2019-12-09), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Skipping invalidated archive 50, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 50, idsite = 1, period = day(2019-12-09 - 2019-12-09), name = done49a9440bd6dba4b8850035e09d043c67]
Skipping invalidated archive 2, segment '' is not in --force-idsegments: [idinvalidation = 2, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = done]
Skipping invalidated archive 6, segment '' is not in --force-idsegments: [idinvalidation = 6, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = done]
Skipping invalidated archive 10, segment '' is not in --force-idsegments: [idinvalidation = 10, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = done]
Processing invalidation: [idinvalidation = 21, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = donee0512c03f7c20af6ef96a8d792c6bb9f].
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 51, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 13, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = done]
Processing invalidation: [idinvalidation = 17, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = donee0512c03f7c20af6ef96a8d792c6bb9f].
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 47, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 14, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = done]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 18, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 48, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 3, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 7, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 11, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 15, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 19, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 49, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 4, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 8, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 12, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 16, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 32, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 62, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=week&date=2019-12-09&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-09, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-02, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive 51, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 51, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = done49a9440bd6dba4b8850035e09d043c67]
Skipping invalidated archive 13, segment '' is not in --force-idsegments: [idinvalidation = 13, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = done]
Skipping invalidated archive 47, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 47, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = done49a9440bd6dba4b8850035e09d043c67]
Skipping invalidated archive 14, segment '' is not in --force-idsegments: [idinvalidation = 14, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = done]
Processing invalidation: [idinvalidation = 18, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = donee0512c03f7c20af6ef96a8d792c6bb9f].
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 48, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 3, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 7, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 11, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 15, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 19, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 49, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 4, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 8, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 12, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 16, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 32, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 62, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=week&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-02, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive 48, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 48, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = done49a9440bd6dba4b8850035e09d043c67]
Skipping invalidated archive 3, segment '' is not in --force-idsegments: [idinvalidation = 3, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Skipping invalidated archive 7, segment '' is not in --force-idsegments: [idinvalidation = 7, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Skipping invalidated archive 11, segment '' is not in --force-idsegments: [idinvalidation = 11, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Skipping invalidated archive 15, segment '' is not in --force-idsegments: [idinvalidation = 15, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done]
Processing invalidation: [idinvalidation = 19, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f].
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 49, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 4, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 8, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 12, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: [idinvalidation = 16, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 32, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f]
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 62, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=month&date=2019-12-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = month, date = 2019-12-01, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive 49, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 49, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
Skipping invalidated archive 4, segment '' is not in --force-idsegments: [idinvalidation = 4, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Skipping invalidated archive 8, segment '' is not in --force-idsegments: [idinvalidation = 8, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Skipping invalidated archive 12, segment '' is not in --force-idsegments: [idinvalidation = 12, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Skipping invalidated archive 16, segment '' is not in --force-idsegments: [idinvalidation = 16, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done]
Processing invalidation: [idinvalidation = 32, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f].
Found archive with intersecting period with others in concurrent batch, skipping until next batch: [idinvalidation = 62, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=year&date=2019-01-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = year, date = 2019-01-01, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive 62, segment 'actions>=4' is not in --force-idsegments: [idinvalidation = 62, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = done49a9440bd6dba4b8850035e09d043c67]
No next invalidated archive.
Finished archiving for site 1, 5 API requests, Time elapsed: %fs [1 / 1 done]
No more sites left to archive, stopping.
Done archiving!
---------------------------
SUMMARY
Processed 5 archives.
Total API requests: 5
done: 5 req, %d ms, no error
Time elapsed: %fs
LOG;

        $this->assertStringMatchesFormat($expected, $logger->output);
    }

    public function test_shouldNotStopProcessingWhenOneSiteIsInvalid()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');

        $logger = new FakeLogger();

        $archiver = new CronArchive(null, $logger);
        $archiver->shouldArchiveSpecifiedSites = array(99999, 1);
        $archiver->init();
        $archiver->run();

        $expected = <<<LOG
- Will process 2 websites (--force-idsites)
- Will process specified sites: 1
---------------------------
START
Starting Matomo reports archiving...
Checking for queued invalidations...
  Today archive can be skipped due to no visits for idSite = 1, skipping invalidation...
  Yesterday archive can be skipped due to no visits for idSite = 1, skipping invalidation...
Done invalidating
Start processing archives for site 1.
No next invalidated archive.
LOG;

        self::assertStringContainsString($expected, $logger->output);
    }

    public function provideContainerConfig()
    {
        Date::$now = strtotime('2020-02-03 04:05:06');

        return array(
            'Piwik\CliMulti' => \DI\create('Piwik\Tests\Framework\Mock\FakeCliMulti')
        );
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
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
