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
use Piwik\Version;

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
  Today archive can be skipped due to no visits, skipping invalidation...
  Yesterday archive can be skipped due to no visits, skipping invalidation...
  Segment "actions>=2" was created or changed recently and will therefore archive today (for site ID = 1)
  Segment "actions>=4" was created or changed recently and will therefore archive today (for site ID = 1)
Done invalidating
Start processing archives for site 1.
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2020-01-01 - 2020-01-01, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2020-01-01 - 2020-01-31, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2020-01-01 - 2020-12-31, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-31 - 2019-12-31, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-30 - 2019-12-30, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-30 - 2020-01-05, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-23 - 2019-12-23, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-23 - 2019-12-29, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-16 - 2019-12-16, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-16 - 2019-12-22, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Found invalidated archive we can skip (no visits or latest archive is not invalidated). [idSite = 1, dates = 2019-12-09 - 2019-12-09, segment = actions>=2]
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found archive with different period than others in concurrent batch, skipping until next batch: 1
Found archive with different period than others in concurrent batch, skipping until next batch: 1
Found archive with different period than others in concurrent batch, skipping until next batch: 1
Found archive with different done flag type (segment vs. no segment) in concurrent batch, skipping until next batch: done
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
No next invalidated archive.
Starting archiving for ?module=API&method=API.get&idSite=1&period=week&date=2019-12-09&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=API.get&idSite=1&period=week&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-09, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = week, date = 2019-12-02, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found archive with different period than others in concurrent batch, skipping until next batch: 2
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 3
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
No next invalidated archive.
Starting archiving for ?module=API&method=API.get&idSite=1&period=day&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = day, date = 2019-12-02, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
Found archive with different period than others in concurrent batch, skipping until next batch: 4
No next invalidated archive.
Starting archiving for ?module=API&method=API.get&idSite=1&period=month&date=2019-12-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = month, date = 2019-12-01, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment '' is not in --force-idsegments
Skipping invalidated archive : segment 'actions>=4' is not in --force-idsegments
No next invalidated archive.
Starting archiving for ?module=API&method=API.get&idSite=1&period=year&date=2019-01-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = year, date = 2019-01-01, segment = 'actions%3E%3D2', 0 visits found. Time elapsed: %fs
No next invalidated archive.
Finished archiving for site 1, 5 API requests, Time elapsed: %fs [1 / 1 done]
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
        Date::$now = strtotime('2020-02-03 04:05:06');

        return array(
            'Piwik\CliMulti' => \DI\object('Piwik\Tests\Framework\Mock\FakeCliMulti')
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
