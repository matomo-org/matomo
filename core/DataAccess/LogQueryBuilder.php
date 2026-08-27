<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\LogQueryBuilder\JoinGenerator;
use Piwik\DataAccess\LogQueryBuilder\JoinTables;
use Piwik\Plugin\LogTablesProvider;
use Piwik\Segment\SegmentExpression;

class LogQueryBuilder
{
    public const FORCE_INNER_GROUP_BY_NO_SUBSELECT = '__##nosubquery##__';

    /**
     * Column the visits log groups by to return one row per visit.
     */
    private const LOG_VISIT_ID_COLUMN = 'log_visit.idvisit';

    /**
     * Alias the outer log_visit gets when the joined tables move into a subquery. It is the outer
     * table that is renamed and not the one in the subquery, because the segment conditions can
     * contain subqueries of their own that select from log_visit again, eg. `actionUrl!@x`. Leaving
     * the generated join and the segment conditions untouched keeps those working.
     */
    private const LOG_VISIT_OUTER_ALIAS = 'log_visit_outer';

    /**
     * @var LogTablesProvider
     */
    private $logTableProvider;

    /**
     * Forces to use a subselect when generating the query. Set value to the FORCE_INNER_GROUP_BY_NO_SUBSELECT constant to force not using a subselect.
     * @var string
     */
    private $forcedInnerGroupBy = '';

    public function __construct(LogTablesProvider $logTablesProvider)
    {
        $this->logTableProvider = $logTablesProvider;
    }

    /**
     * Forces to use a subselect when generating the query.
     *
     * @param string $innerGroupBy
     */
    public function forceInnerGroupBySubselect($innerGroupBy)
    {
        $this->forcedInnerGroupBy = $innerGroupBy;
    }

    public function getForcedInnerGroupBySubselect()
    {
        return $this->forcedInnerGroupBy;
    }

