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

    private $reportIds = array(
        'Actions_getPageUrls',
        'Actions_getPageTitles',
        'Actions_getDownloads',
        'Referrers_getAll',
        'Referrers_getKeywords',
        'Referrers_getCampaigns',
        'Referrers_getSocials',
        'Referrers_getSearchEngines',
        'UserCountry_getCountry',
    );

    public function getInsightsOverview($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $reportTableIds = array();

        /** @var DataTable[] $tables */
        $tables = array();
        foreach ($this->reportIds as $reportId) {
            $firstTableId     = DataTable\Manager::getInstance()->getMostRecentTableId();
            $table            = $this->getInsights($idSite, $period, $date, $reportId, $segment, 3, 3, '', 2, 25);
            $reportTableIds[] = $table->getId();
            $this->deleteDataTables($firstTableId, $reportTableIds);

            $tables[] = $table;
        }

        $map = new DataTable\Map();

        foreach ($tables as $table) {
            $map->addTable($table, $table->getMetadata('reportName'));
        }

        return $map;
    }

    public function getOverallMoversAndShakers($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $reportTableIds = array();

        /** @var DataTable[] $tables */
        $tables = array();
        foreach ($this->reportIds as $reportId) {
            $firstTableId     = DataTable\Manager::getInstance()->getMostRecentTableId();
            $table            = $this->getMoversAndShakers($idSite, $period, $date, $reportId, $segment, 4, 4);
            $reportTableIds[] = $table->getId();
            $this->deleteDataTables($firstTableId, $reportTableIds);

            $tables[] = $table;
        }

        $map = new DataTable\Map();

        foreach ($tables as $table) {
            $map->addTable($table, $table->getMetadata('reportName'));
        }

        return $map;
    }

    public function getMoversAndShakers($idSite, $period, $date, $reportUniqueId, $segment = false,
                                        $limitIncreaser = 4, $limitDecreaser = 4)
    {
        $orderBy = 'absolute';
        $minVisitsPercent = 2;
        $minGrowthPercent = 30;
        $minMoversPercent = 2;
        $minNewPercent = 2;
        $minDisappearedPercent = 2;

        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';

        $totalValue    = $this->getTotalValue($idSite, $period, $date, $metric);
        $report        = $this->getReportByUniqueId($idSite, $reportUniqueId);
        $currentReport = $this->requestReport($idSite, $period, $date, $report, $metric, $segment);

        if ($period === 'day') {
            // if website is too young, than use website creation date
            // for faster performance just compare against last week?
            $pastDate   = Date::factory($date);
            $pastDate   = $pastDate->subDay(7);
            $pastDate   = $pastDate->toString();
            $lastReport = $this->requestReport($idSite, 'week', $pastDate, $report, $metric, $segment);
            $lastReport->filter('Piwik\Plugins\Insights\DataTable\Filter\Average', array($metric, 7));
            $lastDate   = Range::factory('week', $pastDate);
            $lastDate   = $lastDate->getRangeString();
        } else {
            $pastDate = Range::getLastDate($date, $period);

            if (empty($pastDate[0])) {
                throw new \Exception('Not possible to calculate movers and shakers for this date/period combination');
            }

            $lastDate   = $pastDate[0];
            $lastReport = $this->requestReport($idSite, $period, $lastDate, $report, $metric, $segment);
        }

        return $this->buildDataTable($report, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minVisitsPercent, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser);
    }

    public function getInsights(
        $idSite, $period, $date, $reportUniqueId, $segment = false, $limitIncreaser = 5, $limitDecreaser = 5,
        $filterBy = '', $minVisitsPercent = 2, $minGrowthPercent = 20,
        $comparedToXPeriods = 1, $orderBy = 'absolute')
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';
        $report = $this->getReportByUniqueId($idSite, $reportUniqueId);

        $lastDate = Range::getDateXPeriodsAgo(abs($comparedToXPeriods), $date, $period);

        if (empty($lastDate[0])) {
            throw new \Exception('Not possible to calculate movers and shakers for this date/period combination');
        }

        $currentReport = $this->requestReport($idSite, $period, $date, $report, $metric, $segment);
        $lastReport    = $this->requestReport($idSite, $period, $lastDate[0], $report, $metric, $segment);

        $totalValue = $this->getRelevantTotalValue($idSite, $period, $date, $currentReport, $metric);

        $minMoversPercent = -1;
        $minNewPercent = -1;
        $minDisappearedPercent = -1;

        switch ($filterBy) {
            case self::FILTER_BY_MOVERS:
                $minMoversPercent = 0;
                break;
            case self::FILTER_BY_NEW:
                $minNewPercent = 0;
                break;
            case self::FILTER_BY_DISAPPEARED:
                $minDisappearedPercent = 0;
                break;
            default:
                $minMoversPercent      = 0;
                $minNewPercent         = 0;
                $minDisappearedPercent = 0;
        }

        return $this->buildDataTable($report, $period, $date, $lastDate[0], $metric, $currentReport, $lastReport, $totalValue, $minVisitsPercent, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser);
    }

    private function requestReport($idSite, $period, $date, $report, $metric, $segment)
    {
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

    private function getMinVisits($totalValue, $percent)
    {
        $minVisits = ceil(($totalValue / 100) * $percent);

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
        $visits     = VisitsSummaryAPI::getInstance()->get($idSite, $period, $date, false, array($metric));
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

    /**
     * @param array $reportMetadata
     * @param string $period
     * @param string $date
     * @param string $lastDate
     * @param string $metric
     * @param DataTable $currentReport
     * @param DataTable $lastReport
     * @param int $totalValue
     * @param int $minVisitsPercent            Row must have at least min percent visits of totalVisits
     * @param int $minVisitsMoversPercent      Exclude rows who moved and the difference is not at least min percent
     *                                         visits of totalVisits. -1 excludes movers.
     * @param int $minVisitsNewPercent         Exclude rows who are new and the difference is not at least min percent
     *                                         visits of totalVisits. -1 excludes all new.
     * @param int $minVisitsDisappearedPercent Exclude rows who are disappeared and the difference is not at least min
     *                                         percent visits of totalVisits. -1 excludes all disappeared.
     * @param int $minGrowthPercent            The actual growth of a row must be at least percent compared to the
     *                                         previous value (not total value)
     * @param string $orderBy                  Order by absolute, relative, importance
     * @param int $limitIncreaser
     * @param int $limitDecreaser
     * @return DataTable
     */
    private function buildDataTable($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minVisitsPercent, $minVisitsMoversPercent, $minVisitsNewPercent, $minVisitsDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser)
    {
        $minVisits = $this->getMinVisits($totalValue, $minVisitsPercent);
        $minChangeMovers = 0;
        $minIncreaseNew = 0;
        $minDecreaseDisappeared = 0;

        $dataTable = new DataTable();
        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\Insight',
            array(
                $currentReport,
                $lastReport,
                $metric,
                $considerMovers = (-1 !== $minVisitsMoversPercent),
                $considerNew = (-1 !== $minVisitsNewPercent),
                $considerDisappeared = (-1 !== $minVisitsDisappearedPercent)
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\MinGrowth',
            array(
                'growth_percent_numeric',
                $minGrowthPercent,
            )
        );

        $dataTable->filter(
            'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
            array(
                $metric,
                $minVisits
            )
        );

        if ($minVisitsNewPercent) {
            $minIncreaseNew = $this->getMinVisits($totalValue, $minVisitsNewPercent);
            $dataTable->filter(
                'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
                array(
                    'difference',
                    $minIncreaseNew,
                    'isNew'
                )
            );
        }

        if ($minVisitsMoversPercent) {
            $minChangeMovers = $this->getMinVisits($totalValue, $minVisitsMoversPercent);
            $dataTable->filter(
                'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
                array(
                    'difference',
                    $minChangeMovers,
                    'isMover'
                )
            );
        }

        if ($minVisitsDisappearedPercent) {
            $minDecreaseDisappeared = $this->getMinVisits($totalValue, $minVisitsDisappearedPercent);
            $dataTable->filter(
                'Piwik\Plugins\Insights\DataTable\Filter\ExcludeLowValue',
                array(
                    'difference',
                    $minDecreaseDisappeared,
                    'isDisappeared'
                )
            );
        }

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
            'reportName' => $reportMetadata['name'],
            'metricName' => $reportMetadata['metrics'][$metric],
            'date' => $date,
            'lastDate' => $lastDate,
            'period' => $period,
            'report' => $reportMetadata,
            'totalValue' => $totalValue,
            'minValue'  => $minVisits,
            'minChangeMovers' => $minChangeMovers,
            'minIncreaseNew' => $minIncreaseNew,
            'minDecreaseDisappeared' => $minDecreaseDisappeared,
            'minValuePercent' => $minVisitsPercent,
            'minGrowthPercent' => $minGrowthPercent,
            'minVisitsMoversPercent' => $minVisitsMoversPercent,
            'minVisitsNewPercent' => $minVisitsNewPercent,
            'minVisitsDisappearedPercent' => $minVisitsDisappearedPercent
        ));

        return $dataTable;
    }

    private function getReportByUniqueId($idSite, $reportUniqueId)
    {
        $processedReport = new ProcessedReport();
        $report = $processedReport->getReportMetadataByUniqueId($idSite, $reportUniqueId);

        return $report;
    }

    private function deleteDataTables($firstTableId, $dataTableIdsToBeIgnored)
    {
        $dataTableManager = DataTable\Manager::getInstance();
        $lastTableId = $dataTableManager->getMostRecentTableId();

        for ($index = $firstTableId; $index <= $lastTableId; $index++) {
            if (!in_array($index, $dataTableIdsToBeIgnored)) {
                $dataTableManager->deleteTable($index);
            }
        }
    }
}
