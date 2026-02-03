<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\Live\Exception\MaxExecutionTimeExceededException;
use Piwik\Plugins\Live\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Integration\SegmentTest;

/**
 * @group Live
 * @group ModelTest
 * @group Plugins
 */
class ModelTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->setSuperUser();
        Fixture::createWebsite('2010-01-01');
    }

    public function testGetStandAndEndDateUsesNowWhenDateOutOfRange()
    {
        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'year', (date('Y') + 1) . '-01-01');

        $validDates = $this->getValidNowDates();

        $this->assertTrue(in_array($dateStart->getDatetime(), $validDates));
        $this->assertTrue(in_array($dateEnd->getDatetime(), $validDates));
        $this->assertNotEquals($dateStart->getDatetime(), $dateEnd->getDatetime());
    }

    public function testGetStandAndEndDateUsesNowWhenEndDateOutOfRange()
    {
        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'year', date('Y') . '-01-01');

        $validDates = $this->getValidNowDates();

        $this->assertEquals(date('Y') . '-01-01 00:00:00', $dateStart->getDatetime());
        $this->assertTrue(in_array($dateEnd->getDatetime(), $validDates));
        $this->assertNotEquals($dateStart->getDatetime(), $dateEnd->getDatetime());
    }

    private function getValidNowDates()
    {
        $now = Date::now();
        $validDates = [$now->getDatetime()];
        $validDates[] = $now->subSeconds(1)->getDatetime();
        $validDates[] = $now->subSeconds(2)->getDatetime();
        $validDates[] = $now->addPeriod(1, 'second')->getDatetime();
        $validDates[] = $now->addPeriod(2, 'second')->getDatetime();

        return $validDates;
    }

    public function testHandleMaxExecutionTimeErrorDoesNotThrowExceptionWhenNotExceededTime()
    {
        self::expectNotToPerformAssertions();

        $db           = Db::get();
        $e            = new \Exception('foo bar baz');
        $sql          = 'SELECT 1';
        $bind         = [];
        $segment      = '';
        $dateStart    = Date::now()->subDay(1);
        $dateEnd      = Date::now();
        $minTimestamp = 1;
        $limit        = 50;
        Model::handleMaxExecutionTimeError($db, $e, $segment, $dateStart, $dateEnd, $minTimestamp, $limit, [$sql, $bind]);
    }

    public function testHandleMaxExecutionTimeErrorWhenTimeIsExceededNoReasonFound()
    {
        $this->expectException(\Piwik\Plugins\Live\Exception\MaxExecutionTimeExceededException::class);
        $this->expectExceptionMessage('Live_QueryMaxExecutionTimeExceeded  Live_QueryMaxExecutionTimeExceededReasonUnknown');

        $db = Db::get();
        $e = new \Exception('[3024] Query execution was interrupted, maximum statement execution time exceeded');
        $sql = 'SELECT 1';
        $bind = array();
        $segment = '';
        $dateStart = Date::now()->subDay(1);
        $dateEnd = Date::now();
        $minTimestamp = null;
        $limit = 50;
        Model::handleMaxExecutionTimeError($db, $e, $segment, $dateStart, $dateEnd, $minTimestamp, $limit, [$sql, $bind]);
    }

    public function testHandleMaxExecutionTimeErrorWhenTimeIsExceededManyReasonsFound()
    {
        $this->expectException(\Piwik\Plugins\Live\Exception\MaxExecutionTimeExceededException::class);
        $this->expectExceptionMessage('Live_QueryMaxExecutionTimeExceeded  Live_QueryMaxExecutionTimeExceededReasonDateRange Live_QueryMaxExecutionTimeExceededReasonSegment Live_QueryMaxExecutionTimeExceededLimit');

        $db = Db::get();
        $e = new \Exception('Query execution was interrupted, maximum statement execution time exceeded');
        $segment = 'userId>=1';
        $dateStart = Date::now()->subDay(10);
        $dateEnd = Date::now();
        $minTimestamp = null;
        $limit = 5000;
        Model::handleMaxExecutionTimeError($db, $e, $segment, $dateStart, $dateEnd, $minTimestamp, $limit, ['param' => 'value']);
    }

    public function testGetLastMinutesCounterForQueryMaxExecutionTime()
    {
        if (SystemTestCase::isMysqli()) {
            $this->markTestSkipped('max_execution_time not supported on mysqli');
            return;
        }
        $this->expectException(MaxExecutionTimeExceededException::class);
        $this->expectExceptionMessage('Live_QueryMaxExecutionTimeExceeded');
        $this->setLowestMaxExecutionTime();

        $this->trackPageView();

        $model = new Model();
        $model->queryAndWhereSleepTestsOnly = true;
        $model->getNumVisits(1, 999999, '');
    }

    public function testQueryAdjacentVisitorIdMaxExecutionTime()
    {
        if (SystemTestCase::isMysqli()) {
            $this->markTestSkipped('max_execution_time not supported on mysqli');
            return;
        }
        $this->expectException(MaxExecutionTimeExceededException::class);
        $this->expectExceptionMessage('Live_QueryMaxExecutionTimeExceeded');
        $this->setLowestMaxExecutionTime();

        $this->trackPageView();
        $model = new Model();
        $model->queryAndWhereSleepTestsOnly = true;
        $model->queryAdjacentVisitorId(1, '1234567812345678', Date::yesterday()->getDatetime(), '', true);
    }

    public function testGetStandAndEndDate()
    {
        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'year', '2018-02-01');

        $this->assertEquals('2018-01-01 00:00:00', $dateStart->getDatetime());
        $this->assertEquals('2019-01-01 00:00:00', $dateEnd->getDatetime());
    }

    public function testIsLookingAtMoreThanOneDayWhenNoDateSet()
    {
        $model = new Model();
        $this->assertTrue($model->isLookingAtMoreThanOneDay(null, null, null));
    }

    public function testIsLookingAtMoreThanOneDayWhenNoStartDateSet()
    {
        $model = new Model();
        $this->assertTrue($model->isLookingAtMoreThanOneDay(null, Date::now(), null));
    }

    public function testIsLookingAtMoreThanOneDayWhenNoStartDateSetAndMinTimestampIsOld()
    {
        $model = new Model();
        $this->assertTrue($model->isLookingAtMoreThanOneDay(null, Date::now(), Date::now()->subDay(5)->getTimestamp()));
    }

    public function testIsLookingAtMoreThanOneDayWhenNoStartDateSetButMinTimestampIsRecent()
    {
        $model = new Model();
        $this->assertFalse($model->isLookingAtMoreThanOneDay(null, Date::now(), Date::now()->subHour(5)->getTimestamp()));
    }

    public function testIsLookingAtMoreThanOneDayWhenNoEndDateIsSetStartDateIsOld()
    {
        $model = new Model();
        $this->assertTrue($model->isLookingAtMoreThanOneDay(Date::now()->subDay(5), null, null));
    }

    public function testIsLookingAtMoreThanOneDayWhenNoEndDateIsSetStartDateIsRecent()
    {
        $model = new Model();
        $this->assertFalse($model->isLookingAtMoreThanOneDay(Date::now()->subHour(5), null, null));
    }

    public function testIsLookingAtMoreThanOneDayWhenStartAndEndDateIsSetOnlyOneDay()
    {
        $model = new Model();
        $this->assertFalse($model->isLookingAtMoreThanOneDay(Date::yesterday()->subDay(1), Date::yesterday(), null));
    }

    public function testIsLookingAtMoreThanOneDayWhenStartAndEndDateIsSetMoreThanOneDay()
    {
        $model = new Model();
        $this->assertTrue($model->isLookingAtMoreThanOneDay(Date::yesterday()->subDay(2), Date::yesterday(), null));
    }

    public function testMakeLogVisitsQueryString()
    {
        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'month', '2010-01-01');
        [$sql, $bind] = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $dateStart,
            $dateEnd,
            $segment = false,
            $offset = 0,
            $limit = 100,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = ' SELECT log_visit.*
                    FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                    WHERE log_visit.idsite in (?)
                      AND log_visit.visit_last_action_time >= ?
                      AND log_visit.visit_last_action_time <= ?
                    ORDER BY log_visit.idsite DESC, log_visit.visit_last_action_time DESC, log_visit.idvisit DESC
                    LIMIT 0, 100';
        $expectedBind = array(
            '1',
            '2010-01-01 00:00:00',
            '2010-02-01 00:00:00',
        );
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedSql), SegmentTest::removeExtraWhiteSpaces($sql));
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedBind), SegmentTest::removeExtraWhiteSpaces($bind));
    }

    public function testMakeLogVisitsQueryStringWithMultipleIdSites()
    {
        Piwik::addAction('Live.API.getIdSitesString', function (&$idSites) {
            $idSites = array(2,3,4);
        });

        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'month', '2010-01-01');
        [$sql, $bind] = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $dateStart,
            $dateEnd,
            $segment = false,
            $offset = 0,
            $limit = 100,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = ' SELECT log_visit.*
                    FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                    WHERE log_visit.idsite in (?,?,?)
                      AND log_visit.visit_last_action_time >= ?
                      AND log_visit.visit_last_action_time <= ?
                    ORDER BY log_visit.visit_last_action_time DESC, log_visit.idvisit DESC
                    LIMIT 0, 100';
        $expectedBind = array(
            '2',
            '3',
            '4',
            '2010-01-01 00:00:00',
            '2010-02-01 00:00:00',
        );
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedSql), SegmentTest::removeExtraWhiteSpaces($sql));
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedBind), SegmentTest::removeExtraWhiteSpaces($bind));
    }

    public function testMakeLogVisitsQueryStringWithOffset()
    {
        $model = new Model();

        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'month', '2010-01-01');
        [$sql, $bind] = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $dateStart,
            $dateEnd,
            $segment = false,
            $offset = 15,
            $limit = 100,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = ' SELECT log_visit.*
                    FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                    WHERE log_visit.idsite in (?)
                      AND log_visit.visit_last_action_time >= ?
                      AND log_visit.visit_last_action_time <= ?
                    ORDER BY log_visit.idsite DESC, log_visit.visit_last_action_time DESC, log_visit.idvisit DESC
                    LIMIT 15, 100';
        $expectedBind = array(
            '1',
            '2010-01-01 00:00:00',
            '2010-02-01 00:00:00',
        );
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedSql), SegmentTest::removeExtraWhiteSpaces($sql));
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedBind), SegmentTest::removeExtraWhiteSpaces($bind));
    }


    public function testMakeLogVisitsQueryStringWhenSegment()
    {
        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'month', '2010-01-01');
        [$sql, $bind] = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $dateStart,
            $dateEnd,
            $segment = 'siteSearchCategory==Test',
            $offset = 10,
            $limit = 100,
            $visitorId = 'abc',
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = ' SELECT log_visit.* 
                        FROM log_visit AS log_visit 
                        LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                        WHERE ( 
                            log_visit.idsite in (?) 
                            AND log_visit.idvisitor = ? 
                            AND log_visit.visit_last_action_time >= ? 
                            AND log_visit.visit_last_action_time <= ? ) 
                            AND ( log_link_visit_action.search_cat = ? ) 
                        GROUP BY log_visit.idvisit 
                        ORDER BY log_visit.idsite DESC, log_visit.visit_last_action_time DESC, log_visit.idvisit DESC
                         LIMIT 10, 100';
        $expectedBind = array(
            '1',
            Common::hex2bin('abc'),
            '2010-01-01 00:00:00',
            '2010-02-01 00:00:00',
            'Test',
        );
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedSql), SegmentTest::removeExtraWhiteSpaces($sql));
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedBind), SegmentTest::removeExtraWhiteSpaces($bind));
    }

    public function testMakeLogVisitsQueryStringAddsMaxExecutionHintIfConfigured()
    {
        $this->setMaxExecutionTime(30);
        Config\DatabaseConfig::setConfigValue('schema', 'Mysql');
        Db\Schema::unsetInstance();

        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'month', '2010-01-01');
        [$sql, $bind] = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $dateStart,
            $dateEnd,
            $segment = false,
            $offset = 0,
            $limit = 100,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = 'SELECT /*+ MAX_EXECUTION_TIME(30000) */
				log_visit.*';

        $this->setMaxExecutionTime(-1);

        $this->assertStringStartsWith($expectedSql, trim($sql));
    }

    public function testMakeLogVisitsQueryStringDoesNotAddsMaxExecutionHintForVisitorIds()
    {
        $this->setMaxExecutionTime(30);

        $model = new Model();
        [$dateStart, $dateEnd] = $model->getStartAndEndDate($idSite = 1, 'month', '2010-01-01');
        [$sql, $bind] = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $dateStart,
            $dateEnd,
            $segment = false,
            $offset = 0,
            $limit = 100,
            $visitorId = '1234567812345678',
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = 'SELECT
				log_visit.*';

        $this->setMaxExecutionTime(-1);

        $this->assertStringStartsWith($expectedSql, trim($sql));
    }

    public function testSplitDatesIntoMultipleQueriesNotMoreThanADayUsesOnlyOneQuery()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-01-02 00:00:00', $limit = 5, $offset = 0);

        $this->assertEquals(array('2010-01-01 00:00:00 2010-01-02 00:00:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesNotMoreThanADayUsesOnlyOneQueryDesc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-01-02 00:00:00', $limit = 5, $offset = 0, 'asc');

        $this->assertEquals(array('2010-01-01 00:00:00 2010-01-02 00:00:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanADayLessThanAWeek()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-01-02 00:01:00', $limit = 5, $offset = 0);

        $this->assertEquals(array('2010-01-01 00:01:00 2010-01-02 00:01:00', '2010-01-01 00:00:00 2010-01-01 00:00:59'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanADayLessThanAWeekAsc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-01-02 00:01:00', $limit = 5, $offset = 0, 'asc');

        $this->assertEquals(array('2010-01-01 00:00:00 2010-01-01 23:59:59', '2010-01-02 00:00:00 2010-01-02 00:01:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanAWeekLessThanMonth()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-01-20 04:01:00', $limit = 5, $offset = 0);

        $this->assertEquals(array('2010-01-19 04:01:00 2010-01-20 04:01:00', '2010-01-12 04:01:00 2010-01-19 04:00:59', '2010-01-01 00:00:00 2010-01-12 04:00:59'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanAWeekLessThanMonthAsc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-01-20 04:01:00', $limit = 5, $offset = 0, 'asc');

        $this->assertEquals(array('2010-01-01 00:00:00 2010-01-01 23:59:59', '2010-01-02 00:00:00 2010-01-08 23:59:59', '2010-01-09 00:00:00 2010-01-20 04:01:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanMonthLessThanYear()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-02-20 04:01:00', $limit = 5, $offset = 0);

        $this->assertEquals(array('2010-02-19 04:01:00 2010-02-20 04:01:00', '2010-02-12 04:01:00 2010-02-19 04:00:59', '2010-01-13 04:01:00 2010-02-12 04:00:59', '2010-01-01 00:00:00 2010-01-13 04:00:59'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanMonthLessThanYearAsc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2010-02-20 04:01:00', $limit = 5, $offset = 0, 'asc');

        $this->assertEquals(array('2010-01-01 00:00:00 2010-01-01 23:59:59', '2010-01-02 00:00:00 2010-01-08 23:59:59', '2010-01-09 00:00:00 2010-02-07 23:59:59', '2010-02-08 00:00:00 2010-02-20 04:01:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanYear()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2012-02-20 04:01:00', $limit = 5, $offset = 0);

        $this->assertEquals(array('2012-02-19 04:01:00 2012-02-20 04:01:00', '2012-02-12 04:01:00 2012-02-19 04:00:59', '2012-01-13 04:01:00 2012-02-12 04:00:59', '2011-01-01 04:01:00 2012-01-13 04:00:59', '2010-01-01 00:00:00 2011-01-01 04:00:59'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanYearAsc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2012-02-20 04:01:00', $limit = 5, $offset = 0, 'asc');

        $this->assertEquals(array('2010-01-01 00:00:00 2010-01-01 23:59:59', '2010-01-02 00:00:00 2010-01-08 23:59:59', '2010-01-09 00:00:00 2010-02-07 23:59:59', '2010-02-08 00:00:00 2011-02-07 23:59:59', '2011-02-08 00:00:00 2012-02-20 04:01:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanYearWithOffsetUsesLessQueries()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2012-02-20 04:01:00', $limit = 5, $offset = 5);

        $this->assertEquals(array('2012-02-19 04:01:00 2012-02-20 04:01:00', '2012-02-12 04:01:00 2012-02-19 04:00:59', '2010-01-01 00:00:00 2012-02-12 04:00:59'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanYearWithOffsetUsesLessQueriesAsc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 04:01:00', '2012-02-20 00:00:00', $limit = 5, $offset = 5, 'asc');

        $this->assertEquals(array('2010-01-01 04:01:00 2010-01-02 04:00:59', '2010-01-02 04:01:00 2010-01-09 04:00:59', '2010-01-09 04:01:00 2012-02-20 00:00:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanYearNoLimitDoesntUseMultipleQueries()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 00:00:00', '2012-02-20 04:01:00', $limit = 0, $offset = 0);

        $this->assertEquals(array('2010-01-01 00:00:00 2012-02-20 04:01:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesMoreThanYearNoLimitDoesntUseMultipleQueriesAsc()
    {
        $dates = $this->splitDatesIntoMultipleQueries('2010-01-01 04:01:00', '2012-02-20 00:00:00', $limit = 0, $offset = 0, 'asc');

        $this->assertEquals(array('2010-01-01 04:01:00 2012-02-20 00:00:00'), $dates);
    }

    public function testSplitDatesIntoMultipleQueriesNoStartDate()
    {
        $dates = $this->splitDatesIntoMultipleQueries(false, '2012-02-20 04:01:00', $limit = 5, $offset = 0);

        $this->assertEquals(array('2012-02-19 04:01:00 2012-02-20 04:01:00', '2012-02-12 04:01:00 2012-02-19 04:00:59', '2012-01-13 04:01:00 2012-02-12 04:00:59', '2011-01-01 04:01:00 2012-01-13 04:00:59', ' 2011-01-01 04:00:59'), $dates);
    }

    private function splitDatesIntoMultipleQueries($startDate, $endDate, $limit, $offset, $order = 'desc')
    {
        if ($startDate) {
            $startDate = Date::factory($startDate);
        }
        if ($endDate) {
            $endDate = Date::factory($endDate);
        }
        $model = new Model();
        $queries = $model->splitDatesIntoMultipleQueries($startDate, $endDate, $limit, $offset, $order);

        return array_map(function ($query) {
            return ($query[0] ? $query[0]->getDatetime() : '') . ' ' . ($query[1] ? $query[1]->getDatetime() : '');
        }, $queries);
    }

    public function testQueryLogVisitsSkipsOffsetAcrossSplitDateRanges()
    {
        // build visits across 3 days so the query will be split into multiple ranges
        $this->trackVisitAtTime('2010-01-01 03:00:00');
        $this->trackVisitAtTime('2010-01-01 06:10:00');
        $this->trackVisitAtTime('2010-01-01 09:20:00');
        $this->trackVisitAtTime('2010-01-02 03:00:00');
        $this->trackVisitAtTime('2010-01-02 06:10:00');
        $this->trackVisitAtTime('2010-01-03 03:00:00');
        $this->trackVisitAtTime('2010-01-03 06:10:00');
        $this->trackVisitAtTime('2010-01-03 09:20:00');

        $this->assertEquals(8, $this->countVisitsBetween('2010-01-01 00:00:00', '2010-01-04 00:00:00'));

        $model = new Model();

        // sanity check: without offset we see all visits
        $allVisits = $model->queryLogVisits(
            1,
            'range',
            '2010-01-01,2010-01-03',
            $segment = '',
            $offset = 0,
            $limit = 10,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );
        $this->assertCount(8, $allVisits);

        $visits = $model->queryLogVisits(
            1,
            'range',
            '2010-01-01,2010-01-03',
            $segment = '',
            $offset = 6,
            $limit = 2,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );

        $this->assertCount(2, $visits);
        $this->assertEquals('2010-01-01 06:10:00', $visits[0]['visit_last_action_time']);
        $this->assertEquals('2010-01-01 03:00:00', $visits[1]['visit_last_action_time']);
    }

    public function testQueryLogVisitsOffsetAcrossSplitDateRangesAscending()
    {
        $this->trackVisitAtTime('2010-02-01 03:00:00');
        $this->trackVisitAtTime('2010-02-01 06:10:00');
        $this->trackVisitAtTime('2010-02-01 09:20:00');
        $this->trackVisitAtTime('2010-02-02 03:00:00');
        $this->trackVisitAtTime('2010-02-02 06:10:00');
        $this->trackVisitAtTime('2010-02-03 03:00:00');
        $this->trackVisitAtTime('2010-02-03 06:10:00');
        $this->trackVisitAtTime('2010-02-03 09:20:00');

        $this->assertEquals(8, $this->countVisitsBetween('2010-02-01 00:00:00', '2010-02-04 00:00:00'));

        $model = new Model();

        $visits = $model->queryLogVisits(
            1,
            'range',
            '2010-02-01,2010-02-03',
            $segment = '',
            $offset = 6,
            $limit = 2,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'asc'
        );

        $this->assertCount(2, $visits);
        $this->assertEquals('2010-02-03 06:10:00', $visits[0]['visit_last_action_time']);
        $this->assertEquals('2010-02-03 09:20:00', $visits[1]['visit_last_action_time']);
    }

    public function testQueryLogVisitsOffsetAcrossYearRange()
    {
        $dates = [
            '2010-01-01 03:00:00',
            '2010-02-01 03:00:00',
            '2010-03-01 03:00:00',
            '2010-04-01 03:00:00',
            '2010-05-01 03:00:00',
            '2010-06-01 03:00:00',
            '2010-07-01 03:00:00',
            '2010-08-01 03:00:00',
            '2010-09-01 03:00:00',
            '2010-10-01 03:00:00',
            '2010-11-01 03:00:00',
            '2010-12-01 03:00:00',
            '2011-01-01 03:00:00',
        ];

        foreach ($dates as $dateTime) {
            $this->trackVisitAtTime($dateTime);
        }

        $this->assertEquals(13, $this->countVisitsBetween('2010-01-01 00:00:00', '2011-02-01 00:00:00'));

        $model = new Model();
        $visits = $model->queryLogVisits(
            1,
            'range',
            '2010-01-01,2011-01-01',
            $segment = '',
            $offset = 10,
            $limit = 2,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );

        $this->assertCount(2, $visits);
        $this->assertEquals('2010-03-01 03:00:00', $visits[0]['visit_last_action_time']);
        $this->assertEquals('2010-02-01 03:00:00', $visits[1]['visit_last_action_time']);
    }

    public function testQueryLogVisitsOffsetBeyondTotalReturnsEmpty()
    {
        $this->trackVisitAtTime('2010-04-01 03:00:00');
        $this->trackVisitAtTime('2010-04-01 06:10:00');
        $this->trackVisitAtTime('2010-04-02 03:00:00');

        $this->assertEquals(3, $this->countVisitsBetween('2010-04-01 00:00:00', '2010-04-03 00:00:00'));

        $model = new Model();
        $visits = $model->queryLogVisits(
            1,
            'range',
            '2010-04-01,2010-04-02',
            $segment = '',
            $offset = 10,
            $limit = 5,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );

        $this->assertSame([], $visits);
    }

    public function testQueryLogVisitsOffsetWeekPeriod()
    {
        $this->trackVisitAtTime('2010-01-05 01:00:00');
        $this->trackVisitAtTime('2010-01-06 01:00:00');
        $this->trackVisitAtTime('2010-01-07 01:00:00');

        $this->assertEquals(3, $this->countVisitsBetween('2010-01-04 00:00:00', '2010-01-08 00:00:00'));

        $model = new Model();
        $visits = $model->queryLogVisits(
            1,
            'week',
            '2010-01-05',
            $segment = '',
            $offset = 1,
            $limit = 1,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );

        $this->assertCount(1, $visits);
        $this->assertEquals('2010-01-06 01:00:00', $visits[0]['visit_last_action_time']);
    }

    public function testQueryLogVisitsOffsetMonthPeriod()
    {
        $this->trackVisitAtTime('2010-06-05 01:00:00');
        $this->trackVisitAtTime('2010-06-10 01:00:00');
        $this->trackVisitAtTime('2010-06-20 01:00:00');
        $this->trackVisitAtTime('2010-06-25 01:00:00');

        $this->assertEquals(4, $this->countVisitsBetween('2010-06-01 00:00:00', '2010-07-01 00:00:00'));

        $model = new Model();
        $visits = $model->queryLogVisits(
            1,
            'month',
            '2010-06-15',
            $segment = '',
            $offset = 2,
            $limit = 1,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );

        $this->assertCount(1, $visits);
        $this->assertEquals('2010-06-10 01:00:00', $visits[0]['visit_last_action_time']);
    }

    public function testQueryLogVisitsOffsetYearPeriod()
    {
        $this->trackVisitAtTime('2011-01-01 03:00:00');
        $this->trackVisitAtTime('2011-03-01 03:00:00');
        $this->trackVisitAtTime('2011-06-01 03:00:00');
        $this->trackVisitAtTime('2011-09-01 03:00:00');
        $this->trackVisitAtTime('2011-12-01 03:00:00');

        $this->assertEquals(5, $this->countVisitsBetween('2011-01-01 00:00:00', '2012-01-01 00:00:00'));

        $model = new Model();
        $visits = $model->queryLogVisits(
            1,
            'year',
            '2011-01-01',
            $segment = '',
            $offset = 3,
            $limit = 2,
            $visitorId = false,
            $minTimestamp = false,
            $filterSortOrder = 'desc'
        );

        $this->assertCount(2, $visits);
        $this->assertEquals('2011-03-01 03:00:00', $visits[0]['visit_last_action_time']);
        $this->assertEquals('2011-01-01 03:00:00', $visits[1]['visit_last_action_time']);
    }

    protected function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }

    private function setLowestMaxExecutionTime(): void
    {
        $this->setMaxExecutionTime(0.001);
    }

    private function setMaxExecutionTime($time): void
    {
        $config = Config::getInstance();
        $general = $config->General;
        $general['live_query_max_execution_time'] = $time;
        $config->General = $general;
    }

    private function trackPageView(): void
    {
        // Needed for the tests that may execute a sleep() to test max execution time. Otherwise if the table is empty
        // the sleep would not be executed making the tests fail randomly
        $t = Fixture::getTracker(1, Date::now()->getDatetime(), $defaultInit = true);
        $t->setTokenAuth(Fixture::getTokenAuth());
        $t->setVisitorId(substr(sha1('X4F66G776HGI'), 0, 16));
        $t->doTrackPageView('foo');
    }

    private function trackVisitAtTime(string $dateTime): void
    {
        $t = Fixture::getTracker(1, $dateTime, $defaultInit = true);
        $t->setTokenAuth(Fixture::getTokenAuth());
        $t->setNewVisitorId();
        $t->setForceVisitDateTime($dateTime);
        $t->setUrl('http://example.org/' . str_replace([' ', ':'], '-', $dateTime));
        Fixture::checkResponse($t->doTrackPageView('Visit at ' . $dateTime));
    }

    private function countVisitsBetween(string $startDate, string $endDate): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE visit_last_action_time >= ? AND visit_last_action_time <= ? AND idsite = ?',
            [$startDate, $endDate, 1]
        );
    }
}
