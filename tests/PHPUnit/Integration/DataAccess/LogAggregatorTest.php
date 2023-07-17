<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\Config;
use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
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
        $method->setAccessible(true);
        $output = $method->invoke($this->logAggregator, $dimensions, $tableName);
        $this->assertEquals($expectedResult, $output);
    }

    public function getSelectDimensionTestData(): iterable
    {
        yield 'normal column names' => [
            ['column', 'column2'],
            'log_visit',
            ['log_visit.column AS `column`', 'log_visit.column2 AS `column2`']
        ];

        yield 'normal column names with alias' => [
            ['alias' => 'column', 'alias2' => 'column2'],
            'log_conversion',
            ['log_conversion.column AS `alias`', 'log_conversion.column2 AS `alias2`']
        ];

        yield 'normal column names with and without alias' => [
            ['alias' => 'column', 'column2'],
            'log_conversion',
            ['log_conversion.column AS `alias`', 'log_conversion.column2 AS `column2`']
        ];

        yield 'column expression' => [
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"]
        ];

        yield 'column expression with alias' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, '')) AS `alias`"]
        ];

        yield 'mixed dimension content' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))", 'mycolumn', 'newalias' => 'column2'],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, '')) AS `alias`", 'log_conversion.mycolumn AS `mycolumn`', 'log_conversion.column2 AS `newalias`']
        ];
    }


    /**
     * @dataProvider getGroupByDimensionTestData
     */
    public function testGetGroupByDimensions($dimensions, $tableName, $expectedResult)
    {
        $class = new \ReflectionClass(LogAggregator::class);
        $method = $class->getMethod('getGroupByDimensions');
        $method->setAccessible(true);
        $output = $method->invoke($this->logAggregator, $dimensions, $tableName);
        $this->assertEquals($expectedResult, $output);
    }

    public function getGroupByDimensionTestData(): iterable
    {
        yield 'normal column names' => [
            ['column', 'column2'],
            'log_visit',
            ['log_visit.column', 'log_visit.column2']
        ];

        yield 'normal column names with alias' => [
            ['alias' => 'column', 'alias2' => 'column2'],
            'log_conversion',
            ['alias', 'alias2']
        ];

        yield 'normal column names with and without alias' => [
            ['alias' => 'column', 'column2'],
            'log_conversion',
            ['alias', 'log_conversion.column2']
        ];

        yield 'column expression' => [
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ["CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"]
        ];

        yield 'column expression with alias' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))"],
            'log_conversion',
            ['alias']
        ];

        yield 'mixed dimension content' => [
            ['alias' => "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))", 'mycolumn', 'newalias' => 'column2'],
            'log_conversion',
            ['alias', 'log_conversion.mycolumn', 'newalias']
        ];
    }

    public function test_generateQuery()
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
                2 => 1
            )
        );
        $this->assertSame($expected, $query);
    }

    public function test_generateQuery_withSegment_shouldNotUseTmpTableWhenNotEnabled()
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
                '1'
            )
        );
        $this->assertSame($expected, $query);
    }

    public function test_generateQuery_withSegment_shouldUseTmpTableWhenEnabled()
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
            )
        );
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
            $this->logAggregator->getDb()->query('SELECT SLEEP(5) FROM ' . Common::prefixTable('log_visit'));
            $this->fail('Query was not aborted by max execution limit');
        } catch (\Zend_Db_Statement_Exception $e) {
            $isMaxExecutionTimeError = $this->logAggregator->getDb()->isErrNo($e, DbMigration::ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_QUERY_INTERRUPTED)
                || $this->logAggregator->getDb()->isErrNo($e, DbMigration::ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_SORT_ABORTED)
                || strpos($e->getMessage(), 'maximum statement execution time exceeded') !== false;

            $this->assertTrue($isMaxExecutionTimeError);
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

    public function test_generateQuery_withSegment_shouldUseTmpTableWhenEnabledAndPrimaryKeyRequired()
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
            )
        );
        $this->assertSame($expected, $query);
    }

    public function test_getSegmentTmpTableName()
    {
        $this->assertEquals('logtmpsegmentcc2efa0acbd5f209e8ee8618e72f3f9b', $this->logAggregator->getSegmentTmpTableName());
    }

    public function test_getSegmentTmpTableNameWithLongPrefix()
    {
        Config::getInstance()->database['tables_prefix'] = 'myverylongtableprefixtestfoobartest';
        $this->assertEquals('logtmpsegmentcc2efa0acbd5f209', $this->logAggregator->getSegmentTmpTableName());
    }

    public function test_generateQuery_WithQueryHint_ShouldAddQueryHintAsComment()
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
                2 => 1
            )
        );
        $this->assertSame($expected, $query);
    }

    public function test_queryVisitsByDimension_withComplexDimensionSelect()
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
}

LogAggregatorTest::$fixture = new OneVisitorTwoVisits();
