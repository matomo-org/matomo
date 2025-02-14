<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache as PiwikCache;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogQueryBuilder;
use Piwik\Plugins\SegmentEditor\SegmentEditor;
use Piwik\Segment\SegmentExpression;
use Piwik\Plugins\SegmentEditor\Model as SegmentEditorModel;
use Piwik\Segment\SegmentsList;

/**
 * Limits the set of visits Piwik uses when aggregating analytics data.
 *
 * A segment is a condition used to filter visits. They can, for example,
 * select visits that have a specific browser or come from a specific
 * country, or both.
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
     * @var string
     */
    protected $originalString = null;

    /**
     * @var array
     */
    protected $idSites = null;

    /**
     * @var Date
     */
    protected $startDate = null;

    /**
     * @var Date
     */
    protected $endDate = null;

    /**
     * @var LogQueryBuilder
     */
    private $segmentQueryBuilder;

    /**
     * @var bool
     */
    private $isSegmentEncoded;

    /**
     * @var Exception|null
     */
    private $missingDatesException = null;

    /**
     * Truncate the Segments to 8k
     */
    public const SEGMENT_TRUNCATE_LIMIT = 8192;

    public const CACHE_KEY = 'segmenthashes';
    public const SEGMENT_HAS_BUILT_CACHE_KEY = 'segmenthashbuilt';

    /**
     * Constructor.
     *
     * When using segments that contain a != or !@ condition on a non visit dimension (e.g. action, conversion, ...) it
     * is needed to use a subquery to get correct results. To avoid subqueries that fetch too many data it's required to
     * set a startDate and/or an endDate in this case. That date will be used to limit the subquery (along with possibly
     * given idSites). If no startDate and endDate is given for such a segment it will generate a query that directly
     * joins the according tables, but trigger a php warning as results might be incorrect.
     *
     * @param string $segmentCondition The segment condition, eg, `'browserCode=ff;countryCode=CA'`.
     * @param array $idSites The list of sites the segment will be used with. Some segments are
     *                       dependent on the site, such as goal segments.
     * @param Date|null $startDate start date used to limit subqueries
     * @param Date|null $endDate end date used to limit subqueries
     * @throws
     */
    public function __construct($segmentCondition, $idSites, ?Date $startDate = null, ?Date $endDate = null)
    {
        $this->segmentQueryBuilder = StaticContainer::get('Piwik\DataAccess\LogQueryBuilder');

        $segmentCondition = trim($segmentCondition ?: '');
        if (
            !SettingsPiwik::isSegmentationEnabled()
            && !empty($segmentCondition)
        ) {
            throw new Exception("The Super User has disabled the Segmentation feature.");
        }

        $this->originalString = $segmentCondition;

        if ($startDate instanceof Date) {
            $this->startDate = $startDate;
        }

        if ($endDate instanceof Date) {
            $this->endDate = $endDate;
        }

        // The segment expression can be urlencoded. Unfortunately, both the encoded and decoded versions
        // can usually be parsed successfully. To pick the right one, we try both and pick the one w/ more
        // successfully parsed subexpressions.
        $subexpressionsDecoded = 0;

        if (urldecode($segmentCondition) !== $segmentCondition) {
            try {
                $this->initializeSegment(urldecode($segmentCondition), $idSites);
                $subexpressionsDecoded = $this->segmentExpression->getSubExpressionCount();
            } catch (Exception $e) {
                // ignore
            }
        }

        $subexpressionsRaw = 0;
        try {
            $this->initializeSegment($segmentCondition, $idSites);
            $subexpressionsRaw = $this->segmentExpression->getSubExpressionCount();
        } catch (Exception $e) {
            // ignore
        }

        if ($subexpressionsRaw > $subexpressionsDecoded) {
            // segment initialized above
            $this->isSegmentEncoded = false;
        } else {
            $this->initializeSegment(urldecode($segmentCondition), $idSites);
            $this->isSegmentEncoded = true;
        }
    }

    /**
     * Checks if the provided segmentCondition is valid and available for the given idSites
     *
     * @param string $segmentCondition
     * @params array $idSites
     * @return bool
     * @api since Matomo 5.3.0
     */
    public static function isAvailable(string $segmentCondition, array $idSites): bool
    {
        try {
            new self($segmentCondition, $idSites);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the segment expression.
     * @return SegmentExpression
     * @api since Piwik 3.2.0
     */
    public function getSegmentExpression()
    {
        return $this->segmentExpression;
    }

    /**
     * @throws Exception
     */
    private function getAvailableSegments()
    {
        // start cache
        $cache = PiwikCache::getTransientCache();

        //covert cache id
        $cacheId = 'API.getSegmentsMetadata.' . SettingsPiwik::getPiwikInstanceId() . '.' . implode(",", $this->idSites);

        //fetch cache lockId
        $availableSegments = $cache->fetch($cacheId);
        // segment metadata
        if (empty($availableSegments)) {
            $availableSegments = Request::processRequest('API.getSegmentsMetadata', array(
              'idSites'                 => $this->idSites,
              '_hideImplementationData' => 0,
              'filter_limit'            => -1,
              'filter_offset'           => 0,
              '_showAllSegments'        => 1,
            ), []);

            // index by segment name
            $availableSegments = array_column($availableSegments, null, 'segment');

            // remove segments we don't have permission to use
            foreach ($availableSegments as $segment => $segmentInfo) {
                if (isset($segmentInfo['permission']) && $segmentInfo['permission'] != 1) {
                    $availableSegments[$segment] = null;
                }
            }

            $cache->save($cacheId, $availableSegments);
        }

        return $availableSegments;
    }

    private function getSegmentByName($name)
    {
        $segments = $this->getAvailableSegments();

        if (array_key_exists($name, $segments)) {
            if ($segments[$name] === null) {
                throw new NoAccessException("You do not have enough permission to access the segment " . $name);
            }

            return $segments[$name];
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

        if (empty($idSites)) {
            $idSites = [];
        } elseif (!is_array($idSites)) {
            $idSites = [$idSites];
        }
        $this->idSites = $idSites;
        $segment = new SegmentExpression($string);
        $this->segmentExpression = $segment;

        // parse segments
        $expressions = $segment->parseSubExpressions();
        $expressions = $this->getExpressionsWithUnionsResolved($expressions);
        $expressions = $this->mergeSubqueryExpressionsInTree($expressions);

        // convert segments name to sql segment
        // check that user is allowed to view this segment
        // and apply a filter to the value to match if necessary (to map DB fields format)

        $cleanedExpressions = array_map(function (array $orExpressions) {
            return array_map(function (array $operand) {
                return $this->getCleanedExpression($operand);
            }, $orExpressions);
        }, $expressions);

        $segment->setSubExpressionsAfterCleanup($cleanedExpressions);
    }

    private function getExpressionsWithUnionsResolved(array $expressions): array
    {
        $expressionsWithUnions = array_map(function ($orExpressions) {
            $mappedOrExpressions = [];
            foreach ($orExpressions as $operand) {
                $name = $operand[SegmentExpression::INDEX_OPERAND_NAME];

                $availableSegment = $this->getSegmentByName($name);

                // We leave segments using !@ and != operands untouched for segments not on log_visit table as they will be build using a subquery
                if (
                    !$this->doesSegmentNeedSubquery($operand[SegmentExpression::INDEX_OPERAND_OPERATOR], $name)
                    && !empty($availableSegment['unionOfSegments'])
                ) {
                    foreach ($availableSegment['unionOfSegments'] as $segmentNameOfUnion) {
                        $operand[SegmentExpression::INDEX_OPERAND_NAME] = $segmentNameOfUnion;
                        $mappedOrExpressions[] = $operand;
                    }
                } else {
                    $mappedOrExpressions[] = $operand;
                }
            }
            return $mappedOrExpressions;
        }, $expressions);

        return $expressionsWithUnions;
    }

    private function isVisitSegment($name)
    {
        $availableSegment = $this->getSegmentByName($name);

        if (!empty($availableSegment['unionOfSegments'])) {
            foreach ($availableSegment['unionOfSegments'] as $segmentNameOfUnion) {
                $unionSegment = $this->getSegmentByName($segmentNameOfUnion);
                if (strpos($unionSegment['sqlSegment'], 'log_visit.') === 0) {
                    return true;
                }
            }
        } elseif (strpos($availableSegment['sqlSegment'], 'log_visit.') === 0) {
            return true;
        }

        return false;
    }

    private function doesSegmentNeedSubquery($operator, $segmentName)
    {
        $requiresSubQuery = in_array($operator, [
                SegmentExpression::MATCH_DOES_NOT_CONTAIN,
                SegmentExpression::MATCH_NOT_EQUAL
            ]) && !$this->isVisitSegment($segmentName);

        if ($requiresSubQuery && empty($this->startDate) && empty($this->endDate)) {
            if (Development::isEnabled()) {
                $this->missingDatesException = new Exception();
            }
            return false;
        }

        return $requiresSubQuery;
    }

    private function getInvertedOperatorForSubQuery($operator)
    {
        if ($operator === SegmentExpression::MATCH_DOES_NOT_CONTAIN) {
            return SegmentExpression::MATCH_CONTAINS;
        } elseif ($operator === SegmentExpression::MATCH_NOT_EQUAL) {
            return SegmentExpression::MATCH_EQUAL;
        }

        throw new Exception("Operator not support for subqueries");
    }

    /**
     * Returns `true` if the segment is empty, `false` if otherwise.
     */
    public function isEmpty()
    {
        return $this->segmentExpression->isEmpty();
    }

    /**
     * Detects whether the Piwik instance is configured to be able to archive this segment. It checks whether the segment
     * will be either archived via browser or cli archiving. It does not check if the segment has been archived. If you
     * want to know whether the segment has been archived, the actual report data needs to be requested.
     *
     * This method does not take any date/period into consideration. Meaning a Piwik instance might be able to archive
     * this segment in general, but not for a certain period if eg the archiving of range dates is disabled.
     *
     * @return bool
     */
    public function willBeArchived()
    {
        if ($this->isEmpty()) {
            return true;
        }

        $idSites = $this->idSites;

        return Rules::isRequestAuthorizedToArchive()
            || Rules::isBrowserArchivingAvailableForSegments()
            || Rules::isSegmentPreProcessed($idSites, $this);
    }

    protected function getCleanedExpression(array $expression): array
    {
        $name      = $expression[SegmentExpression::INDEX_OPERAND_NAME];
        $matchType = $expression[SegmentExpression::INDEX_OPERAND_OPERATOR];
        $value     = $expression[SegmentExpression::INDEX_OPERAND_VALUE];

        if (empty($this->idSites)) {
            $segmentsList = SegmentsList::get();
        } else {
            $segmentsList = Context::changeIdSite(implode(',', $this->idSites), function () {
                return SegmentsList::get();
            });
        }
        $segmentObject = $segmentsList->getSegment($name);

        $sqlName = $segmentObject ? $segmentObject->getSqlSegment() : null;

        $joinTable = null;
        if (
            $segmentObject
            && $segmentObject->dimension
            && $segmentObject->dimension->getDbColumnJoin()
        ) {
            $join = $segmentObject->dimension->getDbColumnJoin();
            $dbDiscriminator = $segmentObject->dimension->getDbDiscriminator();

            // we append alias since an archive query may add the table with a different join. we could eg add $table_$segmentName but
            // then we would join an extra table per segment when we ideally want to join each table only once. However, we still need
            // to see which table/column it joins to join it accurately each table extra if the same table is joined with different columns;
            $tableAlias = $join->getTable() . '_segment_' . str_replace('.', '', $sqlName ?: '');
            $joinTable = [
                'table' => $join->getTable(),
                'tableAlias' => $tableAlias,
                'field' => $tableAlias . '.' . $join->getTargetColumn(),
                'joinOn' => $sqlName . ' = ' . $tableAlias . '.' . $join->getColumn(),
            ];

            if ($dbDiscriminator) {
                $joinTable['discriminator'] = $tableAlias . '.' . $dbDiscriminator->getColumn() . ' = \'' .  $dbDiscriminator->getValue() . '\'';
            }
        }

        if ($matchType == SegmentExpression::MATCH_IDVISIT_NOT_IN) {
            $segmentObj = new Segment($value, $this->idSites, $this->startDate, $this->endDate);

            $select = 'log_visit.idvisit';
            $from = 'log_visit';
            $datetimeField = 'visit_last_action_time';
            $where = [];
            $bind = [];
            if (!empty($this->idSites)) {
                $where[] = "$from.idsite IN (" . Common::getSqlStringFieldsArray($this->idSites) . ")";
                $bind = $this->idSites;
            }
            if ($this->startDate instanceof Date) {
                $where[] = "$from.$datetimeField >= ?";
                $bind[] = $this->startDate->toString(Date::DATE_TIME_FORMAT);
            }
            if ($this->endDate instanceof Date) {
                $where[] = "$from.$datetimeField <= ?";
                $bind[] = $this->endDate->toString(Date::DATE_TIME_FORMAT);
            }

            $logQueryBuilder = StaticContainer::get('Piwik\DataAccess\LogQueryBuilder');
            $forceGroupByBackup = $logQueryBuilder->getForcedInnerGroupBySubselect();
            $logQueryBuilder->forceInnerGroupBySubselect(LogQueryBuilder::FORCE_INNER_GROUP_BY_NO_SUBSELECT);
            $query = $segmentObj->getSelectQuery($select, $from, implode(' AND ', $where), $bind);
            $logQueryBuilder->forceInnerGroupBySubselect($forceGroupByBackup);

            return ['log_visit.idvisit', SegmentExpression::MATCH_ACTIONS_NOT_CONTAINS, $query, null, null];
        }

        if (empty($segmentObject)) {
            throw new Exception("Segment '$name' is not a supported segment.");
        }

        $segment = $this->getSegmentByName($name);

        if (
            $matchType != SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY
            && $matchType != SegmentExpression::MATCH_IS_NULL_OR_EMPTY
        ) {
            if (isset($segment['sqlFilterValue'])) {
                $value = call_user_func($segment['sqlFilterValue'], $value, $segment['sqlSegment']);
            }

            // apply presentation filter
            if (isset($segment['sqlFilter'])) {
                $value = call_user_func($segment['sqlFilter'], $value, $segment['sqlSegment'], $matchType, $name);

                if (is_null($value)) { // null is returned in TableLogAction::getIdActionFromSegment()
                    return array(null, $matchType, null, null, $segment);
                }

                // sqlFilter-callbacks might return arrays for more complex cases
                // e.g. see TableLogAction::getIdActionFromSegment()
                if (is_array($value) && isset($value['SQL'])) {
                    // Special case: returned value is a sub sql expression!
                    $matchType = SegmentExpression::MATCH_ACTIONS_CONTAINS;
                    $joinTable = null;
                }

                if (is_array($value) && isset($value['value'])) {
                    $value = $value['value'];
                    $joinTable = !empty($value['joinTable']);
                }
            }
        }

        return array($sqlName, $matchType, $value, $joinTable, $segment);
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
        return self::getSegmentHash($this->string);
    }

    public static function getSegmentHash($definition)
    {
        $cache = Cache::getEagerCache();
        $cacheKey = self::CACHE_KEY . md5($definition);

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $defaultHash = md5(urldecode($definition));

        // if the cache for segments already built, but this segment was not found,
        // we return the default segment, this can be a segment from url or
        // something like "visitorType==new"
        if ($cache->contains(self::SEGMENT_HAS_BUILT_CACHE_KEY)) {
            return $defaultHash;
        }

        // the segment hash is not built yet, let's do it
        $model = new SegmentEditorModel();
        $segments = $model->getAllSegmentsAndIgnoreVisibility();

        foreach ($segments as $segment) {
            $cacheKeyTemp = self::CACHE_KEY . md5($segment['definition']);
            $cache->save($cacheKeyTemp, $segment['hash']);

            $cacheKeyTemp = self::CACHE_KEY . md5(urldecode($segment['definition']));
            $cache->save($cacheKeyTemp, $segment['hash']);

            $cacheKeyTemp = self::CACHE_KEY . md5(urlencode($segment['definition']));
            $cache->save($cacheKeyTemp, $segment['hash']);
        }

        $cache->save(self::SEGMENT_HAS_BUILT_CACHE_KEY, true);

        // if we found the segment, return it's hash, but maybe this
        // segment is not stored in the db, return the default
        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        return $defaultHash;
    }

    /**
     * Extend an SQL query that aggregates data over one of the 'log_' tables with segment expressions.
     *
     * @param string $select The select clause. Should NOT include the **SELECT** just the columns, eg,
     *                       `'t1.col1 as col1, t2.col2 as col2'`.
     * @param array|string $from Array of table names (without prefix), eg, `array('log_visit', 'log_conversion')`.
     * @param false|string $where (optional) Where clause, eg, `'t1.col1 = ? AND t2.col2 = ?'`.
     * @param array|string $bind (optional) Bind parameters, eg, `array($col1Value, $col2Value)`.
     * @param false|string $orderBy (optional) Order by clause, eg, `"t1.col1 ASC"`.
     * @param false|string $groupBy (optional) Group by clause, eg, `"t2.col2"`.
     * @param int $limit Limit number of result to $limit
     * @param int $offset Specified the offset of the first row to return
     * @param bool $forceGroupBy Force the group by and not using a subquery. Note: This may make the query slower see https://github.com/matomo-org/matomo/issues/9200#issuecomment-183641293
     *                           A $groupBy value needs to be set for this to work.
     * @param int If set to value >= 1 then the Select query (and All inner queries) will be LIMIT'ed by this value.
     *              Use only when you're not aggregating or it will sample the data.
     * @return array The entire select query.
     */
    public function getSelectQuery($select, $from, $where = false, $bind = array(), $orderBy = false, $groupBy = false, $limit = 0, $offset = 0, $forceGroupBy = false)
    {
        if (Development::isEnabled() && !empty($this->missingDatesException)) {
            $e = new Exception();
            Log::warning('Avoiding segment subquery due to missing start date and/or an end date. '
                        . 'Please ensure a start date and/or end date is set when initializing segment: '
                        . "\n\nCreation stacktrace:\n" . $this->missingDatesException->getTraceAsString()
                        . "\n\nUsage stacktrace:\n" . $e->getTraceAsString());
        }

        $segmentExpression = $this->segmentExpression;

        $limitAndOffset = null;
        if ($limit > 0) {
            $limitAndOffset = (int) $offset . ', ' . (int) $limit;
        }

        try {
            if ($forceGroupBy && $groupBy) {
                $this->segmentQueryBuilder->forceInnerGroupBySubselect(LogQueryBuilder::FORCE_INNER_GROUP_BY_NO_SUBSELECT);
            }
            $result = $this->segmentQueryBuilder->getSelectQueryString(
                $segmentExpression,
                $select,
                $from,
                $where,
                $bind,
                $groupBy,
                $orderBy,
                $limitAndOffset
            );
        } catch (Exception $e) {
            if ($forceGroupBy && $groupBy) {
                $this->segmentQueryBuilder->forceInnerGroupBySubselect('');
            }
            throw $e;
        }

        if ($forceGroupBy && $groupBy) {
            $this->segmentQueryBuilder->forceInnerGroupBySubselect('');
        }
        return $result;
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

    /**
     * Combines this segment with another segment condition, if the segment condition is not already
     * in the segment.
     *
     * The combination is naive in that it does not take order of operations into account.
     *
     * @param string $segment
     * @param string $operator The operator to use. Should be either SegmentExpression::AND_DELIMITER
     *                         or SegmentExpression::OR_DELIMITER.
     * @param string $segmentCondition The segment condition to add.
     * @return string
     * @throws Exception
     */
    public static function combine($segment, $operator, $segmentCondition)
    {
        if (empty($segment)) {
            return $segmentCondition;
        }

        if (
            empty($segmentCondition)
            || self::containsCondition($segment, $operator, $segmentCondition)
        ) {
            return $segment;
        }

        return $segment . $operator . $segmentCondition;
    }

    private static function containsCondition($segment, $operator, $segmentCondition)
    {
        // check when segment/condition are of same encoding
        return strpos($segment, $operator . $segmentCondition) !== false
            || strpos($segment, $segmentCondition . $operator) !== false

            // check when both operator & condition are urlencoded in $segment
            || strpos($segment, urlencode($operator . $segmentCondition)) !== false
            || strpos($segment, urlencode($segmentCondition . $operator)) !== false

            // check when operator is not urlencoded, but condition is in $segment
            || strpos($segment, $operator . urlencode($segmentCondition)) !== false
            || strpos($segment, urlencode($segmentCondition) . $operator) !== false

            // check when segment condition is urlencoded & $segment isn't
            || strpos($segment, $operator . urldecode($segmentCondition)) !== false
            || strpos($segment, urldecode($segmentCondition) . $operator) !== false

            || $segment === $segmentCondition
            || $segment === urlencode($segmentCondition)
            || $segment === urldecode($segmentCondition);
    }

    public function getStoredSegmentName($idSite)
    {
        $segment = $this->getString();
        if (empty($segment)) {
            return Piwik::translate('SegmentEditor_DefaultAllVisits');
        }

        $availableSegments = SegmentEditor::getAllSegmentsForSite($idSite);

        $foundStoredSegment = null;
        foreach ($availableSegments as $storedSegment) {
            if (
                $storedSegment['definition'] == $segment
                || $storedSegment['definition'] == urldecode($segment)
                || $storedSegment['definition'] == urlencode($segment)

                || $storedSegment['definition'] == $this->originalString
                || $storedSegment['definition'] == urldecode($this->originalString)
                || $storedSegment['definition'] == urlencode($this->originalString)
            ) {
                $foundStoredSegment = $storedSegment;
            }
        }

        if (isset($foundStoredSegment)) {
            return $foundStoredSegment['name'];
        }

        return $this->isSegmentEncoded ? urldecode($segment) : $segment;
    }

    public function getOriginalString()
    {
        return $this->originalString;
    }

    /**
     * Build subqueries for segments that are not on log_visit table but use !@ or != as operator
     * This is required to ensure segments like actionUrl!@value really do not include any visit having an action containing `value`
     *
     * Adjacent segment conditions that both require subqueries are merged here into single NOT IN sql subqueries,
     * which improves performance.
     *
     * Subquery segment conditions that are next to each other in a chain of OR's are merged together and
     * subquery segment conditions that are next to each other in a chain of AND's, but are also alone and not
     * a part of an OR expression, are merged.
     *
     * The operands for the merged conditions in the parsed intermediate structure use the special MATCH_IDVISIT_NOT_IN
     * operator.
     */
    private function mergeSubqueryExpressionsInTree(array $tree): array
    {
        $andExpressions = array_map(function ($orExpressions) {
            return $this->mergeSubqueryExpressionsInExpr($orExpressions, false);
        }, $tree);

        $mappedAndExpressions = $this->mergeSubqueryExpressionsInExpr($andExpressions, true);

        return $mappedAndExpressions;
    }

    private function mergeSubqueryExpressionsInExpr(array $expressions, bool $isAndChain): array
    {
        // nothing to merge if there's only one expression
        if (!$isAndChain && count($expressions) <= 1) {
            return $expressions;
        }

        $mappedExpressions = [];
        $idvisitNotInExpressions = [];

        foreach ($expressions as $childExpressionsOrOperand) {
            // if this is an AND chain w/ more than one sub-expression being OR-ed together, we can't do anything about the NOT IN subqueries there
            if (
                $isAndChain
                && count($childExpressionsOrOperand) > 1
            ) {
                $mappedExpressions[] = $childExpressionsOrOperand;
                continue;
            }

            $operand = $isAndChain ? $childExpressionsOrOperand[0] : $childExpressionsOrOperand;

            $name = $operand[SegmentExpression::INDEX_OPERAND_NAME];
            $matchType = $operand[SegmentExpression::INDEX_OPERAND_OPERATOR];
            $value = $operand[SegmentExpression::INDEX_OPERAND_VALUE];

            if (!$this->doesSegmentNeedSubquery($matchType, $name)) {
                $mappedExpressions[] = $childExpressionsOrOperand;
                continue;
            }

            // if the segment is pageTitle!=def, then NOT IN sql will have to be idvisit NOT IN (... WHERE pageTitle == def),
            // so we must invert the operator before we create a MATCH_IDVISIT_NOT_IN operand below
            $operator = $this->getInvertedOperatorForSubQuery($matchType);
            $idvisitNotInExpressions[] = $name . $operator . $this->escapeSegmentValue($value);
        }

        if (!empty($idvisitNotInExpressions)) {
            $newOperand = [
                SegmentExpression::INDEX_OPERAND_NAME => null,
                SegmentExpression::INDEX_OPERAND_OPERATOR => SegmentExpression::MATCH_IDVISIT_NOT_IN,
                SegmentExpression::INDEX_OPERAND_VALUE => implode($isAndChain ? SegmentExpression::OR_DELIMITER : SegmentExpression::AND_DELIMITER, $idvisitNotInExpressions),
            ];

            $mappedExpressions[] = $isAndChain ? [$newOperand] : $newOperand;
        }

        return $mappedExpressions;
    }


    /**
     * Escapes segment expression delimiters in a segment value with a backslash if not already done.
     */
    private function escapeSegmentValue(string $value): string
    {
        $delimiterPattern = SegmentExpression::AND_DELIMITER . SegmentExpression::OR_DELIMITER;
        $pattern = '/((?<!\\\)[' . preg_quote($delimiterPattern) . '])/';

        return preg_replace($pattern, '\\\$1', $value);
    }
}
