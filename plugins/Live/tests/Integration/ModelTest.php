<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Live\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Integration\SegmentTest;

/**
 * @group Live
 * @group ModelTest
 * @group Plugins
 */
class ModelTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setSuperUser();
        Fixture::createWebsite('2010-01-01');
    }

    public function test_makeLogVisitsQueryString()
    {
        $model = new Model();
        list($sql, $bind) = $model->makeLogVisitsQueryString(
                $idSite = 1,
                $period = 'month',
                $date = '2010-01-01',
                $segment = false,
                $offset = 0,
                $limit = 100,
                $visitorId = false,
                $minTimestamp = false,
                $filterSortOrder = false
        );
        $expectedSql = ' SELECT SQL_CALC_FOUND_ROWS sub.* FROM
                (
                    SELECT log_visit.*
                    FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                    WHERE log_visit.idsite in (?)
                      AND log_visit.visit_last_action_time >= ?
                      AND log_visit.visit_last_action_time <= ?
                    ORDER BY idsite DESC, visit_last_action_time DESC
                    LIMIT 0, 100
                 ) AS sub
                 GROUP BY sub.idvisit
                 ORDER BY sub.visit_last_action_time DESC
                 LIMIT 100
        ';
        $expectedBind = array(
            '1',
            '2010-01-01 00:00:00',
            '2010-02-01 00:00:00',
        );
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedSql), SegmentTest::removeExtraWhiteSpaces($sql));
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedBind), SegmentTest::removeExtraWhiteSpaces($bind));
    }

    public function test_makeLogVisitsQueryString_withMultipleIdSites()
    {
        Piwik::addAction('Live.API.getIdSitesString', function (&$idSites) {
            $idSites = array(2,3,4);
        });

        $model = new Model();
        list($sql, $bind) = $model->makeLogVisitsQueryString(
                $idSite = 1,
                $period = 'month',
                $date = '2010-01-01',
                $segment = false,
                $offset = 0,
                $limit = 100,
                $visitorId = false,
                $minTimestamp = false,
                $filterSortOrder = false
        );
        $expectedSql = ' SELECT SQL_CALC_FOUND_ROWS sub.* FROM
                (
                    SELECT log_visit.*
                    FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                    WHERE log_visit.idsite in (?,?,?)
                      AND log_visit.visit_last_action_time >= ?
                      AND log_visit.visit_last_action_time <= ?
                    ORDER BY visit_last_action_time DESC
                    LIMIT 0, 100
                 ) AS sub
                 GROUP BY sub.idvisit
                 ORDER BY sub.visit_last_action_time DESC
                 LIMIT 100
        ';
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

    public function test_makeLogVisitsQueryStringWithOffset()
    {
        $model = new Model();
        list($sql, $bind) = $model->makeLogVisitsQueryString(
                $idSite = 1,
                $period = 'month',
                $date = '2010-01-01',
                $segment = false,
                $offset = 15,
                $limit = 100,
                $visitorId = false,
                $minTimestamp = false,
                $filterSortOrder = false
        );
        $expectedSql = ' SELECT SQL_CALC_FOUND_ROWS sub.* FROM
                (
                    SELECT log_visit.*
                    FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                    WHERE log_visit.idsite in (?)
                      AND log_visit.visit_last_action_time >= ?
                      AND log_visit.visit_last_action_time <= ?
                    ORDER BY idsite DESC, visit_last_action_time DESC
                    LIMIT 15, 100
                 ) AS sub
                 GROUP BY sub.idvisit
                 ORDER BY sub.visit_last_action_time DESC
                 LIMIT 100
        ';
        $expectedBind = array(
            '1',
            '2010-01-01 00:00:00',
            '2010-02-01 00:00:00',
        );
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedSql), SegmentTest::removeExtraWhiteSpaces($sql));
        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expectedBind), SegmentTest::removeExtraWhiteSpaces($bind));
    }


    public function test_makeLogVisitsQueryString_whenSegment()
    {
        $model = new Model();
        list($sql, $bind) = $model->makeLogVisitsQueryString(
            $idSite = 1,
            $period = 'month',
            $date = '2010-01-01',
            $segment = 'customVariablePageName1==Test',
            $offset = 10,
            $limit = 100,
            $visitorId = 'abc',
            $minTimestamp = false,
            $filterSortOrder = false
        );
        $expectedSql = ' SELECT SQL_CALC_FOUND_ROWS sub.* FROM
                (

                    SELECT log_inner.*
                    FROM (
                        SELECT log_visit.*
                        FROM ' . Common::prefixTable('log_visit') . ' AS log_visit
                          LEFT JOIN ' . Common::prefixTable('log_link_visit_action') . ' AS log_link_visit_action
                          ON log_link_visit_action.idvisit = log_visit.idvisit
                        WHERE ( log_visit.idsite in (?)
                          AND log_visit.idvisitor = ?
                          AND log_visit.visit_last_action_time >= ?
                          AND log_visit.visit_last_action_time <= ? )
                          AND ( log_link_visit_action.custom_var_k1 = ? )
                        ORDER BY idsite DESC, visit_last_action_time DESC
                        LIMIT 10, 1000
                        ) AS log_inner
                    ORDER BY idsite DESC, visit_last_action_time DESC
                 ) AS sub
                 GROUP BY sub.idvisit
                 ORDER BY sub.visit_last_action_time DESC
                 LIMIT 100
        ';
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

    protected function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}