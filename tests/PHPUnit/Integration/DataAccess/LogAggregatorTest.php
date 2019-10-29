<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
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
}

LogAggregatorTest::$fixture = new OneVisitorTwoVisits();
