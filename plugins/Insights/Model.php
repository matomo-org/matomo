<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\DataTable;
use Piwik\Date;
use Piwik\Log;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\API\ProcessedReport;
use Piwik\API\Request as ApiRequest;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;

/**
 * API for plugin Insights
 *
 * @method static \Piwik\Plugins\Insights\API getInstance()
 */
class Model
{

    public function requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment)
    {
        $report = $this->getReportByUniqueId($idSite, $reportUniqueId);

        $params = array(
            'method' => $report['module'] . '.' . $report['action'],
            'format' => 'original',
            'idSite' => $idSite,
            'period' => $period,
            'date'   => $date,
            'filter_limit' => 1000,
            'showColumns'  => $metric
        );

        if (!empty($segment)) {
            $params['segment'] = $segment;
        }

        if (!empty($report['parameters']) && is_array($report['parameters'])) {
            $params = array_merge($params, $report['parameters']);
        }

        $request = new ApiRequest($params);
        $table   = $request->process();

        return $table;
    }

    public function getLastDate($date, $period, $comparedToXPeriods)
    {
        $pastDate = Range::getDateXPeriodsAgo(abs($comparedToXPeriods), $date, $period);

        if (empty($pastDate[0])) {
            throw new \Exception('Not possible to compare this date/period combination');
        }

        return $pastDate[0];
    }

    public function getRelevantTotalValue(DataTable $currentReport, $idSite, $period, $date, $metric)
    {
        $totalMetric = $this->getMetricTotalValue($currentReport, $metric);
        $totalValue  = $this->getTotalValue($idSite, $period, $date, $metric);

        if ($totalMetric > $totalValue) {
            return $totalMetric;
        }

        if (($totalMetric * 2) < $totalValue) {
            return $totalMetric;
        }

        return $totalValue;
    }

    public function getTotalValue($idSite, $period, $date, $metric)
    {
        $visits   = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, false, array($metric));
        $firstRow = $visits->getFirstRow();

        if (empty($firstRow)) {
            return 0;
        }

        $totalValue = $firstRow->getColumn($metric);

        return (int) $totalValue;
    }

    public function getMetricTotalValue(DataTable $currentReport, $metric)
    {
        $totals = $currentReport->getMetadata('totals');

        if (!empty($totals[$metric])) {
            $totalValue = (int) $totals[$metric];
        } else {
            $totalValue = 0;
        }

        return $totalValue;
    }

    public function getReportByUniqueId($idSite, $reportUniqueId)
    {
        $processedReport = new ProcessedReport();
        $report = $processedReport->getReportMetadataByUniqueId($idSite, $reportUniqueId);

        return $report;
    }
}
