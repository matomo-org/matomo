<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\API\DataTable\MergeDataTables;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;

/**
 * VisitFrequency API lets you access a list of metrics related to Returning Visitors.
 * @method static \Piwik\Plugins\VisitFrequency\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    // visitorType==returning,visitorType==returningCustomer
    const RETURNING_VISITOR_SEGMENT = "visitorType%3D%3Dreturning%2CvisitorType%3D%3DreturningCustomer";
    const RETURNING_COLUMN_SUFFIX = "_returning";

    const NEW_VISITOR_SEGMENT = 'visitorType%3D%3Dnew';
    const NEW_COLUMN_SUFFIX = "_new";

    /**
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @param bool|array $columns
     * @return mixed
     */
    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $visitTypes = array(
            self::NEW_COLUMN_SUFFIX => self::NEW_VISITOR_SEGMENT,
            self::RETURNING_COLUMN_SUFFIX => self::RETURNING_VISITOR_SEGMENT
        );

        $columns = Piwik::getArrayFromApiParameter($columns);

        /** @var \Piwik\DataTable\DataTableInterface $resultSet */
        if ($idSite === 'all') {
            $resultSet = new DataTable\Map();
            $resultSet->setKeyName('idSite');
        } else if (Period::isMultiplePeriod($date, $period)) {
            $resultSet = new DataTable\Map();
            $resultSet->setKeyName('period');
        } else {
            $resultSet = new DataTable\Simple();
        }

        foreach ($visitTypes as $columnSuffix => $visitorTypeSegment) {
            $modifiedSegment = Segment::combine($segment, SegmentExpression::AND_DELIMITER, $visitorTypeSegment);

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
                'format_metrics' => 0
            );

            /** @var \Piwik\DataTable\Map $response */
            $response = Request::processRequest('VisitsSummary.get', $params);
            $this->prefixColumns($response, $period, $columnSuffix);

            if ($resultSet === null) {
                $resultSet = $response;
            } else {
                $merger = new MergeDataTables();
                $merger->mergeDataTables($resultSet, $response);
            }
        }

        return $resultSet;
    }

    protected function unprefixColumns(array $requestedColumns, $suffix)
    {
        $result = array();
        foreach ($requestedColumns as $column) {
            if (strpos($column, $suffix) !== false) {
                $result[] = str_replace($suffix, '', $column);
            }
        }
        return $result;
    }

    protected function prefixColumns($table, $period, $suffix)
    {
        $rename = array();
        foreach ($table->getColumns() as $oldColumn) {
            $rename[$oldColumn] = $oldColumn . $suffix;
        }
        $table->filter('ReplaceColumnNames', array($rename));
    }
}
