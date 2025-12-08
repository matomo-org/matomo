<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\System;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\LogAggregator;
use Piwik\Period;
use Piwik\RankingQuery;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\ManyVisitsWithGeoIP;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group RankingQuery
 */
class RankingQueryTest extends SystemTestCase
{
    /**
     * @var ManyVisitsWithGeoIP
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var LogAggregator
     */
    private $logAggregator;

    public function setUp(): void
    {
        parent::setUp();

        $site = new Site(self::$fixture->idSite);
        $period = Period\Factory::build('month', self::$fixture->dateTime);
        $segment = new Segment('', [$site->getId()]);
        $params = new Parameters($site, $period, $segment);

        $this->logAggregator = new LogAggregator($params);
    }

    /**
     * Make sure the results are consistently structured across database vendors.
     *
     * We always expect the same order, with second-level results ordered by the given base query:
     *
     * 1. top-level rollup (both labels NULL)
     * 2. first-level rollups (secondary label NULL)
     * 3. first-level "Others"
     * 4. second-level results
     * 5. second-level "Others"
     *
     * @dataProvider getRollupResultStructureTestData
     */
    public function testRollupResultStructure(string $sortOrder, array $expectedResultSet): void
    {
        $select = '
            log_visit.config_browser_engine AS config_browser_engine,
            log_visit.custom_var_v1 AS custom_var_v1,
            COUNT(log_visit.idvisit) AS nb_visits
        ';

        $where = '
            log_visit.visit_last_action_time >= ?
            AND log_visit.visit_last_action_time <= ?
            AND log_visit.idsite IN (?)
            AND custom_var_v1 IS NOT NULL
        ';

        $from = 'log_visit';
        $groupBy = 'config_browser_engine, custom_var_v1';
        $orderBy = "nb_visits $sortOrder, config_browser_engine, custom_var_v1";

        $query = $this->logAggregator->generateQuery(
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            $limit = 0,
            $offset = 0,
            true
        );

        $rankingQuery = new RankingQuery(3);
        $rankingQuery->addLabelColumn(['config_browser_engine', 'custom_var_v1']);
        $rankingQuery->addColumn(['nb_visits'], 'sum');

        $query['sql'] = $rankingQuery->generateRankingQuery($query['sql'], true);
        $resultSet = $this->logAggregator->getDb()->query($query['sql'], $query['bind'])->fetchAll();

        self::assertEquals($expectedResultSet, $resultSet);
    }

    public function getRollupResultStructureTestData(): iterable
    {
        yield 'nb_visits DESC' => [
            'DESC',
            [
                [
                    'config_browser_engine' => null,
                    'custom_var_v1' => null,
                    'nb_visits' => 30,
                ],
                [
                    'config_browser_engine' => 'Gecko',
                    'custom_var_v1' => null,
                    'nb_visits' => 20,
                ],
                [
                    'config_browser_engine' => 'Blink',
                    'custom_var_v1' => null,
                    'nb_visits' => 4,
                ],
                [
                    'config_browser_engine' => 'WebKit',
                    'custom_var_v1' => null,
                    'nb_visits' => 4,
                ],
                [
                    'config_browser_engine' => RankingQuery::LABEL_SUMMARY_ROW,
                    'custom_var_v1' => null,
                    'nb_visits' => 2,
                ],
                [
                    'config_browser_engine' => 'Gecko',
                    'custom_var_v1' => 'Cvar1 value is 1',
                    'nb_visits' => 4,
                ],
                [
                    'config_browser_engine' => 'Blink',
                    'custom_var_v1' => 'Cvar1 value is 0',
                    'nb_visits' => 3,
                ],
                [
                    'config_browser_engine' => 'Gecko',
                    'custom_var_v1' => 'Cvar1 value is 0',
                    'nb_visits' => 2,
                ],
                [
                    'config_browser_engine' => 'Blink',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 1,
                ],
                [
                    'config_browser_engine' => 'Gecko',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 14,
                ],
                [
                    'config_browser_engine' => 'Trident',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 2,
                ],
                [
                    'config_browser_engine' => 'WebKit',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 4,
                ],
            ],
        ];

        yield 'nb_visits ASC' => [
            'ASC',
            [
                [
                    'config_browser_engine' => null,
                    'custom_var_v1' => null,
                    'nb_visits' => 30,
                ],
                [
                    'config_browser_engine' => 'Trident',
                    'custom_var_v1' => null,
                    'nb_visits' => 2,
                ],
                [
                    'config_browser_engine' => 'Blink',
                    'custom_var_v1' => null,
                    'nb_visits' => 4,
                ],
                [
                    'config_browser_engine' => 'WebKit',
                    'custom_var_v1' => null,
                    'nb_visits' => 4,
                ],
                [
                    'config_browser_engine' => RankingQuery::LABEL_SUMMARY_ROW,
                    'custom_var_v1' => null,
                    'nb_visits' => 20,
                ],
                [
                    'config_browser_engine' => 'Blink',
                    'custom_var_v1' => 'Cvar1 value is 3',
                    'nb_visits' => 1,
                ],
                [
                    'config_browser_engine' => 'Trident',
                    'custom_var_v1' => 'Cvar1 value is 1',
                    'nb_visits' => 1,
                ],
                [
                    'config_browser_engine' => 'Trident',
                    'custom_var_v1' => 'Cvar1 value is 2',
                    'nb_visits' => 1,
                ],
                [
                    'config_browser_engine' => 'Blink',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 3,
                ],
                [
                    'config_browser_engine' => 'Gecko',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 20,
                ],
                [
                    'config_browser_engine' => 'WebKit',
                    'custom_var_v1' => RankingQuery::LABEL_SUMMARY_ROW,
                    'nb_visits' => 4,
                ],
            ],
        ];
    }
}

RankingQueryTest::$fixture = new ManyVisitsWithGeoIP();
