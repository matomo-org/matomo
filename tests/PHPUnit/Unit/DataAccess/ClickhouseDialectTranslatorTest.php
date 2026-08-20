<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\ClickhouseDialectTranslator;

/**
 * @group Core
 * @group ClickHouse
 */
class ClickhouseDialectTranslatorTest extends \PHPUnit\Framework\TestCase
{
    public function testTranslatesMysqlFunctionNames()
    {
        $sql = "SELECT HOUR(server_time) AS h, DATE(visit_last_action_time) AS d, IFNULL(a, b), CONCAT(x, y) FROM t";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toHour(server_time)', $translated);
        self::assertStringContainsString('toDate(visit_last_action_time)', $translated);
        self::assertStringContainsString('ifNull(a, b)', $translated);
        self::assertStringContainsString('concat(x, y)', $translated);
    }

    public function testRewritesHourOnVisitorLocaltimeStringColumn()
    {
        $sql = "SELECT HOUR(log_visit.visitor_localtime) AS label FROM t";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toUInt8(substring(log_visit.visitor_localtime, 1, 2))', $translated);
        self::assertStringNotContainsString('toHour(', $translated);
    }

    public function testStripsIndexHints()
    {
        $sql = "SELECT * FROM log_visit USE INDEX (index_idsite_datetime) WHERE idsite = 1";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('USE INDEX', $translated);
        self::assertStringNotContainsString('index_idsite_datetime', $translated);
    }

    public function testStripsMaxExecutionTimeHints()
    {
        $sql = "SELECT /*+ MAX_EXECUTION_TIME(7200000) */ idvisit FROM log_visit";
        $translated = ClickhouseDialectTranslator::translate($sql);
        self::assertStringNotContainsString('MAX_EXECUTION_TIME', $translated);

        $sql = "SET STATEMENT max_statement_time=7200 FOR SELECT idvisit FROM log_visit";
        $translated = ClickhouseDialectTranslator::translate($sql);
        self::assertStringNotContainsString('SET STATEMENT', $translated);
        self::assertStringStartsWith('SELECT', trim($translated));
    }

    public function testRewritesVisitDedupGroupByToLimitOneBy()
    {
        $sql = "SELECT sub.* FROM log_visit AS sub WHERE idsite = 1 GROUP BY sub.idvisit ORDER BY visit_last_action_time DESC LIMIT 0, 100";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('GROUP BY', $translated);
        self::assertStringContainsString('LIMIT 1 BY sub.idvisit', $translated);
        // LIMIT 1 BY must come before the row LIMIT
        self::assertLessThan(
            strpos($translated, 'LIMIT 0, 100'),
            strpos($translated, 'LIMIT 1 BY')
        );
    }

    public function testVisitDedupLeavesMultiColumnGroupByAlone()
    {
        $sql = "SELECT log_conversion.idvisit AS idvisit, lac.idaction AS idaction, COUNT(*) AS `1` "
            . "FROM log_conversion GROUP BY log_conversion.idvisit, lac.idaction";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('GROUP BY log_conversion.idvisit, lac.idaction', $translated);
        self::assertStringNotContainsString('LIMIT 1 BY', $translated);
    }

    public function testVisitDedupLeavesIdvisitorGroupByAlone()
    {
        $sql = "SELECT log_visit.idvisitor AS `idvisitor`, count(*) AS `2` FROM log_visit AS log_visit "
            . "WHERE log_visit.idsite IN (1) GROUP BY log_visit.idvisitor";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('GROUP BY log_visit.idvisitor', $translated);
        self::assertStringNotContainsString('LIMIT 1 BY', $translated);
    }

    public function testVisitDedupLeavesSubqueryGroupByAlone()
    {
        $sql = "SELECT * FROM t WHERE idvisit IN (SELECT idvisit FROM log_link_visit_action GROUP BY idvisit) AND a = 1";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('GROUP BY idvisit)', $translated);
        self::assertStringNotContainsString('LIMIT 1 BY', $translated);
    }

    public function testRewritesEmptyStringComparisonsWithToString()
    {
        $sql = "SELECT * FROM log_visit WHERE (log_visit.referer_keyword = '' OR log_visit.referer_keyword = '0')";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString("toString(log_visit.referer_keyword) = ''", $translated);
        self::assertStringContainsString("toString(log_visit.referer_keyword) = '0'", $translated);
    }

    public function testRewritesLikeOnColumnsWithToString()
    {
        $sql = "SELECT * FROM log_visit WHERE log_visit.idvisit LIKE ? AND name NOT LIKE ?";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toString(log_visit.idvisit) LIKE ?', $translated);
        self::assertStringContainsString('toString(name) NOT LIKE ?', $translated);
    }

    public function testWrapsNonAggregatedSelectColumnsWithAny()
    {
        $sql = "SELECT log_action.name, log_action.type, count(*) AS `1` FROM log_action GROUP BY log_action.idaction";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('any(log_action.name) AS `name`', $translated);
        self::assertStringContainsString('any(log_action.type) AS `type`', $translated);
    }

    public function testKeepsSelectAliasWhenWrappingWithAny()
    {
        $sql = "SELECT log_action.name AS label, count(*) AS nb FROM log_action GROUP BY log_action.idaction";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('any(log_action.name) AS label', $translated);
    }

    public function testDoesNotWrapGroupByKeysOrAggregates()
    {
        $sql = "SELECT log_action.idaction, count(*) AS nb FROM log_action GROUP BY log_action.idaction";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('any(', $translated);
    }

    public function testDoesNotWrapColumnWhoseAliasIsGroupByKey()
    {
        $sql = "SELECT log_action.name AS label, count(*) AS nb FROM log_action GROUP BY label";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('any(', $translated);
    }

    public function testDoesNotWrapIntegerLiterals()
    {
        $sql = "SELECT 1, count(*) AS nb FROM log_visit GROUP BY idsite";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('any(1)', $translated);
    }

    public function testWrapsRollupGroupingKeysInToNullable()
    {
        $sql = "SELECT COALESCE(log_action.name, '') AS action_name, count(*) AS nb "
            . "FROM log_action GROUP BY action_name WITH ROLLUP";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString("toNullable(COALESCE(log_action.name, '')) AS action_name", $translated);
    }

    public function testWrapsRowNumberWindowFunctionInToInt64()
    {
        $sql = "SELECT ROW_NUMBER() OVER (PARTITION BY x ORDER BY cnt DESC) AS counter FROM t";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toInt64(ROW_NUMBER() OVER (PARTITION BY x ORDER BY cnt DESC))', $translated);
    }

    public function testRewritesRankingQueryCounterCaseWithAny()
    {
        $sql = "SELECT CASE WHEN counter = 51 THEN '__mtm_ranking_query_others__' ELSE label END AS label, sum(nb) AS nb "
            . "FROM (SELECT label, nb, counter FROM sub) AS wrapped "
            . "GROUP BY CASE WHEN counter >= 51 THEN 51 ELSE counter END";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString("CASE WHEN any(counter) >= 51 THEN '__mtm_ranking_query_others__' ELSE toString(any(label)) END", $translated);
    }

    public function testTranslateLeavesPlainQueriesUntouched()
    {
        $sql = "SELECT idvisit, idsite FROM log_visit WHERE idsite = ? AND visit_last_action_time >= ?";
        self::assertSame($sql, ClickhouseDialectTranslator::translate($sql));
    }
}
