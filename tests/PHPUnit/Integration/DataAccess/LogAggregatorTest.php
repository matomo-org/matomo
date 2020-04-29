<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\ArchivingStatus;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\Config;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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

    public function setUp()
    {
        parent::setUp();

        $idSite = 1;

        $site = new Site($idSite);
        $date = Date::factory('2010-03-06');
        $period = Period\Factory::build('month', $date);
        $segment = new Segment('', array($site->getId()));


        $params = new Parameters($site, $period, $segment);
        $this->logAggregator = new LogAggregator($params);
    }

    public function test_generateQuery()
    {
        $query = $this->logAggregator->generateQuery('test, test2', 'log_visit', '1=1', false, '5');

        $expected = array(
            'sql' => '
			SELECT
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
            'sql' => 'SELECT /* MyPluginName */
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

    public function test_logAggregatorUpdatesArchiveStatusExpireTime()
    {
        $t = Fixture::getTracker(self::$fixture->idSite, '2010-03-06 14:22:33');
        $t->setUrl('http://example.com/here/we/go');
        $t->doTrackPageView('here we go');

        Date::$now = strtotime('2015-03-04 00:08:04');

        $params = new Parameters(new Site(self::$fixture->idSite), Period\Factory::build('day', self::$fixture->dateTime), new Segment('', [self::$fixture->idSite]));
        $archiveStatus = StaticContainer::get(ArchivingStatus::class);
        $archiveStatus->archiveStarted($params);

        $locks = $this->getAllLocks();
        $this->assertCount(1, $locks);
        $expireTime = $locks[0]['expiry_time'];

        sleep(1);

        Date::$now = strtotime('2015-03-04 10:08:04');

        $this->logAggregator->queryVisitsByDimension(['visit_total_time']);

        $locks = $this->getAllLocks();
        $this->assertCount(1, $locks);
        $expireTimeNew = $locks[0]['expiry_time'];

        $this->assertGreaterThan($expireTime, $expireTimeNew);
    }

    private function getAllLocks()
    {
        return Db::fetchAll("SELECT `key`, expiry_time FROM `" . Common::prefixTable('locks') . '`');
    }
}

LogAggregatorTest::$fixture = new OneVisitorTwoVisits();
