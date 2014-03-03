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
class API extends \Piwik\Plugin\API
{
    const FILTER_BY_NEW = 'new';
    const FILTER_BY_MOVERS = 'movers';
    const FILTER_BY_DISAPPEARED = 'disappeared';
    const ORDER_BY_RELATIVE = 'relative';
    const ORDER_BY_ABSOLUTE = 'absolute';
    const ORDER_BY_IMPORTANCE = 'importance';

    public function getInsightsOverview($idSite, $period, $date)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        /** @var DataTable[] $tables */
        $reports = array(
            'Actions_getPageUrls',
            'Actions_getPageTitles',
            'Referrers_getKeywords',
            'Referrers_getCampaigns',
            'Referrers_getAll'
        );
        // post event to add other reports?

        $reportTableIds   = array();
        $dataTableManager = DataTable\Manager::getInstance();

        $tables = array();
        foreach ($reports as $report) {
            $firstTableId     = $dataTableManager->getMostRecentTableId();
            $table            = $this->getInsightOverview($idSite, $period, $date, $report);
            $reportTableIds[] = $table->getId();
            $lastTableId      = $dataTableManager->getMostRecentTableId();

            for ($index = $firstTableId; $index <= $lastTableId; $index++) {
                if (!in_array($index, $reportTableIds)) {
                    DataTable\Manager::getInstance()->deleteTable($index);
                }
            }

            $tables[] = $table;
        }

        $map = new DataTable\Map();

        foreach ($tables as $table) {
            $map->addTable($table, $table->getMetadata('reportName'));
        }

