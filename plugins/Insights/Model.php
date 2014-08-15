<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\API\Request as ApiRequest;
use Piwik\DataTable;
use Piwik\Period\Range;
use Piwik\Plugins\API\ProcessedReport;
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

    /**
     * Returns either the $totalValue (eg 5500 visits) or the total value of the report
     * (eg 2500 visits of 5500 total visits as for instance only 2500 visits came to the website using a search engine).
     *
     * If the metric total (2500) is much lower than $totalValue, the metric total will be returned, otherwise the
     * $totalValue
     */
    public function getRelevantTotalValue(DataTable $currentReport, $metric, $totalValue)
    {
        $totalMetric = $this->getMetricTotalValue($currentReport, $metric);

        if ($totalMetric > $totalValue) {
            return $totalMetric;
        }

        if (($totalMetric * 2) < $totalValue) {
            return $totalMetric;
        }

        return $totalValue;
    }

    public function getTotalValue($idSite, $period, $date, $metric, $segment)
    {
        $visits   = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, $segment, array($metric));
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
