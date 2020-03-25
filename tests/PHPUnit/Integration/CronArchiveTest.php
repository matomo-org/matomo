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
        $cronarchive->init();

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

        $this->assertContains('Will skip segments archiving for today unless they were created recently', $logger->output);
        $this->assertContains('Segment "actions>=1" was created or changed recently and will therefore archive today', $logger->output);
        $this->assertNotContains('Segment "actions>=2" was created recently', $logger->output);
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
Running Matomo %s as Super User
---------------------------
NOTES
- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Matomo UI > Settings > General Settings.
  See the doc at: https://matomo.org/docs/setup-auto-archiving/
- Async process archiving supported, using CliMulti.
- Reports for today will be processed at most every %s seconds. You can change this value in Matomo UI > Settings > General Settings.
- Reports for the current week/month/year will be requested at most every %s seconds.
- Will process all 1 websites
- Limiting segment archiving to following segments:
  * actions>=2;browserCode=FF
  * actions>=2
---------------------------
START
Starting Matomo reports archiving...
Will pre-process for website id = 1, period = day, date = last%s
- pre-processing all visits
- skipping segment archiving for 'actions>=4'.
- pre-processing segment 1/1 actions>=2 [date = last52]
Archived website id = 1, period = day, 1 segments, 1 visits in last %s days, 1 visits today, Time elapsed: %s
- skipping segment archiving for 'actions>=4'.
Will pre-process for website id = 1, period = week, date = last%s
- pre-processing all visits
- pre-processing segment 1/1 actions>=2 [date = last260]
Archived website id = 1, period = week, 1 segments, 1 visits in last %s weeks, 1 visits this week, Time elapsed: %s
- skipping segment archiving for 'actions>=4'.
Will pre-process for website id = 1, period = month, date = last%s
- pre-processing all visits
- pre-processing segment 1/1 actions>=2 [date = last52]
Archived website id = 1, period = month, 1 segments, 1 visits in last %s months, 1 visits this month, Time elapsed: %s
- skipping segment archiving for 'actions>=4'.
Will pre-process for website id = 1, period = year, date = last%s
- pre-processing all visits
- pre-processing segment 1/1 actions>=2 [date = last7]
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
- 0 skipped because existing daily reports are less than 900 seconds old
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
Starting Matomo reports archiving...
Will pre-process for website id = 1, period = day, date = last52
- pre-processing all visits
LOG;

        $this->assertContains($expected, $logger->output);
    }

    /**
     * @dataProvider getTestDataForIsThereAValidArchiveForPeriod
     */
    public function test_isThereAValidArchiveForPeriod_modifiesLastNCorrectly($archiveRows, $idSite, $period, $date, $expected)
    {
        Date::$now = strtotime('2015-03-04 05:05:05');

        Fixture::createWebsite('2014-12-12 00:01:02');

        $this->insertArchiveData($archiveRows);

        $cronArchive = new CronArchive();
        $result = $cronArchive->isThereAValidArchiveForPeriod($idSite, $period, $date);

        $this->assertEquals($expected, $result);
    }

    public function getTestDataForIsThereAValidArchiveForPeriod()
    {
        return [
            // single periods that include today (we don't perform the check and just archive since we assume today is invalid)
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-04', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 1],
                ],
                1,
                'day',
                '2015-03-04',
                [false, '2015-03-04'],
            ],
            [
                [],
                1,
                'week',
                '2015-03-04',
                [false, '2015-03-04'],
            ],
            [
                [],
                1,
                'month',
                '2015-03-04',
                [false, '2015-03-04'],
            ],
            [
                [],
                1,
                'year',
                '2015-03-04',
                [false, '2015-03-04'],
            ],

            // single periods that do not include today
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-02', 'date2' => '2015-03-02', 'name' => 'done', 'value' => 1],
                ],
                1,
                'day',
                '2015-03-02',
                [true, '2015-03-02'],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-01', 'date2' => '2015-03-01', 'name' => 'done', 'value' => 1],
                ],
                1,
                'day',
                '2015-03-02',
                [false, '2015-03-02'],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2015-02-16', 'date2' => '2015-02-22', 'name' => 'done', 'value' => 1],
                ],
                1,
                'week',
                '2015-02-17',
                [true, '2015-02-17'],
            ],
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2015-02-15', 'date2' => '2015-02-21', 'name' => 'done', 'value' => 4],
                ],
                1,
                'week',
                '2015-02-17',
                [false, '2015-02-17'],
            ],

            // lastN periods
            [ // last day invalid, some valid in between
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-04', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-03', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-02', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-01', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 5, 'idsite' => 1, 'period' => 1, 'date1' => '2015-02-28', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 4],
                ],
                1,
                'day',
                'last5',
                [false, 'last5'],
            ],
            [ // last two invalid, rest valid
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-04', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-03', 'date2' => '2015-03-03', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-02', 'date2' => '2015-03-02', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-01', 'date2' => '2015-03-01', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 5, 'idsite' => 1, 'period' => 1, 'date1' => '2015-02-28', 'date2' => '2015-02-28', 'name' => 'done', 'value' => 1],
                ],
                1,
                'day',
                'last5',
                [false, 'last2'],
            ],
            [ // all valid
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-04', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-03', 'date2' => '2015-03-03', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-02', 'date2' => '2015-03-02', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 1, 'date1' => '2015-03-01', 'date2' => '2015-03-01', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 5, 'idsite' => 1, 'period' => 1, 'date1' => '2015-02-28', 'date2' => '2015-02-28', 'name' => 'done', 'value' => 1],
                ],
                1,
                'day',
                'last5',
                [false, 'last2'],
            ],
            [ // month w/ last 3 invalid, today valid
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 3, 'date1' => '2015-03-01', 'date2' => '2015-03-31', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 2, 'idsite' => 1, 'period' => 3, 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 3, 'idsite' => 1, 'period' => 3, 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'name' => 'done', 'value' => 4],
                    ['idarchive' => 4, 'idsite' => 1, 'period' => 3, 'date1' => '2014-12-01', 'date2' => '2014-12-31', 'name' => 'done', 'value' => 1],
                    ['idarchive' => 5, 'idsite' => 1, 'period' => 3, 'date1' => '2014-11-01', 'date2' => '2014-11-30', 'name' => 'done', 'value' => 1],
                ],
                1,
                'month',
                'last5',
                [false, 'last3'],
            ],

            // range periods
            [ // includes today
                [
                    ['idarchive' => 5, 'idsite' => 1, 'period' => 5, 'date1' => '2015-03-02', 'date2' => '2015-03-04', 'name' => 'done', 'value' => 1],
                ],
                1,
                'range',
                '2015-03-02,2015-03-04',
                [false, '2015-03-02,2015-03-04'],
            ],
            [ // does not include today, invalid
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2015-03-01', 'date2' => '2015-03-03', 'name' => 'done', 'value' => 4],
                ],
                1,
                'range',
                '2015-03-01,2015-03-03',
                [false, '2015-03-01,2015-03-03'],
            ],
            [ // does not include today, valid
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 5, 'date1' => '2015-03-01', 'date2' => '2015-03-03', 'name' => 'done', 'value' => 1],
                ],
                1,
                'range',
                '2015-03-01,2015-03-03',
                [true, '2015-03-01,2015-03-03'],
            ],
        ];
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
