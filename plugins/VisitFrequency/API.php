<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitFrequency;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\API\DataTable\MergeDataTables;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Site;

/**
 * VisitFrequency API lets you access a list of metrics related to Returning Visitors.
 *
 * @method static \Piwik\Plugins\VisitFrequency\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    // visitorType==returning,visitorType==returningCustomer
    public const RETURNING_VISITOR_SEGMENT = "visitorType%3D%3Dreturning%2CvisitorType%3D%3DreturningCustomer";
    public const RETURNING_COLUMN_SUFFIX = "_returning";

    public const NEW_VISITOR_SEGMENT = 'visitorType%3D%3Dnew';
    public const NEW_COLUMN_SUFFIX = "_new";

    protected $autoSanitizeInputParams = false;

    /**
     * Returns visit summary metrics split between new and returning visitors.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to append to the visitor type filters.
     * @param list<string>|string|null $columns Metrics to include in the response.
     *                                          Accepts a comma-separated list or array of metric names.
     * @return DataTable\DataTableInterface Visit summary metrics with `_new` and `_returning` column suffixes.
     */
    public function get($idSite, string $period, string $date, ?string $segment = null, $columns = null): DataTable\DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        $visitTypes = array(
            self::NEW_COLUMN_SUFFIX => self::NEW_VISITOR_SEGMENT,
            self::RETURNING_COLUMN_SUFFIX => self::RETURNING_VISITOR_SEGMENT,
        );

        $columns = Piwik::getArrayFromApiParameter($columns);

        if ($idSite === 'all' || count(Site::getIdSitesFromIdSitesString($idSite, false, true)) > 1) {
            $resultSet = new DataTable\Map();
            $resultSet->setKeyName('idSite');
        } elseif (Period::isMultiplePeriod($date, $period)) {
            $resultSet = new DataTable\Map();
            $resultSet->setKeyName('period');
        } else {
            $resultSet = new DataTable\Simple();
        }

        foreach ($visitTypes as $columnSuffix => $visitorTypeSegment) {
            $modifiedSegment = Segment::combine($segment ?? '', SegmentExpression::AND_DELIMITER, $visitorTypeSegment);

            $columnsForVisitType = empty($columns) ? array() : $this->unprefixColumns($columns, $columnSuffix);

            // Only make the API call if either $columns is empty (i.e. no list of columns was passed in, so we
            // should fetch all columns) or if one of the columns that was passed in is for this visitor type
            if (!empty($columns) && empty($columnsForVisitType)) {
                continue;
            }

            $params = array(
                'idSite'    => $idSite,
                'period'    => $period,
                'date'      => $date,
                'segment'   => $modifiedSegment,
                'columns'   => implode(',', $columnsForVisitType),
                'format'    => 'original',
                'format_metrics' => 0,
            );

            /** @var DataTable\Map|DataTable $response */
            $response = Request::processRequest('VisitsSummary.get', $params);
            $this->prefixColumns($response, $columnSuffix);

            $merger = new MergeDataTables();
            $merger->mergeDataTables($resultSet, $response);
        }

        return $resultSet;
    }

    /**
     * @param string[] $requestedColumns
     * @return string[]
     */
    protected function unprefixColumns(array $requestedColumns, string $suffix): array
    {
        $result = array();
        foreach ($requestedColumns as $column) {
            if (strpos($column, $suffix) !== false) {
                $result[] = str_replace($suffix, '', $column);
            }
        }
        return $result;
    }

    protected function prefixColumns(DataTable\DataTableInterface $table, string $suffix): void
    {
        $rename = array();
        foreach ($table->getColumns() as $oldColumn) {
            $rename[$oldColumn] = $oldColumn . $suffix;
        }
        $table->filter('ReplaceColumnNames', [$rename]);
    }
}
