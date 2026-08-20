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

        // The same pass also swaps LIKE for the case insensitive ILIKE.
        self::assertStringContainsString('toString(log_visit.idvisit) ILIKE ?', $translated);
        self::assertStringContainsString('toString(name) NOT ILIKE ?', $translated);
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

    public function testWrapsRollupGroupingKeysInGroupByAndNeverAggregatesThem()
    {
        // The shape RankingQuery's rollup variant wraps (Referrers AIReferrers): the
        // GROUP BY repeats the SELECT expressions. ClickHouse fills a rollup row's
        // grouping keys with the key type's default, so a String key yields '' and only
        // a Nullable key yields NULL - and the rollup CASE branches downstream detect
        // the rollup rows with IS NULL. Wrapping the SELECT item alone is not enough,
        // and an any() wrapper would return a real value for the rollup group.
        $sql = "SELECT log_visit.referer_name AS `referer_name`, log_action.name AS `action_name`, count(*) AS `1` "
            . "FROM log_visit AS log_visit "
            . "LEFT JOIN log_action AS log_action ON log_action.idaction = log_visit.visit_entry_idaction_url "
            . "GROUP BY log_visit.referer_name, log_action.name WITH ROLLUP";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toNullable(log_visit.referer_name) AS `referer_name`', $translated);
        self::assertStringContainsString('toNullable(log_action.name) AS `action_name`', $translated);
        self::assertStringContainsString(
            'GROUP BY toNullable(log_visit.referer_name), toNullable(log_action.name) WITH ROLLUP',
            $translated
        );
        self::assertStringNotContainsString('any(toNullable(', $translated);
    }

    public function testKeepsRollupGroupingKeysNullableInsideRankingQueryEnvelope()
    {
        $inner = "SELECT log_visit.referer_name AS `referer_name`, log_action.name AS `action_name`, count(*) AS `1` "
            . "FROM log_visit AS log_visit "
            . "LEFT JOIN log_action AS log_action ON log_action.idaction = log_visit.visit_entry_idaction_url "
            . "GROUP BY log_visit.referer_name, log_action.name WITH ROLLUP";
        $sql = "SELECT CASE WHEN withCounter.`action_name` IS NULL THEN NULL "
            . "ELSE withCounter.`action_name` END AS `action_name`, sum(`1`) AS `1` "
            . "FROM ( SELECT *, 0 AS counter, 0 AS counterRollup FROM ( $inner ) actualQuery ) withCounter "
            . "GROUP BY `action_name`";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString(
            'GROUP BY toNullable(log_visit.referer_name), toNullable(log_action.name) WITH ROLLUP',
            $translated
        );
        self::assertStringNotContainsString('any(toNullable(', $translated);
    }

    public function testTranslatesCaseFoldingToTheUtf8AwareVariants()
    {
        // ClickHouse's lower()/upper() fold ASCII only, so accented characters survive
        // untouched where MySQL's utf8mb4 collation folds them - the campaign keyword
        // 'mot_clé_pépère' came back as 'mot_clé_pÉpÈre'.
        $sql = "SELECT LOWER(log_visit.campaign_keyword) AS kwd, UPPER(name) AS n FROM log_visit";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('lowerUTF8(log_visit.campaign_keyword) AS kwd', $translated);
        self::assertStringContainsString('upperUTF8(name) AS n', $translated);
    }

    public function testDoesNotDoubleAliasAColumnSelectedTwice()
    {
        // The visits log selects log_link_visit_action.idlink_va twice, once bare and
        // once as pageId. Aliasing the bare reference by replacing its text everywhere
        // also hit the aliased copy and emitted `... AS `idlink_va` AS pageId`, which
        // ClickHouse rejects with a syntax error.
        $sql = "SELECT log_link_visit_action.idlink_va, log_link_visit_action.idlink_va AS pageId "
            . "FROM log_link_visit_action";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('log_link_visit_action.idlink_va AS `idlink_va`', $translated);
        self::assertStringContainsString('log_link_visit_action.idlink_va AS pageId', $translated);
        self::assertStringNotContainsString('AS `idlink_va` AS pageId', $translated);
    }

    public function testTranslatesStraightJoinToInnerJoin()
    {
        // Ecommerce visitor details use MySQL's STRAIGHT_JOIN to force the join order;
        // ClickHouse has no equivalent hint and rejected the keyword outright.
        $sql = "SELECT idgoal FROM log_visit AS log_visit "
            . "STRAIGHT_JOIN log_conversion AS log_conversion ON log_visit.idvisit = log_conversion.idvisit "
            . "GROUP BY idgoal";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('INNER JOIN log_conversion AS log_conversion', $translated);
        self::assertStringNotContainsString('STRAIGHT_JOIN', $translated);
    }

    public function testTranslatesLikeToTheCaseInsensitiveIlike()
    {
        // MySQL matches LIKE case insensitively under its default collation, which is what
        // segments such as pageUrl=@Foo rely on; ClickHouse's LIKE is case sensitive.
        $sql = "SELECT idvisit FROM log_link_visit_action WHERE log_action.name LIKE ? AND idvisit NOT LIKE ?";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toString(log_action.name) ILIKE ?', $translated);
        self::assertStringContainsString('toString(idvisit) NOT ILIKE ?', $translated);
        self::assertStringNotContainsString(' LIKE ', $translated);
    }

    public function testLeavesTheWordLikeInsideAStringLiteralAlone()
    {
        $sql = "SELECT idvisit FROM log_visit WHERE location_country = 'like'";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString("= 'like'", $translated);
        self::assertStringNotContainsString('ILIKE', $translated);
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

    public function testWrapsScalarFunctionExpressionsWithAny()
    {
        $sql = "SELECT log_visit.user_id AS user_id, count(*) AS `2`, LOWER(HEX(idvisitor)) as idvisitor "
            . "FROM log_visit GROUP BY log_visit.user_id";
        $translated = ClickhouseDialectTranslator::translate($sql);

        // LOWER() is translated to its UTF-8 aware variant on the way through.
        self::assertStringContainsString('any(lowerUTF8(HEX(idvisitor))) AS idvisitor', $translated);
    }

    public function testDoesNotWrapExpressionsContainingAggregates()
    {
        $sql = "SELECT log_conversion.idgoal AS idgoal, ROUND(SUM(log_conversion.revenue), 2) AS `2` "
            . "FROM log_conversion GROUP BY log_conversion.idgoal";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('any(ROUND', $translated);
    }

    public function testDoesNotWrapScalarExpressionThatIsAGroupingExpression()
    {
        $sql = "SELECT toUInt8(substring(log_visit.visitor_localtime, 1, 2)) AS label, count(*) AS nb "
            . "FROM log_visit GROUP BY toUInt8(substring(log_visit.visitor_localtime, 1, 2))";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringNotContainsString('any(toUInt8', $translated);
    }

    public function testWrapsAliasedCaseExpressionsWithAnyAndKeepsUnaliasedGroupingExpression()
    {
        // Shape of the Transitions queries: an unaliased if() that IS the grouping
        // expression, plus paren-free CASE expressions that are neither aggregated
        // nor grouped (MySQL relaxed mode picks an arbitrary row value).
        $sql = "SELECT if(llva.idaction_url_ref IS NULL, llva.idaction_name_ref, llva.idaction_url_ref), "
            . "count(*) AS `3`, "
            . "CASE WHEN llva.idaction_url_ref = 6 THEN 1 ELSE 0 END AS is_self "
            . "FROM log_link_visit_action AS llva "
            . "GROUP BY if(llva.idaction_url_ref IS NULL, llva.idaction_name_ref, llva.idaction_url_ref)";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString(
            'any(CASE WHEN llva.idaction_url_ref = 6 THEN 1 ELSE 0 END) AS is_self',
            $translated
        );
        // The unaliased grouping expression must stay untouched
        self::assertStringContainsString(
            'SELECT if(llva.idaction_url_ref IS NULL, llva.idaction_name_ref, llva.idaction_url_ref), count(*)',
            $translated
        );
    }

    public function testAliasesUnaliasedQualifiedSelectColumnsAtEveryLevel()
    {
        $sql = "SELECT custom_dimension_1, url, toInt64(ROW_NUMBER() OVER (ORDER BY `12` DESC)) AS counter "
            . "FROM (SELECT log_link_visit_action.custom_dimension_1, log_action.name AS url, count(*) AS `12` "
            . "FROM log_link_visit_action AS log_link_visit_action "
            . "LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit "
            . "GROUP BY log_link_visit_action.custom_dimension_1, url ORDER BY `12` DESC) AS actualQuery";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString(
            'log_link_visit_action.custom_dimension_1 AS `custom_dimension_1`',
            $translated
        );
        // Already-aliased items are left alone
        self::assertStringContainsString('log_action.name AS url', $translated);
        self::assertStringNotContainsString('log_action.name AS url AS', $translated);
    }

    public function testDoesNotAliasStarOrFunctionSelectItems()
    {
        $sql = "SELECT log_visit.*, count(*) AS nb FROM log_visit";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('log_visit.*, count(*) AS nb', $translated);
    }

    public function testTranslateLeavesPlainQueriesUntouched()
    {
        $sql = "SELECT idvisit, idsite FROM log_visit WHERE idsite = ? AND visit_last_action_time >= ?";
        self::assertSame($sql, ClickhouseDialectTranslator::translate($sql));
    }
}
