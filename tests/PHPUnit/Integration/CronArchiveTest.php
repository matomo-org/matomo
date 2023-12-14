<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
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
use Piwik\Sequence;
use Piwik\Site;
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
    /**
     * @dataProvider getTestDataForRepairInvalidationsIfNeeded
     */
    public function test_repairInvalidationsIfNeeded_insertsProperInvalidations($existingInvalidations, $archive,
                                                                                $expectedInvalidations)
    {
        $this->insertInvalidations($existingInvalidations);

        $cronArchive = new CronArchive();
        $cronArchive->init();

        $cronArchive->repairInvalidationsIfNeeded($archive);

        $invalidations = $this->getInvalidationsInTable(true);

        $this->assertEquals($expectedInvalidations, $invalidations);
    }

    public function getTestDataForRepairInvalidationsIfNeeded()
    {
        return [
            // day w/ nothing else
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                ],
                ['idarchive' => 1, 'idsite' => 1, 'period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                [
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '1',
                        'date1' => '2020-03-04',
                        'date2' => '2020-03-04',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                ],
            ],

            // week with nothing else
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00', 'status' => 1], // duplicate w/ status = 1
                ],
                ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                [
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array ( // status = 1 version
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                ],
            ],

            // week w/ month
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 3, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                ],
                ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                [
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                ],
            ],

            // week on edge of month w/ nothing else
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-02-24', 'date2' => '2020-03-01', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                ],
                ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-02-24', 'date2' => '2020-03-01', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 01:00:00'],
                [
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-02-24',
                        'date2' => '2020-03-01',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                ],
            ],

            // week for report w/ some other similar archives
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done.MyPlugin', 'report' => 'myReport', 'ts_invalidated' => '2020-03-04 03:04:04'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done.MyPlugin', 'report' => 'myOtherReport', 'ts_invalidated' => '2020-03-04 03:04:04'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done.MyOtherPlugin', 'report' => 'myReport', 'ts_invalidated' => '2020-03-04 03:04:04'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2020-03-04 03:04:04'],
                ],
                ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'name' => 'done.MyPlugin', 'report' => 'myReport', 'ts_invalidated' => '2020-03-04 03:04:04'],
                [
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done.MyPlugin',
                        'report' => 'myReport',
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done.MyPlugin',
                        'report' => 'myOtherReport',
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done.MyOtherPlugin',
                        'report' => 'myReport',
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done.MyPlugin',
                        'report' => 'myReport',
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => NULL,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done.MyPlugin',
                        'report' => 'myReport',
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                ],
            ],

            // week split across two years - make sure the year invalidation isn't changed to the week start year
            [
                [
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2021-12-27', 'date2' => '2022-01-02', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2022-03-04 01:00:00'],
                    ['idarchive' => 1, 'idsite' => 1, 'period' => 4, 'date1' => '2022-01-01', 'date2' => '2022-12-31', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2022-03-04 01:00:00'],
                ],
                ['idarchive' => 1, 'idsite' => 1, 'period' => 2, 'date1' => '2021-12-27', 'date2' => '2022-01-02', 'name' => 'done', 'report' => null, 'ts_invalidated' => '2022-03-04 01:00:00'],
                [
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2021-12-27',
                        'date2' => '2022-01-02',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2022-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => 1,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2022-01-01',
                        'date2' => '2022-12-31',
                        'name' => 'done',
                        'report' => NULL,
                        'ts_invalidated' => '2022-03-04 01:00:00',
                    ),
                ],
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForInvalidateRecentDate
     */
    public function test_invalidateRecentDate_invalidatesCorrectPeriodsAndSegments($dateStr, $segments,
                                                                                   $expectedInvalidationCalls)
    {
        $idSite = Fixture::createWebsite('2019-04-04 03:45:45', 0, false, false, 1, null, null, 'Australia/Sydney');

        Rules::setBrowserTriggerArchiving(false);
        foreach ($segments as $idx => $segment) {
            SegmentAPI::getInstance()->add('segment #' . $idx, $segment, $idx % 2 === 0 ? $idSite : false, true, true);
        }
        Rules::setBrowserTriggerArchiving(true);

        $t = Fixture::getTracker($idSite, Date::yesterday()->addHour(2)->getDatetime());
        $t->setUrl('http://someurl.com/abc');
        Fixture::checkResponse($t->doTrackPageView('some page'));

        $t = Fixture::getTracker($idSite, Date::today()->addHour(2)->getDatetime());
        $t->setUrl('http://someurl.com/def');
        Fixture::checkResponse($t->doTrackPageView('some page 2'));

        $mockInvalidateApi = $this->getMockInvalidateApi();

        $archiver = new CronArchive();
        $archiver->init();
        $archiver->setApiToInvalidateArchivedReport($mockInvalidateApi);

        $archiver->invalidateRecentDate($dateStr, $idSite);

        $actualInvalidationCalls = $mockInvalidateApi->getInvalidations();

        $this->assertEquals($expectedInvalidationCalls, $actualInvalidationCalls);
    }

    public function getTestDataForInvalidateRecentDate()
    {
        $segments = [
            'browserCode==IE',
            'visitCount>5',
        ];

        return [
            [
                'today',
                $segments,
                [
                    array (
                        1,
                        '2020-02-03',
                        'day',
                        false,
                        false,
                        false,
                    ),
                    array (
                        1,
                        '2020-02-03',
                        'day',
                        'visitCount>5',
                        false,
                        false,
                    ),
                    array (
                        1,
                        '2020-02-03',
                        'day',
                        'browserCode==IE',
                        false,
                        false,
                    ),
                ],
            ],
            [
                'yesterday',
                $segments,
                [
                    array (
                        1,
                        '2020-02-02',
                        'day',
                        false,
                        false,
                        false,
                    ),
                    array (
                        1,
                        '2020-02-02',
                        'day',
                        'visitCount>5',
                        false,
                        false,
                    ),
                    array (
                        1,
                        '2020-02-02',
                        'day',
                        'browserCode==IE',
                        false,
                        false,
                    ),
                ],
            ],
        ];
    }

    private function getMockInvalidateApi()
    {
        $mock = new class {
            private $calls = [];

            public function invalidateArchivedReports()
            {
                $this->calls[] = func_get_args();
            }

            public function getInvalidations()
            {
                return $this->calls;
            }
        };
        return $mock;
    }

    public function test_canWeSkipInvalidatingBecauseThereIsAUsablePeriod_returnsTrueIfPeriodHasToday_AndExistingArchiveIsNewEnough()
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

        $actual = $archiver->canWeSkipInvalidatingBecauseThereIsAUsablePeriod($params);
        $this->assertTrue($actual);
    }

    public function test_canWeSkipInvalidatingBecauseThereIsAUsablePeriod_returnsTrueIfPeriodHasToday_AndExistingArchiveIsNewEnoughAndInvalidated()
    {
        Rules::setBrowserTriggerArchiving(false);

        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('week', '2020-04-05'), new Segment('', [1]));

        $tsArchived = Date::now()->subSeconds(100)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-30'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1,2, '2020-03-30', '2020-04-05', 'done', ArchiveWriter::DONE_INVALIDATED, $tsArchived
        ]);

        $actual = $archiver->canWeSkipInvalidatingBecauseThereIsAUsablePeriod($params);
        $this->assertTrue($actual);
    }

    public function test_canWeSkipInvalidatingBecauseThereIsAUsablePeriod_returnsIfPeriodDoesNotHaveToday_AndExistingArchiveIsOk()
    {
        Rules::setBrowserTriggerArchiving(false);

        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('day', '2020-03-05'), new Segment('', [1]));

        $tsArchived = Date::now()->subHour(0.1)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-05'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, 1, '2020-03-05', '2020-03-05', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $actual = $archiver->canWeSkipInvalidatingBecauseThereIsAUsablePeriod($params);
        $this->assertTrue($actual);
    }

    public function test_canWeSkipInvalidatingBecauseThereIsAUsablePeriod_returnsFalseIfDayHasChangedAndDateIsYesterday()
    {
        Rules::setBrowserTriggerArchiving(false);

        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('day', '2020-04-04'), new Segment('', [1]));

        $tsArchived = Date::now()->subDay(1)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-04-04'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, 1, '2020-04-04', '2020-04-04', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $actual = $archiver->canWeSkipInvalidatingBecauseThereIsAUsablePeriod($params);
        $this->assertFalse($actual);
    }

    public function test_canWeSkipInvalidatingBecauseThereIsAUsablePeriod_returnsTrueIfDayHasNotChangedAndDateIsYesterday()
    {
        Rules::setBrowserTriggerArchiving(false);

        Fixture::createWebsite('2019-04-04 03:45:45');

        Date::$now = strtotime('2020-04-05 06:23:40');

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), Factory::build('day', '2020-04-04'), new Segment('', [1]));

        $tsArchived = Date::now()->subSeconds(1500)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-04-04'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, 1, '2020-04-04', '2020-04-04', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        // $doNotIncludeTtlInExistingArchiveCheck is set to true when running invalidateRecentDate('yesterday');
        $actual = $archiver->canWeSkipInvalidatingBecauseThereIsAUsablePeriod($params, $doNotIncludeTtlInExistingArchiveCheck = true);
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

        $cronarchive = new TestCronArchive();
        $cronarchive->init();
        $cronarchive->setApiToInvalidateArchivedReport($api);
        $cronarchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain(1);
        $cronarchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain(2);

        /**
         * should look like this but the result is random
         *  array(
        array(array(1,2), '2014-04-05'),
        array(array(2), '2014-04-06')
        )
         */
        $invalidatedReports = $api->getInvalidatedReports();
        $this->assertCount(3, $invalidatedReports);

        usort($invalidatedReports, function ($a, $b) {
            return strcmp($a[1], $b[1]);
        });

        $this->assertSame(1, $invalidatedReports[0][0]);
        $this->assertSame('2014-04-05', $invalidatedReports[0][1]);

        $this->assertSame(2, $invalidatedReports[1][0]);
        $this->assertSame('2014-04-05', $invalidatedReports[1][1]);

        $this->assertSame(2, $invalidatedReports[2][0]);
        $this->assertSame('2014-04-06', $invalidatedReports[2][1]);
    }

    public function test_wasSegmentCreatedRecently()
    {
        Fixture::createWebsite('2014-12-12 00:01:02');

        Rules::setBrowserTriggerArchiving(false);
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $segments = new Model();
        $segments->updateSegment($id, array('ts_created' => Date::now()->subHour(30)->getDatetime()));

        $allSegments = $segments->getSegmentsToAutoArchive(1);

        $cronarchive = new TestCronArchive();
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
        Rules::setBrowserTriggerArchiving(false);
        SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $segments = new Model();
        $segments->updateSegment($id, array('ts_created' => Date::now()->subHour(30)->getDatetime()));

        $logger = new FakeLogger();

        $archiver = new CronArchive($logger);
        $archiver->init();
        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setSkipSegmentsForToday(true);
        $archiver->setArchiveFilter($archiveFilter);
        $archiver->shouldArchiveAllSites = true;
        $archiver->init();
        $archiver->run();

        self::assertStringContainsString('Will skip segments archiving for today unless they were created recently', $logger->output);
        self::assertStringNotContainsString('Segment "actions>=2" was created recently', $logger->output);
    }

    public function test_output()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');
        Rules::setBrowserTriggerArchiving(false);
        SegmentAPI::getInstance()->add('foo', 'actions>=2', 1, true, true);
        SegmentAPI::getInstance()->add('burr', 'actions>=4', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

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

        $archiver = new CronArchive($logger);

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
Applying queued rearchiving...
Start processing archives for site 1.
Checking for queued invalidations...
  Will invalidate archived reports for 2019-12-12 for following websites ids: 1
  Will invalidate archived reports for 2019-12-11 for following websites ids: 1
  Will invalidate archived reports for 2019-12-10 for following websites ids: 1
  Will invalidate archived reports for 2019-12-02 for following websites ids: 1
  Today archive can be skipped due to no visits for idSite = 1, skipping invalidation...
  Yesterday archive can be skipped due to no visits for idSite = 1, skipping invalidation...
Done invalidating
Processing invalidation: [idinvalidation = 269, idsite = 1, period = day(2019-12-12 - 2019-12-12), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Processing invalidation: [idinvalidation = 268, idsite = 1, period = day(2019-12-11 - 2019-12-11), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Processing invalidation: [idinvalidation = 267, idsite = 1, period = day(2019-12-10 - 2019-12-10), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-12&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-11&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-10&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = day, date = 2019-12-12, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-11, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-10, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 266, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Processing invalidation: [idinvalidation = 257, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=week&date=2019-12-09&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-09, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-02, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 258, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=week&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-02, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 256, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=month&date=2019-12-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = month, date = 2019-12-01, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 65, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=year&date=2019-01-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = year, date = 2019-01-01, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
No next invalidated archive.
Finished archiving for site 1, 8 API requests, Time elapsed: %fs [1 / 1 done]
No more sites left to archive, stopping.
Done archiving!
---------------------------
SUMMARY
Processed 8 archives.
Total API requests: 8
done: 8 req, %d ms, no error
Time elapsed: %fs
LOG;

        // remove a bunch of debug lines since we can't have a sprintf format that long
        $output = $this->cleanOutput($logger->output);

        $this->assertStringMatchesFormat($expected, $output);
    }

    public function test_output_withSkipIdSites()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');
        Fixture::createWebsite('2014-12-12 00:01:02');
        Fixture::createWebsite('2014-12-12 00:01:02');

        $tracker = Fixture::getTracker(1, '2019-12-12 02:03:00');
        $tracker->enableBulkTracking();
        foreach ([1,2,3] as $idSite) {
            $tracker->setIdSite($idSite);
            $tracker->setUrl('http://someurl.com');
            Fixture::assertTrue($tracker->doTrackPageView('abcdefg'));

            $tracker->setForceVisitDateTime('2019-12-11 03:04:05');
            $tracker->setUrl('http://someurl.com/2');
            Fixture::assertTrue($tracker->doTrackPageView('abcdefg2'));

            $tracker->setForceVisitDateTime('2019-12-10 03:04:05');
            $tracker->setUrl('http://someurl.com/3');
            Fixture::assertTrue($tracker->doTrackPageView('abcdefg3'));
        }
        $tracker->doBulkTrack();

        $logger = new FakeLogger();

        // prevent race condition in sequence creation during test
        $sequence = new Sequence(ArchiveTableCreator::getNumericTable(Date::factory('2019-12-10')));
        $sequence->create();

        $archiver = new CronArchive($logger);

        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiver->setArchiveFilter($archiveFilter);
        $archiver->shouldSkipSpecifiedSites = [1,3];

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
---------------------------
START
Starting Matomo reports archiving...
Applying queued rearchiving...
Start processing archives for site 2.
Checking for queued invalidations...
  Will invalidate archived reports for 2019-12-11 for following websites ids: 2
  Will invalidate archived reports for 2019-12-10 for following websites ids: 2
  Today archive can be skipped due to no visits for idSite = 2, skipping invalidation...
  Yesterday archive can be skipped due to no visits for idSite = 2, skipping invalidation...
Done invalidating
Processing invalidation: [idinvalidation = 1, idsite = 2, period = day(2019-12-11 - 2019-12-11), name = done, segment = ].
Processing invalidation: [idinvalidation = 5, idsite = 2, period = day(2019-12-10 - 2019-12-10), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=day&date=2019-12-11&format=json&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=day&date=2019-12-10&format=json&trigger=archivephp
Archived website id 2, period = day, date = 2019-12-11, segment = '', 1 visits found. Time elapsed: %fs
Archived website id 2, period = day, date = 2019-12-10, segment = '', 1 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 2, idsite = 2, period = week(2019-12-09 - 2019-12-15), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=week&date=2019-12-09&format=json&trigger=archivephp
Archived website id 2, period = week, date = 2019-12-09, segment = '', 2 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 3, idsite = 2, period = month(2019-12-01 - 2019-12-31), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=month&date=2019-12-01&format=json&trigger=archivephp
Archived website id 2, period = month, date = 2019-12-01, segment = '', 2 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = 4, idsite = 2, period = year(2019-01-01 - 2019-12-31), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=year&date=2019-01-01&format=json&trigger=archivephp
Archived website id 2, period = year, date = 2019-01-01, segment = '', 2 visits found. Time elapsed: %fs
No next invalidated archive.
Finished archiving for site 2, 5 API requests, Time elapsed: %fs [1 / 1 done]
No more sites left to archive, stopping.
Done archiving!
---------------------------
SUMMARY
Processed 5 archives.
Total API requests: 5
done: 5 req, %d ms, no error
Time elapsed: %fs
LOG;

        // remove a bunch of debug lines since we can't have a sprintf format that long
        $output = $this->cleanOutput($logger->output);

        $this->assertStringMatchesFormat($expected, $output);
    }

    private function cleanOutput($output)
    {
        $output = explode("\n", $output);
        $output = array_filter($output, function ($l) { return strpos($l, 'Skipping invalidated archive') === false; });
        $output = array_filter($output, function ($l) { return strpos($l, 'Found archive with intersecting period') === false; });
        $output = array_filter($output, function ($l) { return strpos($l, 'Found duplicate invalidated archive') === false; });
        $output = array_filter($output, function ($l) { return strpos($l, 'No usable archive exists') === false; });
        $output = array_filter($output, function ($l) { return strpos($l, 'Found invalidated archive we can skip (no visits)') === false; });
        $output = implode("\n", $output);
        return $output;
    }

    public function test_shouldNotStopProcessingWhenOneSiteIsInvalid()
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
            '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');

        $logger = new FakeLogger();

        $archiver = new CronArchive($logger);
        $archiver->shouldArchiveSpecifiedSites = array(99999, 1);
        $archiver->init();
        $archiver->run();

        $expected = <<<LOG
- Will process 2 websites (--force-idsites)
- Will process specified sites: 1
---------------------------
START
Starting Matomo reports archiving...
Applying queued rearchiving...
Start processing archives for site 1.
Checking for queued invalidations...
  Today archive can be skipped due to no visits for idSite = 1, skipping invalidation...
  Yesterday archive can be skipped due to no visits for idSite = 1, skipping invalidation...
Done invalidating
No next invalidated archive.
LOG;

        self::assertStringContainsString($expected, $logger->output);
    }

    public function provideContainerConfig()
    {
        Date::$now = strtotime('2020-02-03 04:05:06');

        return array(
            'Piwik\CliMulti' => \Piwik\DI::create('Piwik\Tests\Framework\Mock\FakeCliMulti')
        );
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function insertInvalidations(array $invalidations)
    {
        $now = Date::now()->getDatetime();

        $table = Common::prefixTable('archive_invalidations');
        foreach ($invalidations as $inv) {
            $bind = [
                $inv['idarchive'],
                $inv['name'],
                $inv['idsite'],
                $inv['date1'],
                $inv['date2'],
                $inv['period'],
                isset($inv['ts_invalidated']) ? $inv['ts_invalidated'] : $now,
                $inv['report'],
                isset($inv['status']) ? $inv['status'] : 0,
            ];
            Db::query("INSERT INTO `$table` (idarchive, name, idsite, date1, date2, period, ts_invalidated, report, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $bind);
        }
    }

    private function getInvalidationsInTable($includeInvalidated = false)
    {
        $table = Common::prefixTable('archive_invalidations');
        $suffix = $includeInvalidated ? ', ts_invalidated' : '';
        $sql = "SELECT idarchive, idsite, period, date1, date2, name, report$suffix FROM `$table`";
        return Db::fetchAll($sql);
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