    public function getSelectQueryString(
        SegmentExpression $segmentExpression,
        $select,
        $from,
        $where,
        $bind,
        $groupBy,
        $orderBy,
        $limitAndOffset,
        bool $withRollup = false
    ) {
        if (!is_array($from)) {
            $from = array($from);
        }

        $fromInitially = $from;
        $whereWithoutSegment = $where;
        $segmentWhere = null;

        if (!$segmentExpression->isEmpty()) {
            $segmentExpression->parseSubExpressionsIntoSqlExpressions($from);
            $segmentSql = $segmentExpression->getSql();
            $segmentWhere = $segmentSql['where'];
            $where = $this->getWhereMatchBoth($where, $segmentWhere);
            $bind = array_merge($bind, $segmentSql['bind']);
        }

        // hack to allow db planner and db query optimiser to use an anti-join which results in a lower cost query
        // and filtering on the log_visit table first when it doesn't need to consider null-extended rows
        if ($from === ['log_link_visit_action', 'log_visit']) {
            $from[1] = ['table' => 'log_visit', 'join' => 'INNER JOIN'];
        }

        $tables = new JoinTables($this->logTableProvider, $from);
        $join = new JoinGenerator($tables);
        $join->generate();
        $from = $join->getJoinString();
        $joinWithSubSelect = $join->shouldJoinWithSelect();

        // hack for https://github.com/piwik/piwik/issues/9194#issuecomment-164321612
        $useSpecialConversionGroupBy = (!empty($segmentSql)
            && strpos($groupBy, 'log_conversion.idgoal') !== false
            && $fromInitially == array('log_conversion')
            && strpos($from, 'log_link_visit_action') !== false);

        if (!empty($this->forcedInnerGroupBy)) {
            if ($this->forcedInnerGroupBy === self::FORCE_INNER_GROUP_BY_NO_SUBSELECT) {
                $sql = null;

                if ($this->isVisitSemiJoinEnabled()) {
                    $sql = $this->buildVisitQueryWithoutGroupBy($select, $from, $whereWithoutSegment, $segmentWhere, $groupBy, $orderBy, $limitAndOffset, $tables);
                }

                if ($sql === null) {
                    $sql = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset);
                }
            } else {
                $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $tables, $this->forcedInnerGroupBy);
            }
        } elseif ($useSpecialConversionGroupBy) {
            $innerGroupBy = "CONCAT(log_conversion.idvisit, '_' , log_conversion.idgoal, '_', log_conversion.buster)";
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $tables, $innerGroupBy);
        } elseif ($joinWithSubSelect) {
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $tables);
        } else {
            $sql = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $withRollup);
        }
        return array(
            'sql' => $sql,
            'bind' => $bind,
        );
    }

    private function getKnownTables()
    {
        $names = array();
        foreach ($this->logTableProvider->getAllLogTablesWithTemporary() as $logTable) {
            $names[] = $logTable->getName();
        }
        return $names;
    }

    /**
     * Build a query that returns one row per visit by construction, so that `GROUP BY
     * log_visit.idvisit` is not needed to remove the duplicate visits the joined log tables create.
     *
     * Grouping by `log_visit.idvisit` while ordering by another log_visit column makes MySQL buffer
     * and sort every matching visit before it can apply the LIMIT, so the query costs as much as the
     * whole date range even though only one page of visits is returned.
     *
     * Two shapes are built:
     *
     * - when log_visit is the only table of the query, the GROUP BY is dropped. log_visit.idvisit is
     *   the primary key of that table, so grouping by it cannot merge any rows.
     * - when other log tables are joined, they move into a correlated EXISTS subquery. The subquery
     *   matches a visit exactly when at least one of its joined rows matches the segment, which is
     *   the set of visits the GROUP BY produced, so a visit matching several actions still counts
     *   once against the LIMIT (see https://github.com/matomo-org/matomo/issues/13861). MySQL can
     *   then stop reading log_visit as soon as it has enough visits.
     *
     * @param string          $select         Select clause, has to select from log_visit only.
     * @param string          $from           Generated join string.
     * @param string|false    $where          Where clause of the caller, without the segment conditions.
     * @param string|null     $segmentWhere   Where clause generated for the segment.
     * @param string|false    $groupBy
     * @param string|false    $orderBy
     * @param string|int|null $limitAndOffset
     * @param JoinTables      $tables         Tables of the generated join, in the order they are joined.
     * @return string|null The query, or null when it does not have the shape this rewrite is
     *                     equivalent for and the caller has to build the grouped query instead.
     */
    private function buildVisitQueryWithoutGroupBy($select, $from, $where, $segmentWhere, $groupBy, $orderBy, $limitAndOffset, JoinTables $tables)
    {
        if (trim((string) $groupBy) !== self::LOG_VISIT_ID_COLUMN) {
            return null;
        }

        // the rewrite reasons about visits, so the query may only select and sort log_visit rows
        if (trim((string) $select) !== 'log_visit.*') {
            return null;
        }

        if (!$this->referencesLogVisitOnly($where) || !$this->referencesLogVisitOnly($orderBy)) {
            return null;
        }

        // the caller's conditions are rewritten to the alias, which would rewrite the inside of a
        // subquery that selects from log_visit as well. Those references belong to the log_visit of
        // that subquery, so the query has to stay grouped instead.
        if (preg_match('/\bSELECT\b/i', (string) $where)) {
            return null;
        }

        // log_visit has to be the table the other ones are joined on, so that every row of the join
        // belongs to exactly one visit
        if (!isset($tables[0]) || $tables[0] !== 'log_visit') {
            return null;
        }

        if (count($tables) === 1) {
            $where = empty($segmentWhere) ? $where : $this->getWhereMatchBoth($where, $segmentWhere);

            return $this->buildSelectQuery($select, $from, $where, false, $orderBy, $limitAndOffset);
        }

        if (empty($segmentWhere)) {
            return null;
        }

        $alias = self::LOG_VISIT_OUTER_ALIAS;

        $visitMatchesSegment = $this->buildSelectQuery(
            '1',
            $from,
            self::LOG_VISIT_ID_COLUMN . " = $alias.idvisit
				AND ($segmentWhere)",
            false,
            false,
            null
        );

        return $this->buildSelectQuery(
            $this->aliasLogVisitTable($select, $alias),
            Common::prefixTable('log_visit') . " AS $alias",
            $this->getWhereMatchBoth($this->aliasLogVisitTable($where, $alias), "EXISTS ($visitMatchesSegment)"),
            false,
            $this->aliasLogVisitTable($orderBy, $alias),
            $limitAndOffset
        );
    }

    /**
     * Whether the visits log may drop its group by. Off by default while the query change is
     * validated, see the [Live] section of global.ini.php.
     *
     * @return bool
     */
    private function isVisitSemiJoinEnabled()
    {
        $live = Config::getInstance()->Live;

        return !empty($live['use_semi_join_query']);
    }

    /**
     * @param string|false $sqlExpression
     * @return bool
     */
    private function referencesLogVisitOnly($sqlExpression)
    {
        foreach (SegmentExpression::parseColumnsFromSqlExpr((string) $sqlExpression) as $column) {
            if (strpos($column, 'log_visit.') !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|false $sqlExpression
     * @param string $alias
     * @return string
     */
    private function aliasLogVisitTable($sqlExpression, $alias)
    {
        return preg_replace('/(?<![a-zA-Z0-9_])log_visit\./', $alias . '.', (string) $sqlExpression);
    }

    /**
     * Build a select query where actions have to be joined on visits (or conversions)
     * In this case, the query gets wrapped in another query so that grouping by visit is possible
     * @param string $select
     * @param string $from
     * @param string $where
     * @param string $groupBy
     * @param string $orderBy
     * @param string $limitAndOffset
     * @param null|string $innerGroupBy  If given, this inner group by will be used. If not, we try to detect one
     * @throws Exception
     * @return string
     */
    private function buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, JoinTables $tables, $innerGroupBy = null)
    {
        $matchTables = $this->getKnownTables();
        foreach ($tables as $table) {
            if (is_array($table) && isset($table['tableAlias']) && !in_array($table['tableAlias'], $matchTables, $strict = true)) {
                $matchTables[] = $table['tableAlias'];
            } elseif (is_array($table) && isset($table['table']) && !in_array($table['table'], $matchTables, $strict = true)) {
                $matchTables[] = $table['table'];
            } elseif (is_string($table) && !in_array($table, $matchTables, $strict = true)) {
                $matchTables[] = $table;
            }
        }

        $matchTables = '(' . implode('|', $matchTables) . ')';
        preg_match_all("/" . $matchTables . "\.[a-z0-9_\*]+/", $select, $matches);
        $neededFields = array_unique($matches[0]);

        if (count($neededFields) == 0) {
            throw new Exception("No needed fields found in select expression. "
                . "Please use a table prefix.");
        }

        $fieldNames = array();
        $toBeReplaced = array();
        $epregReplace = array();
        foreach ($neededFields as &$neededField) {
            $parts = explode('.', $neededField);
            if (count($parts) === 2 && !empty($parts[1])) {
                if (in_array($parts[1], $fieldNames, $strict = true)) {
                    // eg when selecting 2 dimensions log_action_X.name
                    $columnAs = $parts[1] . md5($neededField);
                    $fieldNames[] = $columnAs;
                    // we make sure to not replace a idvisitor column when duplicate column is idvisit
                    $toBeReplaced[$neededField . ' '] = $parts[0] . '.' . $columnAs . ' ';
                    $toBeReplaced[$neededField . ')'] = $parts[0] . '.' . $columnAs . ')';
                    $toBeReplaced[$neededField . '`'] = $parts[0] . '.' . $columnAs . '`';
                    $toBeReplaced[$neededField . ','] = $parts[0] . '.' . $columnAs . ',';
                    // replace when string ends this, we need to use regex to check for this
                    $epregReplace["/(" . $neededField . ")$/"] = $parts[0] . '.' . $columnAs;
                    $neededField .= ' as ' .  $columnAs;
                } else {
                    $fieldNames[] = $parts[1];
                }
            }
        }

        preg_match_all("/" . $matchTables . "/", $from, $matchesFrom);

        $innerSelect = implode(", \n", $neededFields);
        $innerFrom = $from;
        $innerWhere = $where;

        $innerLimitAndOffset = $limitAndOffset;

        $innerOrderBy = "NULL";
        if ($innerLimitAndOffset && $orderBy) {
            // only When LIMITing we can apply to the inner query the same ORDER BY as the parent query
            $innerOrderBy = $orderBy;
        }
        if ($innerLimitAndOffset) {
            // When LIMITing, no need to GROUP BY (GROUPing by is done before the LIMIT which is super slow when large amount of rows is matched)
            $innerGroupBy = false;
        }

        if (!isset($innerGroupBy) && in_array('log_visit', $matchesFrom[1])) {
            $innerGroupBy = "log_visit.idvisit";
        } elseif (!isset($innerGroupBy)) {
            throw new Exception('Cannot use subselect for join as no group by rule is specified');
        }

        if (!empty($toBeReplaced)) {
            $select = preg_replace(array_keys($epregReplace), array_values($epregReplace), $select);
            $select = str_replace(array_keys($toBeReplaced), array_values($toBeReplaced), $select);
            if (!empty($groupBy)) {
                $groupBy = preg_replace(array_keys($epregReplace), array_values($epregReplace), $groupBy);
                $groupBy = str_replace(array_keys($toBeReplaced), array_values($toBeReplaced), $groupBy);
            }
            if (!empty($orderBy)) {
                $orderBy = preg_replace(array_keys($epregReplace), array_values($epregReplace), $orderBy);
                $orderBy = str_replace(array_keys($toBeReplaced), array_values($toBeReplaced), $orderBy);
            }
        }

        $innerQuery = $this->buildSelectQuery($innerSelect, $innerFrom, $innerWhere, $innerGroupBy, $innerOrderBy, $innerLimitAndOffset);

        $select = preg_replace('/' . $matchTables . '\./', 'log_inner.', $select);

        $from = "
        (
            $innerQuery
        ) AS log_inner";
        $where = false;
        $orderBy = preg_replace('/' . $matchTables . '\./', 'log_inner.', $orderBy);
        $groupBy = preg_replace('/' . $matchTables . '\./', 'log_inner.', $groupBy);

        $outerLimitAndOffset = null;
        $query = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $outerLimitAndOffset);
        return $query;
    }


    /**
     * Build select query the normal way
     *
     * @param string $select fieldlist to be selected
     * @param string $from tablelist to select from
     * @param string|false $where where clause
     * @param string|false $groupBy group by clause
     * @param string|false $orderBy order by clause
     * @param string|int|null $limitAndOffset limit by clause eg '5' for Limit 5 Offset 0 or '10, 5' for Limit 5 Offset 10
     * @return string
     */
    private function buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, bool $withRollup = false)
    {
        $sql = "
			SELECT
				$select
			FROM
				$from";

        if ($where) {
            $sql .= "
			WHERE
				$where";
        }

        if ($groupBy) {
            $sql .= "
			GROUP BY
				$groupBy";

            if ($withRollup) {
                $sql .= "
                    WITH ROLLUP";
            }
        }

        if ($orderBy) {
            if ($withRollup) {
                $sql = "
                        SELECT * FROM (
                            $sql
                        ) AS rollupQuery";
            }
            $sql .= "
			ORDER BY
				$orderBy";
        }

        $sql = $this->appendLimitClauseToQuery($sql, $limitAndOffset);

        return $sql;
    }

    /**
     * @param $sql
     * @param $limit LIMIT clause eg. "10, 50" (offset 10, limit 50)
     * @return string
     */
    private function appendLimitClauseToQuery($sql, $limit)
    {
        $limitParts = explode(',', (string) $limit);
        $isLimitWithOffset = 2 === count($limitParts);

        if ($isLimitWithOffset) {
            // $limit = "10, 5". We would not have to do this but we do to prevent possible injections.
            $offset = trim($limitParts[0]);
            $limit  = trim($limitParts[1]);
            $sql   .= sprintf(' LIMIT %d, %d', $offset, $limit);
        } else {
            // $limit = "5"
            $limit = (int)$limit;
            if ($limit >= 1) {
                $sql .= " LIMIT $limit";
            }
        }

        return $sql;
    }

    /**
     * @param $where
     * @param $segmentWhere
     * @return string
     */
    protected function getWhereMatchBoth($where, $segmentWhere)
    {
        if (empty($segmentWhere) && empty($where)) {
            throw new \Exception("Segment where clause should be non empty.");
        }
        if (empty($segmentWhere)) {
            return $where;
        }
        if (empty($where)) {
            return $segmentWhere;
        }
        return "( $where )
                AND
                ($segmentWhere)";
    }
}
