<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

/**
 * Translates MySQL-flavoured SQL produced by LogAggregator and its callers into
 * ClickHouse-compatible SQL before the query is sent to the analytics database.
 *
 * This class is intentionally stateless; all methods are static.
 *
 * ## What is translated
 *
 * ### SQL function names
 * Matomo plugins pass MySQL function names directly into dimension expressions
 * (e.g. `HOUR(log_visit.visitor_localtime)`). The following mappings are applied:
 *
 * | MySQL          | ClickHouse        | Notes                              |
 * |----------------|-------------------|------------------------------------|
 * | HOUR(x)        | toHour(x)         | VisitTime, ServerTime plugins      |
 * | DATE(x)        | toDate(x)         | CoreHome date columns              |
 * | WEEK(x,n)      | toWeek(x,n)       | VisitDay plugin                    |
 * | DAYOFWEEK(x)   | toDayOfWeek(x)    | VisitDay plugin                    |
 * | IFNULL(a,b)    | ifNull(a,b)       | Segment expression generation      |
 * | CONCAT(...)    | concat(...)       | MySQL is case-insensitive; CH is not|
 *
 * ### ReplacingMergeTree deduplication
 * The log table copies in ClickHouse use the ReplacingMergeTree engine, so reads must
 * collapse row versions. That is NOT handled here: the adapter sends the `final = 1`
 * query setting with every request (see Piwik\Db\Adapter\Clickhouse), which applies
 * FINAL to every FINAL-capable table in the query — including subqueries and aliased
 * references that a textual rewrite could miss.
 */
class ClickhouseDialectTranslator
{
    /**
     * Translates a MySQL SQL string to ClickHouse-compatible SQL.
     *
     * @param string $sql  MySQL-flavoured SQL
     * @return string      ClickHouse-compatible SQL
     */
    public static function translate(string $sql): string
    {
        $sql = self::stripMysqlExecutionTimeHints($sql);
        $sql = self::translateFunctions($sql);
        $sql = self::rewriteVisitDedup($sql);
        $sql = self::rewriteEmptyStringComparisons($sql);
        $sql = self::rewriteCountDistinctOnNullableColumns($sql);
        $sql = self::rewriteActionNameLikeToIndexedColumn($sql);
        $sql = self::rewriteLikeComparisons($sql);
        $sql = self::aliasQualifiedSelectColumns($sql);
        $sql = self::fixGroupBy($sql);
        return $sql;
    }

    /**
     * Gives unaliased qualified SELECT items an explicit alias: `tbl.col` → `tbl.col AS col`.
     *
     * MySQL always names such a result column by its short name. ClickHouse names it by
     * its qualified name whenever the short name is ambiguous in a JOIN (the column
     * exists on both sides), so an outer query wrapping the subquery — RankingQuery's
     * envelope does exactly that — fails to resolve the short reference with
     * UNKNOWN_IDENTIFIER. The explicit alias pins MySQL's naming at every query level.
     */
    private static function aliasQualifiedSelectColumns(string $sql): string
    {
        // Recurse into top-level FROM (...) subqueries first.
        $sql = self::mapFromBlocks($sql, [self::class, 'aliasQualifiedSelectColumns']);

        $topFromPos = self::findTopLevelFromPos($sql);
        if ($topFromPos === null || !preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $sql, $selPrefix)) {
            return $sql;
        }

        $selPrefixLen = strlen($selPrefix[0]);
        $selectClause = substr($sql, $selPrefixLen, $topFromPos - $selPrefixLen);

        // Rebuild the clause item by item rather than replacing text in it. A query may
        // select the same column twice under different aliases - the visits log selects
        // log_link_visit_action.idlink_va both as `idlink_va` and as pageId - and
        // replacing the bare reference everywhere would append this alias to the
        // already-aliased copy too, producing `col AS `idlink_va` AS pageId`.
        $newItems = [];
        $changed   = false;

        foreach (self::splitByComma(trim($selectClause)) as $item) {
            $trimmedItem = trim($item);
            if (preg_match('/^(`?\w+`?)\.(`?\w+`?)$/', $trimmedItem, $m)) {
                $newItems[] = $trimmedItem . ' AS `' . str_replace('`', '', $m[2]) . '`';
                $changed    = true;
            } else {
                $newItems[] = $trimmedItem;
            }
        }

        if (!$changed) {
            return $sql;
        }

