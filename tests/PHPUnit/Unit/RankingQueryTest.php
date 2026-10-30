<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\RankingQuery;

/**
 * @group Core
 * @group RankingQuery
 */
class RankingQueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getBasicTestData
     * @dataProvider getBasicTestDataWithRollup
     * @dataProvider getExcludeRowsTestData
     * @dataProvider getPartitionResultTestData
     */
    public function testRankingQuery(
        RankingQuery $rankingQuery,
        string $innerQuery,
        bool $withRollup,
        string $expectedQuery
    ): void {
        $query = $rankingQuery->generateRankingQuery($innerQuery, $withRollup);

        $queryNoWhitespace = preg_replace("/\s+/", "", $query);
        $expectedNoWhitespace = preg_replace("/\s+/", "", $expectedQuery);

        $message = 'Unexpected query: ' . $query;
        $this->assertEquals($expectedNoWhitespace, $queryNoWhitespace, $message);
    }

    public function getBasicTestData(): iterable
    {
        $rankingQuery = new RankingQuery();
        $rankingQuery->setOthersLabel('Others');
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->addColumn('column');
        $rankingQuery->addColumn('columnSum', 'sum');
        $rankingQuery->setLimit(10);

        $innerQuery = 'SELECT `label`, `column`, `columnSum` FROM `myTable`';

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                `column`,
                sum(`columnSum`) AS `columnSum`
            FROM (
                SELECT
                    `label`,
                    LEAST(ROW_NUMBER() OVER (ORDER BY `label`), 11) AS counter,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT `label`, `column`, `columnSum` FROM `myTable` ) actualQuery
            ) AS withCounter
            GROUP BY counter
        ";

        yield 'basic' => [
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];
    }

    public function getBasicTestDataWithRollup(): iterable
    {
        $rankingQuery = new RankingQuery();
        $rankingQuery->setOthersLabel('Others');
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->addLabelColumn('url');
        $rankingQuery->addColumn('column');
        $rankingQuery->addColumn('columnSum', 'sum');
        $rankingQuery->setLimit(10);

        $innerQuery = '
            SELECT * FROM (
                SELECT `label`, `url`, `column`, `columnSum`
                FROM `myTable`
                GROUP BY `label`, `url` WITH ROLLUP
            ) AS rollupQuery
            ORDER BY `column`
        ';

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counterRollup = 11 THEN 'Others'
                    WHEN counterRollup > 0 THEN `label`
                    WHEN counter = 11 AND counterRollup = 0 THEN `label`
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                CASE
                    WHEN `url` IS NULL THEN NULL
                    WHEN counter = 11 AND counterRollup = 0 THEN 'Others'
                    ELSE `url`
                END AS `url`,
                `column`,
                sum(`columnSum`) AS `columnSum`
            FROM (
                SELECT
                    `label`, `url`,
                    CASE
                        WHEN `label` IS NULL THEN -1
                        WHEN `url` IS NULL THEN -1
                        ELSE LEAST(
                            ROW_NUMBER() OVER (
                                ORDER BY
                                    CASE
                                        WHEN `label` IS NULL THEN 1
                                        WHEN `url` IS NULL THEN 1
                                        ELSE 0
                                    END,
                                    `column`
                            ),
                            11
                        )
                    END AS counter,
                    CASE
                        WHEN `label` IS NULL AND `url` IS NULL THEN -1
                        WHEN `label` IS NOT NULL AND `url` IS NOT NULL THEN 0
                        ELSE LEAST(
                            ROW_NUMBER() OVER (
                                ORDER BY
                                    CASE
                                        WHEN `label` IS NULL AND `url` IS NULL THEN 1
                                        WHEN `label` IS NULL OR `url` IS NULL THEN 0
                                        ELSE 1
                                    END,
                                    `column`
                            ),
                            11
                        )
                    END AS counterRollup,
                    `column`,
                    `columnSum`
                FROM
                    (
                        SELECT * FROM (
                            SELECT `label`, `url`, `column`, `columnSum`
                            FROM `myTable`
                            GROUP BY `label`, `url` WITH ROLLUP
                        ) AS rollupQuery
                        ORDER BY `column`
                    ) actualQuery
            ) AS withCounter
            GROUP BY counter, counterRollup,
                CASE
                    WHEN counterRollup = 11 THEN 'Others'
                    WHEN counterRollup > 0 THEN `label`
                    WHEN counter = 11 AND counterRollup = 0 THEN `label`
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END,
                CASE
                    WHEN `url` IS NULL THEN NULL
                    WHEN counter = 11 AND counterRollup = 0 THEN 'Others'
                    ELSE `url`
                END
            ORDER BY counter, counterRollup
        ";

        yield 'basic with rollup' => [
            $rankingQuery,
            $innerQuery,
            true,
            $expectedQuery,
        ];
    }

    public function getExcludeRowsTestData(): iterable
    {
        $rankingQuery = new RankingQuery(20);
        $rankingQuery->setOthersLabel('Others');
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->setColumnToMarkExcludedRows('exclude_marker');

        $innerQuery = "SELECT `label`, 1 AS exclude_marker FROM myTable";

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counter = 21 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                `exclude_marker`
            FROM (
                SELECT
                    `label`,
                    CASE
                        WHEN exclude_marker != 0 THEN -1 * exclude_marker
                        ELSE LEAST(
                            ROW_NUMBER() OVER (
                                ORDER BY
                                    CASE
                                        WHEN exclude_marker != 0 THEN 1 * exclude_marker
                                        ELSE 0
                                    END,
                                    `label`
                            ),
                            21
                        )
                    END AS counter,
                    `exclude_marker`
                FROM
                    ( SELECT `label`, 1 AS exclude_marker FROM myTable ) actualQuery
                ) AS withCounter
            GROUP BY counter
        ";

        yield 'exclude rows' => [
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];

        $rankingQuery = new RankingQuery('20');
        $rankingQuery->setOthersLabel('Others');
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->setColumnToMarkExcludedRows('exclude_marker');

        yield 'exclude rows - string limit' => [
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];
    }

    public function getPartitionResultTestData(): iterable
    {
        $rankingQuery = new RankingQuery(1000);
        $rankingQuery->setOthersLabel('Others');
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->partitionResultIntoMultipleGroups('partition', [1, 2, 3]);

        $innerQuery = "SELECT `label`, `partition` FROM `myTable`";

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counter = 1001 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                `partition`
            FROM (
                SELECT
                    `label`,
                    CASE
                        WHEN `partition` IS NULL OR `partition` NOT IN (1, 2, 3) THEN 0
                        ELSE LEAST(ROW_NUMBER() OVER (PARTITION BY `partition` ORDER BY `label`), 1001)
                    END AS counter,
                    `partition`
                FROM
                    ( SELECT `label`, `partition` FROM `myTable` ) actualQuery
                ) AS withCounter
            GROUP BY counter, `partition`
        ";

        yield 'partition result' => [
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];
    }

    public function testPartitionWithoutPossibleValuesRanksNoRow(): void
    {
        $rankingQuery = new RankingQuery(1000);
        $rankingQuery->addLabelColumn('label');
        $rankingQuery->partitionResultIntoMultipleGroups('partition', []);

        $query = preg_replace('/\s+/', ' ', $rankingQuery->generateRankingQuery('SELECT `label`, `partition` FROM `myTable`'));

        $this->assertStringNotContainsString('NOT IN', $query);
        $this->assertStringContainsString('CASE WHEN 1 = 1 THEN 0 ELSE LEAST(ROW_NUMBER()', $query);
    }

    /**
     * @dataProvider getLimitTestData
     */
    public function testLimitSetsTheCounterCap(int $limit, int $expectedCap): void
    {
        $rankingQuery = new RankingQuery($limit);
        $rankingQuery->addLabelColumn('label');

        $query = preg_replace('/\s+/', ' ', $rankingQuery->generateRankingQuery('SELECT `label` FROM `myTable`'));

        $this->assertStringContainsString('ORDER BY `label`), ' . $expectedCap . ') AS counter', $query);
        $this->assertStringContainsString('WHEN counter = ' . $expectedCap . ' THEN', $query);
    }

    public function getLimitTestData(): iterable
    {
        yield 'positive limit' => [10, 11];
        yield 'limit 0 groups every row into "Others"' => [0, 1];
        yield 'limit -1 groups every row into "Others"' => [-1, 0];
        yield 'limit below -1 ranks every row' => [-2, PHP_INT_MAX];
    }

    public function testLimitIsCappedAtTheLargestInteger(): void
    {
        $rankingQuery = new RankingQuery(PHP_INT_MAX);
        $rankingQuery->addLabelColumn('label');

        $query = $rankingQuery->generateRankingQuery('SELECT `label` FROM `myTable`');

        $this->assertStringContainsString('WHEN counter = ' . PHP_INT_MAX . ' THEN', $query);
    }

    /**
     * @dataProvider getGenerateWindowOrderByStringTestData
     */
    public function testGenerateWindowOrderByString(
        RankingQuery $rankingQuery,
        string $innerQuery,
        bool $withRollup,
        string $expectedOrderBy
    ): void {
        $query = $rankingQuery->generateRankingQuery($innerQuery, $withRollup);

        $queryNoWhitespace = preg_replace("/\s+/", "", $query);
        $expectedNoWhitespace = preg_replace("/\s+/", "", $expectedOrderBy);

        // the window ORDER BY ends the ROW_NUMBER() window of the counter (limit 1 + "Others")
        $this->assertMatchesRegularExpression(
            '/(?:OVER\(ORDERBY|END,)' . preg_quote($expectedNoWhitespace, '/') . '\),2\)/',
            $queryNoWhitespace,
            'Unexpected query: ' . $query
        );
    }

    public function getGenerateWindowOrderByStringTestData(): iterable
    {
        $rankingQuery = new RankingQuery(1);
        $rankingQuery->addLabelColumn('label_1');
        $rankingQuery->addLabelColumn('label_2');

        $labelColumnsString = '`label_1`, `label_2`';

        yield 'SELECT extraction fails' => [
            $rankingQuery,
            'SET @counter = 1',
            false,
            $labelColumnsString,
        ];

        yield 'SELECT extraction fails - with rollup' => [
            $rankingQuery,
            'SELECT * FROM (SET @counter = 1)',
            true,
            $labelColumnsString,
        ];

        yield 'window matches ORDER BY' => [
            $rankingQuery,
            'SELECT column_one, column_two FROM my_table GROUP BY column_two ORDER BY column_one ASC',
            false,
            'column_one ASC',
        ];

        yield 'window matches ORDER BY - rollup' => [
            $rankingQuery,
            '
                SELECT *
                FROM (
                    SELECT column_one, column_two
                    FROM my_table
                    GROUP BY column_two WITH ROLLUP
                ) AS rollupQuery
                ORDER BY column_one ASC
            ',
            true,
            'column_one ASC',
        ];

        yield 'window matches ORDER BY - wrapped query' => [
            $rankingQuery,
            '
                SELECT *
                FROM (
                    SELECT column_one, COUNT(*) AS hits
                    FROM my_table
                    GROUP BY column_one
                ) AS wrappedQuery
                ORDER BY wrappedQuery.hits DESC
            ',
            false,
            '`hits` DESC',
        ];

        yield 'window ignores expressions - wrapped query' => [
            $rankingQuery,
            'SELECT * FROM (SELECT column_one FROM my_table) AS wrappedQuery ORDER BY count(*) DESC',
            false,
            '`label_1`, `label_2`',
        ];

        yield 'window matches GROUP BY' => [
            $rankingQuery,
            '
                SELECT column_one, column_two
                FROM my_table
                GROUP BY column_one
            ',
            false,
            'column_one',
        ];

        yield 'window matches GROUP BY - rollup' => [
            $rankingQuery,
            '
                SELECT *
                FROM (
                    SELECT column_one, column_two
                    FROM my_table
                    GROUP BY column_one WITH ROLLUP
                ) AS rollupQuery
            ',
            true,
            'column_one',
        ];

        yield 'unselected column is removed' => [
            $rankingQuery,
            'SELECT column_one FROM my_table ORDER BY column_one, column_two',
            false,
            'column_one',
        ];

        yield 'column only matching the end of a selected column is removed' => [
            $rankingQuery,
            'SELECT column_one, date_created FROM my_table ORDER BY column_one, created DESC',
            false,
            'column_one',
        ];

        yield 'unselected function call is removed' => [
            $rankingQuery,
            'SELECT column_one FROM my_table GROUP BY column_one ORDER BY count(*) DESC, column_one',
            false,
            'column_one',
        ];

        yield 'ordering stops at the first unusable column' => [
            $rankingQuery,
            'SELECT column_one, column_two FROM my_table ORDER BY column_one, count(*) DESC, column_two',
            false,
            'column_one',
        ];

        yield 'function arguments are not split' => [
            $rankingQuery,
            'SELECT column_one, IF(a, b, c) AS score FROM my_table GROUP BY column_one ORDER BY IF(a, b, c) DESC',
            false,
            'column_one',
        ];

        yield 'function calls are resolved to their alias' => [
            $rankingQuery,
            'SELECT column_one, IF(a,b,c) AS score FROM my_table GROUP BY column_one ORDER BY IF(a,b,c) DESC',
            false,
            '`score` DESC',
        ];

        yield 'function calls with table names are resolved to their alias' => [
            $rankingQuery,
            'SELECT column_one, COUNT(my_table.id) AS hits FROM my_table GROUP BY column_one ORDER BY COUNT(my_table.id) DESC',
            false,
            '`hits` DESC',
        ];

        yield 'column aliases are resolved' => [
            $rankingQuery,
            'SELECT column_one AS column_new FROM my_table ORDER BY column_one',
            false,
            '`column_new`',
        ];

        yield 'column aliases are resolved - backtick quoted' => [
            $rankingQuery,
            'SELECT column_one AS `column_new` FROM my_table ORDER BY column_one',
            false,
            '`column_new`',
        ];

        yield 'column aliases are resolved - double quoted' => [
            $rankingQuery,
            'SELECT column_one AS "column_new" FROM my_table ORDER BY column_one',
            false,
            '`column_new`',
        ];

        yield 'column aliases are resolved - single quoted' => [
            $rankingQuery,
            "SELECT column_one AS 'column_new' FROM my_table ORDER BY column_one",
            false,
            '`column_new`',
        ];

        yield 'column aliases are only resolved for the whole column name' => [
            $rankingQuery,
            'SELECT custom_dimension_2 AS `custom_dimension_2`, count(*) AS `2` FROM my_table GROUP BY custom_dimension_2 ORDER BY `2` DESC',
            false,
            '`2` DESC',
        ];

        yield 'column only used as a function argument is removed' => [
            $rankingQuery,
            'SELECT COALESCE(log_action.name, log_action.url_prefix) AS label_1, COUNT(*) AS hits FROM log_action GROUP BY label_1 ORDER BY log_action.name',
            false,
            'label_1',
        ];

        yield 'column at the end of an aliased expression is not resolved to the alias' => [
            $rankingQuery,
            'SELECT column_one, column_two + column_three AS total FROM my_table ORDER BY column_three, column_one',
            false,
            $labelColumnsString,
        ];

        yield 'columns after DISTINCT and comments are found' => [
            $rankingQuery,
            'SELECT DISTINCT column_one, /* second */ column_two FROM my_table ORDER BY column_two, column_one',
            false,
            'column_two, column_one',
        ];

        yield 'FROM inside a function call' => [
            $rankingQuery,
            'SELECT EXTRACT(YEAR FROM created) AS year, COUNT(*) AS hits FROM my_table GROUP BY year ORDER BY hits DESC',
            false,
            'hits DESC',
        ];

        yield 'scalar subquery' => [
            $rankingQuery,
            'SELECT column_one, (SELECT COUNT(*) FROM other_table WHERE other_table.id = my_table.id) AS hits FROM my_table ORDER BY hits DESC',
            false,
            'hits DESC',
        ];

        yield 'common table expression' => [
            $rankingQuery,
            'WITH my_data AS (SELECT column_one, COUNT(*) AS hits FROM my_table GROUP BY column_one) SELECT column_one, hits FROM my_data ORDER BY hits DESC',
            false,
            'hits DESC',
        ];

        yield 'comment containing SELECT - rollup' => [
            $rankingQuery,
            '
                /* select the top rows */
                SELECT *
                FROM (
                    SELECT column_one, COUNT(*) AS hits
                    FROM my_table
                    GROUP BY column_one WITH ROLLUP
                ) AS rollupQuery
                ORDER BY hits DESC
            ',
            true,
            'hits DESC',
        ];

        yield 'column aliases are resolved - rollup' => [
            $rankingQuery,
            '
                SELECT *
                FROM (
                    SELECT column_one AS column_new
                    FROM my_table
                    GROUP BY column_two WITH ROLLUP
                ) AS rollupQuery
                ORDER BY column_new
            ',
            true,
            'column_new',
        ];
    }
}
