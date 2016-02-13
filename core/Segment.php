<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogQueryBuilder;
use Piwik\Plugins\API\API;
use Piwik\Segment\SegmentExpression;

/**
 * Limits the set of visits Piwik uses when aggregating analytics data.
 *
 * A segment is a condition used to filter visits. They can, for example,
 * select visits that have a specific browser or come from a specific
 * country, or both.
 *
 * Individual segment dimensions (such as `browserCode` and `countryCode`)
 * are defined by plugins. Read about the {@hook API.getSegmentDimensionMetadata}
 * event to learn more.
 *
 * Plugins that aggregate data stored in Piwik can support segments by
 * using this class when generating aggregation SQL queries.
 *
 * ### Examples
 *
 * **Basic usage**
 *
 *     $idSites = array(1,2,3);
 *     $segmentStr = "browserCode==ff;countryCode==CA";
 *     $segment = new Segment($segmentStr, $idSites);
 *
 *     $query = $segment->getSelectQuery(
 *         $select = "table.col1, table2.col2",
 *         $from = array("table", "table2"),
 *         $where = "table.col3 = ?",
 *         $bind = array(5),
 *         $orderBy = "table.col1 DESC",
 *         $groupBy = "table2.col2"
 *     );
 *
 *     Db::fetchAll($query['sql'], $query['bind']);
 *
 * **Creating a _null_ segment**
 *
 *     $idSites = array(1,2,3);
 *     $segment = new Segment('', $idSites);
 *     // $segment->getSelectQuery will return a query that selects all visits
 *
 * @api
 */
class Segment
{
    /**
     * @var SegmentExpression
     */
    protected $segmentExpression = null;

    /**
     * @var string
     */
    protected $string = null;

    /**
     * @var array
     */
    protected $idSites = null;

    /**
     * @var LogQueryBuilder
     */
    private $segmentQueryBuilder;

    /**
     * Truncate the Segments to 8k
     */
    const SEGMENT_TRUNCATE_LIMIT = 8192;

    /**
     * Constructor.
     *
     * @param string $segmentCondition The segment condition, eg, `'browserCode=ff;countryCode=CA'`.
     * @param array $idSites The list of sites the segment will be used with. Some segments are
     *                       dependent on the site, such as goal segments.
     * @throws
     */
    public function __construct($segmentCondition, $idSites)
    {
        $this->segmentQueryBuilder = StaticContainer::get('Piwik\DataAccess\LogQueryBuilder');

        $segmentCondition = trim($segmentCondition);
        if (!SettingsPiwik::isSegmentationEnabled()
            && !empty($segmentCondition)
        ) {
            throw new Exception("The Super User has disabled the Segmentation feature.");
        }

        // First try with url decoded value. If that fails, try with raw value.
        // If that also fails, it will throw the exception
        try {
            $this->initializeSegment(urldecode($segmentCondition), $idSites);
        } catch (Exception $e) {
            $this->initializeSegment($segmentCondition, $idSites);
        }
    }

    private function getAvailableSegments()
    {
        // segment metadata
        if (empty($this->availableSegments)) {
            $this->availableSegments = API::getInstance()->getSegmentsMetadata($this->idSites, $_hideImplementationData = false);
        }

        return $this->availableSegments;
    }

    private function getSegmentByName($name)
    {
        $segments = $this->getAvailableSegments();

        foreach ($segments as $segment) {
            if ($segment['segment'] == $name && !empty($name)) {

                // check permission
                if (isset($segment['permission']) && $segment['permission'] != 1) {
                    throw new NoAccessException("You do not have enough permission to access the segment " . $name);
                }

                return $segment;
            }
        }

        throw new Exception("Segment '$name' is not a supported segment.");
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

        $this->string  = $string;
        $this->idSites = $idSites;
        $segment = new SegmentExpression($string);
        $this->segmentExpression = $segment;

        // parse segments
        $expressions = $segment->parseSubExpressions();
        $expressions = $this->getExpressionsWithUnionsResolved($expressions);

        // convert segments name to sql segment
        // check that user is allowed to view this segment
        // and apply a filter to the value to match if necessary (to map DB fields format)
        $cleanedExpressions = array();
        foreach ($expressions as $expression) {
            $operand = $expression[SegmentExpression::INDEX_OPERAND];
            $cleanedExpression = $this->getCleanedExpression($operand);
            $expression[SegmentExpression::INDEX_OPERAND] = $cleanedExpression;
            $cleanedExpressions[] = $expression;
        }

        $segment->setSubExpressionsAfterCleanup($cleanedExpressions);
    }

