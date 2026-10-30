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

    /**
     * The ranked rows and the "Others" row must match what the inner query sorts first.
     *
     * @dataProvider getRankingFollowsInnerQueryOrderTestData
     */
    public function testRankingFollowsInnerQueryOrder(
        string $select,
        string $orderBy,
        bool $wrapQuery,
        string $metric = 'nb_visits'
    ): void {
        $query = $this->generateVisitsQuery($select, 'label', $wrapQuery ? false : $orderBy);

        if ($wrapQuery) {
            $query['sql'] = "SELECT * FROM ( {$query['sql']} ) AS wrappedQuery ORDER BY $orderBy";
        }

        $innerRows = $this->logAggregator->getDb()->fetchAll($query['sql'], $query['bind']);
        self::assertGreaterThan(3, count($innerRows), 'The fixture needs more labels than the limit');

        $expectedRows = array_slice($innerRows, 0, 2);
        $expectedRows[] = [
            'label' => RankingQuery::LABEL_SUMMARY_ROW,
            $metric => array_sum(array_column(array_slice($innerRows, 2), $metric)),
        ];

        $rankingQuery = new RankingQuery(2);
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->addColumn($metric, 'sum');

        $resultRows = $rankingQuery->execute($query['sql'], $query['bind']);

        self::assertEquals($this->sortByLabel($expectedRows, $metric), $this->sortByLabel($resultRows, $metric));
    }

    public function getRankingFollowsInnerQueryOrderTestData(): iterable
    {
        yield 'ORDER BY an alias' => [
            'log_visit.config_browser_engine AS label, COUNT(log_visit.idvisit) AS nb_visits',
            'nb_visits DESC, label',
            false,
        ];

        yield 'ORDER BY an alias that ends a selected column name' => [
            'log_visit.visitor_count_visits AS label, COUNT(log_visit.idvisit) AS visits',
            'visits DESC, label',
            false,
            'visits',
        ];

        yield 'ORDER BY of a wrapped query' => [
            'log_visit.config_browser_engine AS label, COUNT(log_visit.idvisit) AS nb_visits',
            'wrappedQuery.nb_visits ASC, label DESC',
            true,
        ];
    }

    public function testRowsOutsideThePartitionValuesAreNotRanked(): void
    {
        $query = $this->generateVisitsQuery(
            'log_visit.config_browser_engine AS label,
            IF(log_visit.custom_var_v1 IS NULL, NULL, 1) AS type,
            COUNT(log_visit.idvisit) AS nb_visits',
            'label, type',
            'nb_visits DESC, label'
        );

        $innerRows = $this->logAggregator->getDb()->fetchAll($query['sql'], $query['bind']);
        $unpartitionedRows = array_filter($innerRows, function (array $row): bool {
            return null === $row['type'];
        });
        self::assertGreaterThan(1, count($unpartitionedRows), 'The fixture needs more labels than the limit');

        $rankingQuery = new RankingQuery(1);
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->addColumn('nb_visits', 'sum');
        $rankingQuery->partitionResultIntoMultipleGroups('type', [1]);

        $result = $rankingQuery->execute($query['sql'], $query['bind']);

        // rows outside the partition values are aggregated into a single row, never into "Others"
        self::assertCount(1, $result['']);
        self::assertNotEquals(RankingQuery::LABEL_SUMMARY_ROW, $result[''][0]['label']);
        self::assertEquals(
            array_sum(array_column($unpartitionedRows, 'nb_visits')),
            $result[''][0]['nb_visits']
        );
        self::assertCount(2, $result[1]);
    }

    /**
     * @param string|false $orderBy
     * @return array{sql: string, bind: array}
     */
    private function generateVisitsQuery(string $select, string $groupBy, $orderBy): array
    {
        $where = '
            log_visit.visit_last_action_time >= ?
            AND log_visit.visit_last_action_time <= ?
            AND log_visit.idsite IN (?)
        ';

        return $this->logAggregator->generateQuery($select, 'log_visit', $where, $groupBy, $orderBy);
    }

    private function sortByLabel(array $rows, string $metric = 'nb_visits'): array
    {
        usort($rows, function (array $a, array $b): int {
            return strcmp((string) $a['label'], (string) $b['label']);
        });

        return array_map(function (array $row) use ($metric): array {
            return ['label' => $row['label'], $metric => (int) $row[$metric]];
        }, $rows);
    }
}

RankingQueryTest::$fixture = new ManyVisitsWithGeoIP();
