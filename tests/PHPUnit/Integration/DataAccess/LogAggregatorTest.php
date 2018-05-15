<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;
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
     * @var LogAggregator
     */
    private $logAggregator;

    public function setUp()
    {
        parent::setUp();

        $idSite = 1;

        if (!Fixture::siteCreated($idSite)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }

        $site = new Site($idSite);
        $date = Date::factory('2012-01-01');
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
                0 => '2012-01-01 00:00:00',
                1 => '2012-01-31 23:59:59',
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
                0 => '2012-01-01 00:00:00',
                1 => '2012-01-31 23:59:59',
                2 => 1
            )
        );
        $this->assertSame($expected, $query);
    }

}
