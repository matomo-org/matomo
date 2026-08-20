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
        $sql = self::rewriteLikeOnNonStringColumns($sql);
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
     * MySQL allows LIKE on numeric columns by casting (the GDPR data subject search
     * does idvisit LIKE '10%'); ClickHouse requires a String argument. toString() is
     * the identity for String columns and MySQL's cast semantics for everything else.
     */
    private static function rewriteLikeOnNonStringColumns(string $sql): string
    {
        return preg_replace(
            '~((?:`?\w+`?\.)?`?\w+`?)(\s+(?:NOT\s+)?LIKE\s)~i',
            'toString($1)$2',
            $sql
        ) ?? $sql;
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
    private static function containsAggregateFunction(string $expr): bool
    {
        if (preg_match('/\bOVER\s*\(/i', $expr)) {
            return true;
        }

        return (bool) preg_match(
            '/\b(?:count|sum|min|max|avg|group_concat|groupArray|groupUniqArray|countDistinct|uniq|uniqExact|'
            . 'any|anyLast|stddev(?:_pop|_samp)?|var(?:_pop|_samp)?|bit_and|bit_or|bit_xor|'
            . 'sumIf|countIf|minIf|maxIf|avgIf|argMin|argMax|quantile\w*|median\w*|topK\w*)\s*\(/i',
            $expr
        );
    }
}
