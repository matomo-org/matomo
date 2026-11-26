<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

use Exception;

/**
 * Builds the rows of `GROUP BY ... WITH ROLLUP` for engines that don't support it.
 *
 * Every level of the rollup is its own grouping query over the same rows, combined with
 * `UNION ALL`. Grouping uses the original expressions, so their collation applies, and aggregates
 * such as `COUNT(DISTINCT ...)` keep their meaning in the subtotals. In a subtotal row, the selected
 * grouping columns that were rolled up are `NULL`, as with `WITH ROLLUP`.
 *
 * The source tables are referenced once per level, which MySQL doesn't allow for temporary tables.
 * Use {@link build()} only when {@link Schema::supportsGroupByWithRollup()} is false.
 */
final class RollupQuery
{
    /**
     * Returns the SQL and bindings that emulate
     * `SELECT $select FROM $from WHERE $where GROUP BY $groupBy WITH ROLLUP`.
     *
     * The rows come in no particular order. `order` is an `ORDER BY` list that puts each subtotal
     * before its detail rows; append it to the sort of the result to break ties that way.
     *
     * @param string $select  Select list. A selected grouping column must be selected either as the
     *                        same expression as in `$groupBy` or under the alias used in `$groupBy`.
     * @param string $from    Everything between `FROM` and `WHERE`, e.g. the joins.
     * @param string $where   Condition, or an empty string.
     * @param string $groupBy Grouping columns, outermost first.
     * @param array  $bind    Positional bindings: those of `$select`, `$from` and `$where`, then any of
     *                        clauses appended after the rollup (returned once, after the levels').
     *                        `$groupBy` can't have bindings.
     *
     * The select list needs an aggregate, as the grand total level is filtered with `HAVING`.
     * @return array{sql: string, bind: array, order: string}
     * @throws Exception If a rolled up column can't be replaced by NULL in the select list.
     */
    public static function build(string $select, string $from, string $where, string $groupBy, array $bind): array
    {
        $groupItems = SqlListParser::splitList($groupBy);
        $selectItems = SqlListParser::splitList($select);

        if ($groupItems === [] || $selectItems === []) {
            throw new Exception('A rollup query needs a select list and grouping columns.');
        }

        if (SqlListParser::countPlaceholders($groupBy) > 0) {
            throw new Exception('Cannot roll up grouping expressions with bindings.');
        }

        // the bindings of the select list, FROM and WHERE are repeated in each level; any others
        // belong to clauses the caller appends after the rollup, such as ORDER BY
        $levelBindCount = SqlListParser::countPlaceholders($select) + SqlListParser::countPlaceholders($from)
            + SqlListParser::countPlaceholders($where);
        $bind = array_values($bind);
        if (count($bind) < $levelBindCount) {
            throw new Exception('The rollup query has more placeholders than bindings.');
        }

        // the select items that output each grouping column
        $selectIndexes = [];
        foreach ($groupItems as $groupItem) {
            $selectIndexes[] = self::findSelectItems($selectItems, $groupItem);
        }

        $whereSql = trim($where) === '' ? '' : " WHERE $where";
        $levels = [];

        for ($kept = count($groupItems); $kept >= 0; $kept--) {
            $levelSelect = $selectItems;

            for ($i = $kept; $i < count($groupItems); $i++) {
                foreach ($selectIndexes[$i] as $index) {
                    [$expression, $name] = SqlListParser::splitAlias($selectItems[$index]);
                    if ($name === null || str_contains($expression, '?')) {
                        throw new Exception('Cannot roll up the select item "' . $selectItems[$index] . '": give it an alias and no bindings.');
                    }

                    $levelSelect[$index] = 'NULL AS `' . $name . '`';
                }
            }

            $sql = 'SELECT ' . implode(', ', $levelSelect) . " FROM $from" . $whereSql;

            if ($kept > 0) {
                $sql .= ' GROUP BY ' . implode(', ', array_slice($groupItems, 0, $kept));
            } else {
                // the grand total, which WITH ROLLUP only returns when there are rows
                $sql .= ' HAVING COUNT(*) > 0';
            }

            $levels[] = $sql;
        }

        return [
            'sql'   => implode(' UNION ALL ', $levels),
            'bind'  => array_merge(
                ...array_fill(0, count($levels), array_slice($bind, 0, $levelBindCount)),
                ...[array_slice($bind, $levelBindCount)]
            ),
            'order' => self::getOrder($selectItems, $selectIndexes),
        ];
    }

