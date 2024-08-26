<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
use Piwik\Option;
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
    public function testRepairInvalidationsIfNeededInsertsProperInvalidations(
        $existingInvalidations,
        $archive,
        $expectedInvalidations
    ) {
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
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => null,
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
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array ( // status = 1 version
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => null,
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
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'period' => '2',
                        'date1' => '2020-03-02',
                        'date2' => '2020-03-08',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => null,
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
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2020-01-01',
                        'date2' => '2020-12-31',
                        'name' => 'done',
                        'report' => null,
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
                        'report' => null,
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => null,
                        'idsite' => '1',
                        'period' => '3',
                        'date1' => '2020-03-01',
                        'date2' => '2020-03-31',
                        'name' => 'done.MyPlugin',
                        'report' => 'myReport',
                        'ts_invalidated' => '2020-03-04 03:04:04',
                    ),
                    array (
                        'idarchive' => null,
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
                        'report' => null,
                        'ts_invalidated' => '2022-03-04 01:00:00',
                    ),
                    array (
                        'idarchive' => 1,
                        'idsite' => '1',
                        'period' => '4',
                        'date1' => '2022-01-01',
                        'date2' => '2022-12-31',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2022-03-04 01:00:00',
                    ),
                ],
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForInvalidateRecentDate
     */
    public function testInvalidateRecentDateInvalidatesCorrectPeriodsAndSegments(
        $dateStr,
        $segments,
        $expectedInvalidationCalls
    ) {
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

    /**
     * @dataProvider getInvalidateYesterdayTestData
     */
    public function testInvalidateRecentDateForYesterdayIsSkippedWhenAlreadyInProgress($segmentsToCreate, $timezone, $nowTs, $existingInvalidations, $expectedInvalidationCalls)
    {
        $idSite = Fixture::createWebsite('2019-04-04 03:45:45', 0, false, false, 1, null, null, $timezone);

        Rules::setBrowserTriggerArchiving(false);
        foreach ($segmentsToCreate as $segment) {
            SegmentAPI::getInstance()->add($segment, $segment, 1, true, true);
        }
        Rules::setBrowserTriggerArchiving(true);

        $offset = Date::getUtcOffset($timezone);
        Date::$now = $nowTs;

        $this->insertInvalidations($existingInvalidations);

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

        $archiver->invalidateRecentDate('yesterday', $idSite);
        $actualInvalidationCalls = $mockInvalidateApi->getInvalidations();

        $this->assertEquals($expectedInvalidationCalls, $actualInvalidationCalls);
    }

    public function getInvalidateYesterdayTestData()
    {
        $timezones = [
            'UTC-12',
            'America/Caracas', // UTC-4
            'UTC-0.5',
            'UTC',
            'Asia/Kathmandu', // UTC+5:45
            'Australia/Brisbane', // UTC+10
            'UTC+14',
        ];

        foreach ($timezones as $timezone) {
            $offset = Date::getUtcOffset($timezone);

            yield "invalidating yesterday all visits should be skipped if archiving was started after midnight in sites timezone ($timezone)" => [
                [],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:12:33')->subSeconds($offset)->getDatetime()
                    ],
                ],
                [],
            ];

            yield "invalidating yesterday all visits should not be skipped if archiving for a segment was started after midnight in sites timezone ($timezone)" => [
                ['actions>=1'],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=1'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:12:33')->subSeconds($offset)->getDatetime()
                    ],
                ],
                [
                    [
                        1,
                        '2020-02-02',
                        'day',
                        false,
                        false,
                        false,
                    ],
                ],
            ];

            yield "invalidating yesterdays segments should not be skipped even if archiving all visits was started after midnight in sites timezone ($timezone)" => [
                ['actions>=1', 'actions>=2',],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:12:33')->subSeconds($offset)->getDatetime()
                    ],
                ],
                [
                    [
                        1,
                        '2020-02-02',
                        'day',
                        'actions>=1',
                        false,
                        false,
                    ],
                    [
                        1,
                        '2020-02-02',
                        'day',
                        'actions>=2',
                        false,
                        false,
                    ],
                ],
            ];

            yield "invalidating yesterdays segments should be skipped if an archiving for it was started after midnight in sites timezone ($timezone)" => [
                ['actions>=1', 'actions>=2',],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:12:33')->subSeconds($offset)->getDatetime()
                    ],
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=2'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:12:33')->subSeconds($offset)->getDatetime()
                    ],
                ],
                [
                    [
                        1,
                        '2020-02-02',
                        'day',
                        'actions>=1',
                        false,
                        false,
                    ],
                ],
            ];

            yield "invalidating yesterday all visits should not be skipped if archiving was started before midnight in sites timezone ($timezone)" => [
                [],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done',
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-02 23:48:33')->subSeconds($offset)->getDatetime()
                    ],
                ],
                [
                    [
                        1,
                        '2020-02-02',
                        'day',
                        false,
                        false,
                        false,
                    ],
                ],
            ];

            yield "invalidating yesterdays segment should not be skipped if archiving was started before midnight in sites timezone ($timezone)" => [
                ['actions>=1'],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=1'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-02 23:48:33')->subSeconds($offset)->getDatetime()
                    ],
                ],
                [
                    [
                        1,
                        '2020-02-02',
                        'day',
                        false,
                        false,
                        false,
                    ],
                    [
                        1,
                        '2020-02-02',
                        'day',
                        'actions>=1',
                        false,
                        false,
                    ],
                ],
            ];

            yield "invalidating yesterdays data should be skipped correctly for multiple segments with various in progress invalidations in sites timezone ($timezone)" => [
                ['actions>=1', 'actions>=2', 'actions>=3'],
                $timezone,
                Date::factory('2020-02-03 04:05:06')->subSeconds($offset)->getTimestamp(),
                [
                    [
                        'idarchive' => 3,
                        'idsite' => 1,
                        'period' => 3,
                        'date1' => '2020-02-01',
                        'date2' => '2020-02-29',
                        'name' => 'done' . md5('actions>=1'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-02 23:48:33')->subSeconds($offset)->getDatetime()
                    ], // different period, so should be ignored
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=1'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-02 23:48:33')->subSeconds($offset)->getDatetime()
                    ], // started too early, so should be ignored
                    [
                        'idarchive' => 1,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=2') . '.MyPlugin',
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:10:33')->subSeconds($offset)->getDatetime()
                    ], // partial archive, so should be ignored
                    [
                        'idarchive' => 2,
                        'idsite' => 2,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=2'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:10:33')->subSeconds($offset)->getDatetime()
                    ], // different site, so should be ignored
                    [
                        'idarchive' => 2,
                        'idsite' => 1,
                        'period' => 1,
                        'date1' => '2020-02-02',
                        'date2' => '2020-02-02',
                        'name' => 'done' . md5('actions>=3'),
                        'report' => null,
                        'ts_invalidated' => '2020-02-02 21:00:00',
                        'status' => 1,
                        'ts_started' => Date::factory('2020-02-03 00:10:33')->subSeconds($offset)->getDatetime()
                    ], // should be considered and invalidation skipped
                ],
                [
                    [
                        1,
                        '2020-02-02',
                        'day',
                        false,
                        false,
                        false,
                    ],
                    [
                        1,
                        '2020-02-02',
                        'day',
                        'actions>=1',
                        false,
                        false,
                    ],
                    [
                        1,
                        '2020-02-02',
                        'day',
                        'actions>=2',
                        false,
                        false,
                    ],
                ],
            ];
        }
    }

    /**
     * @dataProvider getArchivingTestData
     */
    public function testCanWeSkipInvalidatingBecauseThereIsAUsablePeriodReturnsExpectedValue(
        string $timezone,
        string $nowDateTime,
        string $dayToArchive,
        string $periodToArchive,
        string $tsArchived,
        int $archiveStatus,
        bool $expected
    ) {
        Rules::setBrowserTriggerArchiving(false);

        Fixture::createWebsite('2019-04-04 03:45:45', 0, false, false, 1, 0, 0, $timezone);

        Date::$now = strtotime($nowDateTime);

        if ($dayToArchive === 'yesterday' || $dayToArchive === 'today') {
            $dateToArchive = Date::factoryInTimezone($dayToArchive, $timezone)->toString();
        } else {
            $dateToArchive = $dayToArchive;
        }
        $period = Factory::build($periodToArchive, $dateToArchive);

        $archiver = new CronArchive();

        $params = new Parameters(new Site(1), $period, new Segment('', [1]));

        $archiveTable = ArchiveTableCreator::getNumericTable($period->getDateStart());
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1, $period::PERIOD_ID, $period->getDateStart()->toString(), $period->getDateEnd()->toString(), 'done', $archiveStatus, $tsArchived
        ]);

        // $skipWhenRunningOrNewEnoughArchiveExists is set to true when running invalidateRecentDate('yesterday');

        $class = new \ReflectionClass(CronArchive::class);
        $method = $class->getMethod('canWeSkipInvalidatingBecauseThereIsAUsablePeriod');
        $method->setAccessible(true);

        $actual = $method->invoke($archiver, $params, $dayToArchive === 'yesterday');
        $this->assertSame($expected, $actual);
    }

    public function getArchivingTestData(): iterable
    {
        $timezones = [
            'UTC-12',
            'America/Caracas', // UTC-4
            'UTC-0.5',
            'UTC',
            'Asia/Kathmandu', // UTC+5:45
            'Australia/Brisbane', // UTC+10
            'UTC+14',
        ];

        foreach ($timezones as $timezone) {
            $offset = Date::getUtcOffset($timezone);

            yield "Invalidating yesterday should not be skipped if an archive for yesterday was built before midnight in site's timezone ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 00:22:00')->subSeconds($offset)->getDatetime(),
                'yesterday',
                'day',
                Date::factory('2020-04-04 23:45:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                false
            ];

            yield "Invalidating yesterday should not be skipped if an archive for yesterday was built some time before midnight in site's timezone ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 06:22:00')->subSeconds($offset)->getDatetime(),
                'yesterday',
                'day',
                Date::factory('2020-04-04 18:25:35')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                false
            ];

            yield "Invalidating yesterday should not be skipped if an archive for yesterday was built long before midnight in site's timezone ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 19:22:00')->subSeconds($offset)->getDatetime(),
                'yesterday',
                'day',
                Date::factory('2020-04-04 09:25:35')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                false
            ];

            yield "Invalidating yesterday should be skipped if an archive for yesterday was built after midnight in site's timezone ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 00:22:00')->subSeconds($offset)->getDatetime(),
                'yesterday',
                'day',
                Date::factory('2020-04-05 00:05:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            yield "Invalidating yesterday should be skipped if an archive for yesterday was built some time after midnight in site's timezone ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 16:22:00')->subSeconds($offset)->getDatetime(),
                'yesterday',
                'day',
                Date::factory('2020-04-05 09:05:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            yield "Invalidating yesterday should be skipped if an archive for yesterday was built long after midnight in site's timezone ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 22:22:00')->subSeconds($offset)->getDatetime(),
                'yesterday',
                'day',
                Date::factory('2020-04-05 19:05:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            yield "Invalidation should be skipped when checking an older date that was archived within ttl, as invalidation will be processed later ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 00:00:00')->subSeconds($offset)->getDatetime(),
                '2020-03-05',
                'day',
                Date::factory('2020-04-04 23:49:44')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            yield "Invalidation should not be skipped when checking an older period that was not archived within ttl ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 04:00:00')->subSeconds($offset)->getDatetime(),
                '2020-03-05',
                'week',
                Date::factory('2020-04-03 23:49:44')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                false
            ];

            yield "Invalidation should be skipped when checking an older period that was archived within ttl, as invalidation will be processed later ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 04:00:00')->subSeconds($offset)->getDatetime(),
                '2020-03-05',
                'week',
                Date::factory('2020-04-05 03:55:44')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            // ttl is defined by time_before_today_archive_considered_outdated (default = 900)
            yield "Invalidating today should be skipped when checking today archive, which is newer than ttl, as invalidation will be processed later ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 19:15:00')->subSeconds($offset)->getDatetime(),
                'today',
                'day',
                Date::factory('2020-04-05 19:05:00')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            // ttl is defined by time_before_today_archive_considered_outdated (default = 900)
            yield "Invalidating today should not be skipped when checking today archive, which is older than ttl ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 19:15:00')->subSeconds($offset)->getDatetime(),
                'today',
                'day',
                Date::factory('2020-04-05 16:05:00')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                false
            ];

            yield "Invalidating current week should be skipped if a recently built archive is valid ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 19:15:00')->subSeconds($offset)->getDatetime(),
                'today',
                'week',
                Date::factory('2020-04-05 19:13:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                true
            ];

            yield "Invalidating current week should also be skipped if a recently built archive is already invalidated ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 19:15:00')->subSeconds($offset)->getDatetime(),
                'today',
                'week',
                Date::factory('2020-04-05 19:13:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_INVALIDATED,
                true
            ];

            yield "Invalidating current week should not be skipped if a recently built archive is older than ttl ($timezone)" => [
                $timezone,
                Date::factory('2020-04-05 19:15:00')->subSeconds($offset)->getDatetime(),
                'today',
                'week',
                Date::factory('2020-04-05 18:28:40')->subSeconds($offset)->getDatetime(),
                ArchiveWriter::DONE_OK,
                false
            ];
        }
    }

    public function testGetColumnNamesFromTable()
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

    public function testWasSegmentCreatedRecently()
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

    public function testSkipSegmentsTodayDoesNotRequestAnySegmentInvalidationsForToday()
    {
        Date::$now = strtotime('2020-09-09 09:00:00');

        Fixture::createWebsite('2014-12-12 00:01:02');

        // track a visit for today, so todays data will get invalidated
        $tracker = Fixture::getTracker(1, '2020-09-09 07:00:00');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        // remove invalidation options created through tracking
        Option::deleteLike('%report_to_invalidate_%');

        Rules::setBrowserTriggerArchiving(false);
        $id1 = SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id2 = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $segments = new Model();
        $segments->updateSegment($id1, array('ts_created' => Date::now()->subHour(300)->getDatetime()));
        $segments->updateSegment($id2, array('ts_created' => Date::now()->subHour(30)->getDatetime()));

        $logger = new FakeLogger();
        $mockInvalidateApi = $this->getMockInvalidateApi();

        $archiver = new CronArchive($logger);
        $archiver->init();
        $archiver->setApiToInvalidateArchivedReport($mockInvalidateApi);
        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setSkipSegmentsForToday(true);
        $archiver->setArchiveFilter($archiveFilter);
        $archiver->shouldArchiveAllSites = true;
        $archiver->init();
        $archiver->run();

        // check that no segment invalidations were requested
        $requestedInvalidations = $mockInvalidateApi->getInvalidations();
        $expectedInvalidations = [
            [
                1,
                Date::now()->toString(),
                'day',
                false,
                false,
                false,
            ],
        ];
        self::assertEquals($expectedInvalidations, $requestedInvalidations);

        self::assertStringContainsString('Will skip segments archiving for today unless they were created recently', $logger->output);
    }

    public function testSkipSegmentsTodayDoesStillRequestSegmentInvalidationsForRecentlyCreatedSegments()
    {
        Date::$now = strtotime('2020-09-09 09:00:00');

        Fixture::createWebsite('2014-12-12 00:01:02');

        // track a visit for today, so todays data will get invalidated
        $tracker = Fixture::getTracker(1, '2020-09-09 07:00:00');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        // remove invalidation options created through tracking
        Option::deleteLike('%report_to_invalidate_%');

        Rules::setBrowserTriggerArchiving(false);
        $id1 = SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id2 = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $segments = new Model();
        $segments->updateSegment($id1, array('ts_created' => Date::now()->subHour(300)->getDatetime()));
        $segments->updateSegment($id2, array('ts_created' => Date::now()->subHour(12)->getDatetime()));

        $logger = new FakeLogger();
        $mockInvalidateApi = $this->getMockInvalidateApi();

        $archiver = new CronArchive($logger);
        $archiver->init();
        $archiver->setApiToInvalidateArchivedReport($mockInvalidateApi);
        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setSkipSegmentsForToday(true);
        $archiver->setArchiveFilter($archiveFilter);
        $archiver->shouldArchiveAllSites = true;
        $archiver->init();
        $archiver->run();

        // check that no segment invalidations were requested
        $requestedInvalidations = $mockInvalidateApi->getInvalidations();
        $expectedInvalidations = [
            [
                1,
                Date::now()->toString(),
                'day',
                false,
                false,
                false,
            ],
            [
                1,
                Date::now()->toString(),
                'day',
                'actions>=2',
                false,
                false,
            ],
        ];
        self::assertEquals($expectedInvalidations, $requestedInvalidations);

        self::assertStringContainsString('Will skip segments archiving for today unless they were created recently', $logger->output);
    }

    public function testInvalidatingYesterdayWillStillRequestSegmentInvalidationsWithSkipSegmentsToday()
    {
        Date::$now = strtotime('2020-08-05 09:00:00');

        Fixture::createWebsite('2014-12-12 00:01:02');

        // track a visit for yesterday, so yesterdays data will get invalidated
        $tracker = Fixture::getTracker(1, '2020-08-04 16:00:00');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        // track a visit for today, so todays data will get invalidated
        $tracker = Fixture::getTracker(1, '2020-08-05 07:00:00');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        // remove invalidation options created through tracking
        Option::deleteLike('%report_to_invalidate_%');

        Rules::setBrowserTriggerArchiving(false);
        $id1 = SegmentAPI::getInstance()->add('foo', 'actions>=1', 1, true, true);
        $id2 = SegmentAPI::getInstance()->add('barb', 'actions>=2', 1, true, true);
        Rules::setBrowserTriggerArchiving(true);

        $segments = new Model();
        $segments->updateSegment($id1, array('ts_created' => Date::now()->subHour(120)->getDatetime()));
        $segments->updateSegment($id2, array('ts_created' => Date::now()->subHour(120)->getDatetime()));

        $logger = new FakeLogger();
        $mockInvalidateApi = $this->getMockInvalidateApi();

        $archiver = new CronArchive($logger);
        $archiver->init();
        $archiver->setApiToInvalidateArchivedReport($mockInvalidateApi);
        $archiveFilter = new CronArchive\ArchiveFilter();
        $archiveFilter->setSkipSegmentsForToday(true);
        $archiver->setArchiveFilter($archiveFilter);
        $archiver->shouldArchiveAllSites = true;
        $archiver->init();
        $archiver->run();

        // check that no segment invalidations were requested
        $requestedInvalidations = $mockInvalidateApi->getInvalidations();
        $expectedInvalidations = [
            [
                1,
                '2020-08-05',
                'day',
                false,
                false,
                false,
            ],
            [
                1,
                '2020-08-04',
                'day',
                false,
                false,
                false,
            ],
            [
                1,
                '2020-08-04',
                'day',
                'actions>=1',
                false,
                false,
            ],
            [
                1,
                '2020-08-04',
                'day',
                'actions>=2',
                false,
                false,
            ],
        ];
        self::assertEquals($expectedInvalidations, $requestedInvalidations);

        self::assertStringContainsString('Will skip segments archiving for today unless they were created recently', $logger->output);
    }

    public function testOutput()
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
Processing invalidation: [idinvalidation = %d, idsite = 1, period = day(2019-12-12 - 2019-12-12), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Processing invalidation: [idinvalidation = %d, idsite = 1, period = day(2019-12-11 - 2019-12-11), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Processing invalidation: [idinvalidation = %d, idsite = 1, period = day(2019-12-10 - 2019-12-10), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-12&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-11&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-10&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = day, date = 2019-12-12, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-11, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-10, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 1, period = week(2019-12-09 - 2019-12-15), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
Processing invalidation: [idinvalidation = %d, idsite = 1, period = day(2019-12-02 - 2019-12-02), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=week&date=2019-12-09&format=json&segment=actions%3E%3D2&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=day&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-09, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Archived website id 1, period = day, date = 2019-12-02, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 1, period = week(2019-12-02 - 2019-12-08), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=week&date=2019-12-02&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = week, date = 2019-12-02, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 1, period = month(2019-12-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=1&period=month&date=2019-12-01&format=json&segment=actions%3E%3D2&trigger=archivephp
Archived website id 1, period = month, date = 2019-12-01, segment = 'actions>=2', 0 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 1, period = year(2019-01-01 - 2019-12-31), name = donee0512c03f7c20af6ef96a8d792c6bb9f, segment = actions>=2].
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

    public function testOutputWithSkipIdSites()
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
Processing invalidation: [idinvalidation = %d, idsite = 2, period = day(2019-12-11 - 2019-12-11), name = done, segment = ].
Processing invalidation: [idinvalidation = %d, idsite = 2, period = day(2019-12-10 - 2019-12-10), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=day&date=2019-12-11&format=json&trigger=archivephp
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=day&date=2019-12-10&format=json&trigger=archivephp
Archived website id 2, period = day, date = 2019-12-11, segment = '', 1 visits found. Time elapsed: %fs
Archived website id 2, period = day, date = 2019-12-10, segment = '', 1 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 2, period = week(2019-12-09 - 2019-12-15), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=week&date=2019-12-09&format=json&trigger=archivephp
Archived website id 2, period = week, date = 2019-12-09, segment = '', 2 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 2, period = month(2019-12-01 - 2019-12-31), name = done, segment = ].
No next invalidated archive.
Starting archiving for ?module=API&method=CoreAdminHome.archiveReports&idSite=2&period=month&date=2019-12-01&format=json&trigger=archivephp
Archived website id 2, period = month, date = 2019-12-01, segment = '', 2 visits found. Time elapsed: %fs
Processing invalidation: [idinvalidation = %d, idsite = 2, period = year(2019-01-01 - 2019-12-31), name = done, segment = ].
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
        $output = array_filter($output, function ($l) {
            return strpos($l, 'Skipping invalidated archive') === false;
        });
        $output = array_filter($output, function ($l) {
            return strpos($l, 'Found archive with intersecting period') === false;
        });
        $output = array_filter($output, function ($l) {
            return strpos($l, 'Found duplicate invalidated archive') === false;
        });
        $output = array_filter($output, function ($l) {
            return strpos($l, 'No usable archive exists') === false;
        });
        $output = array_filter($output, function ($l) {
            return strpos($l, 'Found invalidated archive we can skip (no visits)') === false;
        });
        $output = implode("\n", $output);
        return $output;
    }

    public function testShouldNotStopProcessingWhenOneSiteIsInvalid()
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
                isset($inv['ts_started']) ? $inv['ts_started'] : null,
            ];
            Db::query("INSERT INTO `$table` (idarchive, name, idsite, date1, date2, period, ts_invalidated, report, status, ts_started)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $bind);
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