        return $map;
    }

    public function getInsightOverview($idSite, $period, $date, $reportUniqueId, $segment = false, $limitIncreaser = 4,
                                       $limitDecreaser = 4, $minVisitsPercent = 2, $minGrowthPercent = 20, $orderBy = 'absolute',
                                       $considerMovers = true, $considerNew = true, $considerDisappeared = false)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';
        // consider disappeared if impact > 10%?

        $totalValue = $this->getTotalValue($idSite, $period, $date, $metric);
        $minVisits  = $this->getMinVisits($totalValue, $minVisitsPercent);

        $report        = $this->getReportByUniqueId($idSite, $reportUniqueId);
        $currentReport = $this->requestReport($idSite, $period, $date, $report, $metric, $segment);

        if ($period === 'day') {
            // if website is too young, than use website creation date
            // for faster performance just compare against last week?
            $pastDate   = Date::factory($date);
            $pastDate   = $pastDate->subDay(7);
            $lastDate   = $pastDate->toString();
            $lastReport = $this->requestReport($idSite, 'week', $lastDate, $report, $metric, $segment);
            $lastReport->filter('Piwik\Plugins\Insights\DataTable\Filter\Average', array($metric, 7));
        } else {
            $pastDate = Range::getLastDate($date, $period);

            if (empty($pastDate[0])) {
                return new DataTable();
            }

            $lastDate   = $pastDate[0];
            $lastReport = $this->requestReport($idSite, $period, $lastDate, $report, $metric, $segment);
        }

        return $this->buildInsightsReport($period, $date, $limitIncreaser, $limitDecreaser, $minGrowthPercent, $orderBy, $currentReport, $lastReport, $metric, $considerMovers, $considerNew, $considerDisappeared, $minVisits, $report, $lastDate, $totalValue);
    }

    // force $limitX and ignore minVisitsPercent, minGrowthPercent
    public function getInsights(
        $idSite, $period, $date, $reportUniqueId, $limitIncreaser = 5, $limitDecreaser = 5,
        $filterBy = '', $minVisitsPercent = 2, $minGrowthPercent = 20,
        $comparedToXPeriods = 1, $orderBy = 'absolute', $segment = false)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';
        $report = $this->getReportByUniqueId($idSite, $reportUniqueId);

        $lastDate = Range::getDateXPeriodsAgo(abs($comparedToXPeriods), $date, $period);

        $currentReport = $this->requestReport($idSite, $period, $date, $report, $metric, $segment);
        $lastReport    = $this->requestReport($idSite, $period, $lastDate[0], $report, $metric, $segment);

        $totalValue = $this->getRelevantTotalValue($idSite, $period, $date, $currentReport, $metric);
        $minVisits  = $this->getMinVisits($totalValue, $minVisitsPercent);

        $considerMovers = false;
        $considerNew = false;
        $considerDisappeared = false;

        switch ($filterBy) {
            case self::FILTER_BY_MOVERS:
                $considerMovers = true;
                break;
            case self::FILTER_BY_NEW:
                $considerNew = true;
                break;
            case self::FILTER_BY_DISAPPEARED:
                $considerDisappeared = true;
                break;
            default:
                $considerMovers = true;
                $considerNew = true;
                $considerDisappeared = true;
        }

        return $this->buildInsightsReport($period, $date, $limitIncreaser, $limitDecreaser, $minGrowthPercent, $orderBy, $currentReport, $lastReport, $metric, $considerMovers, $considerNew, $considerDisappeared, $minVisits, $report, $lastDate[0], $totalValue);
    }

    private function requestReport($idSite, $period, $date, $report, $metric, $segment)
    {
        $params = array(
            'method' => $report['module'] . '.' . $report['action'],
            'format' => 'original',
            'idSite' => $idSite,
            'period' => $period,
            'date'   => $date,
            'flat'   => 1,
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

    private function getOrderByColumn($orderBy)
    {
        if (self::ORDER_BY_RELATIVE == $orderBy) {
            $orderByColumn = 'growth_percent_numeric';
        } elseif (self::ORDER_BY_ABSOLUTE == $orderBy) {
            $orderByColumn = 'difference';
        } elseif (self::ORDER_BY_IMPORTANCE == $orderBy) {
            $orderByColumn = 'importance';
        } else {
            throw new \Exception('Unsupported orderBy');
        }

        return $orderByColumn;
    }

    private function getMinVisits($totalValue, $minVisitsPercent)
    {
        $minVisits = ceil(($totalValue / 100) * $minVisitsPercent);

        return (int) $minVisits;
    }

    private function getRelevantTotalValue($idSite, $period, $date, DataTable $currentReport, $metric)
    {
        $totalMetric = $this->getMetricTotalValue($currentReport, $metric);
        $totalValue  = $this->getTotalValue($idSite, $period, $date, $metric);

        if (($totalMetric * 2) < $totalValue) {
            return $totalMetric;
        }

        return $totalValue;
    }

    private function getTotalValue($idSite, $period, $date, $metric)
    {
        $visits = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, false, array($metric));
        $totalValue = $visits->getFirstRow()->getColumn($metric);
        return $totalValue;
    }

    private function getMetricTotalValue(DataTable $currentReport, $metric)
    {
        $totals = $currentReport->getMetadata('totals');

        if (!empty($totals[$metric])) {
            $totalValue = (int) $totals[$metric];
        } else {
            $totalValue = 0;
        }

        return $totalValue;
    }

    private function buildInsightsReport($period, $date, $limitIncreaser, $limitDecreaser, $minGrowthPercent, $orderBy, $currentReport, $lastReport, $metric, $considerMovers, $considerNew, $considerDisappeared, $minVisits, $report, $lastDate, $totalValue)
    {
        $dataTable = new DataTable();
        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\Insight',
            array(
                $currentReport,
                $lastReport,
                $metric,
                $considerMovers,
                $considerNew,
                $considerDisappeared
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\MinGrowth',
            array(
                'growth_percent_numeric',
                $minGrowthPercent
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
            array(
                $metric,
                $minVisits
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\OrderBy',
            array(
                $this->getOrderByColumn($orderBy)
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\Limit',
            array(
                'growth_percent_numeric',
                $limitIncreaser,
                $limitDecreaser
            )
        );

        $dataTable->setMetadataValues(array(
            'reportName' => $report['name'],
            'metricName' => $report['metrics'][$metric],
            'date' => $date,
            'lastDate' => $lastDate,
            'period' => $period,
            'report' => $report,
            'totalValue' => $totalValue,
            'minVisits' => $minVisits
        ));

        return $dataTable;
    }

    private function getReportByUniqueId($idSite, $reportUniqueId)
    {
        $processedReport = new ProcessedReport();
        $report = $processedReport->getReportMetadataByUniqueId($idSite, $reportUniqueId);
        return $report;
    }
}