    /**
     * Returns an `ORDER BY` list that puts each subtotal of
     * `SELECT $select ... GROUP BY $groupBy WITH ROLLUP` before its detail rows, for use on the
     * columns of the result. Rows with equal sort values otherwise come in no particular order, and
     * a consumer that looks up the subtotal of a detail row would miss it. Returns an empty string
     * when it can't tell, e.g. for select lists with backslashes.
     *
     * @throws Exception If select items with different expressions match a grouping column.
     */
    public static function getParentFirstOrder(string $select, string $groupBy): string
    {
        if (str_contains($select . $groupBy, '\\')) {
            // possibly a MySQL backslash escape, which the list parser doesn't read
            return '';
        }

        $selectItems = SqlListParser::splitList($select);
        $selectIndexes = [];
        foreach (SqlListParser::splitList($groupBy) as $groupItem) {
            $selectIndexes[] = self::findSelectItems($selectItems, $groupItem);
        }

        return self::getOrder($selectItems, $selectIndexes);
    }

    /**
     * The sort term of each level is on a result column of the grouping value, which is NULL
     * exactly in that level's subtotals: not a constant, and under a name no other column has. A
     * level without one gets no term, which leaves its ties in no particular order.
     *
     * @param string[] $selectItems
     * @param int[][]  $selectIndexes The select items of each grouping column, outermost first.
     */
    private static function getOrder(array $selectItems, array $selectIndexes): string
    {
        $nameCounts = [];
        foreach ($selectItems as $selectItem) {
            $name = SqlListParser::splitAlias($selectItem)[1];
            if ($name !== null) {
                $nameCounts[strtolower($name)] = ($nameCounts[strtolower($name)] ?? 0) + 1;
            }
        }

        $order = [];
        foreach (array_reverse($selectIndexes) as $indexes) {
            foreach ($indexes as $index) {
                [$expression, $name] = SqlListParser::splitAlias($selectItems[$index]);
                if ($name !== null && $nameCounts[strtolower($name)] === 1 && !SqlListParser::isConstant($expression)) {
                    $order[] = '`' . $name . '` IS NULL DESC';
                    break;
                }
            }
        }

        return implode(', ', $order);
    }

    /**
     * @return int[]
     * @throws Exception If select items with different expressions match the grouping column.
     */
    private static function findSelectItems(array $selectItems, string $groupItem): array
    {
        $wanted = SqlListParser::normalize($groupItem);

        // the expression a grouping alias stands for, so its other projections are found too, unless
        // it's a constant: another projection of it isn't the grouping value
        $wantedExpressions = [$wanted => true];
        foreach ($selectItems as $selectItem) {
            [$expression, $name] = SqlListParser::splitAlias($selectItem);
            if (
                $expression !== $selectItem
                && strtolower($name) === $wanted
                && !SqlListParser::isConstant($expression)
            ) {
                $wantedExpressions[SqlListParser::normalize($expression)] = true;
            }
        }

        $indexes = [];
        $expressions = [];

        foreach ($selectItems as $index => $selectItem) {
            [$expression, $name] = SqlListParser::splitAlias($selectItem);
            $isAliased = $expression !== $selectItem;
            $normalized = SqlListParser::normalize($expression);

            if (isset($wantedExpressions[$normalized]) || ($isAliased && strtolower($name) === $wanted)) {
                $indexes[] = $index;
                $expressions[$normalized] = true;
            }
        }

        if (count($expressions) > 1) {
            throw new Exception('The grouping column "' . $groupItem . '" matches different select items.');
        }

        return $indexes;
    }
}
