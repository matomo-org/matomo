<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * Splits the comma separated lists of a SQL statement (select, group by, order by lists) and reads
 * the result column names of select items. Not a SQL parser: it only understands parentheses,
 * quotes and comments, which is what the query builders that use it emit.
 *
 * A quoted string or name ends at the next quote of the same kind that isn't doubled; a backslash
 * doesn't escape it, as in standard SQL and in the `ESCAPE '\'` clause. Matomo passes values as
 * bindings, so its statements don't contain MySQL backslash escapes; callers that must not
 * misread one check for backslashes first. `--` starts a comment, as in SQLite.
 *
 * @internal
 */
final class SqlListParser
{
    /**
     * Splits a comma separated SQL list, ignoring commas inside parentheses, quotes and comments.
     *
     * @return string[]
     */
    public static function splitList(string $list): array
    {
        $items = [];
        $start = 0;

        foreach (self::scan($list) as [$i, $depth]) {
            if ($depth === 0 && $list[$i] === ',') {
                $items[] = trim(substr($list, $start, $i - $start));
                $start = $i + 1;
            }
        }

        $last = trim(substr($list, $start));
        if ($last !== '') {
            $items[] = $last;
        }

        return $items;
    }

    /**
     * Returns the select list of a `SELECT ... FROM` statement, without leading comments or hints,
     * or null if the statement doesn't start that way.
     */
    public static function getSelectList(string $query): ?string
    {
        if (!preg_match('/^\s*SELECT\s+(?:\/\*.*?\*\/\s*)*/is', $query, $matches)) {
            return null;
        }

        $start = strlen($matches[0]);
        foreach (self::scan($query) as [$i, $depth]) {
            if ($depth === 0 && $i > $start && self::isKeywordAt($query, $i, 'FROM')) {
                return trim(substr($query, $start, $i - $start));
            }
        }

        return null;
    }

    /**
     * Returns the ORDER BY list of a statement's outermost query, without a following LIMIT, or
     * null if it has none. Sorts inside parentheses (subqueries, window clauses) are ignored.
     */
    public static function getOrderBy(string $query): ?string
    {
        $orderBy = null;
        $end = null;

        foreach (self::scan($query) as [$i, $depth]) {
            if ($depth !== 0) {
                continue;
            }
            if (self::isKeywordAt($query, $i, 'ORDER') && preg_match('/\GORDER\s+BY\s+/i', $query, $matches, 0, $i)) {
                $orderBy = $i + strlen($matches[0]);
                $end = null;
            } elseif ($orderBy !== null && $end === null && self::isKeywordAt($query, $i, 'LIMIT')) {
                $end = $i;
            }
        }

        if ($orderBy === null) {
            return null;
        }

        $list = trim(substr($query, $orderBy, $end === null ? null : $end - $orderBy), " \t\n\r;");

        return $list === '' ? null : $list;
    }

    /**
     * Counts the positional placeholders (`?`) outside quotes and comments.
     */
    public static function countPlaceholders(string $sql): int
    {
        $count = 0;
        foreach (self::scan($sql) as [$i]) {
            if ($sql[$i] === '?') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Returns the expression of a select item and the name of its result column, or null as name
     * for an expression without alias. `t.col` and `col` are named `col`.
     *
     * @return array{0: string, 1: string|null}
     */
    public static function splitAlias(string $selectItem): array
    {
        if (preg_match('/^(.*\S)\s+AS\s+`?(\w+)`?$/is', $selectItem, $matches)) {
            return [$matches[1], $matches[2]];
        }

        return [$selectItem, self::getColumnName($selectItem)];
    }

    /**
     * Returns the column name of a plain, optionally qualified and quoted column reference, or null.
     */
    public static function getColumnName(string $expression): ?string
    {
        // quoted names may start with a digit, as Matomo's metric ids do
        if (preg_match('/^(?:(?:`\w+`|[a-zA-Z_]\w*)\.)?(?:`(\w+)`|([a-zA-Z_]\w*))$/', trim($expression), $matches)) {
            return $matches[1] !== '' ? $matches[1] : $matches[2];
        }

        return null;
    }

    /**
     * Normalizes an expression for comparisons: no comments, and outside string literals no
     * whitespace and lower case. Backticks are dropped, except around names starting with a digit,
     * which would otherwise read as numbers.
     */
    public static function normalize(string $sql): string
    {
        $normalized = '';
        $inName = false;
        $keepBackticks = false;

        foreach (self::scan($sql, true) as [$i, , $inLiteral]) {
            $char = $sql[$i];
            if ($inLiteral) {
                $normalized .= $char;
            } elseif ($char === '`') {
                if (!$inName) {
                    $keepBackticks = $i + 1 < strlen($sql) && ctype_digit($sql[$i + 1]);
                }
                $inName = !$inName;
                $normalized .= $keepBackticks ? '`' : '';
            } elseif (!ctype_space($char)) {
                $normalized .= strtolower($char);
            }
        }

        return $normalized;
    }

    /**
     * Returns true if the expression refers to no column: only literals, numbers and operators.
     */
    public static function isConstant(string $expression): bool
    {
        foreach (self::scan($expression) as [$i]) {
            $char = $expression[$i];
            if ($char === '`' || $char === '_' || ctype_alpha($char)) {
                return false;
            }
        }

        return true;
    }

    private static function isKeywordAt(string $sql, int $i, string $keyword): bool
    {
        return ($i === 0 || !preg_match('/\w/', $sql[$i - 1]))
            && preg_match('/\G' . $keyword . '\b/i', $sql, $unused, 0, $i) === 1;
    }

    /**
     * Yields [offset, parenthesis depth, inside a string literal] for the characters outside
     * comments. The contents of quoted strings and names are skipped unless $withContents is true,
     * in which case those of names are yielded as code, so they normalize like unquoted names.
     *
     * @return iterable<array{0: int, 1: int, 2: bool}>
     */
    private static function scan(string $sql, bool $withContents = false): iterable
    {
        $depth = 0;
        $quote = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($quote !== null) {
                $isLiteral = $quote !== '`';
                if ($char === $quote && $i + 1 < $length && $sql[$i + 1] === $quote) {
                    // a doubled quote stays inside
                    if ($withContents) {
                        yield [$i, $depth, $isLiteral];
                        yield [$i + 1, $depth, $isLiteral];
                    }
                    $i++;
                } elseif ($char === $quote) {
                    $quote = null;
                    yield [$i, $depth, false];
                } elseif ($withContents) {
                    yield [$i, $depth, $isLiteral];
                }
                continue;
            }

            if ($char === '/' && $i + 1 < $length && $sql[$i + 1] === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i = $end === false ? $length : $end + 1;
                continue;
            }
            if ($char === '-' && $i + 1 < $length && $sql[$i + 1] === '-') {
                $end = strpos($sql, "\n", $i);
                $i = $end === false ? $length : $end;
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }

            // a parenthesis counts at the depth outside it
            yield [$i, $char === '(' ? $depth - 1 : $depth, false];
        }
    }
}
