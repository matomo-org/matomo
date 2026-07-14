<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\Config;
use Piwik\Config\DatabaseConfig;
use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Db\Schema;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestDataHelper\LogHelper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Updater\Migration\Db as DbMigration;

/**
 * @group Core
 * @group DataAccess
 * @group LogAggregator
 * @group LogAggregatorTest
 */
class LogAggregatorTest extends IntegrationTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture;

    /**
     * @var LogAggregator
     */
    private $logAggregator;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var Period
     */
    private $period;

    public function setUp(): void
    {
        parent::setUp();

        $idSite = 1;

        $this->site = new Site($idSite);
        $date = Date::factory('2010-03-06');
        $this->period = Period\Factory::build('month', $date);
        $segment = new Segment('', [$this->site->getId()]);

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
    }

    /**
     * @dataProvider getSelectDimensionTestData
     */
    public function testGetSelectDimensions($dimensions, $tableName, $expectedResult)
    {
        $class = new \ReflectionClass(LogAggregator::class);
        $method = $class->getMethod('getSelectDimensions');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $output = $method->invoke($this->logAggregator, $dimensions, $tableName);
        $this->assertEquals($expectedResult, $output);
    }

    public function getSelectDimensionTestData(): iterable
    {
        yield 'normal column names' => [
            ['column', 'column2'],
            'log_visit',
            ['log_visit.column AS `column`', 'log_visit.column2 AS `column2`'],
        ];

        yield 'normal column names with alias' => [
            ['alias' => 'column', 'alias2' => 'column2'],
            'log_conversion',
            ['log_conversion.column AS `alias`', 'log_conversion.column2 AS `alias2`'],
        ];

        yield 'normal column names with and without alias' => [
            ['alias' => 'column', 'column2'],
            'log_conversion',
            ['log_conversion.column AS `alias`', 'log_conversion.column2 AS `column2`'],
        ];

        yield 'column expression' => [
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
        ];

        yield 'column expression with alias' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, '')) AS `alias`"],
        ];

        yield 'mixed dimension content' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))", 'mycolumn', 'newalias' => 'column2'],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, '')) AS `alias`", 'log_conversion.mycolumn AS `mycolumn`', 'log_conversion.column2 AS `newalias`'],
        ];
    }


    /**
     * @dataProvider getGroupByDimensionTestData
     */
    public function testGetGroupByDimensions($dimensions, $tableName, $expectedResult)
    {
        $class = new \ReflectionClass(LogAggregator::class);
        $method = $class->getMethod('getGroupByDimensions');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $output = $method->invoke($this->logAggregator, $dimensions, $tableName);
        $this->assertEquals($expectedResult, $output);
    }

    public function getGroupByDimensionTestData(): iterable
    {
        yield 'normal column names' => [
            ['column', 'column2'],
            'log_visit',
            ['log_visit.column', 'log_visit.column2'],
        ];

        yield 'normal column names with alias' => [
            ['alias' => 'column', 'alias2' => 'column2'],
            'log_conversion',
            ['alias', 'alias2'],
        ];

        yield 'normal column names with and without alias' => [
            ['alias' => 'column', 'column2'],
            'log_conversion',
            ['alias', 'log_conversion.column2'],
        ];

        yield 'column expression' => [
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
        ];

        yield 'column expression with alias' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ['alias'],
        ];

        yield 'mixed dimension content' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))", 'mycolumn', 'newalias' => 'column2'],
            'log_conversion',
            ['alias', 'log_conversion.mycolumn', 'newalias'],
        ];
    }

    public function testGenerateQuery()
    {
        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $expected = array(
            'sql' => 'SELECT /* sites 1 */ /* 2010-03-01,2010-03-31 */
				test, test2
			FROM
				log_visit AS log_visit
			WHERE
				1=1
			ORDER BY
				5',
            'bind' => array (
                0 => '2010-03-01 00:00:00',
                1 => '2010-03-31 23:59:59',
                2 => 1,
            ),
        );
        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryWithSegmentShouldNotUseTmpTableWhenNotEnabled()
    {
        $segment = new Segment('userId==1', array($this->site->getId()));

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);

        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $expected = array(
            'sql' => 'SELECT /* segmenthash 4eaf469650796451c610972d0ca1e9e8 */ /* sites 1 */ /* 2010-03-01,2010-03-31 */
				test, test2
			FROM
				log_visit AS log_visit
			WHERE
				( 1=1 )
                AND
                (log_visit.user_id = ?)
			ORDER BY
				5',
            'bind' => array (
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
                '1',
            ),
        );
        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryWithSegmentShouldUseTmpTableWhenEnabled()
    {
        $segment = new Segment('userId==1', array($this->site->getId()));

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
        $this->logAggregator->allowUsageSegmentCache();

        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $expected = array(
            'sql' => 'SELECT /* segmenthash 4eaf469650796451c610972d0ca1e9e8 */ /* sites 1 */ /* 2010-03-01,2010-03-31 */
				test, test2
			FROM
				logtmpsegment0e053be69df974017fba4276a0d4347d AS logtmpsegment0e053be69df974017fba4276a0d4347d INNER JOIN log_visit AS log_visit ON log_visit.idvisit = logtmpsegment0e053be69df974017fba4276a0d4347d.idvisit
			WHERE
				1=1
			ORDER BY
				5',
            'bind' => array (
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
            ),
        );
        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryWithSegmentVisitLogRightJoinShouldKeepWhereCondition()
    {
        $segment = new Segment('userId==1', [$this->site->getId()]);

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
        $this->logAggregator->allowUsageSegmentCache();

        $select = "MINUTE(log_link_visit_action.server_time) AS 'CoreHome.ServerMinute', max(log_link_visit_action.pageview_position) AS 'max_actions_pageviewposition'";
        $from = ['log_link_visit_action', ['table' => 'log_visit', 'join' => 'RIGHT JOIN']];
        $where = 'log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite IN (?)';
        $orderBy = 'max_actions_pageviewposition';

        $query = $this->logAggregator->generateQuery($select, $from, $where, false, $orderBy);

        $expected = [
            'sql' => "SELECT /* segmenthash 4eaf469650796451c610972d0ca1e9e8 */ /* sites 1 */ /* 2010-03-01,2010-03-31 */
				MINUTE(log_link_visit_action.server_time) AS 'CoreHome.ServerMinute', max(log_link_visit_action.pageview_position) AS 'max_actions_pageviewposition'
			FROM
				logtmpsegment0e053be69df974017fba4276a0d4347d AS logtmpsegment0e053be69df974017fba4276a0d4347d INNER JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = logtmpsegment0e053be69df974017fba4276a0d4347d.idvisit RIGHT JOIN log_visit AS log_visit ON log_visit.idvisit = logtmpsegment0e053be69df974017fba4276a0d4347d.idvisit
			WHERE
				log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite IN (?)
			ORDER BY
				max_actions_pageviewposition",
            'bind' => [
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
            ],
        ];
        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryWithSegmentVisitLogJoinRightJoinOnOtherTableShouldKeepWhereCondition()
    {
        $segment = new Segment('userId==1', [$this->site->getId()]);

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
        $this->logAggregator->allowUsageSegmentCache();

        $select = "log_link_visit_action.server_time AS 'CoreHome.ServerMinute', log_visit.visit_total_searches AS 'total_searches', log_conversion.items AS 'items'";
        $from = ['log_link_visit_action', 'log_visit', ['table' => 'log_conversion', 'join' => 'right join']];
        $where = 'log_conversion.server_time >= ?
				AND log_conversion.server_time <= ?
				AND log_conversion.idsite IN (?)';
        $orderBy = 'max_actions_pageviewposition';

        $query = $this->logAggregator->generateQuery($select, $from, $where, false, $orderBy);

        $expected = [
            'sql' => "SELECT /* segmenthash 4eaf469650796451c610972d0ca1e9e8 */ /* sites 1 */ /* 2010-03-01,2010-03-31 */
				log_link_visit_action.server_time AS 'CoreHome.ServerMinute', log_visit.visit_total_searches AS 'total_searches', log_conversion.items AS 'items'
			FROM
				logtmpsegment0e053be69df974017fba4276a0d4347d AS logtmpsegment0e053be69df974017fba4276a0d4347d INNER JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = logtmpsegment0e053be69df974017fba4276a0d4347d.idvisit LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = logtmpsegment0e053be69df974017fba4276a0d4347d.idvisit right join log_conversion AS log_conversion ON log_conversion.idvisit = logtmpsegment0e053be69df974017fba4276a0d4347d.idvisit
			WHERE
				log_conversion.server_time >= ?
				AND log_conversion.server_time <= ?
				AND log_conversion.idsite IN (?)
			ORDER BY
				max_actions_pageviewposition",
            'bind' => [
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
            ],
        ];
        $this->assertSame($expected, $query);
    }

    public function testSetMaxExecutionTimeOfArchivingQueries()
    {
        if (SystemTestCase::isMysqli()) {
            // See https://github.com/matomo-org/matomo/issues/17871
            $this->markTestSkipped('Max execution query hint does not work for Mysqli.');
        }

        // limit query to one milli second
        Config::getInstance()->General['archiving_query_max_execution_time'] = 0.001;
        try {
            $this->logAggregator->getDb()->query('SELECT *, SLEEP(5) FROM ' . Common::prefixTable('log_visit'));
            $this->fail('Query was not aborted by max execution limit');
        } catch (\Zend_Db_Statement_Exception $e) {
            $isMaxExecutionTimeError = $this->logAggregator->getDb()->isErrNo($e, DbMigration::ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_QUERY_INTERRUPTED)
                || $this->logAggregator->getDb()->isErrNo($e, DbMigration::ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_SORT_ABORTED)
                || strpos($e->getMessage(), 'maximum statement execution time exceeded') !== false
                || strpos($e->getMessage(), 'max_statement_time exceeded') !== false;

            $this->assertTrue($isMaxExecutionTimeError, $e->getMessage());
        }
    }

    private function setSqlRequirePrimaryKeySetting($val)
    {
        try {
            $this->logAggregator->getDb()->exec('SET SESSION sql_require_primary_key=' . $val);
        } catch (\Exception $e) {
            if ($this->logAggregator->getDb()->isErrNo($e, 1193)) {
                // ignore General error: 1193 Unknown system variable 'sql_require_primary_key'
                try {
                    // on mariadb this might work
                    $this->logAggregator->getDb()->exec('SET GLOBAL innodb_force_primary_key = ' . ($val ? 'on' : 'off'));
                } catch (\Exception $e) {
                    if ($this->logAggregator->getDb()->isErrNo($e, 1193)) {
                        // ignore General error: 1193 Unknown system variable 'sql_require_primary_key'
                        return;
                    } elseif ($this->logAggregator->getDb()->isErrNo($e, 1229)) {
                        try {
                            // Mariadb: General error: 1229 Variable 'innodb_force_primary_key' is a GLOBAL variable and should be set with SET GLOBAL
                            $this->logAggregator->getDb()->exec('SET GLOBAL innodb_force_primary_key=' . $val);
                        } catch (\Exception $e) {
                            return;
                        }
                    } else {
                        throw $e;
                    }
                }
            } else {
                throw $e;
            }
        }
    }

    public function testGenerateQueryWithSegmentShouldUseTmpTableWhenEnabledAndPrimaryKeyRequired()
    {
        $segment = new Segment('userId==2', array($this->site->getId()));

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
        $this->logAggregator->allowUsageSegmentCache();

        $this->setSqlRequirePrimaryKeySetting(1);

        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $this->setSqlRequirePrimaryKeySetting(0);// reset variable
        $expected = array(
            'sql' => 'SELECT /* segmenthash 4a4d16d6897e7fed2d5d151016a5a19c */ /* sites 1 */ /* 2010-03-01,2010-03-31 */
				test, test2
			FROM
				logtmpsegment4ef74412006a3160b17ca5fe99a5f866 AS logtmpsegment4ef74412006a3160b17ca5fe99a5f866 INNER JOIN log_visit AS log_visit ON log_visit.idvisit = logtmpsegment4ef74412006a3160b17ca5fe99a5f866.idvisit
			WHERE
				1=1
			ORDER BY
				5',
            'bind' => array (
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
            ),
        );
        $this->assertSame($expected, $query);
    }

    public function testGenerateQuerySwitchesSupportsUncommittedToTrueWhenSupports()
    {
        $segment = new Segment('userId==1111', array($this->site->getId()));

        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
        $this->logAggregator->allowUsageSegmentCache();

        $this->setSqlRequirePrimaryKeySetting(1);

        $db = Db::get();

        $db->setSupportsTransactionLevelForNonLockingReads(null);

        $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $this->setSqlRequirePrimaryKeySetting(0);

        $this->assertTrue($db->getSupportsTransactionLevelForNonLockingReads());
    }

    public function testGetSegmentTmpTableName()
    {
        $this->assertEquals('logtmpsegmentcc2efa0acbd5f209e8ee8618e72f3f9b', $this->logAggregator->getSegmentTmpTableName());
    }

    public function testGetSegmentTmpTableNameWithLongPrefix()
    {
        Config::getInstance()->database['tables_prefix'] = 'myverylongtableprefixtestfoobartest';
        $this->assertEquals('logtmpsegmentcc2efa0acbd5f209', $this->logAggregator->getSegmentTmpTableName());
    }

    public function testGetSegmentTableSqlShouldAddJoinHintAsCommentIfEnabled()
    {
        DatabaseConfig::setConfigValue('enable_segment_first_table_join_prefix', '1');

        $query = $this->getSegmentSql();
        $expected = [
            'sql' => "SELECT /*+ JOIN_PREFIX(log_visit) */
				distinct log_visit.idvisit as idvisit
			FROM
				log_visit AS log_visit
			WHERE
				( log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite IN (?) )
                AND
                (log_visit.user_id = ?)
			ORDER BY
				log_visit.idvisit ASC",
            'bind' => [
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
                '1',
            ],
        ];
        $this->assertSame($expected, $query);
    }

    public function testGetSegmentTableSqlShouldNotAddJoinHintAsCommentIfDisabled()
    {
        DatabaseConfig::setConfigValue('enable_segment_first_table_join_prefix', '0');

        $query = $this->getSegmentSql();
        $expected = [
            'sql' => "
			SELECT
				distinct log_visit.idvisit as idvisit
			FROM
				log_visit AS log_visit
			WHERE
				( log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite IN (?) )
                AND
                (log_visit.user_id = ?)
			ORDER BY
				log_visit.idvisit ASC",
            'bind' => [
                '2010-03-01 00:00:00',
                '2010-03-31 23:59:59',
                1,
                '1',
            ],
        ];
        $this->assertSame($expected, $query);
    }

    private function getSegmentSql()
    {
        $segment = new Segment('userId==1', [$this->site->getId()]);
        $params = new Parameters($this->site, $this->period, $segment);
        $this->logAggregator = new LogAggregator($params);
        return $this->logAggregator->getSegmentTableSql();
    }

    public function testGenerateQueryWithQueryHintShouldAddQueryHintAsComment()
    {
        $this->logAggregator->setQueryOriginHint('MyPluginName');
        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $expected = array(
            'sql' => 'SELECT /* sites 1 */ /* 2010-03-01,2010-03-31 */ /* MyPluginName */
				test, test2
			FROM
				log_visit AS log_visit
			WHERE
				1=1
			ORDER BY
				5',
            'bind' => array (
                0 => '2010-03-01 00:00:00',
                1 => '2010-03-31 23:59:59',
                2 => 1,
            ),
        );
        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryShouldAddJoinQueryHintAsCommentIfEnabled()
    {
        DatabaseConfig::setConfigValue('enable_first_table_join_prefix', '1');
        $this->logAggregator->setQueryOriginHint('MyPluginName');
        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $expected = [
            'sql' => 'SELECT /*+ JOIN_PREFIX(log_visit) */ /* sites 1 */ /* 2010-03-01,2010-03-31 */ /* MyPluginName */
				test, test2
			FROM
				log_visit AS log_visit
			WHERE
				1=1
			ORDER BY
				5',
            'bind' => [
                0 => '2010-03-01 00:00:00',
                1 => '2010-03-31 23:59:59',
                2 => 1,
            ],
        ];
        $this->assertSame($expected, $query);
    }

    public function testQueryVisitsByDimensionShouldAddJoinQueryHintOriginHintMaxExecutionTimeHintIfEnabled()
    {
        $dimensions = [
            'CASE WHEN HOUR(log_visit.visit_first_action_time) <= 11 THEN \'l\'' .
            'ELSE \'r\'' .
            'END AS label',
        ];

        DatabaseConfig::setConfigValue('enable_first_table_join_prefix', '1');
        DatabaseConfig::setConfigValue('schema', 'Mysql');
        Schema::unsetInstance();

        $this->logAggregator->setQueryOriginHint('MyPluginName');

        $query = $this->logAggregator->getQueryByDimensionSql(
            $dimensions,
            false,
            [],
            false,
            false,
            false,
            5,
            false
        );

        $expected = [
            'sql' => "SELECT /*+ MAX_EXECUTION_TIME(5000) JOIN_PREFIX(log_visit) */ /* sites 1 */ /* 2010-03-01,2010-03-31 */ /* MyPluginName */
				CASE WHEN HOUR(log_visit.visit_first_action_time) <= 11 THEN 'l'ELSE 'r'END AS label, 
			count(distinct log_visit.idvisitor) AS `1`, 
			count(*) AS `2`, 
			sum(log_visit.visit_total_actions) AS `3`, 
			max(log_visit.visit_total_actions) AS `4`, 
			sum(log_visit.visit_total_time) AS `5`, 
			sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) AS `6`, 
			sum(case log_visit.visit_goal_converted when 1 then 1 else 0 end) AS `7`, 
			count(distinct log_visit.user_id) AS `39`
			FROM
				log_visit AS log_visit
			WHERE
				log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite IN (?)
			GROUP BY
				label",
            'bind' => [
                0 => '2010-03-01 00:00:00',
                1 => '2010-03-31 23:59:59',
                2 => 1,
            ],
        ];
        $this->assertSame($expected, $query);
    }

    public function testQueryVisitsByDimensionWithComplexDimensionSelect()
    {
        $dimensions = [
            'CASE WHEN HOUR(log_visit.visit_first_action_time) <= 11 THEN \'l\'' .
            'ELSE \'r\'' .
            'END AS label',
        ];

        /** @var \Zend_Db_Statement $query */
        $query = $this->logAggregator->queryVisitsByDimension($dimensions);
        $result = $query->fetchAll();

        $expected = [
            [
                'label' => 'l',
                1 => '1',
                2 => '1',
                3 => '7',
                4 => '7',
                5 => '1621',
                6 => '0',
                7 => '1',
                39 => '0',
            ],
            [
                'label' => 'r',
                1 => '1',
                2 => '1',
                3 => '1',
                4 => '1',
                5 => '1',
                6 => '1',
                7 => '1',
                39 => '0',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testQueryEcommerceItemsExcludesPricesAboveMaxAllowedRevenueFromRevenueAndPriceMetrics()
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('log_action') . ' (name, hash, type) VALUES (?, ?, ?)',
            ['Overflow SKU', 0, Action::TYPE_ECOMMERCE_ITEM_SKU]
        );
        $idActionSku = Db::fetchOne('SELECT LAST_INSERT_ID()');

        $logInserter = new LogHelper();
        $visit = $logInserter->insertVisit([
            'visit_last_action_time' => '2010-03-06 12:00:00',
        ]);

        $logInserter->insertConversionItem($visit['idvisit'], 'normal-order', [
            'server_time' => '2010-03-06 12:00:00',
            'idaction_sku' => $idActionSku,
            'price' => 40,
            'quantity' => 4,
        ]);
        $logInserter->insertConversionItem($visit['idvisit'], 'zero-quantity-order', [
            'server_time' => '2010-03-06 12:00:00',
            'idaction_sku' => $idActionSku,
            'price' => -50,
            'quantity' => 0,
        ]);
        // Above the tracking bound but small enough that quantity * price would NOT overflow
        // MySQL DOUBLE: must still be excluded so archiving matches what tracking now rejects.
        $logInserter->insertConversionItem($visit['idvisit'], 'above-cap-order', [
            'server_time' => '2010-03-06 12:00:00',
            'idaction_sku' => $idActionSku,
            'price' => GoalManager::MAX_ALLOWED_REVENUE * 2,
            'quantity' => 1,
        ]);
        // Extreme legacy value whose quantity * price would overflow MySQL DOUBLE (error 1690).
        $logInserter->insertConversionItem($visit['idvisit'], 'overflow-order', [
            'server_time' => '2010-03-06 12:00:00',
            'idaction_sku' => $idActionSku,
            'price' => '1e308',
            'quantity' => 999999,
        ]);

        $query = $this->logAggregator->queryEcommerceItems('idaction_sku');
        $rows = $query->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertSame('Overflow SKU', $rows[0]['label']);
        // Only the in-range rows contribute: revenue = 40 * 4 (+ 0 for the zero-quantity row).
        $this->assertSame(160.0, (float) $rows[0][Metrics::INDEX_ECOMMERCE_ITEM_REVENUE]);
        // The item price metric excludes both out-of-range rows too: price sum = 40 + (-50).
        $this->assertSame(-10.0, (float) $rows[0][Metrics::INDEX_ECOMMERCE_ITEM_PRICE]);
    }

    public function testQueryConversionsByDimensionExcludesOrderRevenueAboveMaxAllowedRevenue()
    {
        $logInserter = new LogHelper();
        $visit = $logInserter->insertVisit([
            'visit_last_action_time' => '2010-03-06 12:00:00',
        ]);

        $conversionDefaults = [
            'idgoal' => GoalManager::IDGOAL_ORDER,
            'server_time' => '2010-03-06 12:00:00',
        ];

        // In-range order: the only conversion whose money values should be counted.
        // The log_conversion primary key is (idvisit, idgoal, buster), so each order uses a
        // distinct buster to sit in the same idgoal group without colliding.
        $logInserter->insertConversion($visit['idvisit'], array_merge($conversionDefaults, [
            'idorder' => 'normal-order',
            'buster' => 1,
            'revenue' => 100,
            'revenue_subtotal' => 90,
            'revenue_tax' => 5,
            'revenue_shipping' => 4,
            'revenue_discount' => 1,
        ]));
        // Above the tracking bound but not large enough to overflow on its own: must still be
        // excluded so archiving matches what tracking now rejects.
        $logInserter->insertConversion($visit['idvisit'], array_merge($conversionDefaults, [
            'idorder' => 'above-cap-order',
            'buster' => 2,
            'revenue' => GoalManager::MAX_ALLOWED_REVENUE * 2,
            'revenue_subtotal' => GoalManager::MAX_ALLOWED_REVENUE * 2,
            'revenue_tax' => GoalManager::MAX_ALLOWED_REVENUE * 2,
            'revenue_shipping' => GoalManager::MAX_ALLOWED_REVENUE * 2,
            'revenue_discount' => GoalManager::MAX_ALLOWED_REVENUE * 2,
        ]));
        // Extreme legacy value: an unguarded SUM() would either abort archiving with error 1690
        // (MariaDB / MySQL 8) or silently collapse the whole group to 0 (MySQL 5.7).
        $logInserter->insertConversion($visit['idvisit'], array_merge($conversionDefaults, [
            'idorder' => 'overflow-order',
            'buster' => 3,
            'revenue' => '1e308',
            'revenue_subtotal' => '1e308',
            'revenue_tax' => '1e308',
            'revenue_shipping' => '1e308',
            'revenue_discount' => '1e308',
        ]));

        $query = $this->logAggregator->queryConversionsByDimension();
        $rows = $query->fetchAll();

        // The fixture also has conversions for other goals; isolate the ecommerce-order group.
        $orderRows = array_values(array_filter($rows, static function ($row) {
            return (int) $row['idgoal'] === GoalManager::IDGOAL_ORDER;
        }));

        $this->assertCount(1, $orderRows);
        $row = $orderRows[0];
        // Only the in-range order contributes to every order-level money metric.
        $this->assertSame(100.0, (float) $row[Metrics::INDEX_GOAL_REVENUE]);
        $this->assertSame(90.0, (float) $row[Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL]);
        $this->assertSame(5.0, (float) $row[Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_TAX]);
        $this->assertSame(4.0, (float) $row[Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING]);
        $this->assertSame(1.0, (float) $row[Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT]);
        // The three orders are still all counted; only their out-of-range money is excluded.
        $this->assertSame(3, (int) $row[Metrics::INDEX_GOAL_NB_CONVERSIONS]);
    }
}

LogAggregatorTest::$fixture = new OneVisitorTwoVisits();