    private function getExpressionsWithUnionsResolved($expressions)
    {
        $expressionsWithUnions = array();
        foreach ($expressions as $expression) {
            $operand = $expression[SegmentExpression::INDEX_OPERAND];
            $name    = $operand[SegmentExpression::INDEX_OPERAND_NAME];

            $availableSegment = $this->getSegmentByName($name);

            if (!empty($availableSegment['unionOfSegments'])) {
                $count = 0;
                foreach ($availableSegment['unionOfSegments'] as $segmentNameOfUnion) {
                    $count++;
                    $operator = SegmentExpression::BOOL_OPERATOR_OR; // we connect all segments within that union via OR
                    if ($count === count($availableSegment['unionOfSegments'])) {
                        $operator = $expression[SegmentExpression::INDEX_BOOL_OPERATOR];
                    }

                    $operand[SegmentExpression::INDEX_OPERAND_NAME] = $segmentNameOfUnion;
                    $expressionsWithUnions[] = array(
                        SegmentExpression::INDEX_BOOL_OPERATOR => $operator,
                        SegmentExpression::INDEX_OPERAND => $operand
                    );
                }
            } else {
                $expressionsWithUnions[] = array(
                    SegmentExpression::INDEX_BOOL_OPERATOR => $expression[SegmentExpression::INDEX_BOOL_OPERATOR],
                    SegmentExpression::INDEX_OPERAND => $operand
                );
            }
        }

        return $expressionsWithUnions;
    }

    /**
     * Returns `true` if the segment is empty, `false` if otherwise.
     */
    public function isEmpty()
    {
        return $this->segmentExpression->isEmpty();
    }

    protected $availableSegments = array();

    protected function getCleanedExpression($expression)
    {
        $name      = $expression[SegmentExpression::INDEX_OPERAND_NAME];
        $matchType = $expression[SegmentExpression::INDEX_OPERAND_OPERATOR];
        $value     = $expression[SegmentExpression::INDEX_OPERAND_VALUE];

        $segment = $this->getSegmentByName($name);
        $sqlName = $segment['sqlSegment'];

        if ($matchType != SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY
            && $matchType != SegmentExpression::MATCH_IS_NULL_OR_EMPTY) {

            if (isset($segment['sqlFilterValue'])) {
                $value = call_user_func($segment['sqlFilterValue'], $value);
            }

            // apply presentation filter
            if (isset($segment['sqlFilter'])) {
                $value = call_user_func($segment['sqlFilter'], $value, $segment['sqlSegment'], $matchType, $name);

                if(is_null($value)) { // null is returned in TableLogAction::getIdActionFromSegment()
                    return array(null, $matchType, null);
                }

                // sqlFilter-callbacks might return arrays for more complex cases
                // e.g. see TableLogAction::getIdActionFromSegment()
                if (is_array($value) && isset($value['SQL'])) {
                    // Special case: returned value is a sub sql expression!
                    $matchType = SegmentExpression::MATCH_ACTIONS_CONTAINS;
                }
            }
        }

        return array($sqlName, $matchType, $value);
    }

    /**
     * Returns the segment condition.
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Returns a hash of the segment condition, or the empty string if the segment
     * condition is empty.
     *
     * @return string
     */
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
     * Extend an SQL query that aggregates data over one of the 'log_' tables with segment expressions.
     *
     * @param string $select The select clause. Should NOT include the **SELECT** just the columns, eg,
     *                       `'t1.col1 as col1, t2.col2 as col2'`.
     * @param array $from Array of table names (without prefix), eg, `array('log_visit', 'log_conversion')`.
     * @param false|string $where (optional) Where clause, eg, `'t1.col1 = ? AND t2.col2 = ?'`.
     * @param array|string $bind (optional) Bind parameters, eg, `array($col1Value, $col2Value)`.
     * @param false|string $orderBy (optional) Order by clause, eg, `"t1.col1 ASC"`.
     * @param false|string $groupBy (optional) Group by clause, eg, `"t2.col2"`.
     * @param int $limit Limit number of result to $limit
     * @param int $offset Specified the offset of the first row to return
     * @param int If set to value >= 1 then the Select query (and All inner queries) will be LIMIT'ed by this value.
     *              Use only when you're not aggregating or it will sample the data.
     * @return string The entire select query.
     */
    public function getSelectQuery($select, $from, $where = false, $bind = array(), $orderBy = false, $groupBy = false, $limit = 0, $offset = 0)
    {
        $segmentExpression = $this->segmentExpression;

        $limitAndOffset = null;
        if($limit > 0) {
            $limitAndOffset = (int) $offset . ', ' . (int) $limit;
        }

        return $this->segmentQueryBuilder->getSelectQueryString($segmentExpression, $select, $from, $where, $bind,
            $groupBy, $orderBy, $limitAndOffset);
    }

    /**
     * Returns the segment string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getString();
    }
}
