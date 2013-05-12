<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 *
 * @package Piwik
 */
class Piwik_Segment
{
    /**
     * @var Piwik_SegmentExpression
     */
    protected $segment = null;

    /**
     * Truncate the Segments to 4k
     */
    const SEGMENT_TRUNCATE_LIMIT = 8192;

    public function __construct($string, $idSites)
    {
        $string = trim($string);
        if (!Piwik_Archive::isSegmentationEnabled()
            && !empty($string)
        ) {
            throw new Exception("The Super User has disabled the Segmentation feature.");
        }

        // First try with url decoded value. If that fails, try with raw value.
        // If that also fails, it will throw the exception
        try {
            $this->initializeSegment( urldecode($string), $idSites);
        } catch(Exception $e) {
            $this->initializeSegment($string, $idSites);
        }
    }

    /**
     * @param $string
     * @param $idSites
     * @throws Exception
     */
    protected function initializeSegment($string, $idSites)
    {
        // As a preventive measure, we restrict the filter size to a safe limit
        $string = substr($string, 0, self::SEGMENT_TRUNCATE_LIMIT);

        $this->string = $string;
        $this->idSites = $idSites;
        $segment = new Piwik_SegmentExpression($string);
        $this->segment = $segment;

        // parse segments
        $expressions = $segment->parseSubExpressions();

        // convert segments name to sql segment
        // check that user is allowed to view this segment
        // and apply a filter to the value to match if necessary (to map DB fields format)
        $cleanedExpressions = array();
        foreach ($expressions as $expression) {
            $operand = $expression[Piwik_SegmentExpression::INDEX_OPERAND];
            $cleanedExpression = $this->getCleanedExpression($operand);
            $expression[Piwik_SegmentExpression::INDEX_OPERAND] = $cleanedExpression;
            $cleanedExpressions[] = $expression;
        }
        $segment->setSubExpressionsAfterCleanup($cleanedExpressions);
    }

    public function isEmpty()
    {
        return empty($this->string);
    }

    protected $availableSegments = array();
    protected $segmentsHumanReadable = '';

    protected function getCleanedExpression($expression)
    {
        if (empty($this->availableSegments)) {
            $this->availableSegments = Piwik_API_API::getInstance()->getSegmentsMetadata($this->idSites, $_hideImplementationData = false);
        }

        $name = $expression[0];
        $matchType = $expression[1];
        $value = $expression[2];
        $sqlName = '';

        foreach ($this->availableSegments as $segment) {
            if ($segment['segment'] != $name) {
                continue;
            }

            $sqlName = $segment['sqlSegment'];

            // check permission
            if (isset($segment['permission'])
                && $segment['permission'] != 1
            ) {
                throw new Exception("You do not have enough permission to access the segment " . $name);
            }

            // apply presentation filter
            if (isset($segment['sqlFilter'])
                && !empty($segment['sqlFilter'])
                && $matchType != Piwik_SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY
                && $matchType != Piwik_SegmentExpression::MATCH_IS_NULL_OR_EMPTY
            ) {
                $value = call_user_func($segment['sqlFilter'], $value, $segment['sqlSegment'], $matchType, $name);

                // sqlFilter-callbacks might return arrays for more complex cases
                // e.g. see Piwik_Actions::getIdActionFromSegment()
                if (is_array($value)
                    && isset($value['SQL'])
                ) {
                    // Special case: returned value is a sub sql expression!
                    $matchType = Piwik_SegmentExpression::MATCH_ACTIONS_CONTAINS;
                }
            }
            break;
        }

        if (empty($sqlName)) {
            throw new Exception("Segment '$name' is not a supported segment.");
        }

        return array($sqlName, $matchType, $value);
    }

    public function getString()
    {
        return $this->string;
    }

    public function getHash()
    {
        if (empty($this->string)) {
            return '';
        }
        // normalize the string as browsers may send slightly different payloads for the same archive
        $normalizedSegmentString = urldecode($this->string);
        return md5($normalizedSegmentString);
    }


    /**
     * Extend SQL query with segment expressions
     *
     * @param string $select   select clause
     * @param array $from     array of table names (without prefix)
     * @param bool|string $where    (optional )where clause
     * @param array|string $bind     (optional) params to bind
     * @param bool|string $orderBy  (optional) order by clause
     * @param bool|string $groupBy  (optional) group by clause
     * @return string entire select query
     */
    public function getSelectQuery($select, $from, $where = false, $bind = array(), $orderBy = false, $groupBy = false)
    {
        $joinWithSubSelect = false;

        if (!is_array($from)) {
            $from = array($from);
        }

        if (!$this->isEmpty()) {
            $this->segment->parseSubExpressionsIntoSqlExpressions($from);

            $joins = $this->generateJoins($from);
            $from = $joins['sql'];
            $joinWithSubSelect = $joins['joinWithSubSelect'];

            $segmentSql = $this->segment->getSql();
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
            'sql'  => $sql,
            'bind' => $bind
        );
    }

    /**
     * Generate the join sql based on the needed tables
     * @param array $tables  tables to join
     * @throws Exception if tables can't be joined
     * @return array
     */
    private function generateJoins($tables)
    {
        $knownTables = array("log_visit", "log_link_visit_action", "log_conversion");
        $visitsAvailable = $actionsAvailable = $conversionsAvailable = false;
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
				LEFT JOIN " . Piwik_Common::prefixTable($table['table']) . " AS " . $alias
                    . " ON " . $table['joinOn'];
                continue;
            }

            if (!in_array($table, $knownTables)) {
                throw new Exception("Table '$table' can't be used for segmentation");
            }

            $tableSql = Piwik_Common::prefixTable($table) . " AS $table";

            if ($i == 0) {
                // first table
                $sql .= $tableSql;
            } else {
                $join = "";

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
                } else {
                    throw new Exception("Table '$table', can't be joined for segmentation");
                }

                // the join sql the default way
                $sql .= "
				LEFT JOIN $tableSql ON $join";
            }

            // remember which tables are available
            $visitsAvailable = ($visitsAvailable || $table == "log_visit");
            $actionsAvailable = ($actionsAvailable || $table == "log_link_visit_action");
            $conversionsAvailable = ($conversionsAvailable || $table == "log_conversion");
        }

        return array(
            'sql'               => $sql,
            'joinWithSubSelect' => $joinWithSubSelect
        );
    }

    /**
     * Build select query the normal way
     * @param string $select   fieldlist to be selected
     * @param string $from     tablelist to select from
     * @param string $where    where clause
     * @param string $orderBy  order by clause
     * @param string $groupBy  group by clause
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
        preg_match_all("/(log_visit|log_conversion|log_action).[a-z0-9_\*]+/", $select, $matches);
        $neededFields = array_unique($matches[0]);

        if (count($neededFields) == 0) {
            throw new Exception("No needed fields found in select expression. "
                . "Please use a table prefix.");
        }

        $select = preg_replace('/(log_visit|log_conversion|log_action)\./', 'log_inner.', $select);
        $orderBy = preg_replace('/(log_visit|log_conversion|log_action)\./', 'log_inner.', $orderBy);
        $groupBy = preg_replace('/(log_visit|log_conversion|log_action)\./', 'log_inner.', $groupBy);

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
        return $this->buildSelectQuery($select, $from, $where, $orderBy, $groupBy);
    }

}
