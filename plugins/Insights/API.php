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

    /**
     * @var Model
     */
    private $model;

    /**
     * Those reports will be included in the insight / moversAndShakers overview reports.
     * You can configure any API parameter for each report such as "flat", "limitIncreaser", "minGrowth", ...
     * @var array
     */
    private $reportIds = array(
        'Actions_getPageUrls' => array(),
        'Actions_getPageTitles' => array(),
        'Actions_getDownloads' => array('flat' => 1),
        'Referrers_getWebsites' => array(),
        'Referrers_getCampaigns' => array(),
        'Referrers_getSocials' => array(),
        'Referrers_getSearchEngines' => array(),
        'UserCountry_getCountry' => array(),
    );

    protected function __construct()
    {
        parent::__construct();

        $this->model = new Model();
    }

    public function canGenerateInsights($date, $period)
    {
        Piwik::checkUserHasSomeViewAccess();

        try {
            $model    = new Model();
            $lastDate = $model->getLastDate($date, $period, 1);
        } catch (\Exception $e) {
            return false;
        }

        if (empty($lastDate)) {
            return false;
        }

        return true;
    }

    public function getInsightsOverview($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $defaultParams = array(
            'limitIncreaser' => 3,
            'limitDecreaser' => 3,
            'minImpactPercent' => 1,
            'minGrowthPercent' => 25,
        );

        $map = $this->generateOverviewReport('getInsights', $idSite, $period, $date, $segment, $defaultParams);

        return $map;
    }

    public function getMoversAndShakersOverview($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $defaultParams = array(
            'limitIncreaser' => 4,
            'limitDecreaser' => 4
        );

        $map = $this->generateOverviewReport('getMoversAndShakers', $idSite, $period, $date, $segment, $defaultParams);

        return $map;
    }

    private function generateOverviewReport($method, $idSite, $period, $date, $segment, array $defaultParams)
    {
        $tableManager = DataTable\Manager::getInstance();

        /** @var DataTable[] $tables */
        $tables = array();
        foreach ($this->reportIds as $reportId => $reportParams) {
            foreach ($defaultParams as $key => $defaultParam) {
                if (!array_key_exists($key, $reportParams)) {
                    $reportParams[$key] = $defaultParam;
                }
            }

            $firstTableId     = $tableManager->getMostRecentTableId();
            $table            = $this->requestApiMethod($method, $idSite, $period, $date, $reportId, $segment, $reportParams);
            $reportTableIds[] = $table->getId();
            $tableManager->deleteTablesExceptIgnored($reportTableIds, $firstTableId);

            $tables[] = $table;
        }

        $map = new DataTable\Map();

        foreach ($tables as $table) {
            $map->addTable($table, $table->getMetadata('reportName'));
        }

        return $map;
    }

    public function getMoversAndShakers($idSite, $period, $date, $reportUniqueId, $segment = false,
                                        $comparedToXPeriods = 1, $limitIncreaser = 4, $limitDecreaser = 4)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric  = 'nb_visits';
        $orderBy = InsightReport::ORDER_BY_ABSOLUTE;

        $reportMetadata = $this->model->getReportByUniqueId($idSite, $reportUniqueId);

        $totalValue     = $this->model->getTotalValue($idSite, $period, $date, $metric);
        $currentReport  = $this->model->requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment);

        $lastDate       = $this->model->getLastDate($date, $period, $comparedToXPeriods);
        $lastTotalValue = $this->model->getTotalValue($idSite, $period, $lastDate, $metric);
        $lastReport     = $this->model->requestReport($idSite, $period, $lastDate, $reportUniqueId, $metric, $segment);

        $insight = new InsightReport();
        return $insight->generateMoverAndShaker($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $lastTotalValue, $orderBy, $limitIncreaser, $limitDecreaser);
    }

    public function getInsights(
        $idSite, $period, $date, $reportUniqueId, $segment = false, $limitIncreaser = 5, $limitDecreaser = 5,
        $filterBy = '', $minImpactPercent = 2, $minGrowthPercent = 20,
        $comparedToXPeriods = 1, $orderBy = 'absolute')
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';

        $reportMetadata = $this->model->getReportByUniqueId($idSite, $reportUniqueId);

        $totalValue     = $this->model->getTotalValue($idSite, $period, $date, $metric);
        $currentReport  = $this->model->requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment);

        $lastDate       = $this->model->getLastDate($date, $period, $comparedToXPeriods);
        $lastTotalValue = $this->model->getTotalValue($idSite, $period, $lastDate, $metric);
        $lastReport     = $this->model->requestReport($idSite, $period, $lastDate, $reportUniqueId, $metric, $segment);

        $minGrowthPercentPositive = abs($minGrowthPercent);
        $minGrowthPercentNegative = -1 * $minGrowthPercentPositive;

        $relevantTotal = $this->model->getRelevantTotalValue($currentReport, $metric, $totalValue);

        $minMoversPercent      = -1;
        $minNewPercent         = -1;
        $minDisappearedPercent = -1;

        switch ($filterBy) {
            case self::FILTER_BY_MOVERS:
                $minMoversPercent = $minImpactPercent;
                break;
            case self::FILTER_BY_NEW:
                $minNewPercent = $minImpactPercent;
                break;
            case self::FILTER_BY_DISAPPEARED:
                $minDisappearedPercent = $minImpactPercent;
                break;
            default:
                $minMoversPercent      = $minImpactPercent;
                $minNewPercent         = $minImpactPercent;
                $minDisappearedPercent = $minImpactPercent;
        }

        $insight = new InsightReport();
        $table   = $insight->generateInsight($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $relevantTotal, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercentPositive, $minGrowthPercentNegative, $orderBy, $limitIncreaser, $limitDecreaser);
        $insight->markMoversAndShakers($table, $currentReport, $lastReport, $totalValue, $lastTotalValue);

        return $table;
    }

    private function requestApiMethod($method, $idSite, $period, $date, $reportId, $segment, $additionalParams)
    {
        $params = array(
            'method' => 'Insights.' . $method,
            'idSite' => $idSite,
            'date'   => $date,
            'period' => $period,
            'format' => 'original',
            'reportUniqueId' => $reportId,
        );

        if (!empty($segment)) {
            $params['segment'] = $segment;
        }

        if (!empty($additionalParams)) {
            foreach ($additionalParams as $key => $value) {
                $params[$key] = $value;
            }
        }

        $request = new ApiRequest($params);
        return $request->process();
    }

}
