<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\CliMulti;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\API;
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
        $cronarchive->init();

        $expectedInvalidations = array(
            array(array(1,2), '2014-04-05'),
            array(array(2), '2014-04-06')
        );

        $this->assertEquals($expectedInvalidations, $api->getInvalidatedReports());
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
Running Piwik %s as Super User
---------------------------
NOTES
- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Piwik UI > Settings > General Settings.
  See the doc at: http://piwik.org/docs/setup-auto-archiving/
- Reports for today will be processed at most every %s seconds. You can change this value in Piwik UI > Settings > General Settings.
- Reports for the current week/month/year will be refreshed at most every %s seconds.
- Will process all 1 websites
- Limiting segment archiving to following segments:
  * actions>=2;browserCode=FF
  * actions>=2
---------------------------
START
Starting Piwik reports archiving...
Will pre-process for website id = 1, period = day, date = last%s
- pre-processing all visits
- skipping segment archiving for 'actions>=4'.
- pre-processing segment 1/1 actions>=2
Archived website id = 1, period = day, 1 segments, 1 visits in last %s days, 1 visits today, Time elapsed: %s
Will pre-process for website id = 1, period = week, date = last%s
- pre-processing all visits
- skipping segment archiving for 'actions>=4'.
- pre-processing segment 1/1 actions>=2
Archived website id = 1, period = week, 1 segments, 1 visits in last %s weeks, 1 visits this week, Time elapsed: %s
Will pre-process for website id = 1, period = month, date = last%s
- pre-processing all visits
- skipping segment archiving for 'actions>=4'.
- pre-processing segment 1/1 actions>=2
Archived website id = 1, period = month, 1 segments, 1 visits in last %s months, 1 visits this month, Time elapsed: %s
Will pre-process for website id = 1, period = year, date = last%s
- pre-processing all visits
- skipping segment archiving for 'actions>=4'.
- pre-processing segment 1/1 actions>=2
Archived website id = 1, period = year, 1 segments, 1 visits in last %s years, 1 visits this year, Time elapsed: %s
Archived website id = 1, %s API requests, Time elapsed: %s [1/1 done]
Done archiving!
---------------------------
SUMMARY
Total visits for today across archived websites: 1
Archived today's reports for 1 websites
Archived week/month/year for 1 websites
Skipped 0 websites
- 0 skipped because no new visit since the last script execution
- 0 skipped because existing daily reports are less than 150 seconds old
- 0 skipped because existing week/month/year periods reports are less than 3600 seconds old
Total API requests: %s
done: 1/1 100%, 1 vtoday, 1 wtoday, 1 wperiods, %s req, %s ms, no error
Time elapsed: %s

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
Will ignore websites and help finish a previous started queue instead. IDs: 1
---------------------------
START
Starting Piwik reports archiving...
Will pre-process for website id = 1, period = day, date = last52
- pre-processing all visits
LOG;

        $this->assertContains($expected, $logger->output);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\CliMulti' => \DI\object('Piwik\Tests\Framework\Mock\FakeCliMulti')
        );
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
}
