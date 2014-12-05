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


    /**
     * @param $segmentExpression
     * @param $select
     * @param $from
     * @param $where
     * @param $bind
     * @param $orderBy
     * @param $groupBy
     * @return array
     * @throws Exception
     */
    public function getSelectQueryString(SegmentExpression $segmentExpression, $select, $from, $where, $bind, $orderBy, $groupBy)
    {
        if (!is_array($from)) {
            $from = array($from);
        }

        if (!$segmentExpression->isEmpty()) {
            $segmentExpression->parseSubExpressionsIntoSqlExpressions($from);

            $joins = $this->generateJoins($from);
            $from = $joins['sql'];
            $joinWithSubSelect = $joins['joinWithSubSelect'];

            $segmentSql = $segmentExpression->getSql();
            $segmentWhere = $segmentSql['where'];
            if (!empty($segmentWhere)) {
                if (!empty($where)) {
                    $where = "( $where )
				AND
				($segmentWhere)";
                } else {
                    $where = $segmentWhere;
                }
            }

            $bind = array_merge($bind, $segmentSql['bind']);
        } else {
            $joins = $this->generateJoins($from);
            $from = $joins['sql'];
            $joinWithSubSelect = $joins['joinWithSubSelect'];
        }

        if ($joinWithSubSelect) {
            $sql = $this->buildWrappedSelectQuery($select, $from, $where, $orderBy, $groupBy);
        } else {
            $sql = $this->buildSelectQuery($select, $from, $where, $orderBy, $groupBy);
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
    private function generateJoins($tables)
    {
        $knownTables = array("log_visit", "log_link_visit_action", "log_conversion", "log_conversion_item");
        $visitsAvailable = $actionsAvailable = $conversionsAvailable = $conversionItemAvailable = false;
        $joinWithSubSelect = false;
        $sql = '';

        // make sure the tables are joined in the right order
        // base table first, then action before conversion
        // this way, conversions can be joined on idlink_va
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
                    // have actions, need conversions => join on idlink_va
                    $join = "log_conversion.idlink_va = log_link_visit_action.idlink_va "
                        . "AND log_conversion.idsite = log_link_visit_action.idsite";
                } else if ($actionsAvailable && $table == "log_visit") {
                    // have actions, need visits => join on idvisit
                    $join = "log_visit.idvisit = log_link_visit_action.idvisit";
                } else if ($visitsAvailable && $table == "log_link_visit_action") {
                    // have visits, need actions => we have to use a more complex join
                    // we don't hande this here, we just return joinWithSubSelect=true in this case
                    $joinWithSubSelect = true;
                    $join = "log_link_visit_action.idvisit = log_visit.idvisit";
                } else if ($conversionsAvailable && $table == "log_link_visit_action") {
                    // have conversions, need actions => join on idlink_va
                    $join = "log_conversion.idlink_va = log_link_visit_action.idlink_va";
                } else if (($visitsAvailable && $table == "log_conversion")
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
     * Build select query the normal way
     * @param string $select fieldlist to be selected
     * @param string $from tablelist to select from
     * @param string $where where clause
     * @param string $orderBy order by clause
     * @param string $groupBy group by clause
     * @return string
     */
    private function buildSelectQuery($select, $from, $where, $orderBy, $groupBy)
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

        return $sql;
    }

    /**
     * Build a select query where actions have to be joined on visits (or conversions)
     * In this case, the query gets wrapped in another query so that grouping by visit is possible
     * @param string $select
     * @param string $from
     * @param string $where
     * @param string $orderBy
     * @param string $groupBy
     * @throws Exception
     * @return string
     */
    private function buildWrappedSelectQuery($select, $from, $where, $orderBy, $groupBy)
    {
        $matchTables = "(log_visit|log_conversion_item|log_conversion|log_action)";
        preg_match_all("/". $matchTables ."\.[a-z0-9_\*]+/", $select, $matches);
        $neededFields = array_unique($matches[0]);

        if (count($neededFields) == 0) {
            throw new Exception("No needed fields found in select expression. "
                . "Please use a table prefix.");
        }

        $select = preg_replace('/'.$matchTables.'\./', 'log_inner.', $select);
        $orderBy = preg_replace('/'.$matchTables.'\./', 'log_inner.', $orderBy);
        $groupBy = preg_replace('/'.$matchTables.'\./', 'log_inner.', $groupBy);

        $from = "(
			SELECT
				" . implode(",
				", $neededFields) . "
			FROM
				$from
			WHERE
				$where
			GROUP BY log_visit.idvisit
				) AS log_inner";

        $where = false;
        $query = $this->buildSelectQuery($select, $from, $where, $orderBy, $groupBy);
        return $query;
    }

} 