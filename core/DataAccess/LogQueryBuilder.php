<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess;

use Exception;
use Piwik\DataAccess\LogQueryBuilder\JoinGenerator;
use Piwik\DataAccess\LogQueryBuilder\JoinTables;
use Piwik\Plugin\LogTablesProvider;
use Piwik\Segment\SegmentExpression;

class LogQueryBuilder
{
    const FORCE_INNER_GROUP_BY_NO_SUBSELECT = '__##nosubquery##__';

    /**
     * @var LogTablesProvider
     */
    private $logTableProvider;

    /**
     * Forces to use a subselect when generating the query. Set value to `false` to force not using a subselect.
     * @var string
     */
    private $forcedInnerGroupBy = '';

    public function __construct(LogTablesProvider $logTablesProvider)
    {
        $this->logTableProvider = $logTablesProvider;
    }

    /**
     * Forces to use a subselect when generating the query.
     * @var string
     */
    public function forceInnerGroupBySubselect($innerGroupBy)
    {
        $this->forcedInnerGroupBy = $innerGroupBy;
    }

    public function getForcedInnerGroupBySubselect()
    {
        return $this->forcedInnerGroupBy;
    }

    public function getSelectQueryString(SegmentExpression $segmentExpression, $select, $from, $where, $bind, $groupBy,
                                         $orderBy, $limitAndOffset)
    {
        if (!is_array($from)) {
            $from = array($from);
        }

        $fromInitially = $from;

        if (!$segmentExpression->isEmpty()) {
            $segmentExpression->parseSubExpressionsIntoSqlExpressions($from);
            $segmentSql = $segmentExpression->getSql();
            $where = $this->getWhereMatchBoth($where, $segmentSql['where']);
            $bind = array_merge($bind, $segmentSql['bind']);
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
                $sql = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset);
            } else {
                $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $tables, $this->forcedInnerGroupBy);
            }
        } elseif ($useSpecialConversionGroupBy) {
            $innerGroupBy = "CONCAT(log_conversion.idvisit, '_' , log_conversion.idgoal, '_', log_conversion.buster)";
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $tables, $innerGroupBy);
        } elseif ($joinWithSubSelect) {
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $tables);
        } else {
            $sql = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset);
        }
        return array(
            'sql' => $sql,
            'bind' => $bind
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
        preg_match_all("/". $matchTables ."\.[a-z0-9_\*]+/", $select, $matches);
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

        preg_match_all("/". $matchTables . "/", $from, $matchesFrom);

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

        $select = preg_replace('/'.$matchTables.'\./', 'log_inner.', $select);

        $from = "
        (
            $innerQuery
        ) AS log_inner";
        $where = false;
        $orderBy = preg_replace('/'.$matchTables.'\./', 'log_inner.', $orderBy);
        $groupBy = preg_replace('/'.$matchTables.'\./', 'log_inner.', $groupBy);

        $outerLimitAndOffset = null;
        $query = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $outerLimitAndOffset);
        return $query;
    }


    /**
     * Build select query the normal way
     *
     * @param string $select fieldlist to be selected
     * @param string $from tablelist to select from
     * @param string $where where clause
     * @param string $groupBy group by clause
     * @param string $orderBy order by clause
     * @param string|int $limitAndOffset limit by clause eg '5' for Limit 5 Offset 0 or '10, 5' for Limit 5 Offset 10
     * @return string
     */
    private function buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset)
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
        }

        if ($orderBy) {
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
     * @throws
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
