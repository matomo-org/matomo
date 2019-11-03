<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\API\Request;
use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\API\DataTable\MergeDataTables;
use Piwik\Plugins\VisitsSummary\API as APIVisitsSummary;
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
    const NEW_VISITOR_SUFFIX = "_new";

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
            self::NEW_VISITOR_SUFFIX => self::NEW_VISITOR_SEGMENT,
            self::RETURNING_COLUMN_SUFFIX => self::RETURNING_VISITOR_SEGMENT
        );

        foreach (array_keys($visitTypes) as $columnSuffix) {
            $this->unprefixColumns($columns, $columnSuffix);
        }

        /** @var \Piwik\DataTable\Map $resultSet */
        $resultSet = null;

        foreach ($visitTypes as $columnSuffix => $visitorTypeSegment) {
            $modifiedSegment = $this->appendVisitorTypeSegment($segment, $visitorTypeSegment);

            $params = array(
                'idSite'    => $idSite,
                'period'    => $period,
                'date'      => $date,
                'segment'   => $modifiedSegment,
                'columns'   => implode(',', $columns),
                'format'    => 'original',
                'format_metrics' => 0
            );

            /** @var \Piwik\DataTable\Map $response */
            $response = Request::processRequest('VisitsSummary.get', $params);
            $this->prefixColumns($response, $period, $columnSuffix);

            if ($resultSet == null) {
                $resultSet = $response;
            } else {
                $merger = new MergeDataTables();
                $merger->mergeDataTables($resultSet, $response);
            }
        }

        return $resultSet;
    }

    protected function appendVisitorTypeSegment($segment, $toAppend)
    {
        if (empty($segment)) {
            $segment = '';
        } else {
            $segment .= urlencode(SegmentExpression::AND_DELIMITER);
        }
        $segment .= $toAppend;
        return $segment;
    }

    protected function unprefixColumns(&$columns, $suffix)
    {
        $columns = Piwik::getArrayFromApiParameter($columns);
        foreach ($columns as &$column) {
            $column = str_replace($suffix, "", $column);
        }
    }

    protected function prefixColumns($table, $period, $suffix)
    {
        $rename = array();
        foreach (APIVisitsSummary::getInstance()->getColumns($period) as $oldColumn) {
            $rename[$oldColumn] = $oldColumn . $suffix;
        }
        $table->filter('ReplaceColumnNames', array($rename));
    }
}