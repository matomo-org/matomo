<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitFrequency;

use Piwik\ArchiveProcessor;
use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\SegmentExpression;
use Piwik\Plugins\VisitsSummary\API as APIVisitsSummary;
use Piwik\SettingsPiwik;

/**
 * Introduced to provide backwards compatibility for pre-2.0 data. Uses a segment to archive
 * data for day periods and aggregates this data for non-day periods.
 *
 * We normally would want to just forward requests to the VisitsSummary API w/ the correctly
 * modified segment, but in order to combine pre-2.0 data with post-2.0 data, there has
 * to be a VisitFrequency Archiver. Otherwise, the VisitsSummary metrics archiving will
 * be called, and the pre-2.0 VisitFrequency data (which is not retrieved by VisitsSummary) will
 * be ignored.
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    // visitorType==returning,visitorType==returningCustomer
    const RETURNING_VISITOR_SEGMENT = "visitorType%3D%3Dreturning%2CvisitorType%3D%3DreturningCustomer";
    const COLUMN_SUFFIX = "_returning";

    public static $visitFrequencyPeriodMetrics = array(
        'nb_visits_returning',
        'nb_actions_returning',
        'max_actions_returning',
        'sum_visit_length_returning',
        'bounce_count_returning',
        'nb_visits_converted_returning'
    );

    public function aggregateDayReport()
    {
        $this->callVisitsSummaryApiAndArchive();
    }

    public function aggregateMultipleReports()
    {
        if (SettingsPiwik::isUniqueVisitorsEnabled($this->getProcessor()->getParams()->getPeriod()->getLabel())) {
            // NOTE: this call CANNOT be after aggregateNumericMetrics. for some reason, it breaks the archiver.
            $this->callVisitsSummaryApiAndArchive(array('nb_uniq_visitors'));
        }

        $this->getProcessor()->aggregateNumericMetrics(self::$visitFrequencyPeriodMetrics);
    }

    private function callVisitsSummaryApiAndArchive($columns = false)
    {
        $archiveParams = $this->getProcessor()->getParams();
        $periodLabel = $archiveParams->getPeriod()->getLabel();

        $params = array(
            'idSite'     => $archiveParams->getSite()->getId(),
            'period'     => $periodLabel,
            'date'       => $archiveParams->getPeriod()->getDateStart()->toString(),
            'segment'    => $this->appendReturningVisitorSegment($archiveParams->getSegment()->getString()),
            'format'     => 'original',
            'serialize'  => 0 // make sure we don't serialize (in case serialize is in the query parameters)
        );
        if ($columns) {
            $params['columns'] = implode(",", $columns);
        }

        $table = Request::processRequest('VisitsSummary.get', $params);
        $this->suffixColumns($table, $periodLabel);

        if ($table->getRowsCount() > 0) {
            $this->getProcessor()->insertNumericRecords($table->getFirstRow()->getColumns());
        }
    }

    protected function appendReturningVisitorSegment($segment)
    {
        if (empty($segment)) {
            $segment = '';
        } else {
            $segment .= urlencode(SegmentExpression::AND_DELIMITER);
        }
        $segment .= self::RETURNING_VISITOR_SEGMENT;
        return $segment;
    }

    protected function unsuffixColumns(&$columns)
    {
        $columns = Piwik::getArrayFromApiParameter($columns);
        foreach ($columns as &$column) {
            $column = str_replace(self::COLUMN_SUFFIX, "", $column);
        }
    }

    protected function suffixColumns($table, $period)
    {
        $rename = array();
        foreach (APIVisitsSummary::getInstance()->getColumns($period) as $oldColumn) {
            $rename[$oldColumn] = $oldColumn . self::COLUMN_SUFFIX;
        }
        $table->filter('ReplaceColumnNames', array($rename));
    }
}