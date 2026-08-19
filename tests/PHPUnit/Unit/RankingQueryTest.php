<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Db\Schema;
use Piwik\RankingQuery;

/**
 * @group Core
 * @group RankingQuery
 */
class RankingQueryTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        Schema::unsetInstance();
        parent::tearDown();
    }

    /**
     * @dataProvider getBasicTestData
     * @dataProvider getBasicTestDataWithRollup
     * @dataProvider getExcludeRowsTestData
     * @dataProvider getPartitionResultTestData
     */
    public function testRankingQuery(
        Schema $mockSchema,
        RankingQuery $rankingQuery,
        string $innerQuery,
        bool $withRollup,
        string $expectedQuery
    ): void {
        Schema::setSingletonInstance($mockSchema);

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
                    ROW_NUMBER() OVER (ORDER BY `label`) AS counter,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT `label`, `column`, `columnSum` FROM `myTable` ) actualQuery
            ) AS withCounter
            GROUP BY
                CASE
                    WHEN counter >= 11 THEN 11
                    ELSE counter
                END
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(true);

        yield 'basic - window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];

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
                    CASE
                        WHEN @counter = 11 THEN 11
                        ELSE @counter := @counter + 1
                    END AS counter,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT @counter := 0 ) initCounter,
                    ( SELECT `label`, `column`, `columnSum` FROM `myTable` ) actualQuery
                ) AS withCounter
            GROUP BY counter
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        yield 'basic - no window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];

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
                    CASE
                        WHEN @counter = 11 THEN 11
                        ELSE @counter := @counter + 1
                    END AS counter,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT @counter := 0 ) initCounter,
                    ( SELECT `label`, `column`, `columnSum` FROM `myTable` LIMIT 18446744073709551615 ) actualQuery
                ) AS withCounter
            GROUP BY counter
            ORDER BY counter
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(false);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        yield 'basic - sorting in subquery not supported' => [
            $mockSchema,
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

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(true);

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counterRollup = 11 THEN 'Others'
                    WHEN counterRollup > 0 THEN `label`
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                CASE
                    WHEN counterRollup = 11 THEN NULL 
                    WHEN counterRollup > 0 THEN `url`
                    WHEN counter = 11 THEN 'Others'
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
                        ELSE ROW_NUMBER() OVER (
                            ORDER BY
                                CASE
                                    WHEN `label` IS NULL THEN 1
                                    WHEN `url` IS NULL THEN 1
                                    ELSE 0
                                END,
                                `column`
                        )
                    END AS counter,
                    CASE
                        WHEN `label` IS NULL AND `url` IS NULL THEN -1
                        WHEN `label` IS NOT NULL AND `url` IS NOT NULL THEN 0
                        ELSE ROW_NUMBER() OVER (
                            ORDER BY
                                CASE
                                    WHEN `label` IS NULL AND `url` IS NULL THEN 1
                                    WHEN `label` IS NULL OR `url` IS NULL THEN 0
                                    ELSE 1
                                END,
                                `column`
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
            GROUP BY 
                CASE
                    WHEN counter >= 11 THEN 11
                    ELSE counter
                END,
                CASE
                    WHEN counterRollup >= 11 THEN 11
                    ELSE counterRollup
                END
        ";

        yield 'basic with rollup - window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            true,
            $expectedQuery,
        ];

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counterRollup = 11 THEN 'Others'
                    WHEN counterRollup > 0 THEN `label`
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                CASE
                    WHEN counterRollup = 11 THEN NULL 
                    WHEN counterRollup > 0 THEN `url`
                    WHEN counter = 11 THEN 'Others'
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
                        WHEN @counter = 11 THEN 11 
                        ELSE @counter := @counter + 1
                    END AS counter,
                    CASE
                        WHEN `label` IS NULL AND `url` IS NULL THEN -1
                        WHEN `label` IS NULL AND @counterRollup = 11 THEN 11
                        WHEN `label` IS NULL THEN @counterRollup := @counterRollup + 1
                        WHEN `url` IS NULL AND @counterRollup = 11 THEN 11
                        WHEN `url` IS NULL THEN @counterRollup := @counterRollup + 1
                        ELSE 0
                    END AS counterRollup,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT @counter := 0 ) initCounter,
                    ( SELECT @counterRollup := 0 ) initCounterRollup,
                    (
                        SELECT * FROM (
                            SELECT `label`, `url`, `column`, `columnSum`
                            FROM `myTable`
                            GROUP BY `label`, `url` WITH ROLLUP
                        ) AS rollupQuery
                        ORDER BY `column`
                    ) actualQuery
                ) AS withCounter
            GROUP BY counter, counterRollup
        ";

        yield 'basic with rollup - no window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            true,
            $expectedQuery,
        ];

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counterRollup = 11 THEN 'Others'
                    WHEN counterRollup > 0 THEN `label`
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                CASE
                    WHEN counterRollup = 11 THEN NULL
                    WHEN counterRollup > 0 THEN `url`
                    WHEN counter = 11 THEN 'Others'
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
                        WHEN @counter = 11 THEN 11
                        ELSE @counter := @counter + 1
                    END AS counter,
                    CASE
                        WHEN `label` IS NULL AND `url` IS NULL THEN -1
                        WHEN `label` IS NULL AND @counterRollup = 11 THEN 11
                        WHEN `label` IS NULL THEN @counterRollup := @counterRollup + 1
                        WHEN `url` IS NULL AND @counterRollup = 11 THEN 11
                        WHEN `url` IS NULL THEN @counterRollup := @counterRollup + 1
                        ELSE 0
                    END AS counterRollup,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT @counter := 0 ) initCounter,
                    ( SELECT @counterRollup := 0 ) initCounterRollup,
                    (
                        SELECT * FROM (
                            SELECT `label`, `url`, `column`, `columnSum`
                            FROM `myTable`
                            GROUP BY `label`, `url` WITH ROLLUP
                        ) AS rollupQuery
                        ORDER BY `column`
                        LIMIT 18446744073709551615
                    ) actualQuery
                ) AS withCounter
            GROUP BY counter, counterRollup
            ORDER BY counter, counterRollup
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(false);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        yield 'basic with rollup - sorting in subquery not supported' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            true,
            $expectedQuery,
        ];

        $expectedQuery = "
            SELECT
                CASE
                    WHEN counterRollup = 11 THEN 'Others'
                    WHEN counterRollup > 0 THEN `label`
                    WHEN counter = 11 THEN 'Others'
                    ELSE `label`
                END AS `label`,
                CASE
                    WHEN counterRollup = 11 THEN NULL
                    WHEN counterRollup > 0 THEN `url`
                    WHEN counter = 11 THEN 'Others'
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
                        WHEN @counter = 11 THEN 11
                        ELSE @counter := @counter + 1
                    END AS counter,
                    CASE
                        WHEN `label` IS NULL AND `url` IS NULL THEN -1
                        WHEN `label` IS NULL AND @counterRollup = 11 THEN 11
                        WHEN `label` IS NULL THEN @counterRollup := @counterRollup + 1
                        WHEN `url` IS NULL AND @counterRollup = 11 THEN 11
                        WHEN `url` IS NULL THEN @counterRollup := @counterRollup + 1
                        ELSE 0
                    END AS counterRollup,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT @counter := 0 ) initCounter,
                    ( SELECT @counterRollup := 0 ) initCounterRollup,
                    (
                        SELECT * FROM (
                            SELECT `label`, `url`, `column`, `columnSum`
                            FROM `myTable`
                            GROUP BY `label`, `url` WITH ROLLUP
                        ) AS rollupQuery
                        ORDER BY `column`
                    ) actualQuery
                    ORDER BY `label` IS NULL, `url` IS NULL, `column`
                ) AS withCounter
            GROUP BY counter, counterRollup
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(false);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        yield 'basic with rollup - ranking query without extra sorting not supported' => [
            $mockSchema,
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
                        ELSE ROW_NUMBER() OVER (
                            ORDER BY
                                CASE
                                    WHEN exclude_marker != 0 THEN 1 * exclude_marker
                                    ELSE 0
                                END,
                                `label`
                        )
                    END AS counter,
                    `exclude_marker`
                FROM
                    ( SELECT `label`, 1 AS exclude_marker FROM myTable ) actualQuery
                ) AS withCounter
            GROUP BY
                CASE
                    WHEN counter >= 21 THEN 21
                    ELSE counter
                END
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(true);

        yield 'exclude rows - window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];

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
                        WHEN @counter = 21 THEN 21
                        ELSE @counter := @counter + 1
                    END AS counter,
                    `exclude_marker`
                FROM
                    ( SELECT @counter := 0 ) initCounter,
                    ( SELECT `label`, 1 AS exclude_marker FROM myTable ) actualQuery
                ) AS withCounter
            GROUP BY counter
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        yield 'exclude rows - no window functions' => [
            $mockSchema,
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
                    ROW_NUMBER() OVER (PARTITION BY `partition` ORDER BY `label`) AS counter,
                    `partition`
                FROM
                    ( SELECT `label`, `partition` FROM `myTable` ) actualQuery
                ) AS withCounter
            GROUP BY
                CASE
                    WHEN counter >= 1001 THEN 1001
                    ELSE counter
                END,
                `partition`
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(true);

        yield 'partition result - window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];

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
                        WHEN `partition` = 1 AND @counter1 = 1001 THEN 1001
                        WHEN `partition` = 1 THEN @counter1 := @counter1 + 1
                        WHEN `partition` = 2 AND @counter2 = 1001 THEN 1001
                        WHEN `partition` = 2 THEN @counter2 := @counter2 + 1
                        WHEN `partition` = 3 AND @counter3 = 1001 THEN 1001
                        WHEN `partition` = 3 THEN @counter3 := @counter3 + 1
                        ELSE 0
                    END AS counter,
                    `partition`
                FROM
                    ( SELECT @counter1 := 0 ) initCounter1,
                    ( SELECT @counter2 := 0 ) initCounter2,
                    ( SELECT @counter3 := 0 ) initCounter3,
                    ( SELECT `label`, `partition` FROM `myTable` ) actualQuery
                ) AS withCounter
            GROUP BY counter, `partition`
        ";

        $mockSchema = $this->createMock(Schema::class);
        $mockSchema->method('supportsRankingRollupWithoutExtraSorting')->willReturn(true);
        $mockSchema->method('supportsSortingInSubquery')->willReturn(true);
        $mockSchema->method('supportsWindowFunctions')->willReturn(false);

        yield 'partition result - no window functions' => [
            $mockSchema,
            $rankingQuery,
            $innerQuery,
            false,
            $expectedQuery,
        ];
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
        $reflection = new \ReflectionClass($rankingQuery);
        $method = $reflection->getMethod('generateWindowOrderByExpression');
        $method->setAccessible(true);

        $windowOrderBy = $method->invokeArgs($rankingQuery, [$innerQuery, $withRollup]);

        $this->assertEquals($expectedOrderBy, $windowOrderBy);
    }

    public function getGenerateWindowOrderByStringTestData(): iterable
    {
        $rankingQuery = new RankingQuery(1);
        $rankingQuery->addLabelColumn('label_1');
        $rankingQuery->addLabelColumn('label_2');

        $reflection = new \ReflectionClass($rankingQuery);
        $method = $reflection->getMethod('generateLabelColumnsString');
        $method->setAccessible(true);

        $labelColumnsString = $method->invokeArgs($rankingQuery, []);

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
