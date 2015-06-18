<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\Common;
use Piwik\Db;
use Piwik\Segment;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

/**
 * @group Core
 * @group SegmentTest
 */
class SegmentTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        // setup the access layer (required in Segment contrustor testing if anonymous is allowed to use segments)
        FakeAccess::$superUser = true;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    static public function removeExtraWhiteSpaces($valueToFilter)
    {
        if (is_array($valueToFilter)) {
            foreach ($valueToFilter as $key => $value) {
                $valueToFilter[$key] = self::removeExtraWhiteSpaces($value);
            }
            return $valueToFilter;
        } else {
            return preg_replace('/[\s]+/', ' ', $valueToFilter);
        }
    }

    public function getCommonTestData()
    {
        return array(
            // Normal segment
            array('countryCode==France', array(
                'where' => ' log_visit.location_country = ? ',
                'bind'  => array('France'))),

            // unescape the comma please
            array('countryCode==a\,==', array(
                'where' => ' log_visit.location_country = ? ',
                'bind'  => array('a,=='))),

            // AND, with 2 values rewrites
            array('countryCode==a;visitorType!=returning;visitorType==new', array(
                'where' => ' log_visit.location_country = ? AND ( log_visit.visitor_returning IS NULL OR log_visit.visitor_returning <> ? ) AND log_visit.visitor_returning = ? ',
                'bind'  => array('a', '1', '0'))),

            // OR, with 2 value rewrites
            array('referrerType==search,referrerType==direct', array(
                'where' => ' (log_visit.referer_type = ? OR log_visit.referer_type = ? )',
                'bind'  => array(Common::REFERRER_TYPE_SEARCH_ENGINE,
                                 Common::REFERRER_TYPE_DIRECT_ENTRY))),

            // IS NOT NULL
            array('browserCode==ff;referrerKeyword!=', array(
                'where' => ' log_visit.config_browser_name = ? AND ( log_visit.referer_keyword IS NOT NULL AND (log_visit.referer_keyword <> \'\' OR log_visit.referer_keyword = 0) ) ',
                'bind'  => array('ff')
            )),
            array('referrerKeyword!=,browserCode==ff', array(
                'where' => ' (( log_visit.referer_keyword IS NOT NULL AND (log_visit.referer_keyword <> \'\' OR log_visit.referer_keyword = 0) ) OR log_visit.config_browser_name = ? )',
                'bind'  => array('ff')
            )),

            // IS NULL
            array('browserCode==ff;referrerKeyword==', array(
                'where' => ' log_visit.config_browser_name = ? AND ( log_visit.referer_keyword IS NULL OR log_visit.referer_keyword = \'\' ) ',
                'bind'  => array('ff')
            )),
            array('referrerKeyword==,browserCode==ff', array(
                'where' => ' (( log_visit.referer_keyword IS NULL OR log_visit.referer_keyword = \'\' ) OR log_visit.config_browser_name = ? )',
                'bind'  => array('ff')
            )),

        );
    }

    /**
     * @dataProvider getCommonTestData
     */
    public function testCommon($segment, $expected)
    {
        $select = 'log_visit.idvisit';
        $from = 'log_visit';

        $expected = array(
            'sql'  => '
                SELECT
                    log_visit.idvisit
                FROM
                    ' . Common::prefixTable('log_visit') . ' AS log_visit
                WHERE
                    ' . $expected['where'],
            'bind' => $expected['bind']
        );

        $segment = new Segment($segment, $idSites = array());
        $sql = $segment->getSelectQuery($select, $from, false);

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($sql));

        // calling twice should give same results
        $sql = $segment->getSelectQuery($select, array($from));
        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($sql));

        $this->assertEquals(32, strlen($segment->getHash()));
    }

    public function test_getSelectQuery_whenNoJoin()
    {
        $select = '*';
        $from = 'log_visit';
        $where = 'idsite = ?';
        $bind = array(1);

        $segment = 'customVariableName1==Test;visitorType==new';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                WHERE
                    ( idsite = ? )
                    AND
                    ( log_visit.custom_var_k1 = ? AND log_visit.visitor_returning = ? )",
            "bind" => array(1, 'Test', 0));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinVisitOnAction()
    {
        $select = '*';
        $from = 'log_link_visit_action';
        $where = 'log_link_visit_action.idvisit = ?';
        $bind = array(1);

        $segment = 'customVariablePageName1==Test;visitorType==new';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
                    LEFT JOIN " . Common::prefixTable('log_visit') . " AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
                WHERE
                    ( log_link_visit_action.idvisit = ? )
                    AND
                    ( log_link_visit_action.custom_var_k1 = ? AND log_visit.visitor_returning = ? )",
            "bind" => array(1, 'Test', 0));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinActionOnVisit()
    {
        $select = 'sum(log_visit.visit_total_actions) as nb_actions, max(log_visit.visit_total_actions) as max_actions, sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'customVariablePageName1==Test;visitorType==new';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    sum(log_inner.visit_total_actions) as nb_actions, max(log_inner.visit_total_actions) as max_actions, sum(log_inner.visit_total_time) as sum_visit_length
                FROM
                    (
                SELECT
                    log_visit.visit_total_actions,
                    log_visit.visit_total_time
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                WHERE
                    ( log_visit.idvisit = ? )
                    AND
                    ( log_link_visit_action.custom_var_k1 = ? AND log_visit.visitor_returning = ? )
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
            "bind" => array(1, 'Test', 0));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinConversionOnAction()
    {
        $select = '*';
        $from = 'log_link_visit_action';
        $where = 'log_link_visit_action.idvisit = ?';
        $bind = array(1);

        $segment = 'customVariablePageName1==Test;visitConvertedGoalId==1;customVariablePageName2==Test2';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idlink_va = log_link_visit_action.idlink_va AND log_conversion.idsite = log_link_visit_action.idsite
                WHERE
                    ( log_link_visit_action.idvisit = ? )
                    AND
                    ( log_link_visit_action.custom_var_k1 = ? AND log_conversion.idgoal = ? AND log_link_visit_action.custom_var_k2 = ? )",
            "bind" => array(1, 'Test', 1, 'Test2'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinActionOnConversion()
    {
        $select = '*';
        $from = 'log_conversion';
        $where = 'log_conversion.idvisit = ?';
        $bind = array(1);

        $segment = 'visitConvertedGoalId!=2;customVariablePageName1==Test;visitConvertedGoalId==1';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_conversion') . " AS log_conversion
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_conversion.idlink_va = log_link_visit_action.idlink_va
                WHERE
                    ( log_conversion.idvisit = ? )
                    AND
                    ( ( log_conversion.idgoal IS NULL OR log_conversion.idgoal <> ? ) AND log_link_visit_action.custom_var_k1 = ? AND log_conversion.idgoal = ? )",
            "bind" => array(1, 2, 'Test', 1));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinConversionOnVisit()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'visitConvertedGoalId==1';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_inner.*
                FROM
                    (
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idvisit = log_visit.idvisit
                WHERE
                    ( log_visit.idvisit = ? )
                    AND
                    ( log_conversion.idgoal = ? )
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
            "bind" => array(1, 1));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinConversionOnly()
    {
        $select = 'log_conversion.*';
        $from = 'log_conversion';
        $where = 'log_conversion.idvisit = ?';
        $bind = array(1);

        $segment = 'visitConvertedGoalId==1';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_conversion.*
                FROM
                    " . Common::prefixTable('log_conversion') . " AS log_conversion
                WHERE
                    ( log_conversion.idvisit = ? )
                    AND
                    ( log_conversion.idgoal = ? )",
            "bind" => array(1, 1));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinVisitOnConversion()
    {
        $select = '*';
        $from = 'log_conversion';
        $where = 'log_conversion.idvisit = ?';
        $bind = array(1);

        $segment = 'visitConvertedGoalId==1,visitServerHour==12';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_conversion') . " AS log_conversion
                    LEFT JOIN " . Common::prefixTable('log_visit') . " AS log_visit ON log_conversion.idvisit = log_visit.idvisit
                WHERE
                    ( log_conversion.idvisit = ? )
                    AND
                    ( (log_conversion.idgoal = ? OR HOUR(log_visit.visit_last_action_time) = ? ))",
            "bind" => array(1, 1, 12));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    /**
     * visit is joined on action, then conversion is joined
     * make sure that conversion is joined on action not visit
     */
    public function test_getSelectQuery_whenJoinVisitAndConversionOnAction()
    {
        $select = '*';
        $from = 'log_link_visit_action';
        $where = false;
        $bind = array();

        $segment = 'visitServerHour==12;visitConvertedGoalId==1';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
                    LEFT JOIN " . Common::prefixTable('log_visit') . " AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idlink_va = log_link_visit_action.idlink_va AND log_conversion.idsite = log_link_visit_action.idsite
                WHERE
                     HOUR(log_visit.visit_last_action_time) = ? AND log_conversion.idgoal = ? ",
            "bind" => array(12, 1));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    /**
     * join conversion on visit, then actions
     * make sure actions are joined before conversions
     */
    public function test_getSelectQuery_whenJoinConversionAndActionOnVisit_andPageUrlSet()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'visitConvertedGoalId==1;visitServerHour==12;customVariablePageName1==Test;pageUrl!=';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_inner.*
                FROM
                    (
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idlink_va = log_link_visit_action.idlink_va AND log_conversion.idsite = log_link_visit_action.idsite
                WHERE
                     log_conversion.idgoal = ? AND HOUR(log_visit.visit_last_action_time) = ? AND log_link_visit_action.custom_var_k1 = ?
                      AND (
                            log_link_visit_action.idaction_url IS NOT NULL
                            AND (log_link_visit_action.idaction_url <> ''
                                OR log_link_visit_action.idaction_url = 0)
                            )
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
            "bind" => array(1, 12, 'Test'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    /**
     * Dataprovider for test_bogusSegment_shouldThrowException
     */
    public function getBogusSegments()
    {
        return array(
            array('referrerType==not'),
            array('someRandomSegment==not'),
            array('A=B')
        );
    }

    /**
     * @dataProvider getBogusSegments
     */
    public function test_bogusSegment_shouldThrowException($segment)
    {
        try {
            new Segment($segment, $idSites = array());
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }


    public function test_getSelectQuery_whenLimit_innerQueryShouldHaveLimitAndNoGroupBy()
    {
        $select = 'sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'customVariablePageName1==Test';
        $segment = new Segment($segment, $idSites = array());

        $orderBy = false;
        $groupBy = false;
        $limit = 33;

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy, $limit);

        $expected = array(
            "sql"  => "
                SELECT
                    sum(log_inner.visit_total_time) as sum_visit_length
                FROM
                    (
                SELECT
                    log_visit.visit_total_time
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                WHERE
                    ( log_visit.idvisit = ? )
                    AND
                    ( log_link_visit_action.custom_var_k1 = ? )
                ORDER BY NULL
                LIMIT 33
                    ) AS log_inner
                LIMIT 33",
            "bind" => array(1, 'Test'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenPageUrlExists_asStatementAND()
    {
        $pageUrlFoundInDb = 'example.com/page.html?hello=world';

        $actionIdFoundInDb = $this->insertPageUrlAsAction($pageUrlFoundInDb);

        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'visitServerHour==3;pageUrl==' . urlencode($pageUrlFoundInDb);
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_inner.*
                FROM
                    (
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                WHERE HOUR(log_visit.visit_last_action_time) = ?
                      AND log_link_visit_action.idaction_url = ?
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
            "bind" => array(3, $actionIdFoundInDb));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenPageUrlDoesNotExist_asStatementAND()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'visitServerHour==12;pageUrl==xyz';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                WHERE HOUR(log_visit.visit_last_action_time) = ?
                      AND (1 = 0) ",
            "bind" => array(12));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenPageUrlDoesNotExist_asStatementOR()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'visitServerHour==12,pageUrl==xyz';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                WHERE (HOUR(log_visit.visit_last_action_time) = ?
                      OR (1 = 0) )",
            "bind" => array(12));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenPageUrlDoesNotExist_asBothStatements_OR_AND()
    {
        $pageUrlFoundInDb = 'example.com/found-in-db';
        $actionIdFoundInDb = $this->insertPageUrlAsAction($pageUrlFoundInDb);

        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        /**
         * pageUrl==xyz                              -- Matches none
         * pageUrl!=abcdefg                          -- Matches all
         * pageUrl=@does-not-exist                   -- Matches none
         * pageUrl=='.urlencode($pageUrlFoundInDb)   -- Matches one
         */
        $segment = 'visitServerHour==12,pageUrl==xyz;pageUrl!=abcdefg,pageUrl=@does-not-exist,pageUrl=='.urlencode($pageUrlFoundInDb);
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_inner.*
                FROM
                    (
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                WHERE (HOUR(log_visit.visit_last_action_time) = ?
                        OR (1 = 0))
                      AND ((1 = 1)
                        OR ( log_link_visit_action.idaction_url IN (SELECT idaction FROM log_action WHERE ( name LIKE CONCAT('%', ?, '%') AND type = 1 )) )
                        OR   log_link_visit_action.idaction_url = ? )
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
            "bind" => array(
                12,
                "does-not-exist",
                $actionIdFoundInDb
            ));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

    /**
     * @param $pageUrlFoundInDb
     * @return string
     * @throws Exception
     */
    private function insertPageUrlAsAction($pageUrlFoundInDb)
    {
        TableLogAction::loadIdsAction(array(
            'idaction_url' => array($pageUrlFoundInDb, Action::TYPE_PAGE_URL)
        ));

        $actionIdFoundInDb = Db::fetchOne("SELECT idaction from " . Common::prefixTable('log_action') . " WHERE name = ?", $pageUrlFoundInDb);
        $this->assertNotEmpty($actionIdFoundInDb, "Action $pageUrlFoundInDb was not found in the " . Common::prefixTable('log_action') . " table.");
        return $actionIdFoundInDb;
    }
}
