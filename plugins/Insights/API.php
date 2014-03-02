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

    public function getOverallMoversAndShakers($idSite, $period, $date)
    {
        /** @var DataTable[] $tables */
        $tables = array(
            $this->getInsights($idSite, $period, $date, 'Actions_getPageUrls', 4, 4),
            $this->getInsights($idSite, $period, $date, 'Actions_getPageTitles', 4, 4),
            $this->getInsights($idSite, $period, $date, 'Referrers_getKeywords', 4, 4),
            $this->getInsights($idSite, $period, $date, 'Referrers_getCampaigns', 4, 4),
            $this->getInsights($idSite, $period, $date, 'Referrers_getAll', 4, 4),
        );

        // post event to add other reports?

        $map = new DataTable\Map();

        foreach ($tables as $table) {
            $map->addTable($table, $table->getMetadata('reportName'));
        }

        return $map;
    }

    // force $limitX and ignore minVisitsPercent, minGrowthPercent
    public function getInsights(
        $idSite, $period, $date, $reportUniqueId, $limitIncreaser = 5, $limitDecreaser = 5,
        $basedOnTotalMetric = false, $minVisitsPercent = 2, $minGrowthPercent = 20,
        $comparedToXPeriods = 1, $orderBy = 'absolute', $filterBy = '', $segment = false)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';

        $processedReport = new ProcessedReport();
        $report          = $processedReport->getReportMetadataByUniqueId($idSite, $reportUniqueId);

        $lastDate = Range::getDateXPeriodsAgo(abs($comparedToXPeriods), $date, $period);

        $currentReport = $this->requestReport($idSite, $period, $date, $report, $metric, $segment);
        $lastReport    = $this->requestReport($idSite, $period, $lastDate[0], $report, $metric, $segment);

        $totalValue = $this->getTotalValue($idSite, $period, $date, $basedOnTotalMetric, $currentReport, $metric);
        $minVisits  = $this->getMinVisits($totalValue, $minVisitsPercent);

        $considerMovers = false;
        $considerNew = false;
        $considerDisappeared = false;

        switch ($filterBy) {
            case '':
                $considerMovers = true;
                $considerNew = true;
                $considerDisappeared = true;
                break;
            case 'movers':
                $considerMovers = true;
                break;
            case 'new':
                $considerNew = true;
                break;
            case 'disappeared':
                $considerDisappeared = true;
                break;
        }

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
            'date'       => $date,
            'lastDate'   => $lastDate[0],
            'period'     => $period,
            'report'     => $report,
            'totalValue' => $totalValue,
            'minVisits'  => $minVisits
        ));

        return $dataTable;
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
            'filter_limit' => 10000,
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

    private function getMinVisits($totalValue, $minVisitsPercent)
    {
        $minVisits = ceil(($totalValue / 100) * $minVisitsPercent);

        return (int) $minVisits;
    }

    private function getTotalValue($idSite, $period, $date, $basedOnTotalMetric, DataTable $currentReport, $metric)
    {
        if ($basedOnTotalMetric) {
            $totals = $currentReport->getMetadata('totals');

            if (!empty($totals[$metric])) {
                $totalValue = $totals[$metric];
            } else {
                $totalValue = 0;
            }

            return (int) $totalValue;
        }

        $visits     = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, false, array($metric));
        $totalValue = $visits->getFirstRow()->getColumn($metric);

        return $totalValue;
    }

    private function getOrderByColumn($orderBy)
    {
        if ('relative' == $orderBy) {
            $orderByColumn = 'growth_percent_numeric';
        } elseif ('absolute' == $orderBy) {
            $orderByColumn = 'difference';
        } elseif ('importance' == $orderBy) {
            $orderByColumn = 'importance';
        } else {
            throw new \Exception('Unsupported orderBy');
        }

        return $orderByColumn;
    }
}
