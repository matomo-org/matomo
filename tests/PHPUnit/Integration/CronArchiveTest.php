<?php
/**
 * Matomo - free/libre analytics platform
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

        $logger = new FakeLogger();

        $archiver = new CronArchive(null, $logger);

        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setSegmentsToForce(['actions>=2;browserCode=FF', 'actions>=2']);
        $archiver->setArchiveFilter($archiveFilter);

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
- Limiting segment archiving to following segments:
  * actions>=2;browserCode=FF
  * actions>=2
---------------------------
START
Starting Matomo reports archiving...
Checking for queued invalidations...
  Today archive can be skipped due to no visits, skipping invalidation...
  Yesterday archive can be skipped due to no visits, skipping invalidation...
  Segment "actions>=2" was created or changed recently and will therefore archive today (for site ID = 1)
  Segment "actions>=4" was created or changed recently and will therefore archive today (for site ID = 1)
Done invalidating
Start processing archives for site 1.
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2014-12-11,2014-12-11), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2014-12-12,2014-12-12), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2014-12-13,2014-12-13), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2014-12-14,2014-12-14), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2014-12-15,2014-12-15), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2014-12-22,2014-12-22), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2014-12-29,2014-12-29), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2014-12-30,2014-12-30), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2014-12-31,2014-12-31), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2015-01-01,2015-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2016-01-01,2016-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2017-01-01,2017-01-01), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2018-01-01,2018-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2019-01-01,2019-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-01-01,2020-01-01), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2020-02-01,2020-02-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-03-01,2020-03-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-04-01,2020-04-01), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2020-04-02,2020-04-02), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-04-03,2020-04-03), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-04-04,2020-04-04), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2020-04-05,2020-04-05), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-04-06,2020-04-06), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-04-13,2020-04-13), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, day (2020-04-20,2020-04-20), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, day (2020-04-27,2020-04-27), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2014-12-08,2014-12-14), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, week (2014-12-15,2014-12-21), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2014-12-22,2014-12-28), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2014-12-29,2015-01-04), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, week (2015-12-28,2016-01-03), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2016-12-26,2017-01-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2018-01-01,2018-01-07), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, week (2018-12-31,2019-01-06), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2019-12-30,2020-01-05), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-01-27,2020-02-02), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, week (2020-02-24,2020-03-01), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-03-30,2020-04-05), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-04-06,2020-04-12), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, week (2020-04-13,2020-04-19), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-04-20,2020-04-26), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, week (2020-04-27,2020-05-03), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, month (2014-12-01,2014-12-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2015-01-01,2015-01-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2016-01-01,2016-01-31), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, month (2017-01-01,2017-01-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2018-01-01,2018-01-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2019-01-01,2019-01-31), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, month (2020-01-01,2020-01-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-02-01,2020-02-29), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, month (2020-03-01,2020-03-31), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, month (2020-04-01,2020-04-30), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2014-01-01,2014-12-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2015-01-01,2015-12-31), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found no visits for site ID = 1, year (2016-01-01,2016-12-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2017-01-01,2017-12-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2018-01-01,2018-12-31), site is using the tracker so skipping archiving...
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
No next invalidated archive.
Found no visits for site ID = 1, year (2019-01-01,2019-12-31), site is using the tracker so skipping archiving...
Found no visits for site ID = 1, year (2020-01-01,2020-12-31), site is using the tracker so skipping archiving...
No next invalidated archive.
Finished archiving for site 1, 59 API requests, Time elapsed: %d.%ds [1 / 1 done]
No more sites left to archive, stopping.

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
  Today archive can be skipped due to no visits, skipping invalidation...
  Yesterday archive can be skipped due to no visits, skipping invalidation...
Done invalidating
Start processing archives for site 1.
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
