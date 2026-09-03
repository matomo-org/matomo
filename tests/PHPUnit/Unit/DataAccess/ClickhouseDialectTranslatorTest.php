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

    /**
     * visitor_localtime holds a MySQL TIME, and the replication paths disagree about what that
     * becomes: the sink connector writes a 'HH:MM:SS' String, ClickPipes writes Time64(6).
     * toHour()/toMinute() reject both, and substring() rejects the Time64 - so it is rendered
     * with toString() first, which is identity on one mapping and 'HH:MM:SS.ffffff' on the
     * other. Archiving VisitTime died on the corpus that uses the second mapping with
     * "Illegal type Time64(6) of first argument of function substring".
     */
    public function testRewritesHourOnVisitorLocaltimeForEitherColumnMapping()
    {
        $sql = "SELECT HOUR(log_visit.visitor_localtime) AS label FROM t";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toUInt8(substring(toString(log_visit.visitor_localtime), 1, 2))', $translated);
        self::assertStringNotContainsString('toHour(', $translated);
    }

    public function testRewritesMinuteOnVisitorLocaltimeForEitherColumnMapping()
    {
        $sql = "SELECT MINUTE(log_visit.visitor_localtime) AS label FROM t";
        $translated = ClickhouseDialectTranslator::translate($sql);

        // Offset 4, because toString() renders HH:MM:SS and the minute is the second field.
        self::assertStringContainsString('toUInt8(substring(toString(log_visit.visitor_localtime), 4, 2))', $translated);
        self::assertStringNotContainsString('toMinute(', $translated);
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
        // log_action.name is the exception, and is covered by its own tests below.
        $sql = "SELECT idvisit FROM log_link_visit_action WHERE location_country LIKE ? AND idvisit NOT LIKE ?";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('toString(location_country) ILIKE ?', $translated);
        self::assertStringContainsString('toString(idvisit) NOT ILIKE ?', $translated);
        self::assertStringNotContainsString(' LIKE ', $translated);
    }

    public function testPointsActionNameLikeAtTheLowercasedIndexedColumnInsteadOfIlike()
    {
        // ngrambf_v1 is never consulted for ILIKE, so making the segment case insensitive
        // the obvious way would be correct and unindexable at the same time. Comparing
        // the MATERIALIZED lower(name) column with a lowered needle is both.
        $sql = "SELECT idvisit FROM log_link_visit_action WHERE log_action.name LIKE ?";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString('log_action.`name_lower` LIKE ?', $translated);
        self::assertStringNotContainsString('ILIKE', $translated);
    }

    public function testPointsSegmentAliasedActionNameLikeAtTheIndexedColumnAndLowersALiteralNeedle()
    {
        // The alias JoinGenerator produces for an action-scope segment component.
        $sql = "SELECT idvisit FROM log_link_visit_action "
            . "WHERE log_action_segment_log_link_visit_actionidaction_url.name LIKE '%Budget%'";
        $translated = ClickhouseDialectTranslator::translate($sql);

        self::assertStringContainsString(
            "log_action_segment_log_link_visit_actionidaction_url.`name_lower` LIKE '%budget%'",
            $translated
        );
    }

    public function testLowersBoundNeedlesForTheIndexedColumnOnly()
    {
        $sql = 'SELECT idvisit FROM log_action WHERE `name_lower` LIKE :chBind000 AND type = :chBind001';
        $params = ClickhouseDialectTranslator::lowercaseNeedlesForIndexedColumns(
            $sql,
            ['chBind000' => '%Budget%', 'chBind001' => 'Budget']
        );

        self::assertSame('%budget%', $params['chBind000']);
        // Not feeding a name_lower LIKE, so it keeps its case.
        self::assertSame('Budget', $params['chBind001']);
    }

    public function testGuardsCountDistinctOnColumnsWhoseNullsTheCopyFlattened()
    {
        // MySQL's COUNT(DISTINCT) ignores NULL. user_id lands as a non-nullable String on
        // the ClickPipes destination, so every NULL arrives as '' and uniqExact counts it
        // as a value - one spurious extra distinct, on every day, on every site.
        $sql = "SELECT count(distinct log_visit.user_id) AS `39` FROM log_visit";
        self::assertStringContainsString(
            "uniqExactIf(log_visit.user_id, log_visit.user_id != '')",
            ClickhouseDialectTranslator::translate($sql)
        );

        // idvisitor is NOT NULL in MySQL, so it must keep plain count(distinct).
        $sql = "SELECT count(distinct log_visit.idvisitor) FROM log_visit";
        self::assertStringContainsString(
            'count(distinct log_visit.idvisitor)',
            ClickhouseDialectTranslator::translate($sql)
        );
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
        $sql = "SELECT toUInt8(substring(toString(log_visit.visitor_localtime), 1, 2)) AS label, count(*) AS nb "
            . "FROM log_visit GROUP BY toUInt8(substring(toString(log_visit.visitor_localtime), 1, 2))";
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

    // ---------------------------------------------------------------------
    // Restricting log_action joins
    // ---------------------------------------------------------------------

    /**
     * The archiving query that died on the POC corpus: log_visit LEFT JOIN the whole 242M-row
     * log_action, killed in FillingRightJoinSide. The join is two subqueries deep, so the pass
     * has to recurse rather than only look at the outermost SELECT.
     */
    public function testDayArchivingJoinIsRestrictedToTheIdsTheQueryCanReach(): void
    {
        $sql = 'SELECT any(counter) FROM ( SELECT counter FROM ( SELECT log_visit.visit_entry_idaction_url as idaction, any(log_action.name) AS `name`'
            . ' FROM mc_anonsite_log_visit AS log_visit'
            . ' LEFT JOIN mc_anonsite_log_action AS log_action ON log_visit.visit_entry_idaction_url = log_action.idaction'
            . ' WHERE log_visit.visit_last_action_time >= :chBind000 AND log_visit.idsite IN (:chBind001)'
            . ' GROUP BY log_visit.visit_entry_idaction_url ) actualQuery ) AS withCounter';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString(
            'LEFT JOIN (SELECT *, `name_lower` FROM mc_anonsite_log_action WHERE idaction IN'
            . ' (SELECT log_visit.visit_entry_idaction_url FROM mc_anonsite_log_visit AS log_visit'
            . ' WHERE log_visit.visit_last_action_time >= :chBind000 AND log_visit.idsite IN (:chBind001)))'
            . ' AS log_action ON log_visit.visit_entry_idaction_url = log_action.idaction',
            $out
        );
    }

    /**
     * Named binds are reused rather than renumbered. This is the reason the pass runs after
     * bind conversion: repeating a positional `?` would desynchronise the bind list, and the
     * failure mode of that is wrong values silently bound to the wrong columns.
     */
    public function testRepeatedBindsAreReusedNotRenumbered(): void
    {
        $sql = 'SELECT a FROM log_visit AS log_visit'
            . ' LEFT JOIN log_action AS log_action ON log_visit.idaction_x = log_action.idaction'
            . ' WHERE log_visit.idsite = :chBind000';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertSame(2, substr_count($out, ':chBind000'));
        self::assertStringNotContainsString(':chBind001', $out);
    }

    /**
     * Every log_action join in the Visits Log enrichment gets restricted, not just the first.
     */
    public function testEveryLogActionJoinInTheScopeIsRestricted(): void
    {
        $sql = 'SELECT x FROM log_link_visit_action AS log_link_visit_action'
            . ' LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction'
            . ' LEFT JOIN log_action AS log_action_title ON log_link_visit_action.idaction_name = log_action_title.idaction'
            . ' WHERE log_link_visit_action.idvisit IN (:chBind000)';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertSame(2, substr_count($out, 'WHERE idaction IN (SELECT'));
        self::assertStringContainsString('SELECT log_link_visit_action.idaction_url FROM', $out);
        self::assertStringContainsString('SELECT log_link_visit_action.idaction_name FROM', $out);
    }

    /**
     * A conjunct naming a joined table cannot be resolved inside the restriction subquery, so
     * it is dropped. Dropping a conjunct from an AND-chain WIDENS the id set, which is safe -
     * the restriction only has to be a superset of what the join needs.
     */
    public function testConjunctsReferencingAJoinedTableAreDroppedNotCopied(): void
    {
        $sql = 'SELECT x FROM log_visit AS log_visit'
            . ' LEFT JOIN log_action AS log_action ON log_visit.idaction_x = log_action.idaction'
            . ' WHERE log_visit.idsite = :chBind000 AND log_action.name_lower LIKE :chBind001';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString('WHERE log_visit.idsite = :chBind000))', $out);
        self::assertSame(1, substr_count($out, ':chBind001'), 'the log_action conjunct must not be copied');
    }

    public function testJoinIsLeftAloneWhenNothingSafeCanBeCarriedOver(): void
    {
        $sql = 'SELECT x FROM log_visit AS log_visit'
            . ' LEFT JOIN log_action AS log_action ON log_visit.idaction_x = log_action.idaction'
            . ' WHERE log_action.name = :chBind000';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        // Still restricted, but with no WHERE - a full driving-table scan is slow, not wrong.
        self::assertStringContainsString('idaction IN (SELECT log_visit.idaction_x FROM log_visit AS log_visit))', $out);
    }

    /**
     * The clause is spliced back between two offsets of the original statement, so the
     * whitespace around it is part of the payload. Trimming it welded the rewritten join onto
     * the next keyword - `... = log_action.idactionLEFT JOIN ...` - which ClickHouse reports as
     * a syntax error hundreds of characters from the cause. Real Matomo SQL is newline
     * separated; single-space test fixtures hid this entirely.
     */
    public function testSurroundingWhitespaceSurvivesTheRewrite(): void
    {
        $sql = "SELECT x FROM\n\t\t\tlog_link_visit_action AS log_link_visit_action"
            . "\n\t\t\tLEFT JOIN log_action AS log_action"
            . "\n\t\t\t\tON log_link_visit_action.idaction_name = log_action.idaction"
            . "\n\t\t\tLEFT JOIN log_action AS log_action_name_ref"
            . "\n\t\t\t\tON log_link_visit_action.idaction_name_ref = log_action_name_ref.idaction"
            . "\nWHERE\n\t\t\tlog_link_visit_action.idsite IN (:chBind000)"
            . "\nGROUP BY\n\t\t\tlog_link_visit_action.idaction_name";

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertSame(2, substr_count($out, 'WHERE idaction IN (SELECT'), 'both joins restricted');
        foreach (['idactionLEFT', 'idactionWHERE', 'idactionGROUP', ')LEFT', ')WHERE'] as $welded) {
            self::assertStringNotContainsString($welded, $out);
        }
    }

    /**
     * log_action was the first table big enough to make an unrestricted join fatal, but it is
     * not the only one. This is the segmented Goals\ProductRecord archiving query, which joins
     * log_visit whole for a single visitor_returning conjunct - 364M rows on the POC corpus,
     * and MEMORY_LIMIT_EXCEEDED in FillingRightJoinSide on a 614k-row local corpus.
     */
    public function testLogVisitIsRestrictedTooWhenItIsTheOneOnTheBuildSide(): void
    {
        $sql = 'SELECT x FROM matomo_log_link_visit_action AS log_link_visit_action'
            . ' LEFT JOIN matomo_log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit'
            . ' WHERE ( log_link_visit_action.idsite IN (:chBind000) )'
            . ' AND (log_visit.visitor_returning = 0)';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString(
            'LEFT JOIN (SELECT * FROM matomo_log_visit WHERE idvisit IN'
            . ' (SELECT log_link_visit_action.idvisit FROM matomo_log_link_visit_action AS log_link_visit_action'
            . ' WHERE ( log_link_visit_action.idsite IN (:chBind000) )))'
            . ' AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit',
            $out
        );
    }

    /**
     * Ecommerce's conversion detail in the visits log reaches log_link_visit_action by
     * idlink_va, not by idvisit. Both are complete keys for the join they appear in, and
     * recognising only one of them left the other joining all 2.68M rows.
     */
    public function testLogLinkVisitActionIsRestrictedOnIdlinkVaToo(): void
    {
        $sql = 'SELECT x FROM matomo_log_conversion AS log_conversion'
            . ' LEFT JOIN matomo_log_link_visit_action AS log_link_visit_action'
            . ' ON log_link_visit_action.idlink_va = log_conversion.idlink_va'
            . " WHERE log_conversion.idvisit IN (:chBind000)";

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString(
            'LEFT JOIN (SELECT * FROM matomo_log_link_visit_action WHERE idlink_va IN'
            . ' (SELECT log_conversion.idlink_va FROM matomo_log_conversion AS log_conversion'
            . ' WHERE log_conversion.idvisit IN (:chBind000)))'
            . ' AS log_link_visit_action ON log_link_visit_action.idlink_va = log_conversion.idlink_va',
            $out
        );
    }

    /**
     * Table names are matched with the installation's prefix still attached, and
     * `..._log_link_visit_action` must not be read as a `..._log_action` and restricted on
     * idaction - a column it does not have.
     */
    public function testLogLinkVisitActionIsNotMistakenForLogAction(): void
    {
        $sql = 'SELECT x FROM matomo_log_visit AS log_visit'
            . ' LEFT JOIN matomo_log_link_visit_action AS lvla ON lvla.idvisit = log_visit.idvisit'
            . ' WHERE log_visit.idsite = :chBind000';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString('WHERE idvisit IN (SELECT log_visit.idvisit', $out);
        self::assertStringNotContainsString('idaction', $out);
    }

    /**
     * queryConversionsByEntryPageView(): the log_action join keys off log_visit, not off the
     * driving log_conversion, so it can only be restricted THROUGH log_visit - which the join
     * before it has just turned into a restricted sub-select. Before chaining, this join read
     * log_action whole.
     */
    public function testARestrictedJoinRestrictsTheJoinKeyedOffIt(): void
    {
        $sql = 'SELECT x FROM log_conversion AS log_conversion'
            . ' LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_conversion.idvisit'
            . ' LEFT JOIN log_action AS log_action ON log_action.idaction = log_visit.visit_entry_idaction_name'
            . ' WHERE log_conversion.idsite = :chBind000';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString(
            'LEFT JOIN (SELECT *, `name_lower` FROM log_action WHERE idaction IN'
            . ' (SELECT log_visit.visit_entry_idaction_name FROM'
            . ' (SELECT * FROM log_visit WHERE idvisit IN'
            . ' (SELECT log_conversion.idvisit FROM log_conversion AS log_conversion'
            . ' WHERE log_conversion.idsite = :chBind000)) AS log_visit))'
            . ' AS log_action ON log_action.idaction = log_visit.visit_entry_idaction_name',
            $out
        );
    }

    /**
     * Goals contributes `AND visit_entry_idaction_url IS NOT NULL` to a query driven by
     * log_conversion - a log_visit column, written without its table. Carried into
     * `FROM log_conversion WHERE ...` it does not merely narrow the wrong set, it fails as an
     * unknown identifier, so the whole conjunct group has to be dropped. This surfaced as one
     * failing Actions system test once log_visit joins started being restricted.
     */
    public function testAConjunctNamingAColumnWithoutItsTableIsDropped(): void
    {
        $sql = 'SELECT x FROM log_conversion AS log_conversion'
            . ' LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_conversion.idvisit'
            . ' WHERE ( log_conversion.idsite IN (:chBind000) AND visit_entry_idaction_url IS NOT NULL )';

        $out = ClickhouseDialectTranslator::restrictLogTableJoins($sql);

        self::assertStringContainsString(
            'WHERE idvisit IN (SELECT log_conversion.idvisit FROM log_conversion AS log_conversion))',
            $out,
            'the conjunct group is dropped whole, leaving a restriction with no WHERE'
        );
        self::assertSame(1, substr_count($out, 'visit_entry_idaction_url'));
    }

    /**
     * The keyword allowlist has to leave ordinary bounded conjuncts alone - dropping those
     * would quietly turn every restriction into a full driving-table scan.
     */
    public function testOrdinaryQualifiedConjunctsAreStillCarried(): void
    {
        $where = 'log_link_visit_action.server_time >= :chBind000'
            . ' AND log_link_visit_action.server_time <= :chBind001'
            . " AND log_link_visit_action.idsite IN (:chBind002)"
            . ' AND log_link_visit_action.idaction_url IS NOT NULL'
            . " AND log_link_visit_action.name LIKE '%x%'";

        self::assertSame(
            $where,
            ClickhouseDialectTranslator::keepConjunctsReferencingOnly($where, 'log_link_visit_action')
        );
    }

    /**
     * Chaining only reaches aliases this pass actually restricted. An alias it left alone
     * restricts nothing, because there is no narrower set to restrict through.
     */
    public function testAJoinKeyedOffAnUnrestrictedAliasIsLeftAlone(): void
    {
        // lvla is joined on idvisitor, not idvisit, so it is not restricted - and therefore
        // neither is the log_action join that keys off it.
        $sql = 'SELECT x FROM log_visit AS log_visit'
            . ' LEFT JOIN log_link_visit_action AS lvla ON lvla.idvisitor = log_visit.idvisitor'
            . ' LEFT JOIN log_action AS log_action ON lvla.idaction_url = log_action.idaction'
            . ' WHERE log_visit.idsite = :chBind000';

        self::assertSame($sql, ClickhouseDialectTranslator::restrictLogTableJoins($sql));
    }

    /**
     * A RIGHT (or FULL) join keeps its unmatched right-hand rows, so removing one is only safe
     * if a driving conjunct rejects the NULLs it would be joined against - an argument this
     * pass cannot make from the SQL alone. LogAggregator::queryConversionsByPageView() can make
     * it for its own RIGHT JOIN, and builds that sub-select itself.
     */
    public function testRightAndFullJoinsAreLeftAlone(): void
    {
        foreach (['RIGHT JOIN', 'RIGHT OUTER JOIN', 'FULL OUTER JOIN'] as $joinType) {
            $sql = 'SELECT x FROM log_conversion AS log_conversion'
                . ' ' . $joinType . ' log_link_visit_action AS logva ON log_conversion.idvisit = logva.idvisit'
                . ' WHERE log_conversion.idsite = :chBind000';

            self::assertSame($sql, ClickhouseDialectTranslator::restrictLogTableJoins($sql), $joinType);
        }
    }

    /**
     * @dataProvider unsafeToRewriteProvider
     */
    public function testUnreadableJoinsAreLeftExactlyAsTheyAre(string $sql): void
    {
        self::assertSame($sql, ClickhouseDialectTranslator::restrictLogTableJoins($sql));
    }

    public function unsafeToRewriteProvider(): array
    {
        return [
            'ON is an expression, not a plain equality' => [
                'SELECT x FROM log_link_visit_action AS log_link_visit_action'
                . ' LEFT JOIN log_action AS log_action ON log_action.idaction = if(log_link_visit_action.a > 0, 1, 2)'
                . ' WHERE log_link_visit_action.idsite = :chBind000',
            ],
            'right side already restricted by LogAggregator' => [
                'SELECT x FROM log_link_visit_action AS log_link_visit_action'
                . ' LEFT JOIN (SELECT * FROM log_action WHERE idaction IN (1)) AS log_action'
                . ' ON log_link_visit_action.idaction_url = log_action.idaction'
                . ' WHERE log_link_visit_action.idsite = :chBind000',
            ],
            'join key is not idaction' => [
                'SELECT x FROM log_visit AS log_visit'
                . ' LEFT JOIN log_action AS log_action ON log_visit.idvisit = log_action.idvisit'
                . ' WHERE log_visit.idsite = :chBind000',
            ],
            'driving side is a subquery' => [
                'SELECT x FROM (SELECT 1) AS drv'
                . ' LEFT JOIN log_action AS log_action ON drv.idaction_x = log_action.idaction'
                . ' WHERE drv.idsite = :chBind000',
            ],
            'nothing is joined at all' => [
                'SELECT x FROM log_visit AS log_visit WHERE log_visit.idsite = :chBind000',
            ],
            'the joined table is not a log table' => [
                'SELECT x FROM log_visit AS log_visit'
                . ' LEFT JOIN site AS site ON site.idsite = log_visit.idsite'
                . ' WHERE log_visit.idsite = :chBind000',
            ],
        ];
    }
}
