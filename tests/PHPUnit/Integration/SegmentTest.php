<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\LogTable;
use Piwik\Tracker\TableLogAction;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;

/**
 * @group Core
 * @group Segment
 */
class SegmentTest extends IntegrationTestCase
{
    private $exampleSegment = 'visitCount>=1';

    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer (required in Segment constructor testing if anonymous is allowed to use segments)
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2015-01-01 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 1);
        self::$fixture->getTestEnvironment()->save();
    }

    public function test_getHash_returnsCorrectHashWhenDefinitionIsFromGetStringFromSegmentTableDefinition()
    {
        // definition is encoded as it would be in the URL
        $idSegment = API::getInstance()->add('test segment', 'pageUrl%3D%3Dhttps%25253A%25252F%25252Fserenity.org%25252Fparticipate%25252F');
        $segmentInfo = API::getInstance()->get($idSegment);

        $segment = new Segment($segmentInfo['definition'], []);

        $hash = $segment->getHash();
        $this->assertEquals($segmentInfo['hash'], $hash);

        $segmentStringFromObject = $segment->getOriginalString();
        $segment2 = new Segment($segmentStringFromObject, []);

        $hash = $segment2->getHash();
        $this->assertEquals($segmentInfo['hash'], $hash);
    }

    static public function removeExtraWhiteSpaces($valueToFilter)
    {
        if (is_array($valueToFilter)) {
            return array_map([self::class, 'removeExtraWhiteSpaces'], $valueToFilter);
        } else {
            $result = trim(preg_replace('/[\s]+/', ' ', $valueToFilter));
            $result = preg_replace('/\s*([()])\s*/', '$1', $result);
            return $result;
        }
    }

    public function getCommonTestData()
    {
        $encodedComplexValue = urlencode(urlencode('s#2&#--_*+?#  #5"\'&<>.22,3'));
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
                'where' => ' log_visit.config_browser_name = ? AND ( log_visit.referer_keyword IS NOT NULL AND log_visit.referer_keyword <> \'\' AND log_visit.referer_keyword <> \'0\' ) ',
                'bind'  => array('ff')
            )),
            array('referrerKeyword!=,browserCode==ff', array(
                'where' => ' (( log_visit.referer_keyword IS NOT NULL AND log_visit.referer_keyword <> \'\' AND log_visit.referer_keyword <> \'0\' ) OR log_visit.config_browser_name = ? )',
                'bind'  => array('ff')
            )),

            // IS NULL
            array('browserCode==ff;referrerKeyword==', array(
                'where' => ' log_visit.config_browser_name = ? AND ( log_visit.referer_keyword IS NULL OR log_visit.referer_keyword = \'\' OR log_visit.referer_keyword = \'0\' ) ',
                'bind'  => array('ff')
            )),
            array('referrerKeyword==,browserCode==ff', array(
                'where' => ' (( log_visit.referer_keyword IS NULL OR log_visit.referer_keyword = \'\' OR log_visit.referer_keyword = \'0\' ) OR log_visit.config_browser_name = ? )',
                'bind'  => array('ff')
            )),

            array(urlencode('browserCode!=' . $encodedComplexValue . ',browserCode==' . $encodedComplexValue . ';browserCode!=' . $encodedComplexValue), [
                'where' => ' (( log_visit.config_browser_name IS NULL OR log_visit.config_browser_name <> ? ) OR log_visit.config_browser_name = ?) AND ( log_visit.config_browser_name IS NULL OR log_visit.config_browser_name <> ? ) ',
                'bind' => [
                    's#2&#--_*+?#  #5"\'&<>.22,3',
                    's#2&#--_*+?#  #5"\'&<>.22,3',
                    's#2&#--_*+?#  #5"\'&<>.22,3',
                ],
            ])
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
        $this->assertQueryDoesNotFail($sql);

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($sql));

        // calling twice should give same results
        $sql = $segment->getSelectQuery($select, array($from));
        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($sql));

        $this->assertEquals(32, strlen($segment->getHash()));
    }

    /**
     * @return iterable<string, array{string, string, array{where: string, bind: string}}>
     */
    public function getCommonSubqueryTestData(): iterable
    {
        $encodedValueOr = urlencode(urlencode('a,b'));
        $encodedValueAnd = urlencode(urlencode('a;b'));
        $escapedValueOr = urlencode(urlencode('a\,b'));
        $escapedValueAnd = urlencode(urlencode('a\;b'));

        $segmentFrom = '2020-02-02 02:00:00';

        $whereSingle = '(log_visit.idvisit NOT IN(SELECT log_visit.idvisit FROM log_visit AS log_visit LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit WHERE(log_visit.visit_last_action_time >= ?)AND(log_link_visit_action.idaction_name = ?)))';
        $whereMultiAnd = '(log_visit.idvisit NOT IN(SELECT log_visit.idvisit FROM log_visit AS log_visit LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit WHERE(log_visit.visit_last_action_time >= ?)AND((log_link_visit_action.idaction_name = ? OR log_link_visit_action.idaction_name = ?))))';
        $whereMultiOr = '(log_visit.idvisit NOT IN(SELECT log_visit.idvisit FROM log_visit AS log_visit LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit WHERE(log_visit.visit_last_action_time >= ?)AND(log_link_visit_action.idaction_name = ? AND log_link_visit_action.idaction_name = ?)))';

        yield 'normal segment' => [
            'pageTitle!=a',
            $segmentFrom,
            [
                'where' => $whereSingle,
                'bind' => [$segmentFrom, '1'],
            ],
        ];

        yield 'segment with AND in value' => [
            'pageTitle!=' . $encodedValueAnd,
            $segmentFrom,
            [
                'where' => $whereSingle,
                'bind' => [$segmentFrom, '3'],
            ],
        ];

        yield 'segment with AND in value, already escaped' => [
            'pageTitle!=' . $escapedValueAnd,
            $segmentFrom,
            [
                'where' => $whereSingle,
                'bind' => [$segmentFrom, '3'],
            ],
        ];

        yield 'segment with OR in value' => [
            'pageTitle!=' . $encodedValueOr,
            $segmentFrom,
            [
                'where' => $whereSingle,
                'bind' => [$segmentFrom, '4'],
            ],
        ];

        yield 'segment with OR in value, already escaped' => [
            'pageTitle!=' . $escapedValueOr,
            $segmentFrom,
            [
                'where' => $whereSingle,
                'bind' => [$segmentFrom, '4'],
            ],
        ];

        yield 'segment with two values, AND operator' => [
            'pageTitle!=a;pageTitle!=b',
            $segmentFrom,
            [
                'where' => $whereMultiAnd,
                'bind' => [$segmentFrom, '1', '2'],
            ],
        ];

        yield 'segment with two values, OR operator' => [
            'pageTitle!=a,pageTitle!=b',
            $segmentFrom,
            [
                'where' => $whereMultiOr,
                'bind' => [$segmentFrom, '1', '2'],
            ],
        ];

        yield 'mixed operator in value and two segments' => [
            'pageTitle!=' . $encodedValueAnd . ';pageTitle!=' . $encodedValueOr,
            $segmentFrom,
            [
                'where' => $whereMultiAnd,
                'bind' => [$segmentFrom, '3', '4'],
            ],
        ];

        yield 'mixed operator in value and two segments, already escaped' => [
            'pageTitle!=' . $escapedValueAnd . ',pageTitle!=' . $escapedValueOr,
            $segmentFrom,
            [
                'where' => $whereMultiOr,
                'bind' => [$segmentFrom, '3', '4'],
            ],
        ];
    }

    /**
     * @dataProvider getCommonSubqueryTestData
     *
     * @param array{where: string, bind: string} $expected
     */
    public function testCommonSubquery(string $segment, string $segmentFrom, array $expected): void
    {
        $this->insertPageUrlAsAction('a', 'idaction_name', Action::TYPE_PAGE_TITLE);
        $this->insertPageUrlAsAction('b', 'idaction_name', Action::TYPE_PAGE_TITLE);
        $this->insertPageUrlAsAction('a;b', 'idaction_name', Action::TYPE_PAGE_TITLE);
        $this->insertPageUrlAsAction('a,b', 'idaction_name', Action::TYPE_PAGE_TITLE);

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

        $segment = new Segment($segment, $idSites = array(), Date::factory($segmentFrom));
        $sql = $segment->getSelectQuery($select, $from, false);
        $this->assertQueryDoesNotFail($sql);

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

        $segment = 'deviceBrand==Apple;visitorType==new';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                WHERE
                    ( idsite = ? )
                    AND
                    ( log_visit.config_device_brand = ? AND log_visit.visitor_returning = ? )",
            "bind" => array(1, 'AP', 0));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinVisitOnLogLinkVisitAction()
    {
        $select = '*';
        $from = 'log_link_visit_action';
        $where = 'log_link_visit_action.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test;visitorType==new';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

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
                    ( log_link_visit_action.search_cat = ? AND log_visit.visitor_returning = ? )",
            "bind" => array(1, 'Test', 0));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinActionOnVisit()
    {
        $select = 'sum(log_visit.visit_total_actions) as nb_actions, max(log_visit.visit_total_actions) as max_actions, sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test;visitorType==new';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

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
                    ( log_link_visit_action.search_cat = ? AND log_visit.visitor_returning = ? )
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
            "bind" => array(1, 'Test', 0));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinConversionOnLogLinkVisitAction()
    {
        $select = '*';
        $from = 'log_link_visit_action';
        $where = 'log_link_visit_action.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test;visitConvertedGoalId==1;siteSearchCount==5';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idvisit = log_link_visit_action.idvisit
                WHERE
                    ( log_link_visit_action.idvisit = ? )
                    AND
                    ( log_link_visit_action.search_cat = ? AND log_conversion.idgoal = ? AND log_link_visit_action.search_count = ? )",
            "bind" => array(1, 'Test', 1, 5));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinActionOnConversion()
    {
        $select = '*';
        $from = 'log_conversion';
        $where = 'log_conversion.idvisit = ?';
        $bind = array(1);

        $segment = 'visitConvertedGoalId!=2;siteSearchCategory==Test;visitConvertedGoalId==1';
        $segment = new Segment($segment, $idSites = array(), Date::factory('2020-02-02 02:00:00'));

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $logVisit = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logConversionTable = Common::prefixTable('log_conversion');

        $expected = array(
            "sql"  => "
                SELECT *
                  FROM $logConversionTable AS log_conversion
             LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action
                    ON log_link_visit_action.idvisit = log_conversion.idvisit
             LEFT JOIN $logVisit AS log_visit ON log_visit.idvisit = log_conversion.idvisit
                 WHERE (log_conversion.idvisit = ?)
                   AND (log_link_visit_action.search_cat = ?
                       AND log_conversion.idgoal = ?
                       AND (
                           log_visit.idvisit NOT IN (
                                SELECT log_visit.idvisit
                                  FROM $logVisit AS log_visit
                             LEFT JOIN log_conversion AS log_conversion
                                    ON log_conversion.idvisit = log_visit.idvisit
                                 WHERE (log_visit.visit_last_action_time >= ?) AND (log_conversion.idgoal = ?)
                             )
                       )
                   )",
            "bind" => array(1, 'Test', 1, '2020-02-02 02:00:00', 2));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenSegmentHasComplexSqlExpression()
    {
        $select = '*';
        $from = 'log_conversion';
        $where = '';
        $bind = [];

        $segment = 'customSegment==2';
        $segment = new Segment($segment, $idSites = []);

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $expected = [
            'sql' => '
                SELECT
                    *
                FROM ' . Common::prefixTable('log_conversion') . ' AS log_conversion
                    LEFT JOIN ' . Common::prefixTable('log_visit') . ' AS log_visit ON log_visit.idvisit = log_conversion.idvisit
                WHERE (UNIX_TIMESTAMP(log_visit.visit_first_action_time) - log_visit.visitor_seconds_since_first) = ? ',
            'bind' => [2],
        ];

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
        $this->assertQueryDoesNotFail($query);

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
        $this->assertQueryDoesNotFail($query);

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
        $this->assertQueryDoesNotFail($query);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_conversion') . " AS log_conversion
                    LEFT JOIN " . Common::prefixTable('log_visit') . " AS log_visit ON log_visit.idvisit = log_conversion.idvisit
                WHERE
                    ( log_conversion.idvisit = ? )
                    AND
                    ( (log_conversion.idgoal = ? OR HOUR(log_visit.visit_last_action_time) = ? ))",
            "bind" => array(1, 1, 12));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinLogLinkVisitActionOnActionOnVisit_WithSameTableAlias()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_link_visit_action.custom_dimension_1,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `13`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`';
        $from  = array(
            'log_link_visit_action',
            array('table' => 'log_visit', 'joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit'),
            array('table' => 'log_action', 'joinOn' => 'log_link_visit_action.idaction_url = log_action.idaction'),
            'log_visit'
        );
        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $logVisitTable = Common::prefixTable('log_visit');
        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');

        $expected = array(
            "sql"  => "
             SELECT log_link_visit_action.custom_dimension_1,
                    log_action.name as url,
                    sum(log_link_visit_action.time_spent) as `13`,
                    sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`
             FROM $logLinkVisitActionTable AS log_link_visit_action
                  LEFT JOIN $logVisitTable AS log_visit
                       ON log_visit.idvisit = log_link_visit_action.idvisit
                  LEFT JOIN $logActionTable AS log_action
                       ON log_link_visit_action.idaction_url = log_action.idaction
             WHERE ( log_link_visit_action.server_time >= ?
                 AND log_link_visit_action.server_time <= ?
                 AND log_link_visit_action.idsite = ? )
                 AND ( log_action.type = ? )",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }


    public function test_getSelectQuery_whenJoiningManyCustomTablesItShouldKeepTheOrderAsDefined()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_link_visit_action.custom_dimension_1,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `13`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`';
        $from  = array(
            'log_link_visit_action',
            'log_visit',
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => 'log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit',
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => 'log_link_visit_action_foo.idaction_url = log_action_foo.idaction',
            ),
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_bar',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_bar.idvisit"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_bar',
                'joinOn' => "log_link_visit_action_bar.idaction_url = log_action_bar.idaction"
            ),
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_baz',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_baz.idvisit"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_baz',
                'joinOn' => "log_link_visit_action_baz.idaction_url = log_action_baz.idaction"
            ),
            'log_action',
        );

        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logVisitTable = Common::prefixTable('log_visit');

        $expected = array(
            "sql" => "
            SELECT log_link_visit_action.custom_dimension_1,
                   log_action.name as url,
                   sum(log_link_visit_action.time_spent) as `13`,
                   sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`
            FROM $logLinkVisitActionTable AS log_link_visit_action
            LEFT JOIN $logActionTable AS log_action ON log_link_visit_action.idaction_url = log_action.idaction
            LEFT JOIN $logVisitTable AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action_foo ON log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit
            LEFT JOIN $logActionTable AS log_action_foo ON log_link_visit_action_foo.idaction_url = log_action_foo.idaction
            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action_bar ON log_link_visit_action.idvisit = log_link_visit_action_bar.idvisit
            LEFT JOIN $logActionTable AS log_action_bar ON log_link_visit_action_bar.idaction_url = log_action_bar.idaction
            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action_baz ON log_link_visit_action.idvisit = log_link_visit_action_baz.idvisit
            LEFT JOIN $logActionTable AS log_action_baz ON log_link_visit_action_baz.idaction_url = log_action_baz.idaction
            WHERE ( log_link_visit_action.server_time >= ?
                AND log_link_visit_action.server_time <= ?
                AND log_link_visit_action.idsite = ? )
                AND ( log_action.type = ? )",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinLogLinkVisitActionOnActionOnVisit_WithNoTableAliasButDifferentJoin()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_link_visit_action.custom_dimension_1,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `13`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`';
        $from  = array(
            'log_link_visit_action',
            array('table' => 'log_visit', 'joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit'),
            array('table' => 'log_action', 'joinOn' => 'log_link_visit_action.idaction_name = log_action.idaction')
        );
        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $logVisitTable = Common::prefixTable('log_visit');
        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');

        $expected = array(
            "sql"  => "
             SELECT log_link_visit_action.custom_dimension_1,
                    log_action.name as url,
                    sum(log_link_visit_action.time_spent) as `13`,
                    sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`
             FROM $logLinkVisitActionTable AS log_link_visit_action 
             LEFT JOIN $logVisitTable AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit 
             LEFT JOIN $logActionTable AS log_action ON log_link_visit_action.idaction_name = log_action.idaction
             WHERE ( log_link_visit_action.server_time >= ?
                 AND log_link_visit_action.server_time <= ?
                 AND log_link_visit_action.idsite = ? )
                 AND ( log_action.type = ? )",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    /**
     * visit is joined on action, then conversion is joined
     * make sure that conversion is joined on action not visit
     */
    public function test_getSelectQuery_whenJoinVisitAndConversionOnLogLinkVisitAction()
    {
        $select = '*';
        $from = 'log_link_visit_action';
        $where = false;
        $bind = array();

        $segment = 'visitServerHour==12;visitConvertedGoalId==1';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $expected = array(
            "sql"  => "
                SELECT
                    *
                FROM
                    " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
                    LEFT JOIN " . Common::prefixTable('log_visit') . " AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idvisit = log_link_visit_action.idvisit
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

        $segment = 'visitConvertedGoalId==1;visitServerHour==12;siteSearchCategory==Test;pageUrl!=';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $expected = array(
            "sql"  => "
                SELECT
                    log_inner.*
                FROM (
                SELECT
                    log_visit.*
                FROM
                    " . Common::prefixTable('log_visit') . " AS log_visit
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion ON log_conversion.idvisit = log_visit.idvisit
                    LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                WHERE
                    log_conversion.idgoal = ? AND HOUR(log_visit.visit_last_action_time) = ? AND log_link_visit_action.search_cat = ?
                    AND ((
                          log_action_segment_log_link_visit_actionidaction_url.name IS NOT NULL
                          AND log_action_segment_log_link_visit_actionidaction_url.name <> ''
                          AND log_action_segment_log_link_visit_actionidaction_url.name <> '0' )
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '1')
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                     ) AS log_inner",
            "bind" => array(1, 12, 'Test'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinVisitOnAction()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'count(distinct log_visit.idvisitor) AS `1`,
                   count(*) AS `2`,
                   sum(log_visit.visit_total_actions) AS `3`';
        $from  = 'log_visit';
        $where = 'log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite IN (?)';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $logVisitTable = Common::prefixTable('log_visit');
        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');

        $expected = array(
            "sql"  => "
             SELECT count(distinct log_inner.idvisitor) AS `1`, count(*) AS `2`, sum(log_inner.visit_total_actions) AS `3` FROM ( SELECT log_visit.idvisitor, log_visit.visit_total_actions
             FROM $logVisitTable AS log_visit
                LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action
                    ON log_link_visit_action.idvisit = log_visit.idvisit
                LEFT JOIN $logActionTable AS log_action
                    ON log_link_visit_action.idaction_url = log_action.idaction
             WHERE ( log_visit.visit_last_action_time >= ?
                    AND log_visit.visit_last_action_time <= ?
                    AND log_visit.idsite IN (?) )
                    AND ( log_action.type = ? )
             GROUP BY log_visit.idvisit
             ORDER BY NULL ) AS log_inner",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinLogLinkVisitActionOnActionOnVisit()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_link_visit_action.custom_dimension_1,
                  actionAlias.name as url,
                  sum(log_link_visit_action.time_spent) as `13`,
                  sum(case visitAlias.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`';
        $from  = array(
            'log_link_visit_action',
             array('table' => 'log_visit', 'tableAlias' => 'visitAlias', 'joinOn' => 'visitAlias.idvisit = log_link_visit_action.idvisit'),
             array('table' => 'log_action', 'tableAlias' => 'actionAlias', 'joinOn' => 'log_link_visit_action.idaction_url = actionAlias.idaction')
        );
        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);
        $this->assertQueryDoesNotFail($query);

        $logVisitTable = Common::prefixTable('log_visit');
        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $expected = array(
            "sql"  => "
             SELECT log_link_visit_action.custom_dimension_1,
                    actionAlias.name as url,
                    sum(log_link_visit_action.time_spent) as `13`,
                    sum(case visitAlias.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`
             FROM $logLinkVisitActionTable AS log_link_visit_action
             LEFT JOIN $logVisitTable AS visitAlias ON visitAlias.idvisit = log_link_visit_action.idvisit
             LEFT JOIN $logActionTable AS actionAlias ON log_link_visit_action.idaction_url = actionAlias.idaction
             LEFT JOIN $logActionTable AS log_action ON log_link_visit_action.idaction_url = log_action.idaction
             WHERE ( log_link_visit_action.server_time >= ?
                 AND log_link_visit_action.server_time <= ?
                 AND log_link_visit_action.idsite = ? )
                 AND ( log_action.type = ? )",

            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    private function assertQueryDoesNotFail($query)
    {
        Db::fetchAll($query['sql'], $query['bind']);
    }

    public function test_getSelectQuery_whenJoinLogLinkVisitActionOnAction()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_link_visit_action.custom_dimension_1,
                  sum(log_link_visit_action.time_spent) as `13`';
        $from  = 'log_link_visit_action';
        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');

        $expected = array(
            "sql"  => "
             SELECT log_link_visit_action.custom_dimension_1, sum(log_link_visit_action.time_spent) as `13`
             FROM $logLinkVisitActionTable AS log_link_visit_action
                  LEFT JOIN $logActionTable AS log_action
                        ON log_link_visit_action.idaction_url = log_action.idaction
             WHERE ( log_link_visit_action.server_time >= ?
                 AND log_link_visit_action.server_time <= ?
                 AND log_link_visit_action.idsite = ? )
                 AND ( log_action.type = ? )",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinConversionOnAction()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_conversion.idgoal AS `idgoal`,
                   log_conversion.custom_dimension_1 AS `custom_dimension_1`,
                   count(*) AS `1`,
                   count(distinct log_conversion.idvisit) AS `3`,';
        $from  = 'log_conversion';
        $where = 'log_conversion.server_time >= ?
				  AND log_conversion.server_time <= ?
				  AND log_conversion.idsite IN (?)';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $logConversionsTable = Common::prefixTable('log_conversion');
        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');

        $expected = array(
            "sql"  => "
             SELECT log_conversion.idgoal AS `idgoal`, log_conversion.custom_dimension_1 AS `custom_dimension_1`, count(*) AS `1`, count(distinct log_conversion.idvisit) AS `3`,
             FROM $logConversionsTable AS log_conversion
                  LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action
                       ON log_link_visit_action.idvisit = log_conversion.idvisit
                  LEFT JOIN $logActionTable AS log_action
                       ON log_link_visit_action.idaction_url = log_action.idaction
             WHERE ( log_conversion.server_time >= ?
                 AND log_conversion.server_time <= ?
                 AND log_conversion.idsite IN (?) )
                 AND ( log_action.type = ? )",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenUnionOfSegmentsAreUsed()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'actionUrl=@myTestUrl';
        $segment = new Segment($segment, $idSites = array());

        $logVisitTable = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => " SELECT log_inner.* FROM (
                          SELECT log_visit.* FROM $logVisitTable AS log_visit
                          LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action
                            ON log_link_visit_action.idvisit = log_visit.idvisit
                          LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                          WHERE ((log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1')
                             OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '3')
                             OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '2')
                             OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '10') )
                        GROUP BY log_visit.idvisit ORDER BY NULL ) AS log_inner",
            "bind" => array('%myTestUrl%', '%myTestUrl%', '%myTestUrl%', '%myTestUrl%'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenUnionOfSegmentsAreUsedWithNotContainsCompare_usesSubQueryWithGivenStartDate()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'actionUrl!@myTestUrl';
        $segment = new Segment($segment, $idSites = array(), Date::factory('2020-02-02 02:00:00'), Date::factory('2020-02-29 02:00:00'));

        $logVisitTable = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => " SELECT log_visit.* FROM $logVisitTable AS log_visit 
                        WHERE ( log_visit.idvisit NOT IN (
                            SELECT log_visit.idvisit
                            FROM $logVisitTable AS log_visit
                            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                            LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                            WHERE ( log_visit.visit_last_action_time >= ? AND log_visit.visit_last_action_time <= ? )
                              AND ( ((log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1')
                                 OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '3')
                                 OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '2')
                                 OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '10') )))
                        )",
        "bind" => array('2020-02-02 02:00:00', '2020-02-29 02:00:00', '%myTestUrl%', '%myTestUrl%', '%myTestUrl%', '%myTestUrl%'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenUnionOfSegmentsAreUsedWithNotContainsCompare_usesNoSubQueryWithoutStartDate()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'actionUrl!@myTestUrl';

        // When no start date is given for the segment object, it will not generate a subquery, as it might have too many results
        // instead it will try to directly join the tables, which might cause incorrect results for action dimensions
        $segment = new Segment($segment, $idSites = array(), $startDate = null, $endDate = null);

        $logVisitTable = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => " SELECT log_inner.* FROM (
                            SELECT log_visit.* FROM $logVisitTable AS log_visit
                            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                            LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                            WHERE ((( log_link_visit_action.idaction_url IS NULL OR log_action_segment_log_link_visit_actionidaction_url.name NOT LIKE ? ) AND log_action_segment_log_link_visit_actionidaction_url.type = '1')
                               OR (( log_link_visit_action.idaction_url IS NULL OR log_action_segment_log_link_visit_actionidaction_url.name NOT LIKE ? ) AND log_action_segment_log_link_visit_actionidaction_url.type = '3')
                               OR (( log_link_visit_action.idaction_url IS NULL OR log_action_segment_log_link_visit_actionidaction_url.name NOT LIKE ? ) AND log_action_segment_log_link_visit_actionidaction_url.type = '2')
                               OR (( log_link_visit_action.idaction_url IS NULL OR log_action_segment_log_link_visit_actionidaction_url.name NOT LIKE ? ) AND log_action_segment_log_link_visit_actionidaction_url.type = '10') )
                            GROUP BY log_visit.idvisit ORDER BY NULL )
                        AS log_inner",
        "bind" => array('%myTestUrl%', '%myTestUrl%', '%myTestUrl%', '%myTestUrl%'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenUsingNotEqualsCompareOnActionDimension()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'siteSearchCategory!=myCategory';
        $segment = new Segment($segment, $idSites = array(), Date::factory('2020-02-02 02:00:00'));

        $logVisitTable = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => " SELECT log_visit.* FROM $logVisitTable AS log_visit 
                        WHERE ( log_visit.idvisit NOT IN (
                            SELECT log_visit.idvisit FROM $logVisitTable AS log_visit LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                            WHERE ( log_visit.visit_last_action_time >= ? ) AND ( log_link_visit_action.search_cat = ? )) ) ",
            "bind" => array('2020-02-02 02:00:00', 'myCategory'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenUsingNotEqualsAndNotContainsCompareOnActionDimensionWithIdSitesAndDates()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $segment = 'siteSearchCategory!=myCategory;actionUrl!@myTestUrl';
        $segment = new Segment($segment, $idSites = array(1,5), Date::factory('2020-02-02 12:00:00'), Date::factory('2020-02-05 09:00:00'));

        $logVisitTable = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "SELECT log_visit.*
                        FROM $logVisitTable AS log_visit
                        WHERE (log_visit.idvisit NOT IN (
                            SELECT log_visit.idvisit
                              FROM log_visit AS log_visit
                         LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action
                             ON log_link_visit_action.idvisit = log_visit.idvisit
                         LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url
                             ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                             WHERE (log_visit.idsite IN (?,?) AND log_visit.visit_last_action_time >= ? AND
                                    log_visit.visit_last_action_time <= ?)
                               AND ((log_link_visit_action.search_cat = ? OR
                                     (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND
                                      log_action_segment_log_link_visit_actionidaction_url.type = '1') OR
                                     (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND
                                      log_action_segment_log_link_visit_actionidaction_url.type = '3') OR
                                     (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND
                                      log_action_segment_log_link_visit_actionidaction_url.type = '2') OR
                                     (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND
                                      log_action_segment_log_link_visit_actionidaction_url.type = '10') ))
                         ))",
            "bind" => array(1, 5, '2020-02-02 12:00:00', '2020-02-05 09:00:00', 'myCategory', '%myTestUrl%', '%myTestUrl%', '%myTestUrl%', '%myTestUrl%'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinConversionOnLogLinkVisitAction_segmentUsesPageUrl()
    {
        $this->insertPageUrlAsAction('example.com/anypage');
        $this->insertPageUrlAsAction('example.com/anypage_bis');
        $pageUrlFoundInDb = 'example.com/page.html?hello=world';
        $actionIdFoundInDb = $this->insertPageUrlAsAction($pageUrlFoundInDb);

        $select = 'log_conversion.idgoal AS `idgoal`,
			SUM(log_conversion.items) AS `8`';

        $from = 'log_conversion';
        $where = 'log_conversion.idsite IN (?)';
        $bind = array(1);

        $segment = 'pageUrl==' . urlencode($pageUrlFoundInDb);

        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = array(
            "sql"  => "
                SELECT
                    log_conversion.idgoal AS `idgoal`,
                    SUM(log_conversion.items) AS `8`
                FROM
                    " . Common::prefixTable('log_conversion') . " AS log_conversion
                    LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action ON log_link_visit_action.idvisit = log_conversion.idvisit
                WHERE
                    ( log_conversion.idsite IN (?) )
                    AND
                    ( log_link_visit_action.idaction_url = ? )",
            "bind" => array(1, $actionIdFoundInDb));

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
        $this->expectException(\Exception::class);
        new Segment($segment, $idSites = array());
    }


    public function test_getSelectQuery_whenLimit_innerQueryShouldHaveLimitAndNoGroupBy()
    {
        $select = 'sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test';
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
                    ( log_link_visit_action.search_cat = ? )
                ORDER BY NULL
                LIMIT 0, 33
                    ) AS log_inner",
            "bind" => array(1, 'Test'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenLimit_withCustomJoinsAndSameColumns()
    {
        $select = "log_action_visit_entry_idaction_name.name AS 'EntryPageTitle', log_action_idaction_event_action.name AS 'EventAction', count(distinct log_visit.idvisit) AS 'nb_uniq_visits', count(distinct log_visit.idvisitor) AS 'nb_uniq_visitors', sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) AS 'bounce_count', sum(log_visit.visit_total_actions) AS 'sum_actions', sum(log_visit.visit_goal_converted) AS 'sum_visit_goal_converted'";
        $from = array('log_visit', array('table' => 'log_action', 'tableAlias' => 'log_action_visit_entry_idaction_name', 'joinOn' => 'log_visit.visit_entry_idaction_name = log_action_visit_entry_idaction_name.idaction'), 'log_link_visit_action', array('table' => 'log_action', 'tableAlias' => 'log_action_idaction_event_action', 'joinOn' => 'log_link_visit_action.idaction_event_action = log_action_idaction_event_action.idaction'));
        $where = '';
        $bind = array(1);

        $segment = '';
        $segment = new Segment($segment, $idSites = array());

        $orderBy = 'nb_uniq_visits, log_action_idaction_event_action.name';
        $groupBy = 'log_action_visit_entry_idaction_name.name, log_action_idaction_event_action.name';
        $limit = 33;

        $logVisitTable = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logActionTable = Common::prefixTable('log_action');

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy, $limit);

        $expected = array(
            "sql"  => "
                SELECT log_inner.name AS 'EntryPageTitle', log_inner.name02fd90a35677a359ea5611a4bc456a6f AS 'EventAction', count(distinct log_inner.idvisit) AS 'nb_uniq_visits', count(distinct log_inner.idvisitor) AS 'nb_uniq_visitors', sum(case log_inner.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) AS 'bounce_count', sum(log_inner.visit_total_actions) AS 'sum_actions', sum(log_inner.visit_goal_converted) AS 'sum_visit_goal_converted'
                FROM (
                  SELECT log_action_visit_entry_idaction_name.name, log_action_idaction_event_action.name as name02fd90a35677a359ea5611a4bc456a6f, log_visit.idvisit, log_visit.idvisitor, log_visit.visit_total_actions, log_visit.visit_goal_converted
                  FROM $logVisitTable AS log_visit
                  LEFT JOIN $logActionTable AS log_action_visit_entry_idaction_name ON log_visit.visit_entry_idaction_name = log_action_visit_entry_idaction_name.idaction
                  LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
                  LEFT JOIN $logActionTable AS log_action_idaction_event_action ON log_link_visit_action.idaction_event_action = log_action_idaction_event_action.idaction
                  ORDER BY nb_uniq_visits, log_action_idaction_event_action.name LIMIT 0, 33 )
                AS log_inner
                GROUP BY log_inner.name, log_inner.name02fd90a35677a359ea5611a4bc456a6f
                ORDER BY nb_uniq_visits, log_inner.name02fd90a35677a359ea5611a4bc456a6f",
            "bind" => array(1));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenLimit_withCustomJoinsAndSameColumnsAndSimilarColumns()
    {
        $select = 'log_link_visit_action.idvisit,
                   log_visit.idvisit,
                   count(log_visit.idvisit) as numvisits,
                   count(distinct log_visit.idvisit ) as numvisitors,
                   log_visit.idvisitor,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `13`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`';
        $from = array('log_visit', 'log_link_visit_action');
        $where = '';
        $bind = array(1);

        $segment = '';
        $segment = new Segment($segment, $idSites = array());

        $orderBy = 'url, log_visit.idvisit';
        $groupBy = 'log_visit.idvisit, log_visit.idvisit , log_visit.idvisitor, log_visit.idvisitor , log_link_visit_action.idvisit';
        $limit = 33;

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy, $limit);

        $logLinkVisitAction = Common::prefixTable('log_link_visit_action');

        // should have replaced some idvisit columns but not idvisitor column
        $expected = array(
            "sql"  => "
                SELECT
				log_inner.idvisit,
                   log_inner.idvisit5d489886e80b4258a9407b219a4e2811,
                   count(log_inner.idvisit5d489886e80b4258a9407b219a4e2811) as numvisits,
                   count(distinct log_inner.idvisit5d489886e80b4258a9407b219a4e2811 ) as numvisitors,
                   log_inner.idvisitor,
                  log_inner.name as url,
                  sum(log_inner.time_spent) as `13`,
                  sum(case log_inner.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`
			FROM
				
        (
            
			SELECT
				log_link_visit_action.idvisit, 
log_visit.idvisit as idvisit5d489886e80b4258a9407b219a4e2811, 
log_visit.idvisitor, 
log_action.name, 
log_link_visit_action.time_spent, 
log_visit.visit_total_actions
			FROM
				log_visit AS log_visit LEFT JOIN $logLinkVisitAction AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
			ORDER BY
				url, log_visit.idvisit LIMIT 0, 33
        ) AS log_inner
			GROUP BY
				log_inner.idvisit5d489886e80b4258a9407b219a4e2811, log_inner.idvisit5d489886e80b4258a9407b219a4e2811 , log_inner.idvisitor, log_inner.idvisitor , log_inner.idvisit
			ORDER BY
				url, log_inner.idvisit5d489886e80b4258a9407b219a4e2811",
            "bind" => array(1));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenLimitAndOffset_outerQueryShouldNotHaveOffset()
    {
        $select = 'sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test';
        $segment = new Segment($segment, $idSites = array());

        $orderBy = false;
        $groupBy = false;
        $limit = 33;
        $offset = 10;

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy, $limit, $offset);

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
                    ( log_link_visit_action.search_cat = ? )
                ORDER BY NULL
                LIMIT 10, 33
                    ) AS log_inner",
            "bind" => array(1, 'Test'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenOffsetIsZero()
    {
        $select = 'sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test';
        $segment = new Segment($segment, $idSites = array());

        $orderBy = false;
        $groupBy = false;
        $limit = 33;
        $offset = 0;

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy, $limit, $offset);

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
                    ( log_link_visit_action.search_cat = ? )
                ORDER BY NULL
                LIMIT 0, 33
                    ) AS log_inner",
            "bind" => array(1, 'Test'));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenLimitIsZero()
    {
        $select = 'sum(log_visit.visit_total_time) as sum_visit_length';
        $from = 'log_visit';
        $where = 'log_visit.idvisit = ?';
        $bind = array(1);

        $segment = 'siteSearchCategory==Test';
        $segment = new Segment($segment, $idSites = array());

        $orderBy = false;
        $groupBy = false;
        $limit = 0;
        $offset = 10;

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy, $limit, $offset);

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
                    ( log_link_visit_action.search_cat = ? )
                GROUP BY log_visit.idvisit
                ORDER BY NULL
                    ) AS log_inner",
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

    public function test_getSelectQuery_whenManyIdActionSegmentsAreUsed_OR_AND()
    {
        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        $this->insertPageUrlAsAction('test-content-name', 'idaction_content_name', Action::TYPE_CONTENT);
        $this->insertPageUrlAsAction('content-pice', 'idaction_content_piece', Action::TYPE_CONTENT_PIECE);
        $this->insertPageUrlAsAction('test page title', 'visit_entry_idaction_name', Action::TYPE_PAGE_TITLE);
        $this->insertPageUrlAsAction('abc.com/page/url', 'visit_entry_idaction_url', Action::TYPE_PAGE_URL);
        $this->insertPageUrlAsAction('abc.com/page/url2', 'visit_entry_idaction_url', Action::TYPE_PAGE_URL);
        $this->insertPageUrlAsAction('mypage title', 'idaction_name', Action::TYPE_PAGE_TITLE);
        $this->insertPageUrlAsAction('blahblah.com/', 'idaction_url', Action::TYPE_PAGE_URL);
        $this->insertPageUrlAsAction('exitpage.com/sadlkfjasldkfj', 'visit_exit_idaction_url', Action::TYPE_PAGE_URL);
        $this->insertPageUrlAsAction('http://anothersite.com/landing/page', 'idaction_url', Action::TYPE_OUTLINK);
        $this->insertPageUrlAsAction('023984ASADF', 'idaction_sku', Action::TYPE_ECOMMERCE_ITEM_SKU);
        $this->insertPageUrlAsAction('the test product name', 'idaction_name', Action::TYPE_ECOMMERCE_ITEM_NAME);
        $this->insertPageUrlAsAction('ecom', 'idaction_event_category', Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
        $this->insertPageUrlAsAction('http://thing.com/path/to/place', 'idaction_url', Action::TYPE_EVENT);
        $this->insertPageUrlAsAction('http://otherthing.org/path/to/anotherplace', 'idaction_url', Action::TYPE_EVENT);

        $logVisit = Common::prefixTable('log_visit');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');
        $logConversionItem = Common::prefixTable('log_conversion_item');

        $segment = 'contentTarget!@' . urlencode(urlencode('http://landingpage.example.com'))
        . ',contentName==' . urlencode(urlencode('test-content-name'))
        . ';contentInteraction=@' . urlencode(urlencode('buy'))
        . ';contentPiece!=' . urlencode(urlencode('content-pice'))
        . ',contentPiece=@' . urlencode(urlencode('something'))
        . ',entryPageTitle!=' . urlencode(urlencode('test page title'))
        . ',downloadUrl=@' . urlencode(urlencode('downloadsite1'))
        . ';downloadUrl!@' . urlencode(urlencode('mirror'))
        . ',siteSearchKeyword=@' . urlencode(urlencode('a sitesearch query'))
        . ';exitPageTitle!@' . urlencode(urlencode('another page title'))
        . ';entryPageUrl!=' . urlencode(urlencode('https://abc.com/page/url'))
        . ';entryPageUrl!=' . urlencode(urlencode('https://abc.com/page/url2'))
        . ',pageTitle==' . urlencode(urlencode('mypage title'))
        . ';pageUrl!@' . urlencode(urlencode('http://def.com/wherever'))
        . ';pageUrl==' . urlencode(urlencode('https://blahblah.com/'))
        . ',exitPageUrl=@' . urlencode(urlencode('https://exitpage.com/sadlkfjasldkfj'))
        . ';outlinkUrl==' . urlencode(urlencode('http://anothersite.com/landing/page'))
        . ',actionUrl=@' . urlencode(urlencode('ancientsymbol'))
        . ',actionUrl=@' . urlencode(urlencode('modernsymbol'))
        . ';productViewSku!@' . urlencode(urlencode('123423SDL'))
        . ';productCategory=@' . urlencode(urlencode('A product category'))
        . ';productSku==' . urlencode(urlencode('023984ASADF'))
        . ',productName!=' . urlencode(urlencode('the test product name'))
        . ',productName=@' . urlencode(urlencode('alkjbsl'))
        . ',productViewCategory!@' . urlencode(urlencode('round products'))
        . ',productViewName=@' . urlencode(urlencode('the first round product'))
        . ',eventAction=@' . urlencode(urlencode('product-view'))
        . ';eventName=@' . urlencode(urlencode('product 1'))
        . ';eventCategory!=' . urlencode(urlencode('ecom'))
        . ';eventUrl==' . urlencode(urlencode('http://thing.com/path/to/place'))
        . ';eventUrl!=' . urlencode(urlencode('http://otherthing.org/path/to/anotherplace'))
        . ';eventUrl=@' . urlencode(urlencode('https://securething.cloud/path/to/secureplace'));

        $segment = new Segment($segment, $idSites = array(), Date::factory('2020-02-02 02:00:00'));

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $expected = [
            'sql'  => "
                SELECT 
                  log_inner.* 
                FROM 
                  (
                    SELECT 
                      log_visit.* 
                    FROM 
                      $logVisit AS log_visit 
                      LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                      LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_content_interaction ON log_link_visit_action.idaction_content_interaction = log_action_segment_log_link_visit_actionidaction_content_interaction.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_content_piece ON log_link_visit_action.idaction_content_piece = log_action_segment_log_link_visit_actionidaction_content_piece.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_name ON log_link_visit_action.idaction_name = log_action_segment_log_link_visit_actionidaction_name.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_visitvisit_exit_idaction_name ON log_visit.visit_exit_idaction_name = log_action_segment_log_visitvisit_exit_idaction_name.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_visitvisit_exit_idaction_url ON log_visit.visit_exit_idaction_url = log_action_segment_log_visitvisit_exit_idaction_url.idaction 
                      LEFT JOIN $logConversionItem AS log_conversion_item ON log_conversion_item.idvisit = log_visit.idvisit 
                      AND `log_conversion_item`.deleted = 0 
                      LEFT JOIN $logAction AS log_action_segment_log_conversion_itemidaction_category ON log_conversion_item.idaction_category = log_action_segment_log_conversion_itemidaction_category.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_conversion_itemidaction_category2 ON log_conversion_item.idaction_category2 = log_action_segment_log_conversion_itemidaction_category2.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_conversion_itemidaction_category3 ON log_conversion_item.idaction_category3 = log_action_segment_log_conversion_itemidaction_category3.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_conversion_itemidaction_category4 ON log_conversion_item.idaction_category4 = log_action_segment_log_conversion_itemidaction_category4.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_conversion_itemidaction_category5 ON log_conversion_item.idaction_category5 = log_action_segment_log_conversion_itemidaction_category5.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_conversion_itemidaction_name ON log_conversion_item.idaction_name = log_action_segment_log_conversion_itemidaction_name.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_name ON log_link_visit_action.idaction_product_name = log_action_segment_log_link_visit_actionidaction_product_name.idaction 
                      LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_event_action ON log_link_visit_action.idaction_event_action = log_action_segment_log_link_visit_actionidaction_event_action.idaction 
                    WHERE 
                      (
                        log_link_visit_action.idaction_content_name = ? 
                        OR(
                          log_visit.idvisit NOT IN(
                            SELECT 
                              log_visit.idvisit 
                            FROM 
                              $logVisit AS log_visit 
                              LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_content_target ON log_link_visit_action.idaction_content_target = log_action_segment_log_link_visit_actionidaction_content_target.idaction 
                            WHERE 
                              (
                                log_visit.visit_last_action_time >= ?
                              ) 
                              AND(
                                (
                                  log_action_segment_log_link_visit_actionidaction_content_target.name LIKE ? 
                                  AND log_action_segment_log_link_visit_actionidaction_content_target.type = '15'
                                )
                              )
                          )
                        )
                      ) 
                      AND(
                        log_action_segment_log_link_visit_actionidaction_content_interaction.name LIKE ? 
                        AND log_action_segment_log_link_visit_actionidaction_content_interaction.type = '16'
                      ) 
                      AND(
                        (
                          log_action_segment_log_link_visit_actionidaction_content_piece.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_content_piece.type = '14'
                        ) 
                        OR(
                          log_visit.visit_entry_idaction_name IS NULL 
                          OR log_visit.visit_entry_idaction_name <> ?
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '3'
                        ) 
                        OR(
                          log_visit.idvisit NOT IN(
                            SELECT 
                              log_visit.idvisit 
                            FROM 
                              $logVisit AS log_visit 
                              LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                            WHERE 
                              (
                                log_visit.visit_last_action_time >= ?
                              ) 
                              AND(
                                log_link_visit_action.idaction_content_piece = ?
                              )
                          )
                        )
                      ) 
                      AND(
                        (
                          log_action_segment_log_link_visit_actionidaction_name.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_name.type = '8'
                        ) 
                        OR(
                          log_visit.idvisit NOT IN(
                            SELECT 
                              log_visit.idvisit 
                            FROM 
                              $logVisit AS log_visit 
                              LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction 
                            WHERE 
                              (
                                log_visit.visit_last_action_time >= ?
                              ) 
                              AND(
                                (
                                  log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                                  AND log_action_segment_log_link_visit_actionidaction_url.type = '3'
                                )
                              )
                          )
                        )
                      ) 
                      AND(
                        (
                          log_visit.visit_exit_idaction_name IS NULL 
                          OR log_action_segment_log_visitvisit_exit_idaction_name.name NOT LIKE ?
                        ) 
                        AND log_action_segment_log_visitvisit_exit_idaction_name.type = '4'
                      ) 
                      AND(
                        log_visit.visit_entry_idaction_url IS NULL 
                        OR log_visit.visit_entry_idaction_url <> ?
                      ) 
                      AND(
                        (
                          log_visit.visit_entry_idaction_url IS NULL 
                          OR log_visit.visit_entry_idaction_url <> ?
                        ) 
                        OR log_link_visit_action.idaction_name = ?
                      ) 
                      AND(
                        log_link_visit_action.idaction_url = ? 
                        OR(
                          log_action_segment_log_visitvisit_exit_idaction_url.name LIKE ? 
                          AND log_action_segment_log_visitvisit_exit_idaction_url.type = '1'
                        )
                      ) 
                      AND(
                        log_link_visit_action.idaction_url = ? 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '1'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '3'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '2'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '10'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '1'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '3'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '2'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_url.type = '10'
                        )
                      ) 
                      AND(
                        (
                          log_action_segment_log_conversion_itemidaction_category.name LIKE ? 
                          AND log_action_segment_log_conversion_itemidaction_category.type = '7'
                        ) 
                        OR(
                          log_action_segment_log_conversion_itemidaction_category2.name LIKE ? 
                          AND log_action_segment_log_conversion_itemidaction_category2.type = '7'
                        ) 
                        OR(
                          log_action_segment_log_conversion_itemidaction_category3.name LIKE ? 
                          AND log_action_segment_log_conversion_itemidaction_category3.type = '7'
                        ) 
                        OR(
                          log_action_segment_log_conversion_itemidaction_category4.name LIKE ? 
                          AND log_action_segment_log_conversion_itemidaction_category4.type = '7'
                        ) 
                        OR(
                          log_action_segment_log_conversion_itemidaction_category5.name LIKE ? 
                          AND log_action_segment_log_conversion_itemidaction_category5.type = '7'
                        )
                      ) 
                      AND(
                        log_conversion_item.idaction_sku = ? 
                        OR(
                          log_action_segment_log_conversion_itemidaction_name.name LIKE ? 
                          AND log_action_segment_log_conversion_itemidaction_name.type = '6'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_product_name.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_product_name.type = '6'
                        ) 
                        OR(
                          log_action_segment_log_link_visit_actionidaction_event_action.name LIKE ? 
                          AND log_action_segment_log_link_visit_actionidaction_event_action.type = '11'
                        ) 
                        OR(
                          log_visit.idvisit NOT IN(
                            SELECT 
                              log_visit.idvisit 
                            FROM 
                              $logVisit AS log_visit 
                              LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                              LEFT JOIN $logConversionItem AS log_conversion_item ON log_conversion_item.idvisit = log_visit.idvisit 
                              AND `log_conversion_item`.deleted = 0 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_cat ON log_link_visit_action.idaction_product_cat = log_action_segment_log_link_visit_actionidaction_product_cat.idaction 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_cat2 ON log_link_visit_action.idaction_product_cat2 = log_action_segment_log_link_visit_actionidaction_product_cat2.idaction 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_cat3 ON log_link_visit_action.idaction_product_cat3 = log_action_segment_log_link_visit_actionidaction_product_cat3.idaction 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_cat4 ON log_link_visit_action.idaction_product_cat4 = log_action_segment_log_link_visit_actionidaction_product_cat4.idaction 
                              LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_cat5 ON log_link_visit_action.idaction_product_cat5 = log_action_segment_log_link_visit_actionidaction_product_cat5.idaction 
                            WHERE 
                              (
                                log_visit.visit_last_action_time >= ?
                              ) 
                              AND(
                                log_conversion_item.idaction_name = ? 
                                AND(
                                  (
                                    log_action_segment_log_link_visit_actionidaction_product_cat.name LIKE ? 
                                    AND log_action_segment_log_link_visit_actionidaction_product_cat.type = '7'
                                  ) 
                                  OR(
                                    log_action_segment_log_link_visit_actionidaction_product_cat2.name LIKE ? 
                                    AND log_action_segment_log_link_visit_actionidaction_product_cat2.type = '7'
                                  ) 
                                  OR(
                                    log_action_segment_log_link_visit_actionidaction_product_cat3.name LIKE ? 
                                    AND log_action_segment_log_link_visit_actionidaction_product_cat3.type = '7'
                                  ) 
                                  OR(
                                    log_action_segment_log_link_visit_actionidaction_product_cat4.name LIKE ? 
                                    AND log_action_segment_log_link_visit_actionidaction_product_cat4.type = '7'
                                  ) 
                                  OR(
                                    log_action_segment_log_link_visit_actionidaction_product_cat5.name LIKE ? 
                                    AND log_action_segment_log_link_visit_actionidaction_product_cat5.type = '7'
                                  )
                                )
                              )
                          )
                        )
                      ) 
                      AND(
                        log_action_segment_log_link_visit_actionidaction_name.name LIKE ? 
                        AND log_action_segment_log_link_visit_actionidaction_name.type = '12'
                      ) 
                      AND(1 = 0) 
                      AND(
                        log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                        AND log_action_segment_log_link_visit_actionidaction_url.type = '10'
                      ) 
                      AND(
                        log_visit.idvisit NOT IN(
                          SELECT 
                            log_visit.idvisit 
                          FROM 
                            $logVisit AS log_visit 
                            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit 
                            LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction 
                            LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_product_sku ON log_link_visit_action.idaction_product_sku = log_action_segment_log_link_visit_actionidaction_product_sku.idaction 
                          WHERE 
                            (
                              log_visit.visit_last_action_time >= ?
                            ) 
                            AND(
                              (
                                (
                                  log_action_segment_log_link_visit_actionidaction_url.name LIKE ? 
                                  AND log_action_segment_log_link_visit_actionidaction_url.type = '1'
                                ) 
                                OR(
                                  log_action_segment_log_link_visit_actionidaction_product_sku.name LIKE ? 
                                  AND log_action_segment_log_link_visit_actionidaction_product_sku.type = '5'
                                ) 
                                OR(1 = 0) 
                                OR(1 = 0)
                              )
                            )
                        )
                      ) 
                    GROUP BY 
                      log_visit.idvisit 
                    ORDER BY 
                      NULL
                  ) AS log_inner",
            'bind' => [
                '1',
                '2020-02-02 02:00:00',
                '%http://landingpage.example.com%',
                '%buy%',
                '%something%',
                '3',
                '%downloadsite1%',
                '2020-02-02 02:00:00',
                '2',
                '%a sitesearch query%',
                '2020-02-02 02:00:00',
                '%mirror%',
                '%another page title%',
                '4',
                '5',
                '6',
                '7',
                '%exitpage.com/sadlkfjasldkfj%',
                '9',
                '%ancientsymbol%',
                '%ancientsymbol%',
                '%ancientsymbol%',
                '%ancientsymbol%',
                '%modernsymbol%',
                '%modernsymbol%',
                '%modernsymbol%',
                '%modernsymbol%',
                '%A product category%',
                '%A product category%',
                '%A product category%',
                '%A product category%',
                '%A product category%',
                '10',
                '%alkjbsl%',
                '%the first round product%',
                '%product-view%',
                '2020-02-02 02:00:00',
                '11',
                '%round products%',
                '%round products%',
                '%round products%',
                '%round products%',
                '%round products%',
                '%product 1%',
                '%securething.cloud/path/to/secureplace%',
                '2020-02-02 02:00:00',
                '%def.com/wherever%',
                '%123423SDL%',
            ],
        ];
        $this->assertQueryDoesNotFail($query);

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenPageUrlDoesNotExist_asBothStatements_OR_AND()
    {
        list($pageUrlFoundInDb, $actionIdFoundInDb) = $this->insertActions();

        $select = 'log_visit.*';
        $from = 'log_visit';
        $where = false;
        $bind = array();

        /**
         * pageUrl==xyz                              -- Matches none
         * pageUrl!=abcdefg                          -- Matches all
         * pageUrl=@does-not-exist                   -- Matches none
         * pageUrl=@found-in-db                      -- Matches all
         * pageUrl=='.urlencode($pageUrlFoundInDb)   -- Matches one
         * pageUrl!@not-found                        -- matches all
         * pageUrl!@found                            -- Matches none
         */
        $segment = 'visitServerHour==12,pageUrl==xyz;pageUrl!=abcdefg,pageUrl=@does-not-exist,pageUrl=@found-in-db,pageUrl==' . urlencode($pageUrlFoundInDb) . ',pageUrl!@not-found,pageUrl!@found';
        $segment = new Segment($segment, $idSites = array(), Date::factory('2020-02-02 02:00:00'));

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $logVisit = Common::prefixTable('log_visit');
        $logLinkVisitAction = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $expected = array(
            "sql"  => "
                SELECT log_inner.*
                FROM (
                    SELECT log_visit.*
                      FROM $logVisit AS log_visit
                               LEFT JOIN $logLinkVisitAction AS log_link_visit_action
                                         ON log_link_visit_action.idvisit = log_visit.idvisit
                               LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url
                                         ON log_link_visit_action.idaction_url =
                                            log_action_segment_log_link_visit_actionidaction_url.idaction
                      WHERE (HOUR(log_visit.visit_last_action_time) = ? OR (1 = 0)) " . // pageUrl==xyz
                        " AND (
                            (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1') " . // pageUrl=@does-not-exist
                          " OR (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1') " . // pageUrl=@found-in-db
                          " OR log_link_visit_action.idaction_url = ? " . // pageUrl=='.urlencode($pageUrlFoundInDb)
                          " OR (
                                 log_visit.idvisit NOT IN (
                                    SELECT log_visit.idvisit
                                      FROM $logVisit AS log_visit
                                 LEFT JOIN $logLinkVisitAction AS log_link_visit_action
                                        ON log_link_visit_action.idvisit = log_visit.idvisit
                                 LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url
                                        ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                                      WHERE (log_visit.visit_last_action_time >= ?)
                                        AND ((1 = 0) " . // pageUrl!=abcdefg
                                         " AND (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1')" . // pageUrl!@not-found
                                         " AND (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1')" . // pageUrl!@found
                                      " )
                                  )
                             )
                        )
                      GROUP BY log_visit.idvisit
                      ORDER BY NULL
                ) AS log_inner",
            "bind" => [
                '12',
                '%does-not-exist%',
                '%found-in-db%',
                '1',
                '2020-02-02 02:00:00',
                '%not-found%',
                '%found%',
            ],
        );

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    // se https://github.com/piwik/piwik/issues/9194
    public function test_getSelectQuery_whenQueryingLogConversionWithSegmentThatUsesLogLinkVisitAction_shouldUseSubselect()
    {
        $select = 'log_conversion.idgoal AS `idgoal`,
			       count(*) AS `1`,
			       count(distinct log_conversion.idvisit) AS `3`,
			       ROUND(SUM(log_conversion.revenue),2) AS `2`,
			       SUM(log_conversion.items) AS `8`';
        $from = 'log_conversion';
        $where = 'log_conversion.server_time >= ? AND log_conversion.server_time <= ? AND log_conversion.idsite IN (?)';
        $groupBy = 'log_conversion.idgoal';
        $bind = array('2015-10-14 11:00:00', '2015-10-15 10:59:59', 1);

        $segment = 'pageUrl=@/';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy = false, $groupBy);
        $this->assertQueryDoesNotFail($query);

        $logConversionTable = Common::prefixTable('log_conversion');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $expectedBind = $bind;
        $expectedBind[] = '%/%';
        $expected = array(
            "sql"  => "
                SELECT log_inner.idgoal AS `idgoal`, count(*) AS `1`, count(distinct log_inner.idvisit) AS `3`, ROUND(SUM(log_inner.revenue),2) AS `2`, SUM(log_inner.items) AS `8`
                FROM (
                      SELECT log_conversion.idgoal, log_conversion.idvisit, log_conversion.revenue, log_conversion.items
                      FROM $logConversionTable AS log_conversion
                        LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_conversion.idvisit
                        LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                      WHERE ( log_conversion.server_time >= ?
                          AND log_conversion.server_time <= ?
                          AND log_conversion.idsite IN (?) )
                          AND ( (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1') )
                      GROUP BY CONCAT(log_conversion.idvisit, '_' , log_conversion.idgoal, '_', log_conversion.buster)
                      ORDER BY NULL )
                 AS log_inner GROUP BY log_inner.idgoal",
            "bind" => $expectedBind);

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    // se https://github.com/piwik/piwik/issues/9194
    public function test_getSelectQuery_whenQueryingLogConversionWithSegmentThatUsesLogLinkVisitActionAndGroupsForUsageInConversionsByTypeOfVisit_shouldUseSubselect()
    {
        $select = 'log_conversion.idgoal AS `idgoal`,
                   log_conversion.referer_type AS `referer_type`,
                   log_conversion.referer_name AS `referer_name`,
                   log_conversion.referer_keyword AS `referer_keyword`,
                   count(*) AS `1`,
                   count(distinct log_conversion.idvisit) AS `3`,
                   ROUND(SUM(log_conversion.revenue),2) AS `2`';

        $from = 'log_conversion';
        $where = 'log_conversion.server_time >= ? AND log_conversion.server_time <= ? AND log_conversion.idsite IN (?)';
        $groupBy = 'log_conversion.idgoal, log_conversion.referer_type, log_conversion.referer_name, log_conversion.referer_keyword';
        $bind = array('2015-10-14 11:00:00', '2015-10-15 10:59:59', 1);

        $segment = 'pageUrl=@/';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy = false, $groupBy);
        $this->assertQueryDoesNotFail($query);

        $logConversionTable = Common::prefixTable('log_conversion');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $expectedBind = $bind;
        $expectedBind[] = '%/%';
        $expected = array(
            "sql"  => "
                SELECT log_inner.idgoal AS `idgoal`,
                   log_inner.referer_type AS `referer_type`,
                   log_inner.referer_name AS `referer_name`,
                   log_inner.referer_keyword AS `referer_keyword`,
                   count(*) AS `1`,
                   count(distinct log_inner.idvisit) AS `3`,
                   ROUND(SUM(log_inner.revenue),2) AS `2`
                FROM (
                      SELECT log_conversion.idgoal, log_conversion.referer_type, log_conversion.referer_name, log_conversion.referer_keyword, log_conversion.idvisit, log_conversion.revenue
                      FROM $logConversionTable AS log_conversion
                        LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_conversion.idvisit
                        LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                      WHERE ( log_conversion.server_time >= ?
                          AND log_conversion.server_time <= ?
                          AND log_conversion.idsite IN (?) )
                          AND ( (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1') )
                      GROUP BY CONCAT(log_conversion.idvisit, '_' , log_conversion.idgoal, '_', log_conversion.buster)
                      ORDER BY NULL )
                AS log_inner GROUP BY log_inner.idgoal, log_inner.referer_type, log_inner.referer_name, log_inner.referer_keyword",
            "bind" => $expectedBind);

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    // see https://github.com/piwik/piwik/issues/9194
    public function test_getSelectQuery_whenQueryingLogConversionWithSegmentThatUsesLogLinkVisitActionAndLogVisit_shouldUseSubselectGroupedByIdVisitAndBuster()
    {
        $select = 'log_conversion.idgoal AS `idgoal`,
			       count(*) AS `1`,
			       count(distinct log_conversion.idvisit) AS `3`,
			       ROUND(SUM(log_conversion.revenue),2) AS `2`';

        $from = 'log_conversion';
        $where = 'log_conversion.server_time >= ? AND log_conversion.server_time <= ? AND log_conversion.idsite IN (?)';
        $groupBy = 'log_conversion.idgoal';
        $bind = array('2015-10-14 11:00:00', '2015-10-15 10:59:59', 1);

        $segment = 'visitorType==returning,visitorType==returningCustomer;pageUrl=@/';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy = false, $groupBy);
        $this->assertQueryDoesNotFail($query);

        $logConversionTable = Common::prefixTable('log_conversion');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logVisitTable = Common::prefixTable('log_visit');
        $logAction = Common::prefixTable('log_action');

        $expectedBind = $bind;
        $expectedBind[] = 1;
        $expectedBind[] = 2;
        $expectedBind[] = '%/%';
        $expected = array(
            "sql"  => "
                SELECT log_inner.idgoal AS `idgoal`, count(*) AS `1`, count(distinct log_inner.idvisit) AS `3`, ROUND(SUM(log_inner.revenue),2) AS `2`
                FROM (
                    SELECT log_conversion.idgoal, log_conversion.idvisit, log_conversion.revenue
                    FROM $logConversionTable AS log_conversion
                       LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action ON log_link_visit_action.idvisit = log_conversion.idvisit
                       LEFT JOIN $logVisitTable AS log_visit ON log_visit.idvisit = log_conversion.idvisit
                       LEFT JOIN $logAction AS log_action_segment_log_link_visit_actionidaction_url ON log_link_visit_action.idaction_url = log_action_segment_log_link_visit_actionidaction_url.idaction
                    WHERE ( log_conversion.server_time >= ?
                        AND log_conversion.server_time <= ?
                        AND log_conversion.idsite IN (?) )
                        AND ( (log_visit.visitor_returning = ? OR log_visit.visitor_returning = ?)
                        AND (log_action_segment_log_link_visit_actionidaction_url.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_url.type = '1') )
                    GROUP BY CONCAT(log_conversion.idvisit, '_' , log_conversion.idgoal, '_', log_conversion.buster)
                    ORDER BY NULL ) AS log_inner
                    GROUP BY log_inner.idgoal",
            "bind" => $expectedBind);

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_getSelectQuery_whenJoinCustomLogTableTwoTablesRemovedFromLogVisitFirst_thenJoinTableAdjacentToLogVisit()
    {
        $this->defineEntitiesNotDirectlyJoinableToVisit();

        $segment = new Segment('', $idSites = array());

        $select = 'count(distinct log_thing.idlogthing) as nb_things, count(log_thing_event.idlogthingevent) as nb_thing_events';
        $from = [
            'log_thing',
            'log_thing_event',
        ];
        $where = '';
        $bind = [];

        $query = $segment->getSelectQuery($select, $from, $where, $bind, $orderBy = false);
        $this->assertQueryDoesNotFail($query);

        $expected = <<<SQL
 SELECT count(distinct log_thing.idlogthing) as nb_things, count(log_thing_event.idlogthingevent) as nb_thing_events FROM log_thing AS log_thing LEFT JOIN log_thing_event AS log_thing_event ON `log_thing_event`.`idlogthing` = `log_thing`.`idlogthing`
SQL;

        $sql = $this->removeExtraWhiteSpaces($query['sql']);
        $expected = $this->removeExtraWhiteSpaces($expected);

        $this->assertEquals($expected, $sql);
    }

    public function test_getSelectQuery_whenUrls()
    {
        $select = 'sum(log_visit.idvisit)';
        $from = 'log_visit';
        $where = 'log_visit.idsite = ?';
        $bind = array(1);

        $logVisitTable = Common::prefixTable('log_visit');
        $logAction = Common::prefixTable('log_action');

        $segment = 'entryPageUrl=@https%253A%252F%252Fmatomo.org%252Ffaq%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fdocs%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fcontact%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fhelp%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Ftag-manager-training%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fweb-analytics-training%252F,entryPageUrl=@https%253A%252F%252Fdeveloper.matomo.org%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Ftranslations%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fchangelog%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fsupport%252F,entryPageUrl=@https%253A%252F%252Fmatomo.org%252Fjobs%252F';
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $this->assertQueryDoesNotFail($query);

        $expected = [
            "sql"  => "SELECT sum(log_visit.idvisit)
                        FROM $logVisitTable AS log_visit
                        LEFT JOIN $logAction AS log_action_segment_log_visitvisit_entry_idaction_url ON log_visit.visit_entry_idaction_url = log_action_segment_log_visitvisit_entry_idaction_url.idaction
                        WHERE (log_visit.idsite = ?)
                            AND (
                                (
                                    (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    OR (
                                        log_action_segment_log_visitvisit_entry_idaction_url.name LIKE ?
                                        AND log_action_segment_log_visitvisit_entry_idaction_url.type = '1'
                                        )
                                    )
                                )",
            "bind" => [
                '1',
                '%matomo.org/faq/%',
                '%matomo.org/docs/%',
                '%matomo.org/contact/%',
                '%matomo.org/help/%',
                '%matomo.org/tag-manager-training/%',
                '%matomo.org/web-analytics-training/%',
                '%developer.matomo.org/%',
                '%matomo.org/translations/%',
                '%matomo.org/changelog/%',
                '%matomo.org/support/%',
                '%matomo.org/jobs/%',
            ],
        ];

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));
    }

    public function test_segmentExpression_parseSubExpressionsIntoSqlExpressions_recognizesAvailableTablesCorrectly()
    {
        $segment = 'pageTitle=@abc';

        $from = [
            'log_link_visit_action',
            [
                'table' => 'log_action',
                'tableAlias' => 'log_action_idaction_name',
                'joinOn' => 'log_link_visit_action.idaction_name = log_action_idaction_name.idaction',
            ],
            [
                'table' => 'log_visit',
                'join' => 'RIGHT JOIN',
            ],
            'log_action',
        ];

        $originalFrom = $from;

        $segment = new Segment($segment, []);
        $segmentExpression = $segment->getSegmentExpression();
        $segmentExpression->parseSubExpressionsIntoSqlExpressions($from);
        $sql = $segmentExpression->getSql();

        $expected = [
            "where"  => "(log_action_segment_log_link_visit_actionidaction_name.name LIKE ? AND log_action_segment_log_link_visit_actionidaction_name.type = '4')",
            "bind" => ['%abc%'],
        ];

        $this->assertEquals(SegmentTest::removeExtraWhiteSpaces($expected), SegmentTest::removeExtraWhiteSpaces($sql));

        $expectedFrom = array_merge(
            $originalFrom,
            [
                [
                    'table' => 'log_action',
                    'tableAlias' => 'log_action_segment_log_link_visit_actionidaction_name',
                    'field' => 'log_action_segment_log_link_visit_actionidaction_name.name',
                    'joinOn' => 'log_link_visit_action.idaction_name = log_action_segment_log_link_visit_actionidaction_name.idaction',
                    'discriminator' => 'log_action_segment_log_link_visit_actionidaction_name.type = \'4\'',
                ],
            ]
        );

        $this->assertEquals($expectedFrom, $from);
    }

    /**
     * @param $pageUrlFoundInDb
     * @return string
     * @throws Exception
     */
    private function insertPageUrlAsAction($pageUrlFoundInDb, $idActionColumn = 'idaction_url', $idActionType = Action::TYPE_PAGE_URL)
    {
        TableLogAction::loadIdsAction(array(
            $idActionColumn => array($pageUrlFoundInDb, $idActionType)
        ));

        $actionIdFoundInDb = Db::fetchOne("SELECT idaction from " . Common::prefixTable('log_action') . " WHERE name = ?", $pageUrlFoundInDb);
        $this->assertNotEmpty($actionIdFoundInDb, "Action $pageUrlFoundInDb was not found in the " . Common::prefixTable('log_action') . " table.");
        return $actionIdFoundInDb;
    }

    /**
     * @return array
     */
    private function insertActions()
    {
        $pageUrlFoundInDb = 'example.com/found-in-db';
        $actionIdFoundInDb = $this->insertPageUrlAsAction($pageUrlFoundInDb);

        // Adding some other actions to make test case more realistic
        $this->insertPageUrlAsAction('example.net/found-in-db-bis');
        $this->insertPageUrlAsAction('example.net/found-in-db-ter');
        $this->insertPageUrlAsAction('example.net/page-not-found');

        return array($pageUrlFoundInDb, $actionIdFoundInDb);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }

    public function test_willBeArchived_ByDefault_AllSegmentsWillBeArchived()
    {
        $this->assertWillBeArchived($this->exampleSegment);
    }

    public function test_willBeArchived_SegmentsWillNotBeArchivedWhenBrowserArchivingIsDisabledAndNoSuchSegmentExists()
    {
        $this->disableSegmentBrowserArchiving();
        $this->assertNotWillBeArchived($this->exampleSegment);
    }

    public function test_willBeArchived_SegmentsWillBeArchivedWhenBrowserArchivingIsDisabledButBrowserSegmentsArchivingEnabled()
    {
        $this->disableBrowserArchiving();
        $this->assertWillBeArchived($this->exampleSegment);
    }

    public function test_willSegmentBeArchived_SegmentsWillBeArchivedWhenBrowserArchivingDisabledAndSegmentExistsNotAutoArchiveAndSegmentBrowserArchivingDisabled()
    {
        $this->disableSegmentBrowserArchiving();

        SegmentEditorApi::getInstance()->add('My Name', $this->exampleSegment, $idSite = false, $autoArchive = false);

        $this->assertWillBeArchived($this->exampleSegment);
    }

    public function test_willSegmentBeArchived_SegmentsWillBeArchivedWhenBrowserArchivingDisabledButSegmentExistsWithAuthoArchive()
    {
        $this->disableSegmentBrowserArchiving();

        SegmentEditorApi::getInstance()->add('My Name', $this->exampleSegment, $idSite = false, $autoArchive = true);

        $this->assertWillBeArchived($this->exampleSegment);
    }

    public function test_willBeArchived_AnEmptySegmentShouldBeAlwaysArchived()
    {
        $this->assertWillBeArchived(false);

        $this->disableSegmentBrowserArchiving();
        $this->assertWillBeArchived(false);
    }

    /**
     * @dataProvider getTestDataForCombine
     */
    public function test_combine_shouldCombineSegmentConditionsProperly($segment, $operator, $toCombine, $expected)
    {
        $newSegment = Segment::combine($segment, $operator, $toCombine);
        $this->assertEquals($expected, $newSegment);
    }

    public function getTestDataForCombine()
    {
        return [
            ['', ';', '', ''],
            ['browserCode==ff;visitCount>1', ';', '', 'browserCode==ff;visitCount>1'],
            ['', ';', 'visitCount>1', 'visitCount>1'],
            ['browserCode==ff;visitCount>1', ';', 'visitCount>1', 'browserCode==ff;visitCount>1'],
            ['visitCount>1;browserCode==ff', ';', 'visitCount>1', 'visitCount>1;browserCode==ff'],
            ['visitCount>1,browserCode==ff', ';', 'visitCount>1', 'visitCount>1,browserCode==ff;visitCount>1'],
            ['browserCode==ff', ';', 'visitCount>1', 'browserCode==ff;visitCount>1'],
            ['browserCode==ff;visitCount>1', ',', 'visitCount>2', 'browserCode==ff;visitCount>1,visitCount>2'],
            ['visitorType==new', ';', 'visitorType==new', 'visitorType==new'],

            // urlencoding test
            [urlencode('browserCode==ff;visitCount>1'), ';', 'visitCount>1', urlencode('browserCode==ff;visitCount>1')],
            ['browserCode==ff;visitCount>1', ';', urlencode('visitCount>1'), 'browserCode==ff;visitCount>1'],
            ['browserCode==ff;' . urlencode('visitCount>1'), ';', 'visitCount>1', 'browserCode==ff;' . urlencode('visitCount>1')],
        ];
    }

    /**
     * @dataProvider getTestDataForGetStoredSegmentName
     */
    public function test_getStoredSegmentName($segment, $expectedName)
    {
        SegmentEditorApi::getInstance()->add('test segment 1', 'browserCode==ff');
        SegmentEditorApi::getInstance()->add('test segment 2', urlencode('browserCode==ch'));
        SegmentEditorApi::getInstance()->add('test segment 3', 'pageUrl=@' . urlencode('/a/b?d=blahfty'));
        SegmentEditorApi::getInstance()->add('test segment 4', 'pageUrl=@' . urlencode(urlencode('/a/b?d=wafty')));
        SegmentEditorApi::getInstance()->add('test segment 5', urlencode('pageUrl=@' . urlencode(urlencode('/a/b?d=woo'))));

        $segmentObj = new Segment($segment, [1]);
        $this->assertEquals($expectedName, $segmentObj->getStoredSegmentName(1));
    }

    public function getTestDataForGetStoredSegmentName()
    {
        return [
            ['browserCode==ff', 'test segment 1'],
            [urlencode('browserCode==ff'), 'test segment 1'],

            ['browserCode==ch', 'test segment 2'],
            [urlencode('browserCode==ch'), 'test segment 2'],

            ['pageUrl=@' . urlencode('/a/b?d=blahfty'), 'test segment 3'],
            ['pageUrl=@' . urlencode(urlencode('/a/b?d=blahfty')), 'test segment 3'],

            ['pageUrl=@' . urlencode(urlencode('/a/b?d=wafty')), 'test segment 4'],
            [urlencode('pageUrl=@' . urlencode(urlencode('/a/b?d=wafty'))), 'test segment 4'],

            ['pageUrl=@' . urlencode(urlencode('/a/b?d=woo')), 'test segment 5'],
            [urlencode('pageUrl=@' . urlencode(urlencode('/a/b?d=woo'))), 'test segment 5'],

            // these test cases won't pass because the value is encoded, but the operator isn't in one of the segments. kept here just
            // so there's a `record that they won't work
            // ['pageUrl=@' . urlencode('/a/b?d=wafty'), 'test segment 4'],
            // [urlencode('pageUrl=@' . urlencode('/a/b?d=wafty')), 'test segment 4'],
            // [urlencode('pageUrl=@' . urlencode('/a/b?d=woo')), 'test segment 5'],
            // ['pageUrl=@' . urlencode('/a/b?d=woo'), 'test segment 5'],
        ];
    }

    private function assertWillBeArchived($segmentString)
    {
        $this->assertTrue($this->willSegmentByArchived($segmentString));
    }

    private function assertNotWillBeArchived($segmentString)
    {
        $this->assertFalse($this->willSegmentByArchived($segmentString));
    }

    private function willSegmentByArchived($segmentString)
    {
        $segment = new Segment($segmentString, $idSites = array(1));

        return $segment->willBeArchived();
    }

    private function disableBrowserArchiving()
    {
        Rules::setBrowserTriggerArchiving(false);
    }

    private function disableSegmentBrowserArchiving()
    {
        $this->disableBrowserArchiving();
        $config = Config::getInstance();
        $general = $config->General;
        $general['browser_archiving_disabled_enforce'] = '1';
        $config->General = $general;
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'observers.global' => [
                ['Segment.addSegments', \Piwik\DI::value(function (Segment\SegmentsList $list) {
                    $segment = new \Piwik\Plugin\Segment();
                    $segment->setSegment('customSegment');
                    $segment->setType(\Piwik\Plugin\Segment::TYPE_DIMENSION);
                    $segment->setName('Custom Segment');
                    $segment->setSqlSegment('(UNIX_TIMESTAMP(log_visit.visit_first_action_time) - log_visit.visitor_seconds_since_first)');
                    $list->addSegment($segment);
                })],
            ],
        ];
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
        $fixture->extraPluginsToLoad = ['ExamplePlugin'];
    }

    private function defineEntitiesNotDirectlyJoinableToVisit()
    {
        // create database tables
        DbHelper::createTable('log_thing', '
            `idlogthing` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `idsite` INT UNSIGNED NOT NULL,
            `value` INT UNSIGNED NOT NULL,
            `name` VARCHAR(100) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`idlogthing`)
        ');
        DbHelper::createTable('log_thing_event', '
            `idlogthingevent` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `idsite` INT UNSIGNED NOT NULL,
            `idlogthing` BIGINT UNSIGNED NOT NULL,
            `idvisit` BIGINT(10) UNSIGNED NOT NULL,
            `server_time` DATETIME NOT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`idlogthingevent`)
        ');

        // add logtable classes
        $logThings = new class () extends LogTable {
            public function getName()
            {
                return 'log_thing';
            }

            public function getIdColumn()
            {
                return 'idlogthing';
            }

            public function getPrimaryKey()
            {
                return ['idlogthing'];
            }

            public function getWaysToJoinToOtherLogTables()
            {
                return ['log_thing_event' => 'idlogthing'];
            }
        };

        $logThingEvents = new class () extends LogTable {
            public function getname()
            {
                return 'log_thing_event';
            }

            public function getIdColumn()
            {
                return 'idlogthingevent';
            }

            public function getColumnToJoinOnIdVisit()
            {
                return 'idvisit';
            }

            public function getPrimaryKey()
            {
                return array('idlogthingevent');
            }

            public function getDateTimeColumn()
            {
                return 'server_time';
            }
        };

        Piwik::addAction('LogTables.addLogTables', function (&$logTables) use ($logThings, $logThingEvents) {
            $logTables[] = $logThings;
            $logTables[] = $logThingEvents;
        });

        // add Dimension classes
        $thingEventIdDimension = new class () extends Dimension {
            protected $nameSingular = 'CustomReports_ThingEvent';
            protected $namePlural = 'CustomReports_ThingEvents';
            protected $segmentName = 'thingEventId';
            protected $category = 'CustomReports_Things';
            protected $dbTableName = 'log_thing_event';
            protected $columnName = 'idlogthingevent';
            protected $sqlSegment = 'log_thing_event.idlogthingevent';

            public function getId()
            {
                return 'CustomReports.ThingEvent';
            }

            public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
            {
                $metric = $dimensionMetricFactory->createCustomMetric('nb_things', 'things', ArchivedMetric::AGGREGATION_COUNT);
                $metricsList->addMetric($metric);
            }
        };

        $thingValueDimension = new class () extends Dimension {
            protected $nameSingular = 'CustomReports_ThingValue';
            protected $namePlural = 'CustomReports_ThingValues';
            protected $segmentName = 'thingValue';
            protected $category = 'CustomReports_Things';
            protected $dbTableName = 'log_thing';
            protected $columnName = 'value';
            protected $sqlSegment = 'log_thing.value';

            public function getId()
            {
                return 'CustomReports.ThingValue';
            }

            public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
            {
                parent::configureMetrics($metricsList, $dimensionMetricFactory);

                $metric = $dimensionMetricFactory->createCustomMetric('sum_thing_value', 'thing value', ArchivedMetric::AGGREGATION_SUM);
                $metricsList->addMetric($metric);

                $metric = $dimensionMetricFactory->createCustomMetric('max_thing_value', 'thing value', ArchivedMetric::AGGREGATION_MAX);
                $metricsList->addMetric($metric);

                $metric = $dimensionMetricFactory->createCustomMetric('min_thing_value', 'thing value', ArchivedMetric::AGGREGATION_MIN);
                $metricsList->addMetric($metric);
            }
        };

        $thingNameDimension = new class () extends Dimension {
            protected $nameSingular = 'CustomReports_ThingName';
            protected $namePlural = 'CustomReports_ThingNames';
            protected $segmentName = 'thingName';
            protected $category = 'CustomReports_Things';
            protected $dbTableName = 'log_thing';
            protected $columnName = 'name';
            protected $sqlSegment = 'log_thing.name';

            public function getId()
            {
                return 'CustomReports.ThingName';
            }
        };

        $thingCategoryDimension = new class () extends Dimension {
            protected $nameSingular = 'CustomReports_ThingCategory';
            protected $namePlural = 'CustomReports_ThingCategories';
            protected $segmentName = 'thingCategory';
            protected $category = 'CustomReports_Things';
            protected $dbTableName = 'log_thing_event';
            protected $columnName = 'category';
            protected $sqlSegment = 'log_thing_event.category';

            public function getId()
            {
                return 'CustomReports.ThingCategory';
            }
        };

        Piwik::addAction('Dimension.addDimensions', function (&$dimensions) use ($thingEventIdDimension, $thingValueDimension, $thingNameDimension, $thingCategoryDimension) {
            $dimensions[] = $thingEventIdDimension;
            $dimensions[] = $thingValueDimension;
            $dimensions[] = $thingNameDimension;
            $dimensions[] = $thingCategoryDimension;
        });
    }
}
