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
                                         $orderBy, $limit)
    {
        if (!is_array($from)) {
            $from = array($from);
        }

        if (!$segmentExpression->isEmpty()) {
            $segmentExpression->parseSubExpressionsIntoSqlExpressions($from);
            $segmentSql = $segmentExpression->getSql();
            $where = $this->getWhereMatchBoth($where, $segmentSql['where']);
            $bind = array_merge($bind, $segmentSql['bind']);
        }

        $joins = $this->generateJoinsString($from);
        $joinWithSubSelect = $joins['joinWithSubSelect'];
        $from = $joins['sql'];

        if ($joinWithSubSelect) {
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limit);
        } else {
            $sql = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limit);
        }
        return array(
            'sql' => $sql,
            'bind' => $bind
        );
    }


    /**
     * Generate the join sql based on the needed tables
     * @param array $tables tables to join
     * @throws Exception if tables can't be joined
     * @return array
     */
    private function generateJoinsString($tables)
    {
        $knownTables = array("log_visit", "log_link_visit_action", "log_conversion", "log_conversion_item");
        $visitsAvailable = $actionsAvailable = $conversionsAvailable = $conversionItemAvailable = false;
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
                if ($actionsAvailable && $table == "log_conversion") {
                    // have actions, need conversions => join on idvisit
                    $join = "log_conversion.idvisit = log_link_visit_action.idvisit";
                } elseif ($actionsAvailable && $table == "log_visit") {
                    // have actions, need visits => join on idvisit
                    $join = "log_visit.idvisit = log_link_visit_action.idvisit";
                } elseif ($visitsAvailable && $table == "log_link_visit_action") {
                    // have visits, need actions => we have to use a more complex join
                    // we don't hande this here, we just return joinWithSubSelect=true in this case
                    $joinWithSubSelect = true;
                    $join = "log_link_visit_action.idvisit = log_visit.idvisit";
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
            $actionsAvailable = ($actionsAvailable || $table == "log_link_visit_action");
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
     * @param string $limit
     * @throws Exception
     * @return string
     */
    private function buildWrappedSelectQuery($select, $from, $where, $groupBy, $orderBy, $limit)
    {
        $matchTables = "(log_visit|log_conversion_item|log_conversion|log_action)";
        preg_match_all("/". $matchTables ."\.[a-z0-9_\*]+/", $select, $matches);
        $neededFields = array_unique($matches[0]);

        if (count($neededFields) == 0) {
            throw new Exception("No needed fields found in select expression. "
                . "Please use a table prefix.");
        }

        $innerSelect = implode(", \n", $neededFields);
        $innerFrom = $from;
        $innerWhere = $where;

        $innerLimit = $limit;
        $innerGroupBy = "log_visit.idvisit";
        $innerOrderBy = "NULL";
        if ($innerLimit && $orderBy) {
            // only When LIMITing we can apply to the inner query the same ORDER BY as the parent query
            $innerOrderBy = $orderBy;
        }
        if ($innerLimit) {
            // When LIMITing, no need to GROUP BY (GROUPing by is done before the LIMIT which is super slow when large amount of rows is matched)
            $innerGroupBy = false;
        }

        $innerQuery = $this->buildSelectQuery($innerSelect, $innerFrom, $innerWhere, $innerGroupBy, $innerOrderBy, $innerLimit);

        $select = preg_replace('/'.$matchTables.'\./', 'log_inner.', $select);
        $from = "
        (
            $innerQuery
        ) AS log_inner";
        $where = false;
        $orderBy = preg_replace('/'.$matchTables.'\./', 'log_inner.', $orderBy);
        $groupBy = preg_replace('/'.$matchTables.'\./', 'log_inner.', $groupBy);
        $query = $this->buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limit);
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
     * @param string|int $limit limit by clause eg '5' for Limit 5 Offset 0 or '10, 5' for Limit 5 Offset 10
     * @return string
     */
    private function buildSelectQuery($select, $from, $where, $groupBy, $orderBy, $limit)
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

        $sql = $this->appendLimitClauseToQuery($sql, $limit);

        return $sql;
    }

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