        return substr($sql, 0, $selPrefixLen) . implode(', ', $newItems) . ' ' . substr($sql, $topFromPos);
    }

    /**
     * Applies $callback to the contents of every top-level FROM (...) block.
     *
     * @param callable(string): string $callback
     */
    private static function mapFromBlocks(string $sql, callable $callback): string
    {
        if (!preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $sql, $m)) {
            return $sql;
        }

        $len = strlen($sql);
        $depth = 0;
        $i = strlen($m[0]);
        $result = $m[0];

        while ($i < $len) {
            $ch = $sql[$i];

            if ($ch === '(') {
                $depth++;
                $result .= $ch;
                $i++;
            } elseif ($ch === ')') {
                $depth--;
                $result .= $ch;
                $i++;
            } elseif (
                $depth === 0 && ($ch === 'F' || $ch === 'f')
                && preg_match('/^FROM\s+\(/i', substr($sql, $i), $fm)
            ) {
                $fromPrefixLen = strlen($fm[0]) - 1;
                $parenOpen = $i + $fromPrefixLen;
                $subDepth = 0;
                $parenClose = -1;

                for ($j = $parenOpen; $j < $len; $j++) {
                    $c = $sql[$j];
                    if ($c === '(') {
                        $subDepth++;
                    } elseif ($c === ')') {
                        $subDepth--;
                        if ($subDepth === 0) {
                            $parenClose = $j;
                            break;
                        }
                    }
                }

                if ($parenClose < 0) {
                    $result .= substr($sql, $i);
                    return $result;
                }

                $subContent = substr($sql, $parenOpen + 1, $parenClose - $parenOpen - 1);
                $result .= substr($sql, $i, $fromPrefixLen) . '(' . $callback($subContent) . ')';
                $i = $parenClose + 1;
            } else {
                $result .= $ch;
                $i++;
            }
        }

        return $result;
    }

    /**
     * Strips the MySQL optimizer hint and MariaDB statement prefix Matomo uses to cap
     * query runtime; ClickHouse's equivalent is the max_execution_time query setting,
     * which the adapter controls.
     */
    private static function stripMysqlExecutionTimeHints(string $sql): string
    {
        $sql = preg_replace('~/\*\+\s*MAX_EXECUTION_TIME\(\d+\)\s*\*/~i', '', $sql) ?? $sql;
        $sql = preg_replace('~^\s*SET\s+STATEMENT\s+max_statement_time=\S+\s+FOR\s+~i', '', $sql) ?? $sql;
        return $sql;
    }

    /**
     * Segmented visits-log queries dedup joined rows with MySQL's loose
     * `SELECT log_visit.* … GROUP BY log_visit.idvisit`, which ClickHouse rejects
     * (NOT_AN_AGGREGATE — the starred columns are neither keys nor aggregated).
     * ClickHouse's native equivalent is `LIMIT 1 BY idvisit`: keep the first row per
     * visit after ORDER BY. Rewrites only a top-level `GROUP BY <x>.idvisit` (the last
     * occurrence, and only when nothing after it reopens parentheses — inner subqueries
     * like the intersect-segment filter group validly and are left alone).
     */
    private static function rewriteVisitDedup(string $sql): string
    {
        // The lookaheads keep out multi-column clauses (GROUP BY idvisit, idaction) and
        // columns that merely start with "idvisit" (GROUP BY idvisitor): those are
        // genuine aggregations handled by fixGroupBy(), not row dedup.
        $pattern = '~\bGROUP\s+BY\s+((?:`?\w+`?\.)?`?idvisit`?)(?![\w`])(?!\s*,)\s*~i';
        if (!preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return $sql;
        }

        $last = count($matches[0]) - 1;
        [$groupByText, $groupByOffset] = $matches[0][$last];
        $dedupColumn = $matches[1][$last][0];

        $remainder = substr($sql, $groupByOffset + strlen($groupByText));
        if (strpos($remainder, ')') !== false) {
            // Not provably top-level; leave the query for fixGroupBy() to handle.
            return $sql;
        }

        $sql = substr($sql, 0, $groupByOffset) . ' ' . $remainder;

        if (preg_match('~\bLIMIT\s+\d+(?:\s*,\s*\d+)?\s*$~i', $sql, $limitMatch, PREG_OFFSET_CAPTURE)) {
            $limitOffset = $limitMatch[0][1];
            return substr($sql, 0, $limitOffset) . 'LIMIT 1 BY ' . $dedupColumn . ' ' . substr($sql, $limitOffset);
        }

        return $sql . ' LIMIT 1 BY ' . $dedupColumn;
    }

    /**
     * Matomo's "is (not) empty" segment idiom compares any column against '' and '0'
     * literals, relying on MySQL's loose typing ('' casts to 0). ClickHouse refuses to
     * compare numeric columns with ''; comparing toString(col) instead reproduces the
     * MySQL results exactly — the idiom's own "<> '0'" leg keeps numeric zero excluded.
     */
    private static function rewriteEmptyStringComparisons(string $sql): string
    {
        return preg_replace(
            "~((?:`?\\w+`?\\.)?`?\\w+`?)\\s*(<>|!=|=)\\s*('(?:0)?')~",
            'toString($1) $2 $3',
            $sql
        ) ?? $sql;
    }

    /**
     * Columns that are NULLable in MySQL and whose MySQL NULLs may not have survived the
     * copy into ClickHouse.
     *
     * MySQL's COUNT(DISTINCT x) ignores NULL. Both replication paths in use land these
     * as a *non-nullable* String, so every MySQL NULL arrives as the empty string - which
     * ClickHouse's uniqExact then counts as a value, giving one spurious extra distinct
     * on every row group. Measured 2 Sep 2026 against ClickHouse 25.8 on the two possible
     * mappings, for data whose MySQL answer is 2:
     *
     * | destination column | count(DISTINCT user_id) | uniqExactIf(user_id, != '') |
     * |--------------------|-------------------------|-----------------------------|
     * | String             | 3  (wrong)              | 2                           |
     * | Nullable(String)   | 2                       | 2                           |
     *
     * The guarded form is therefore correct on BOTH mappings, which matters because the
     * defect is invisible where it is most likely to be tested: the in-test log table
     * sync produces Nullable columns and so gets the right answer, while the ClickPipes
     * destination the POC measures against does not. The number is plausible and wrong by
     * exactly one, so no row-count check catches it.
     */
    private const NULL_FLATTENED_STRING_COLUMNS = ['user_id'];

    /*
     * NOT TRANSLATED, AND NOT TRANSLATABLE: the nullable idaction_url_ref /
     * idaction_name_ref columns.
     *
     * ClickPipes maps them to plain UInt32, so every MySQL NULL arrives as 0. Matomo's
     * Transitions query branches on `idaction_url_ref IS NULL` ("site search referrers are
     * logged with url_ref = NULL; when we find one we have to join on name_ref"), and
     * against that destination the branch can never fire.
     *
     * Rewriting IS NULL to `(IS NULL OR = 0)` here looks like the obvious repair and is
     * wrong. 0 and NULL are BOTH meaningful in this column and they mean different things:
     * 0 is "this action had no referring action at all", NULL is "the referrer was a site
     * search". Treating them alike moves site searches into the wrong bucket - measured
     * 2 Sep 2026, it added a spurious previousSiteSearches row to three of the seven
     * Transitions system tests.
     *
     * So the destination has genuinely lost information the adapter cannot reconstruct,
     * and this is a PIPE CONFIGURATION REQUIREMENT rather than a dialect difference: the
     * idaction_*_ref columns must be replicated as Nullable. Recorded in CLICKHOUSE.md §6.
     */

    /**
     * Reproduces MySQL's "COUNT(DISTINCT) ignores NULL" on columns whose NULLs the copy
     * flattened to the empty string.
     *
     * @see NULL_FLATTENED_STRING_COLUMNS for the measurement this is based on.
     */
    private static function rewriteCountDistinctOnNullableColumns(string $sql): string
    {
        $columns = implode('|', self::NULL_FLATTENED_STRING_COLUMNS);

        return preg_replace(
            '~\bcount\s*\(\s*distinct\s+((?:`?\w+`?\.)?`?(?:' . $columns . ')`?)\s*\)~i',
            "uniqExactIf($1, $1 != '')",
            $sql
        ) ?? $sql;
    }

    /**
     * Adjusts LIKE comparisons for ClickHouse in two ways.
     *
     * MySQL allows LIKE on numeric columns by casting (the GDPR data subject search
     * does idvisit LIKE '10%'); ClickHouse requires a String argument. toString() is
     * the identity for String columns and MySQL's cast semantics for everything else.
     *
     * MySQL also matches LIKE case insensitively under its default collation, which is
     * what segments such as pageUrl=@Foo rely on, while ClickHouse's LIKE is case
     * sensitive. ILIKE is the case insensitive equivalent, so segments keep matching the
     * same rows on both. On the hex-encoded binary columns this is marginally more
     * permissive than MySQL's case sensitive binary comparison, but those values are
     * always written lower case, so nothing additional matches in practice.
     *
     * The pattern anchors on an identifier followed by the operator, so the word "like"
     * inside a string literal is left alone.
     */
    private static function rewriteLikeComparisons(string $sql): string
    {
        return preg_replace_callback(
            '~((?:`?\w+`?\.)?`?\w+`?)(\s+(?:NOT\s+)?)LIKE(\s)~i',
            static function (array $matches): string {
                // rewriteActionNameLikeToIndexedColumn() has already made this pair
                // case-insensitive by construction, and turning it into ILIKE here would
                // undo the only reason that rewrite exists.
                if (preg_match('~`?name_lower`?$~i', $matches[1])) {
                    return $matches[0];
                }

                return 'toString(' . $matches[1] . ')' . $matches[2] . 'ILIKE' . $matches[3];
            },
            $sql
        ) ?? $sql;
    }

    /**
     * Points segment LIKEs on log_action.name at the lowercased, indexed copy of that
     * column, and lowercases the needle to match.
     *
     * This is the one place where the correctness fix and the performance fix pull in
     * opposite directions. MySQL's default collation matches LIKE case-insensitively,
     * which is what a segment such as pageUrl=@Foo relies on, and the obvious ClickHouse
     * equivalent is ILIKE. But `ngrambf_v1` is never consulted for ILIKE - only for LIKE -
     * so the obvious fix is correct and unindexable at once, and on the 200M-hit corpus
     * that is the difference between a scan of log_action and a skip.
     *
     * Both properties are available together by comparing against the MATERIALIZED
     * lower(name) column with an already-lowercased needle, which is what the benchmark
     * SQL does and what {@see \Piwik\Db\ClickhouseLogTableSync} now creates.
     *
     * A literal needle is lowered here; a bound one is lowered by
     * {@see lowercaseNeedlesForIndexedColumns()} once the binds are known. Both use
     * strtolower() rather than mb_strtolower() deliberately: the stored column is
     * ClickHouse's lower(), which is also ASCII-only, and lowering the needle further
     * than the column would stop it matching.
     */
    private static function rewriteActionNameLikeToIndexedColumn(string $sql): string
    {
        return preg_replace_callback(
            '~(`?log_action\w*`?)\.`?name`?(\s+(?:NOT\s+)?LIKE\s+)(\'(?:[^\'\\\\]|\\\\.)*\'|\?|:\w+)~i',
            static function (array $matches): string {
                $needle = $matches[3];
                if (isset($needle[0]) && "'" === $needle[0]) {
                    $needle = strtolower($needle);
                }

                return $matches[1] . '.`name_lower`' . $matches[2] . $needle;
            },
            $sql
        ) ?? $sql;
    }

    /**
     * Lowercases the bind values feeding the LIKEs {@see rewriteActionNameLikeToIndexedColumn()}
     * pointed at the lowercased column. Called by the adapter once positional binds have
     * been named, because that is the first point at which SQL and values are both known.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public static function lowercaseNeedlesForIndexedColumns(string $sql, array $params): array
    {
        if (!preg_match_all('~`?name_lower`?\s+(?:NOT\s+)?LIKE\s+:(\w+)~i', $sql, $matches)) {
            return $params;
        }

        foreach ($matches[1] as $name) {
            if (isset($params[$name]) && is_string($params[$name])) {
                $params[$name] = strtolower($params[$name]);
            }
        }

        return $params;
    }

    // -------------------------------------------------------------------------
    // Function name translation
    // -------------------------------------------------------------------------

    /**
     * Replaces MySQL scalar functions with their ClickHouse equivalents.
     *
     * All substitutions are case-insensitive (MySQL is case-insensitive for
     * function names; ClickHouse is not).
     */
    private static function translateFunctions(string $sql): string
    {
        // Order matters where one name is a prefix of another.
        $replacements = [
            // Date/time extraction
            '/\bHOUR\s*\(/i'      => 'toHour(',
            '/\bDAYOFWEEK\s*\(/i' => 'toDayOfWeek(',
            '/\bWEEK\s*\(/i'      => 'toWeek(',
            '/\bDATE\s*\(/i'      => 'toDate(',

            // NULL handling
            '/\bIFNULL\s*\(/i'    => 'ifNull(',

            // String functions — MySQL is case-insensitive, ClickHouse is not
            '/\bCONCAT\s*\(/i'    => 'concat(',

            // ClickHouse's lower()/upper() only fold ASCII, so they leave accented
            // characters as they were: a campaign keyword MySQL reports as
            // 'mot_clé_pépère' came back as 'mot_clé_pÉpÈre'. The UTF-8 aware variants
            // match MySQL's utf8mb4 case folding, and still handle pure-ASCII arguments
            // such as lower(hex(idvisitor)).
            '/\bLOWER\s*\(/i'     => 'lowerUTF8(',
            '/\bUPPER\s*\(/i'     => 'upperUTF8(',

            // STRAIGHT_JOIN is MySQL's inner join with the join order forced to the written
            // order. ClickHouse has no such hint and picks its own order, so the join
            // itself is all that carries over.
            '/\bSTRAIGHT_JOIN\b/i' => 'INNER JOIN',

            // MySQL index hints (USE INDEX, FORCE INDEX, IGNORE INDEX) are not supported by
            // ClickHouse.  Strip them entirely; ClickHouse selects its own scan path.
            '/\s+(?:USE|FORCE|IGNORE)\s+INDEX\s*\([^)]*\)/i' => '',

            // visitor_localtime is stored as a 'HH:MM:SS' string in ClickHouse (MySQL TIME
            // type has no direct equivalent).  toHour() only accepts DateTime/Date, so we
            // extract the first two characters and cast to UInt8 instead.
            '/\btoHour\s*\(([^)]*\bvisitor_localtime\b[^)]*)\)/i'
                => 'toUInt8(substring($1, 1, 2))',

            // Type-compatibility fix for CASE WHEN … THEN 'string' ELSE identifier END.
            //
            // MySQL implicitly coerces numeric identifiers to String when one CASE branch
            // holds a string literal.  ClickHouse enforces strict type compatibility and
            // raises "no supertype for types String, UInt32" (or similar) without an explicit
            // cast.  This pattern appears in RankingQuery::generateLabelColumnsOthersSwitch()
            // where numeric label columns (idaction UInt32, type UInt8 …) are placed in an
            // ELSE branch alongside the string literal '__mtm_ranking_query_others__'.
            //
            // Wrapping the ELSE identifier with toString() is safe for all column types:
            //   • toString() is identity for String/LowCardinality(String) columns.
            //   • For numeric columns it produces the decimal string representation, e.g.
            //     toString(12345) = '12345'.  The PHP layer reads result column values as
            //     strings anyway, so the type change is transparent at the application level.
            '/\b(CASE\s+WHEN\s+\w+\s*=\s*\d+\s+THEN\s+\'[^\']*\'\s+ELSE\s+)(`?\w+`?)(\s+END\b)/is'
                => '$1toString($2)$3',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $sql = preg_replace($pattern, $replacement, $sql) ?? $sql;
        }

        // ClickHouse's ROW_NUMBER() returns UInt64, but RankingQuery uses -1 as a NULL-row
        // sentinel in the same multiIf() expression, which is a signed type.  ClickHouse
        // cannot find a common supertype for UInt64 and Int8.  Wrapping ROW_NUMBER() OVER
        // (...) with toInt64() makes both branches signed integers (Int64 and Int8 → Int64).
        $sql = self::wrapRowNumberWithToInt64($sql);

        // RankingQuery rollup outer wrapper: the rollup variant generates a 4-branch
        // CASE WHEN that references counterRollup, counter, and label columns — none of
        // which are in GROUP BY directly.  Wrap all bare references with any().
        //
        // Pattern (primary dimension):
        //   CASE WHEN counterRollup = N THEN 'others'
        //        WHEN counterRollup > 0  THEN col
        //        WHEN counter = N        THEN 'others'
        //        ELSE col
        //   END
        //
        // Pattern (secondary dimensions, THEN value is NULL instead of 'others'):
        //   CASE WHEN counterRollup = N THEN NULL ...
        //
        // Changed to >= N (same reason as the simple counter pattern below) so that
        // all "others" rows are captured when window functions produce counter > N.
        $sql = preg_replace_callback(
            "/\bCASE\s+WHEN\s+(?:`?\\w+`?\\.)?counterRollup\s*=\s*(\\d+)\s+THEN\s+('[^']*'|NULL)\s+"
            . "WHEN\s+(?:`?\\w+`?\\.)?counterRollup\s*>\\s*0\s+THEN\s+(`?\\w+`?)\s+"
            . "WHEN\s+(?:`?\\w+`?\\.)?counter\s*=\s*(\\d+)\s+THEN\s+('[^']*')\s+"
            . "ELSE\s+(`?\\w+`?)\s+END\b/is",
            function ($m) {
                return 'CASE WHEN any(counterRollup) >= ' . $m[1]
                     . ' THEN ' . $m[2]
                     . ' WHEN any(counterRollup) > 0 THEN any(' . $m[3] . ')'
                     . ' WHEN any(counter) >= ' . $m[4] . ' THEN ' . $m[5]
                     . ' ELSE any(' . $m[6] . ') END';
            },
            $sql
        ) ?? $sql;

        // RankingQuery outer wrapper: aggregate counter and identifier columns that are
        // referenced inside CASE WHEN but are not in GROUP BY keys.
        //
        // The outer wrapper produced by RankingQuery::generateLabelColumnsOthersSwitch()
        // after the toString() normalisation above looks like:
        //   CASE WHEN counter = N THEN 'str' ELSE toString(identifier) END
        //
        // ClickHouse requires every non-constant column reference to be either in GROUP BY
        // or inside an aggregate.  `counter` is only in GROUP BY indirectly via:
        //   CASE WHEN counter >= N THEN N ELSE counter END
        // so we must wrap both it and the ELSE identifier with any():
        //   CASE WHEN any(counter) >= N THEN 'str' ELSE toString(any(identifier)) END
        //
        // any() returns an arbitrary value from the group.
        //   - For the "others" group all rows have counter >= N, so any(counter) >= N is
        //     guaranteed to be TRUE.
        //   - For non-others groups all rows share the same counter value, so
        //     any(counter) == counter and the condition is FALSE.
        //
        // The table-qualifier prefix (e.g. withCounter.counter) is optional in the pattern
        // to handle both qualified and unqualified references.
        $sql = preg_replace_callback(
            '/\bCASE\s+WHEN\s+(?:`?\w+`?\.)?counter\s*=\s*(\d+)\s+THEN\s+(\'[^\']*\')\s+ELSE\s+toString\((`?\w+`?)\)\s+END\b/is',
            function ($m) {
                return 'CASE WHEN any(counter) >= ' . $m[1]
                     . ' THEN ' . $m[2]
                     . ' ELSE toString(any(' . $m[3] . ')) END';
            },
            $sql
        ) ?? $sql;

        return $sql;
    }

    // -------------------------------------------------------------------------
    // GROUP BY completion for ClickHouse strict-mode compatibility
    // -------------------------------------------------------------------------

    /**
     * MySQL without ONLY_FULL_GROUP_BY allows SELECT columns that are neither
     * aggregated nor in GROUP BY (it picks an arbitrary value from each group).
     * ClickHouse enforces the SQL standard and rejects such queries.
     *
     * Matomo's archiving queries rely on this relaxed behaviour for columns
     * selected from 1:1-joined tables (e.g. log_action columns joined on
     * log_action.idaction, which is its primary key). Since the join is on the
     * PK the values are functionally determined by the GROUP BY key, so adding
     * them to GROUP BY is semantically equivalent.
     *
     * This method finds every bare column reference in the SELECT list that is
     * not already in GROUP BY and appends it to the GROUP BY clause.
     * Aggregate expressions (those containing '(') are ignored.
     */
    private static function fixGroupBy(string $sql): string
    {
        if (!preg_match('/\bGROUP\s+BY\b/i', $sql)) {
            return $sql;
        }

        // Process bottom-up: recursively fix GROUP BY inside all top-level FROM (...)
        // blocks before fixing the outer query.  This ensures that when the outer
        // query wraps an inner aggregation (e.g. the RankingQuery envelope), the inner
        // query's GROUP BY issues are resolved first.
        $sql = self::fixGroupByInFromBlocks($sql);

        // WITH ROLLUP needs its grouping keys to be nullable before the SELECT list is
        // rewritten, so that the marker rows it adds are recognisable as rollup rows.
        $sql = self::makeRollupKeysNullable($sql);

        // Now fix the top-level SELECT / GROUP BY of this query.
        return self::fixGroupByOneLevel($sql);
    }

    /**
     * Scans the SQL at depth 0 for top-level FROM (...) blocks and recursively
     * applies fixGroupBy to their contents.
     */
    private static function fixGroupByInFromBlocks(string $sql): string
    {
        if (!preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $sql, $m)) {
            return $sql;
        }

        $len    = strlen($sql);
        $depth  = 0;
        $result = '';
        $i      = strlen($m[0]); // start scanning after SELECT ... hints

        // Copy the SELECT prefix verbatim
        $result = $m[0];

        while ($i < $len) {
            $ch = $sql[$i];

            if ($ch === '(') {
                $depth++;
                $result .= $ch;
                $i++;
            } elseif ($ch === ')') {
                $depth--;
                $result .= $ch;
                $i++;
            } elseif (
                $depth === 0 && ($ch === 'F' || $ch === 'f')
                && preg_match('/^FROM\s+\(/i', substr($sql, $i), $fm)
            ) {
                // Found top-level FROM (  — find matching closing paren
                $fromPrefixLen = strlen($fm[0]) - 1; // "FROM " without the "("
                $parenOpen     = $i + $fromPrefixLen;  // offset of the "("
                $subDepth      = 0;
                $parenClose    = -1;

                for ($j = $parenOpen; $j < $len; $j++) {
                    $c = $sql[$j];
                    if ($c === '(') {
                        $subDepth++;
                    } elseif ($c === ')') {
                        $subDepth--;
                        if ($subDepth === 0) {
                            $parenClose = $j;
                            break;
                        }
                    }
                }

                if ($parenClose < 0) {
                    // Unmatched paren — bail out, copy rest verbatim
                    $result .= substr($sql, $i);
                    return $result;
                }

                // Extract, recursively fix, and reconstruct the subquery
                $subContent = substr($sql, $parenOpen + 1, $parenClose - $parenOpen - 1);
                $fixedSub   = self::fixGroupBy($subContent);

                $result .= substr($sql, $i, $fromPrefixLen) . '(' . $fixedSub . ')';
                $i = $parenClose + 1;
            } else {
                $result .= $ch;
                $i++;
            }
        }

        return $result;
    }

    /**
     * Wraps the grouping keys of a `GROUP BY ... WITH ROLLUP` in toNullable().
     *
     * MySQL marks the extra rows a rollup produces by setting the rolled-up grouping
     * columns to NULL, and Matomo's archiving code identifies them that way — see
     * Referrers\RecordBuilders\AIReferrers, which treats a row as a rollup total when
     * its label is null.
     *
     * ClickHouse instead fills those columns with the default value of their type, so
     * only a Nullable column yields NULL. A grouping key such as
     * `COALESCE(log_action.name, '') AS action_name` is non-nullable by construction and
     * comes back as the empty string, which is indistinguishable from a genuine empty
     * value: the rollup rows are then read as detail rows and their totals are lost.
     *
     * Making each grouping key Nullable restores MySQL's semantics, because the default
     * value of a Nullable type is NULL:
     *
     *   SELECT COALESCE(log_action.name, '') AS action_name          → rollup row: ''
     *   SELECT toNullable(COALESCE(log_action.name, '')) AS action_name → rollup row: NULL
     *
     * Keys that are already nullable are wrapped too; toNullable() is a no-op for them.
     */
    private static function makeRollupKeysNullable(string $sql): string
    {
        if (!preg_match('/\bWITH\s+ROLLUP\b/i', $sql)) {
            return $sql;
        }

        $groupByClause = self::extractTopLevelGroupBy($sql);
        if ($groupByClause === null) {
            return $sql;
        }

        // extractTopLevelGroupBy() keeps the trailing WITH ROLLUP.  Its presence also
        // confirms that the rollup belongs to this query rather than a nested one.
        if (!preg_match('/\bWITH\s+ROLLUP\b\s*$/i', $groupByClause)) {
            return $sql;
        }
        $originalGroupBy = $groupByClause;
        $groupByClause = (string) preg_replace('/\s*\bWITH\s+ROLLUP\b\s*$/i', '', $groupByClause);

        $topFromPos = self::findTopLevelFromPos($sql);
        if ($topFromPos === null || !preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $sql, $selPrefix)) {
            return $sql;
        }

        $selPrefixLen = strlen($selPrefix[0]);
        $selectClause = trim(substr($sql, $selPrefixLen, $topFromPos - $selPrefixLen));
        if ($selectClause === '') {
            return $sql;
        }

        $groupByItems = self::splitByComma($groupByClause);
        $groupByKeys  = array_map([self::class, 'normalizeIdent'], $groupByItems);

        $newSelect = $selectClause;
        // Grouping keys whose SELECT item gets wrapped: the GROUP BY has to be wrapped
        // identically (see below).
        $keysToWrap = [];

        foreach (self::splitByComma($selectClause) as $item) {
            $trimmedItem = trim($item);

            $alias = null;
            if (preg_match('/\s+AS\s+(`[^`]*`|\w+)\s*$/i', $trimmedItem, $aliasMatch)) {
                $alias = $aliasMatch[1];
            }

            $expr = trim((string) preg_replace('/\s+AS\s+(`[^`]*`|\w+)\s*$/i', '', $trimmedItem));
            if ($expr === '' || stripos($expr, 'toNullable(') === 0) {
                continue;
            }

            // A SELECT item belongs to a grouping key when the key names the expression
            // itself, its unqualified column name, or the alias the item was given.
            $names = [
                self::normalizeIdent($expr),
                self::normalizeIdent(self::extractNaturalName($expr)),
            ];
            if ($alias !== null) {
                $names[] = self::normalizeIdent($alias);
            }

            if (empty(array_intersect($names, $groupByKeys))) {
                continue;
            }

            // Only when the GROUP BY repeats the expression: a GROUP BY that names the
            // SELECT alias instead already groups by the wrapped (nullable) expression,
            // and wrapping the alias too would stop fixGroupByOneLevel() from matching
            // the item to its key.
            $exprName = self::normalizeIdent($expr);
            foreach ($groupByKeys as $keyIndex => $groupByKey) {
                if ($groupByKey === $exprName) {
                    $keysToWrap[$keyIndex] = true;
                }
            }

            $aliasForWrapper = $alias ?? ('`' . self::extractNaturalName($expr) . '`');
            $newSelect       = str_replace(
                $trimmedItem,
                'toNullable(' . $expr . ') AS ' . $aliasForWrapper,
                $newSelect
            );
        }

        if ($newSelect === $selectClause) {
            return $sql;
        }

        // The grouping key itself must be nullable, not just the SELECT item: ClickHouse
        // fills a rollup row's grouping keys with the key type's default, so a String key
        // yields '' and only a Nullable key yields NULL. Wrapping the GROUP BY entry the
        // same way as the SELECT item also keeps the two textually identical, so
        // fixGroupByOneLevel() still recognises the item as a grouping key and does not
        // any()-wrap it - an aggregate would return a real value for the rollup group and
        // destroy the marker just as effectively.
        $tail = substr($sql, $topFromPos);

        if (!empty($keysToWrap)) {
            $newGroupByItems = [];
            foreach ($groupByItems as $keyIndex => $groupByItem) {
                $groupByItem = trim($groupByItem);
                $newGroupByItems[] = isset($keysToWrap[$keyIndex])
                    ? 'toNullable(' . $groupByItem . ')'
                    : $groupByItem;
            }

            $newGroupBy = implode(', ', $newGroupByItems) . ' WITH ROLLUP';
            $position   = strpos($tail, $originalGroupBy);

            if ($position !== false) {
                $tail = substr_replace($tail, $newGroupBy, $position, strlen($originalGroupBy));
            }
        }

        return substr($sql, 0, $selPrefixLen) . $newSelect . ' ' . $tail;
    }

    /**
     * Fixes the GROUP BY of the top-level (outermost) SELECT in $sql by wrapping
     * bare column references that appear in SELECT but not in GROUP BY with any().
     *
     * any() is semantically equivalent to MySQL's relaxed GROUP BY behaviour:
     *   - For columns that are functionally determined by the GROUP BY key (e.g.
     *     log_action.name when grouped by log_action.idaction), any() returns the
     *     one and only value in the group — identical to a proper GROUP BY column.
     *   - For outer RankingQuery wrapper columns that derive from aggregates in a
     *     subquery, adding them to GROUP BY would trigger ClickHouse's
     *     ILLEGAL_AGGREGATION; any() sidesteps this by keeping them in SELECT only.
     */
    private static function fixGroupByOneLevel(string $sql): string
    {
        if (!preg_match('/\bGROUP\s+BY\b/i', $sql)) {
            return $sql;
        }

        $topFromPos = self::findTopLevelFromPos($sql);
        if ($topFromPos === null) {
            return $sql;
        }

        if (!preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $sql, $selPrefix)) {
            return $sql;
        }
        $selPrefixLen = strlen($selPrefix[0]);
        $selectClause = trim(substr($sql, $selPrefixLen, $topFromPos - $selPrefixLen));

        $groupByClause = self::extractTopLevelGroupBy($sql);
        if ($selectClause === '' || $groupByClause === null) {
            return $sql;
        }

        // The extracted clause keeps a trailing WITH ROLLUP; it is not part of the keys.
        $groupByClause = (string) preg_replace('/\s*\bWITH\s+ROLLUP\b\s*$/i', '', $groupByClause);

        $groupByNormalized = array_map(
            [self::class, 'normalizeIdent'],
            self::splitByComma($groupByClause)
        );
        $groupByNormalizedExpressions = array_map(
            [self::class, 'normalizeExpression'],
            self::splitByComma($groupByClause)
        );

        $toFix = [];
        foreach (self::splitByComma($selectClause) as $item) {
            $trimmedItem = trim($item);

            // Extract the trailing alias (if present) before stripping it.
            // Preserving the alias is critical: if the original had `col AS label` and
            // GROUP BY references `label`, we must keep `label` in the any() wrapper so
            // that `GROUP BY label` continues to resolve in ClickHouse.
            $selectAliasStr = null;
            if (preg_match('/\s+AS\s+(`[^`]*`|\'[^\']*\'|\w+)\s*$/i', $trimmedItem, $aliasMatch)) {
                $selectAliasStr = $aliasMatch[1]; // original text: label, `label`, or 'label'
            }
            $normalizedAlias = $selectAliasStr !== null
                ? self::normalizeIdent($selectAliasStr)
                : null;

            // Strip trailing alias to isolate the expression.
            $expr = preg_replace('/\s+AS\s+(`[^`]*`|\'[^\']*\'|\w+)\s*$/i', '', $trimmedItem);
            $expr = trim($expr);

            // Constants (bare integers, string literals, NULL, bind placeholders) are
            // always allowed in a grouped SELECT — and wrapping a bare integer would
            // create a spurious alias colliding with aggregates like COUNT(*) AS `1`.
            // Backtick-quoted `1` is a column reference and is NOT skipped here.
            if (
                ctype_digit($expr)
                || preg_match('/^\'[^\']*\'$/', $expr)
                || strcasecmp($expr, 'NULL') === 0
                || $expr === '?'
            ) {
                continue;
            }

            // Identifier shape: col, tbl.col, `tbl`.`col`
            $isIdentifier = (bool) preg_match('/^(`?\w+`?\.)?`?\w+`?$/', $expr);

            if (!$isIdentifier) {
                // Non-identifier expression (function call, CASE ... END, arithmetic):
                // aggregates (and expressions containing one, like ROUND(SUM(x), 2)) are
                // valid under GROUP BY as-is. A pure SCALAR expression — LOWER(HEX(col)),
                // CASE WHEN col = 1 THEN ... — is not: MySQL\'s relaxed mode picks an
                // arbitrary row value, which is exactly any(). Wrap it, unless it is
                // itself one of the grouping expressions or has no alias to keep its
                // result-column name stable.
                if (
                    $selectAliasStr === null
                    || self::containsAggregateFunction($expr)
                    || in_array(self::normalizeExpression($expr), $groupByNormalizedExpressions, true)
                    || in_array($normalizedAlias, $groupByNormalized, true)
                ) {
                    continue;
                }

                $toFix[] = [
                    'expr'           => $expr,
                    'fullItem'       => $trimmedItem,
                    'naturalName'    => self::normalizeIdent($selectAliasStr),
                    'aliasForWrapper' => $selectAliasStr,
                ];
                $groupByNormalized[] = $normalizedAlias;
                continue;
            }

            $normalized = self::normalizeIdent($expr);

            // If the SELECT alias itself is a GROUP BY key (MySQL GROUP BY alias_name),
            // this column is already accounted for — don't wrap it.
            if ($normalizedAlias !== null && in_array($normalizedAlias, $groupByNormalized, true)) {
                continue;
            }

            if (!in_array($normalized, $groupByNormalized, true)) {
                // Use the original SELECT alias as the any() wrapper alias so that
                // references like GROUP BY label remain valid after wrapping.
                // Fall back to the natural column name when there is no alias.
                $naturalName = $selectAliasStr !== null
                    ? self::normalizeIdent($selectAliasStr)
                    : self::extractNaturalName($expr);
                $aliasForWrapper = $selectAliasStr ?? ('`' . self::extractNaturalName($expr) . '`');

                $toFix[] = [
                    'expr'           => $expr,
                    'fullItem'       => $trimmedItem,
                    'naturalName'    => $naturalName,
                    'aliasForWrapper' => $aliasForWrapper,
                ];
                $groupByNormalized[] = $normalized;
                if ($normalizedAlias !== null) {
                    $groupByNormalized[] = $normalizedAlias;
                }
            }
        }

        if (empty($toFix)) {
            return $sql;
        }

        $newSelect = $selectClause;
        foreach ($toFix as $fix) {
            $anyExpr   = 'any(' . $fix['expr'] . ') AS ' . $fix['aliasForWrapper'];
            $newSelect = str_replace($fix['fullItem'], $anyExpr, $newSelect);
        }

        if ($newSelect === $selectClause) {
            return $sql;
        }

        // Reconstruct with a space before FROM.  trim() on $selectClause strips the
        // original whitespace/newline that separated the SELECT list from FROM, so we
        // must ensure at least one space is present to avoid "ecommerceTypeFROM" tokens.
        $sql = substr($sql, 0, $selPrefixLen) . $newSelect . ' ' . substr($sql, $topFromPos);

        // Also fix ORDER BY: in ClickHouse's strict GROUP BY mode every column reference
        // in ORDER BY must be either a GROUP BY key or inside an aggregate function.
        // For columns we just wrapped in any() in SELECT, replace the bare reference in
        // ORDER BY with the SELECT alias (natural name); ClickHouse resolves aliases in
        // ORDER BY to their definition in SELECT, satisfying the aggregate requirement.
        $orderByFixes = [];
        foreach ($toFix as $fix) {
            $orderByFixes[$fix['expr']] = $fix['naturalName'];
        }
        $sql = self::fixOrderByReferences($sql, $orderByFixes);

        return $sql;
    }

    /**
     * Wraps every `ROW_NUMBER() OVER (...)` expression in the SQL with `toInt64(...)`.
     *
     * ClickHouse's ROW_NUMBER() window function returns UInt64.  RankingQuery places the
     * result inside a multiIf() expression that also contains -1 as a NULL-row sentinel
     * (Int8).  ClickHouse requires all branches of multiIf() to share a common supertype,
     * but there is no signed integer type that can exactly represent all UInt64 values, so
     * it raises a "no supertype for types UInt64, Int8" error.
     *
     * Wrapping with toInt64() is safe because row-number values are always small (limited
     * by the number of rows per partition, typically < millions).
     */
    private static function wrapRowNumberWithToInt64(string $sql): string
    {
        $result = '';
        $len    = strlen($sql);
        $i      = 0;

        while ($i < $len) {
            // Quick first-char gate before the more expensive regex
            if (
                ($sql[$i] === 'R' || $sql[$i] === 'r')
                && preg_match('/^ROW_NUMBER\s*\(\s*\)\s+OVER\s*\(/i', substr($sql, $i), $m)
            ) {
                // $m[0] ends with '(' — find the matching closing paren
                $parenStart = $i + strlen($m[0]) - 1; // offset of the '('
                $depth      = 0;
                $parenEnd   = -1;

                for ($j = $parenStart; $j < $len; $j++) {
                    if ($sql[$j] === '(') {
                        $depth++;
                    } elseif ($sql[$j] === ')') {
                        $depth--;
                        if ($depth === 0) {
                            $parenEnd = $j;
                            break;
                        }
                    }
                }

                if ($parenEnd < 0) {
                    // Unmatched paren — copy the character verbatim and advance
                    $result .= $sql[$i];
                    $i++;
                    continue;
                }

                // Wrap the entire ROW_NUMBER() OVER (...) expression
                $fullExpr = substr($sql, $i, $parenEnd - $i + 1);
                $result  .= 'toInt64(' . $fullExpr . ')';
                $i        = $parenEnd + 1;
            } else {
                $result .= $sql[$i];
                $i++;
            }
        }

        return $result;
    }

    /**
     * Returns the byte offset of the top-level FROM keyword (at parenthesis depth 0)
     * in a SELECT statement.  Returns null if the SELECT prefix or FROM is not found.
     */
    private static function findTopLevelFromPos(string $sql): ?int
    {
        if (!preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $sql, $m)) {
            return null;
        }

        $pos   = strlen($m[0]);
        $len   = strlen($sql);
        $depth = 0;

        for ($i = $pos; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
            } elseif ($depth === 0 && ($ch === 'F' || $ch === 'f')) {
                if (preg_match('/^FROM\s/i', substr($sql, $i))) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * Extracts the top-level GROUP BY clause content using parenthesis-depth tracking.
     * "Top-level" means the GROUP BY is at depth 0 (not inside a subquery).
     * Returns the clause text (without the GROUP BY keywords), or null if not found.
     */
    private static function extractTopLevelGroupBy(string $sql): ?string
    {
        $len   = strlen($sql);
        $depth = 0;

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
            } elseif ($depth === 0 && ($ch === 'G' || $ch === 'g')) {
                if (preg_match('/^GROUP\s+BY\s+/i', substr($sql, $i), $gbm)) {
                    $contentStart = $i + strlen($gbm[0]);
                    $clause       = '';
                    $gbDepth      = 0;

                    for ($j = $contentStart; $j < $len; $j++) {
                        $c = $sql[$j];
                        if ($c === '(') {
                            $gbDepth++;
                            $clause .= $c;
                        } elseif ($c === ')') {
                            if ($gbDepth === 0) {
                                break; // closing paren of the enclosing expression
                            }
                            $gbDepth--;
                            $clause .= $c;
                        } elseif ($gbDepth === 0) {
                            if ($c === ';') {
                                break;
                            }
                            if (preg_match('/^(?:ORDER|HAVING|LIMIT|SETTINGS|FORMAT|UNION|EXCEPT|INTERSECT)\b/i', substr($sql, $j))) {
                                break;
                            }
                            $clause .= $c;
                        } else {
                            $clause .= $c;
                        }
                    }

                    $clause = trim($clause);
                    return $clause !== '' ? $clause : null;
                }
            }
        }

        return null;
    }

    /**
     * Replaces bare column references in the top-level ORDER BY clause with the
     * SELECT alias for each column that has been wrapped with any() in SELECT.
     *
     * ClickHouse requires every column reference in an ORDER BY that accompanies a
     * GROUP BY to be either a GROUP BY key or inside an aggregate function. When
     * fixGroupByOneLevel() wraps a column with any() in SELECT and assigns it an
     * alias, ORDER BY can use that alias — ClickHouse resolves it to any(col) at
     * planning time.
     *
     * @param array<string, string> $fixes  map of original_expr → select_alias
     */
    private static function fixOrderByReferences(string $sql, array $fixes): string
    {
        if (empty($fixes)) {
            return $sql;
        }

        // Find the start of the top-level ORDER BY clause (depth 0).
        $len          = strlen($sql);
        $depth        = 0;
        $orderByStart = null;

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
            } elseif ($depth === 0 && ($ch === 'O' || $ch === 'o')) {
                if (preg_match('/^ORDER\s+BY\s+/i', substr($sql, $i), $m)) {
                    $orderByStart = $i + strlen($m[0]);
                    break;
                }
            }
        }

        if ($orderByStart === null) {
            return $sql;
        }

        // Find the end of the ORDER BY clause.
        $orderByEnd = $len;
        $depth2     = 0;

        for ($i = $orderByStart; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === '(') {
                $depth2++;
            } elseif ($ch === ')') {
                if ($depth2 === 0) {
                    $orderByEnd = $i;
                    break;
                }
                $depth2--;
            } elseif ($depth2 === 0) {
                if ($ch === ';') {
                    $orderByEnd = $i;
                    break;
                }
                if (
                    preg_match(
                        '/^(?:LIMIT|HAVING|SETTINGS|FORMAT|UNION|EXCEPT|INTERSECT)\b/i',
                        substr($sql, $i)
                    )
                ) {
                    $orderByEnd = $i;
                    break;
                }
            }
        }

        $orderByClause = substr($sql, $orderByStart, $orderByEnd - $orderByStart);
        $newOrderBy    = $orderByClause;

        foreach ($fixes as $expr => $alias) {
            $escapedExpr = preg_quote($expr, '/');
            // Match whole-identifier occurrences only:
            //   lookbehind: not preceded by dot, word char, or backtick
            //   lookahead:  not followed by word char or backtick
            $newOrderBy = preg_replace(
                '/(?<![.\w`])' . $escapedExpr . '(?![\w`])/i',
                $alias,
                $newOrderBy
            ) ?? $newOrderBy;
        }

        if ($newOrderBy === $orderByClause) {
            return $sql;
        }

        return substr($sql, 0, $orderByStart) . $newOrderBy . substr($sql, $orderByEnd);
    }

    /**
     * Returns the "natural" SQL column name for an identifier expression:
     * strips the table qualifier and backtick quoting.
     *
     * Examples:
     *   log_action.name  →  name
     *   `url_prefix`     →  url_prefix
     *   `1`              →  1
     */
    private static function extractNaturalName(string $expr): string
    {
        $parts = explode('.', $expr);
        return str_replace('`', '', trim((string) end($parts)));
    }

    /**
     * Splits a comma-separated SQL expression list into individual items,
     * respecting nested parentheses so that function-argument commas are ignored.
     *
     * @return string[]
     */
    private static function splitByComma(string $clause): array
    {
        $items   = [];
        $depth   = 0;
        $current = '';

        for ($i = 0, $len = strlen($clause); $i < $len; $i++) {
            $ch = $clause[$i];
            if ($ch === '(') {
                $depth++;
                $current .= $ch;
            } elseif ($ch === ')') {
                $depth--;
                $current .= $ch;
            } elseif ($ch === ',' && $depth === 0) {
                $items[]  = $current;
                $current  = '';
            } else {
                $current .= $ch;
            }
        }
        if ($current !== '') {
            $items[] = $current;
        }
        return $items;
    }

    /**
     * Strips backtick quoting and lowercases an identifier for comparison.
     */
    private static function normalizeIdent(string $ident): string
    {
        return strtolower(str_replace('`', '', trim($ident)));
    }

    /**
     * Normalizes a full SQL expression for textual comparison: lowercased, backticks
     * stripped, whitespace collapsed.
     */
    private static function normalizeExpression(string $expr): string
    {
        return strtolower(trim((string) preg_replace('/\s+/', ' ', str_replace('`', '', $expr))));
    }

    /**
     * Returns true when the expression contains an aggregate (or window) function call,
     * meaning it is already valid in a SELECT under GROUP BY.
     */
    /**
     * Never join `log_action` whole.
     *
     * ClickHouse builds a join hash table from the RIGHT side, so `... JOIN log_action ON
     * <expr> = log_action.idaction` materialises the entire action dictionary regardless of how
     * selective the left side is. On the POC corpus that is 242 million rows and it does not
     * merely run slowly - it dies in FillingRightJoinSide with MEMORY_LIMIT_EXCEEDED. Measured
     * three separate times, in three separate code paths: Transitions, the Visits Log action
     * enrichment (Actions\VisitorDetails::queryActionsForVisits) and day archiving
     * (Actions\RecordBuilders\ActionReports::archiveDayQueryProcess).
     *
     * Restricting the right side to the ids the query can actually reach turns the join into a
     * primary-key lookup, because `idaction` IS log_action's sorting key:
     *
     *     JOIN log_action AS a ON t.idaction_url = a.idaction
     *  -> JOIN (SELECT * FROM log_action
     *            WHERE idaction IN (SELECT t.idaction_url FROM <driving table> WHERE <bounds>))
     *          AS a ON t.idaction_url = a.idaction
     *
     * This lives in the translator rather than at each call site because the property is a
     * property of ClickHouse, not of any one report: LogAggregator::queryActionsByDimension()
     * was fixed locally first, and two further paths turned out to build their joins elsewhere.
     *
     * **It must run AFTER positional binds become named ones.** The restriction repeats the
     * driving query's WHERE, and repeating `?` would desynchronise the bind list; repeating
     * `:chBind007` is free because a named parameter can appear any number of times.
     *
     * Correctness rests on the restriction being a SUPERSET of the ids the join needs. Only
     * top-level AND conjuncts that reference the driving table alone are carried over - dropping
     * a conjunct from an AND-chain widens the set, which is safe, while a conjunct mentioning a
     * joined alias could not be resolved inside the subquery. Anything that cannot be read with
     * confidence is left alone: a missed rewrite costs speed, a wrong one costs rows.
     */
    public static function restrictLogActionJoins(string $sql): string
    {
        if (stripos($sql, 'log_action') === false) {
            return $sql;
        }

        $sql = self::mapFromBlocks($sql, static function (string $inner): string {
            return self::restrictLogActionJoins($inner);
        });

        return self::restrictLogActionJoinsInScope($sql);
    }

    private static function restrictLogActionJoinsInScope(string $sql): string
    {
        $fromPos = self::findTopLevelFromPos($sql);
        if (null === $fromPos) {
            return $sql;
        }

        $clauses = self::topLevelClauses($sql, $fromPos);
        if (empty($clauses)) {
            return $sql;
        }

        $fromKeywordLen = strlen('FROM');
        $drivingEnd = $clauses[0]['pos'];
        $driving = trim(substr($sql, $fromPos + $fromKeywordLen, $drivingEnd - $fromPos - $fromKeywordLen));

        // A driving side that is itself a subquery or a comma-separated list is not something
        // this pass can reason about.
        if (
            $driving === ''
            || strpos($driving, '(') !== false
            || strpos($driving, ',') !== false
            || !preg_match('~^(`?[\w]+`?)(?:\s+(?:AS\s+)?(`?[\w]+`?))?$~i', $driving, $dm)
        ) {
            return $sql;
        }

        $drivingTable = $dm[1];
        $drivingAlias = self::normalizeIdent($dm[2] ?? $dm[1]);

        $restriction = self::drivingRestriction($sql, $clauses, $driving, $drivingAlias);

        // Rewrite from the back so earlier offsets stay valid.
        for ($i = count($clauses) - 1; $i >= 0; $i--) {
            $clause = $clauses[$i];
            if ($clause['kw'] !== 'JOIN') {
                continue;
            }

            $end = $clauses[$i + 1]['pos'] ?? strlen($sql);
            $text = substr($sql, $clause['pos'], $end - $clause['pos']);

            $rewritten = self::restrictOneLogActionJoin($text, $drivingAlias, $restriction);
            if (null === $rewritten) {
                continue;
            }

            $sql = substr($sql, 0, $clause['pos']) . $rewritten . substr($sql, $end);
        }

        return $sql;
    }

    /**
     * `FROM <driving> WHERE <conjuncts that reference only the driving table>`, or the bare
     * driving table when nothing can be carried over safely.
     *
     * @param array<int, array{kw: string, pos: int, len: int}> $clauses
     */
    private static function drivingRestriction(string $sql, array $clauses, string $driving, string $drivingAlias): string
    {
        $from = 'FROM ' . $driving;

        foreach ($clauses as $index => $clause) {
            if ($clause['kw'] !== 'WHERE') {
                continue;
            }

            $end = $clauses[$index + 1]['pos'] ?? strlen($sql);
            $start = $clause['pos'] + $clause['len'];
            $where = trim(substr($sql, $start, $end - $start));

            $kept = self::keepConjunctsReferencingOnly($where, $drivingAlias);
            if ('' !== $kept) {
                $from .= ' WHERE ' . $kept;
            }

            break;
        }

        return $from;
    }

    /**
     * @return string|null the rewritten JOIN clause, or null to leave it alone
     */
    private static function restrictOneLogActionJoin(string $joinText, string $drivingAlias, string $restriction): ?string
    {
        // A join whose right side is already a subquery has been restricted by its caller
        // (LogAggregator::getActionRestrictionSubQuery does this for the dimension queries),
        // and `(` never matches the table pattern below, so the two cannot both apply.
        // The surrounding whitespace is captured, not trimmed. This clause is spliced back
        // between two offsets in the original statement, so eating the whitespace that
        // separated it from the next one welds them together: `... = log_action.idactionLEFT
        // JOIN ...`, which fails as a syntax error a long way from its cause.
        $pattern = '~^(\s*(?:LEFT|RIGHT|INNER|FULL|CROSS|OUTER|STRAIGHT_JOIN|\s)*JOIN\s+)'
            . '(`?[\w]*log_action`?)(\s+(?:AS\s+)?(`?[\w]+`?))?(\s+ON\s+)(.*?)(\s*)$~is';

        if (!preg_match($pattern, $joinText, $m)) {
            return null;
        }

        $alias = self::normalizeIdent($m[4] !== '' ? $m[4] : $m[2]);
        $condition = trim($m[6]);
        $trailing = $m[7];

        // Only the plain `a.b = c.d` shape. Matomo also emits joins whose ON is an IF()
        // expression; those come from queryActionsByDimension(), which restricts them itself.
        if (!preg_match('~^(`?[\w]+`?\.`?[\w]+`?)\s*=\s*(`?[\w]+`?\.`?[\w]+`?)$~i', $condition, $cm)) {
            return null;
        }

        $left = self::normalizeIdent($cm[1]);
        $right = self::normalizeIdent($cm[2]);

        if (self::qualifier($left) === $alias) {
            $joinKey = $left;
            $expression = $cm[2];
        } elseif (self::qualifier($right) === $alias) {
            $joinKey = $right;
            $expression = $cm[1];
        } else {
            return null;
        }

        if (substr($joinKey, -strlen('.idaction')) !== '.idaction') {
            return null;
        }

        // The expression has to be resolvable inside the restriction subquery, whose only table
        // is the driving one. A join keyed off another joined table cannot be restricted here.
        if (self::qualifier(self::normalizeIdent($expression)) !== $drivingAlias) {
            return null;
        }

        $subQuery = '(SELECT * FROM ' . $m[2] . ' WHERE idaction IN (SELECT ' . $expression . ' ' . $restriction . '))';

        return $m[1] . $subQuery . ($m[3] !== '' ? $m[3] : ' AS ' . $alias) . $m[5] . $m[6] . $trailing;
    }

    private static function qualifier(string $identifier): string
    {
        $dot = strpos($identifier, '.');

        return $dot === false ? '' : substr($identifier, 0, $dot);
    }

    /**
     * Keeps only the top-level AND conjuncts that reference $alias and nothing else.
     *
     * This is the "superset rule" both restriction sites depend on. A restriction subquery has
     * exactly one table in scope, so a conjunct naming any other alias cannot be resolved there
     * - and dropping a conjunct from an AND-chain only ever WIDENS the id set, which is safe,
     * because the restriction has to be a superset of what the join needs, not an exact match.
     *
     * @return string the surviving conjuncts joined by AND, or '' when none survive
     */
    public static function keepConjunctsReferencingOnly(string $where, string $alias): string
    {
        $kept = [];
        foreach (self::splitTopLevelAnd($where) as $conjunct) {
            if (self::referencesOnly($conjunct, $alias)) {
                $kept[] = $conjunct;
            }
        }

        return implode(' AND ', $kept);
    }

    /**
     * Whether every table-qualified reference in $expression is $alias, and there is at least
     * one. An unqualified column is treated as unusable rather than assumed to belong to the
     * driving table: guessing wrong produces an unresolved identifier inside the subquery.
     */
    private static function referencesOnly(string $expression, string $alias): bool
    {
        if (!preg_match_all('~`?([A-Za-z_][\w]*)`?\s*\.~', $expression, $matches)) {
            return false;
        }

        foreach ($matches[1] as $qualifier) {
            if (strtolower($qualifier) !== strtolower($alias)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Splits on AND at parenthesis depth 0, outside string literals.
     *
     * @return string[]
     */
    private static function splitTopLevelAnd(string $clause): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $len = strlen($clause);

        for ($i = 0; $i < $len; $i++) {
            $ch = $clause[$i];

            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $end = self::skipQuoted($clause, $i);
                $current .= substr($clause, $i, $end - $i);
                $i = $end - 1;
                continue;
            }

            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
            } elseif ($depth === 0 && ($ch === 'A' || $ch === 'a') && preg_match('~^AND\s~i', substr($clause, $i, 4))) {
                $parts[] = trim($current);
                $current = '';
                $i += 2;
                continue;
            }

            $current .= $ch;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));
    }

    /**
     * Positions of the JOIN and clause keywords at parenthesis depth 0, from $startPos onwards.
     *
     * @return array<int, array{kw: string, pos: int, len: int}>
     */
    private static function topLevelClauses(string $sql, int $startPos): array
    {
        $keywords = [
            'JOIN' => '~^(?:(?:LEFT|RIGHT|INNER|FULL|CROSS)\s+)?(?:OUTER\s+)?JOIN\s~i',
            'WHERE' => '~^WHERE\s~i',
            'GROUP' => '~^GROUP\s+BY\s~i',
            'ORDER' => '~^ORDER\s+BY\s~i',
            'HAVING' => '~^HAVING\s~i',
            'LIMIT' => '~^LIMIT\s~i',
            'UNION' => '~^UNION\s~i',
        ];

        $found = [];
        $depth = 0;
        $len = strlen($sql);

        for ($i = $startPos; $i < $len; $i++) {
            $ch = $sql[$i];

            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $i = self::skipQuoted($sql, $i) - 1;
                continue;
            }

            if ($ch === '(') {
                $depth++;
                continue;
            }

            if ($ch === ')') {
                $depth--;
                if ($depth < 0) {
                    break;
                }
                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            $rest = substr($sql, $i);
            foreach ($keywords as $name => $pattern) {
                if (!preg_match($pattern, $rest, $km)) {
                    continue;
                }
                $found[] = ['kw' => $name, 'pos' => $i, 'len' => strlen(rtrim($km[0]))];
                $i += strlen(rtrim($km[0])) - 1;
                break;
            }
        }

        return $found;
    }

    /**
     * @return int the offset just past the closing quote
     */
    private static function skipQuoted(string $sql, int $start): int
    {
        $quote = $sql[$start];
        $len = strlen($sql);

        for ($i = $start + 1; $i < $len; $i++) {
            if ($sql[$i] === '\\') {
                $i++;
                continue;
            }
            if ($sql[$i] === $quote) {
                return $i + 1;
            }
        }

        return $len;
    }

    private static function containsAggregateFunction(string $expr): bool
    {
        if (preg_match('/\bOVER\s*\(/i', $expr)) {
            return true;
        }

        return (bool) preg_match(
            // uniq\w* rather than uniq|uniqExact: ClickHouse's -If combinator makes
            // uniqExactIf() (which rewriteCountDistinctOnNullableColumns() emits) just as
            // much an aggregate, and missing it gets the call wrapped in any() and rejected
            // with ILLEGAL_AGGREGATION.
            '/\b(?:count|sum|min|max|avg|group_concat|groupArray|groupUniqArray|countDistinct|uniq\w*|'
            . 'any|anyLast|stddev(?:_pop|_samp)?|var(?:_pop|_samp)?|bit_and|bit_or|bit_xor|'
            . 'sumIf|countIf|minIf|maxIf|avgIf|argMin|argMax|quantile\w*|median\w*|topK\w*)\s*\(/i',
            $expr
        );
    }
}
