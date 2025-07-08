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

class RankingQueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group Core
     */
    public function testBasic()
    {
        $query = new RankingQuery();
        $query->setOthersLabel('Others');
        $query->addLabelColumn('label');
        $query->addColumn('column');
        $query->addColumn('columnSum', 'sum');
        $query->setLimit(10);

        $innerQuery = "SELECT label, column, columnSum FROM myTable";

        $expected = "
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
                        ELSE @counter:=@counter+1
                    END AS counter,
                    `column`,
                    `columnSum`
                FROM
                    ( SELECT @counter:=0 ) initCounter,
                    ( SELECT label, column, columnSum FROM myTable ) actualQuery
             ) AS withCounter
            GROUP BY counter
        ";

        if (!Schema::getInstance()->supportsSortingInSubquery()) {
            $expected = "
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
                            ELSE @counter:=@counter+1
                        END AS counter,
                        `column`,
                        `columnSum`
                    FROM
                        ( SELECT @counter:=0 ) initCounter,
                        ( SELECT label, column, columnSum FROM myTable LIMIT 18446744073709551615 ) actualQuery
                 ) AS withCounter
                GROUP BY counter
                ORDER BY counter
            ";
        }

        $this->checkQuery($query, $innerQuery, $expected);
    }

    /**
     * @group Core
     */
    public function testBasicWithRollup()
    {
        $query = new RankingQuery();
        $query->setOthersLabel('Others');
        $query->addLabelColumn('label');
        $query->addLabelColumn('url');
        $query->addColumn('column');
        $query->addColumn('columnSum', 'sum');
        $query->setLimit(10);

        $innerQuery = "SELECT label, url, column, columnSum FROM myTable";

        $expected = "
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
                        ELSE @counter:=@counter+1
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
                    ( SELECT @counter:=0 ) initCounter,
                    ( SELECT @counterRollup:=0 ) initCounterRollup,
                    ( SELECT label, url, column, columnSum FROM myTable) actualQuery
            ) AS withCounter
            GROUP BY counter, counterRollup
        ";

        if (!Schema::getInstance()->supportsSortingInSubquery()) {
            $expected = "
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
                            ELSE @counter:=@counter+1
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
                        ( SELECT @counter:=0 ) initCounter,
                        ( SELECT @counterRollup:=0 ) initCounterRollup,
                        ( SELECT label, url, column, columnSum FROM myTable LIMIT 18446744073709551615 ) actualQuery
                ) AS withCounter
                GROUP BY counter, counterRollup
                ORDER BY counter, counterRollup
            ";
        }

        $this->checkQuery($query, $innerQuery, $expected, true);
    }

    /**
     * @group Core
     */
    public function testExcludeRows()
    {

        $query = new RankingQuery(20);
        $query->setOthersLabel('Others');
        $query->addLabelColumn('label');
        $query->setColumnToMarkExcludedRows('exclude_marker');

        $innerQuery = "SELECT label, 1 AS exclude_marker FROM myTable";

        $expected = "
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
                        ELSE @counter:=@counter+1
                    END AS counter,
                    `exclude_marker`
                FROM
                    ( SELECT @counter:=0 ) initCounter,
                    ( SELECT label, 1 AS exclude_marker FROM myTable ) actualQuery
            ) AS withCounter
            GROUP BY counter
        ";

        if (!Schema::getInstance()->supportsSortingInSubquery()) {
            $expected = "
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
                            ELSE @counter:=@counter+1
                        END AS counter,
                        `exclude_marker`
                    FROM
                        ( SELECT @counter:=0 ) initCounter,
                        ( SELECT label, 1 AS exclude_marker FROM myTable LIMIT 18446744073709551615 ) actualQuery
                ) AS withCounter
                GROUP BY counter
                ORDER BY counter
            ";
        }

        $this->checkQuery($query, $innerQuery, $expected);

        $query = new RankingQuery('20');
        $query->setOthersLabel('Others');
        $query->addLabelColumn('label');
        $query->setColumnToMarkExcludedRows('exclude_marker');
        $this->checkQuery($query, $innerQuery, $expected);
    }

    /**
     * @group Core
     */
    public function testPartitionResult()
    {
        $query = new RankingQuery(1000);
        $query->setOthersLabel('Others');
        $query->addLabelColumn('label');
        $query->partitionResultIntoMultipleGroups('partition', array(1, 2, 3));

        $innerQuery = "SELECT label, partition FROM myTable";

        $expected = "
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
                        WHEN `partition` = 1 THEN @counter1:=@counter1+1
                        WHEN `partition` = 2 AND @counter2 = 1001 THEN 1001
                        WHEN `partition` = 2 THEN @counter2:=@counter2+1
                        WHEN `partition` = 3 AND @counter3 = 1001 THEN 1001
                        WHEN `partition` = 3 THEN @counter3:=@counter3+1
                        ELSE 0
                    END AS counter,
                    `partition`
                FROM
                    ( SELECT @counter1:=0 ) initCounter1,
                    ( SELECT @counter2:=0 ) initCounter2,
                    ( SELECT @counter3:=0 ) initCounter3,
                    ( SELECT label, partition FROM myTable ) actualQuery
            ) AS withCounter
            GROUP BY counter, `partition`
        ";

        if (!Schema::getInstance()->supportsSortingInSubquery()) {
            $expected = "
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
                            WHEN `partition` = 1 THEN @counter1:=@counter1+1
                            WHEN `partition` = 2 AND @counter2 = 1001 THEN 1001
                            WHEN `partition` = 2 THEN @counter2:=@counter2+1
                            WHEN `partition` = 3 AND @counter3 = 1001 THEN 1001
                            WHEN `partition` = 3 THEN @counter3:=@counter3+1
                            ELSE 0
                        END AS counter,
                        `partition`
                    FROM
                        ( SELECT @counter1:=0 ) initCounter1,
                        ( SELECT @counter2:=0 ) initCounter2,
                        ( SELECT @counter3:=0 ) initCounter3,
                        ( SELECT label, partition FROM myTable LIMIT 18446744073709551615 ) actualQuery
                ) AS withCounter
                GROUP BY counter, `partition`
                ORDER BY counter
            ";
        }

        $this->checkQuery($query, $innerQuery, $expected);
    }

    /**
     * @param RankingQuery $rankingQuery
     * @param string $innerQuerySql
     * @param string $expected
     */
    private function checkQuery(
        RankingQuery $rankingQuery,
        string $innerQuerySql,
        string $expected,
        bool $withRollup = false
    ) {
        $query = $rankingQuery->generateRankingQuery($innerQuerySql, $withRollup);

        $queryNoWhitespace = preg_replace("/\s+/", "", $query);
        $expectedNoWhitespace = preg_replace("/\s+/", "", $expected);

        $message = 'Unexpected query: ' . $query;
        $this->assertEquals($queryNoWhitespace, $expectedNoWhitespace, $message);
    }
}
