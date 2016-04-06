<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess;

use Exception;
use Piwik\Common;
use Piwik\Segment\SegmentExpression;

class LogQueryBuilder
{
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

        $joins = $this->generateJoinsString($from);
        $joinWithSubSelect = $joins['joinWithSubSelect'];
        $from = $joins['sql'];

        // hack for https://github.com/piwik/piwik/issues/9194#issuecomment-164321612
        $useSpecialConversionGroupBy = (!empty($segmentSql)
            && strpos($groupBy, 'log_conversion.idgoal') !== false
            && $fromInitially == array('log_conversion')
            && strpos($from, 'log_link_visit_action') !== false);

        if ($useSpecialConversionGroupBy) {
            $innerGroupBy = "CONCAT(log_conversion.idvisit, '_' , log_conversion.idgoal, '_', log_conversion.buster)";
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $innerGroupBy);
        } elseif ($joinWithSubSelect) {
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset);
        } else {
            $sql = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset);
        }
        return array(
            'sql' => $sql,
            'bind' => $bind
        );
    }

    private function hasJoinedTableAlreadyManually($tableToFind, $joinToFind, $tables)
    {
        foreach ($tables as $index => $table) {
            if (is_array($table)
                && !empty($table['table'])
                && $table['table'] === $tableToFind
                && (!isset($table['tableAlias']) || $table['tableAlias'] === $tableToFind)
                && isset($table['joinOn']) && $table['joinOn'] === $joinToFind) {
                return true;
            }
        }

        return false;
    }

    private function findIndexOfManuallyAddedTable($tableToFind, $tables)
    {
        foreach ($tables as $index => $table) {
            if (is_array($table)
                && !empty($table['table'])
                && $table['table'] === $tableToFind
                && (!isset($table['tableAlias']) || $table['tableAlias'] === $tableToFind)) {
                return $index;
            }
        }
    }

    private function hasTableAddedManually($tableToFind, $tables)
    {
        $table = $this->findIndexOfManuallyAddedTable($tableToFind, $tables);

        return isset($table);
    }

    /**
     * Generate the join sql based on the needed tables
     * @param array $tables tables to join
     * @throws Exception if tables can't be joined
     * @return array
     */
    private function generateJoinsString(&$tables)
    {
        $knownTables = array("log_action", "log_visit", "log_link_visit_action", "log_conversion", "log_conversion_item");
        $visitsAvailable = $linkVisitActionsTableAvailable = $conversionsAvailable = $conversionItemAvailable = $actionsTableAvailable = false;
        $defaultLogActionJoin = "log_link_visit_action.idaction_url = log_action.idaction";

        $joinWithSubSelect = false;
        $sql = '';

        // make sure the tables are joined in the right order
        // base table first, then action before conversion
        // this way, conversions can be left joined on idvisit
        $actionIndex = array_search("log_link_visit_action", $tables);
        $conversionIndex = array_search("log_conversion", $tables);
        if ($actionIndex > 0 && $conversionIndex > 0 && $actionIndex > $conversionIndex) {
            $tables[$actionIndex] = "log_conversion";
            $tables[$conversionIndex] = "log_link_visit_action";
        }
        // same as above: action before visit
        $actionIndex = array_search("log_link_visit_action", $tables);
        $visitIndex = array_search("log_visit", $tables);
        if ($actionIndex > 0 && $visitIndex > 0 && $actionIndex > $visitIndex) {
            $tables[$actionIndex] = "log_visit";
            $tables[$visitIndex] = "log_link_visit_action";
        }

        // we need to add log_link_visit_action dynamically to join eg visit with action
        $linkVisitAction = array_search("log_link_visit_action", $tables);
        $actionIndex     = array_search("log_action", $tables);
        if ($linkVisitAction === false && $actionIndex > 0) {
            $tables[] = "log_link_visit_action";
        }

        if ($actionIndex > 0
            && $this->hasTableAddedManually('log_action', $tables)
            && !$this->hasJoinedTableAlreadyManually('log_action', $defaultLogActionJoin, $tables)) {
            // we cannot join the same table with same alias twice, therefore we need to combine the join via AND
            $tableIndex = $this->findIndexOfManuallyAddedTable('log_action', $tables);
            $defaultLogActionJoin = '(' . $tables[$tableIndex]['joinOn'] . ' AND ' . $defaultLogActionJoin . ')';
            unset($tables[$tableIndex]);
        }

        $linkVisitAction = array_search("log_link_visit_action", $tables);
        $actionIndex     = array_search("log_action", $tables);
        if ($linkVisitAction > 0 && $actionIndex > 0 && $linkVisitAction > $actionIndex) {
            $tables[$actionIndex] = "log_link_visit_action";
            $tables[$linkVisitAction] = "log_action";
        }

        foreach ($tables as $i => $table) {
            if (is_array($table)) {
                // join condition provided
                $alias = isset($table['tableAlias']) ? $table['tableAlias'] : $table['table'];
                $sql .= "
				LEFT JOIN " . Common::prefixTable($table['table']) . " AS " . $alias
                    . " ON " . $table['joinOn'];
                continue;
            }

            if (!in_array($table, $knownTables)) {
                throw new Exception("Table '$table' can't be used for segmentation");
            }

            $tableSql = Common::prefixTable($table) . " AS $table";

            if ($i == 0) {
                // first table
                $sql .= $tableSql;
            } else {

                if ($linkVisitActionsTableAvailable && $table === 'log_action') {
                    $join = $defaultLogActionJoin;

                    if ($this->hasJoinedTableAlreadyManually($table, $join, $tables)) {
                        $actionsTableAvailable = true;
                        continue;
                    }

                } elseif ($linkVisitActionsTableAvailable && $table == "log_conversion") {
                    // have actions, need conversions => join on idvisit
                    $join = "log_conversion.idvisit = log_link_visit_action.idvisit";
                } elseif ($linkVisitActionsTableAvailable && $table == "log_visit") {
                    // have actions, need visits => join on idvisit
                    $join = "log_visit.idvisit = log_link_visit_action.idvisit";

                    if ($this->hasJoinedTableAlreadyManually($table, $join, $tables)) {
                        $visitsAvailable = true;
                        continue;
                    }

                } elseif ($visitsAvailable && $table == "log_link_visit_action") {
                    // have visits, need actions => we have to use a more complex join
                    // we don't hande this here, we just return joinWithSubSelect=true in this case
                    $joinWithSubSelect = true;
                    $join = "log_link_visit_action.idvisit = log_visit.idvisit";

                    if ($this->hasJoinedTableAlreadyManually($table, $join, $tables)) {
                        $linkVisitActionsTableAvailable = true;
                        continue;
                    }

                } elseif ($conversionsAvailable && $table == "log_link_visit_action") {
                    // have conversions, need actions => join on idvisit
                    $join = "log_conversion.idvisit = log_link_visit_action.idvisit";
                } elseif (($visitsAvailable && $table == "log_conversion")
                    || ($conversionsAvailable && $table == "log_visit")
                ) {
                    // have visits, need conversion (or vice versa) => join on idvisit
                    // notice that joining conversions on visits has lower priority than joining it on actions
                    $join = "log_conversion.idvisit = log_visit.idvisit";

                    // if conversions are joined on visits, we need a complex join
                    if ($table == "log_conversion") {
                        $joinWithSubSelect = true;
                    }
                } elseif ($conversionItemAvailable && $table === 'log_visit') {
                    $join = "log_conversion_item.idvisit = log_visit.idvisit";
                } elseif ($conversionItemAvailable && $table === 'log_link_visit_action') {
                    $join = "log_conversion_item.idvisit = log_link_visit_action.idvisit";
                } elseif ($conversionItemAvailable && $table === 'log_conversion') {
                    $join = "log_conversion_item.idvisit = log_conversion.idvisit";
                } else {
                    throw new Exception("Table '$table' can't be joined for segmentation");
                }

                // the join sql the default way
                $sql .= "
				LEFT JOIN $tableSql ON $join";
            }

            // remember which tables are available
            $visitsAvailable = ($visitsAvailable || $table == "log_visit");
            $linkVisitActionsTableAvailable = ($linkVisitActionsTableAvailable || $table == "log_link_visit_action");
            $actionsTableAvailable = ($actionsTableAvailable || $table == "log_action");
            $conversionsAvailable = ($conversionsAvailable || $table == "log_conversion");
            $conversionItemAvailable = ($conversionItemAvailable || $table == "log_conversion_item");
        }

        $return = array(
            'sql'               => $sql,
            'joinWithSubSelect' => $joinWithSubSelect
        );
        return $return;
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
    private function buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limitAndOffset, $innerGroupBy = null)
    {
        $matchTables = "(log_visit|log_conversion_item|log_conversion|log_action)";
        preg_match_all("/". $matchTables ."\.[a-z0-9_\*]+/", $select, $matches);
        $neededFields = array_unique($matches[0]);

        if (count($neededFields) == 0) {
            throw new Exception("No needed fields found in select expression. "
                . "Please use a table prefix.");
        }

        preg_match_all("/". $matchTables . "/", $from, $matchesFrom);

        $innerSelect = implode(", \n", $neededFields);
        $innerFrom = $from;
        $innerWhere = $where;

        $innerLimitAndOffset = $limitAndOffset;

        if (!isset($innerGroupBy) && in_array('log_visit', $matchesFrom[1])) {
            $innerGroupBy = "log_visit.idvisit";
        } elseif (!isset($innerGroupBy)) {
            throw new Exception('Cannot use subselect for join as no group by rule is specified');
        }

        $innerOrderBy = "NULL";
        if ($innerLimitAndOffset && $orderBy) {
            // only When LIMITing we can apply to the inner query the same ORDER BY as the parent query
            $innerOrderBy = $orderBy;
        }
        if ($innerLimitAndOffset) {
            // When LIMITing, no need to GROUP BY (GROUPing by is done before the LIMIT which is super slow when large amount of rows is matched)
            $innerGroupBy = false;
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
