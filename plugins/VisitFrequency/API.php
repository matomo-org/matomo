<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitFrequency
 */

/**
 * VisitFrequency API lets you access a list of metrics related to Returning Visitors.
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency_API
{
    const RETURNING_VISITOR_SEGMENT = "visitorType==returning";
    const COLUMN_SUFFIX = "_returning";

    static private $instance = null;
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

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
        $table = Piwik_API_Request::processRequest('VisitsSummary.get', $params);
        $this->prefixColumns($table, $period);
        return $table;
    }

    protected function appendReturningVisitorSegment($segment)
    {
        if (empty($segment)) {
            $segment = '';
        } else {
            $segment .= Piwik_SegmentExpression::AND_DELIMITER;
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
        foreach (Piwik_VisitsSummary_API::getInstance()->getColumns($period) as $oldColumn) {
            $rename[$oldColumn] = $oldColumn . self::COLUMN_SUFFIX;
        }
        $table->filter('ReplaceColumnNames', array($rename));
    }
}
