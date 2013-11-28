<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitFrequency
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugins\VisitsSummary\API as APIVisitsSummary;
use Piwik\SegmentExpression;

/**
 * VisitFrequency API lets you access a list of metrics related to Returning Visitors.
 * @package VisitFrequency
 * @method static \Piwik\Plugins\VisitFrequency\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const RETURNING_VISITOR_SEGMENT = "visitorType==returning,visitorType==returningCustomer";
    const COLUMN_SUFFIX = "_returning";

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
        $segment = $this->appendReturningVisitorSegment($segment);

        $this->unprefixColumns($columns);
        $params = array(
            'idSite'    => $idSite,
            'period'    => $period,
            'date'      => $date,
            'segment'   => $segment,
            'columns'   => implode(',', $columns),
            'format'    => 'original',
            'serialize' => 0 // tests set this to 1
        );
        $table = Request::processRequest('VisitsSummary.get', $params);
        $this->prefixColumns($table, $period);
        return $table;
    }

    protected function appendReturningVisitorSegment($segment)
    {
        if (empty($segment)) {
            $segment = '';
        } else {
            $segment .= SegmentExpression::AND_DELIMITER;
        }
        $segment .= self::RETURNING_VISITOR_SEGMENT;
        return $segment;
    }

    protected function unprefixColumns(&$columns)
    {
        $columns = Piwik::getArrayFromApiParameter($columns);
        foreach ($columns as &$column) {
            $column = str_replace(self::COLUMN_SUFFIX, "", $column);
        }
    }

    protected function prefixColumns($table, $period)
    {
        $rename = array();
        foreach (APIVisitsSummary::getInstance()->getColumns($period) as $oldColumn) {
            $rename[$oldColumn] = $oldColumn . self::COLUMN_SUFFIX;
        }
        $table->filter('ReplaceColumnNames', array($rename));
    }
}
