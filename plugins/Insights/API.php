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

    protected function __construct()
    {
        parent::__construct();

        $this->model = new Model();
    }

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
            DataTable\Manager::getInstance()->deleteTablesExceptIgnored($reportTableIds, $firstTableId);

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
            DataTable\Manager::getInstance()->deleteTablesExceptIgnored($reportTableIds, $firstTableId);

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
        $minGrowthPercent = 30;
        $minMoversPercent = 2;
        $minNewPercent = 2;
        $minDisappearedPercent = 2;

        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';

        $reportMetadata = $this->model->getReportByUniqueId($idSite, $reportUniqueId);
        $totalValue     = $this->model->getTotalValue($idSite, $period, $date, $metric);
        $currentReport  = $this->model->requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment);

        if ($period === 'day') {
            // if website is too young, than use website creation date
            // for faster performance just compare against last week?
            $pastDate   = $this->model->getLastDate($date, $period, 7);
            $lastReport = $this->model->requestReport($idSite, 'week', $pastDate, $reportUniqueId, $metric, $segment);
            $lastReport->filter('Piwik\Plugins\Insights\DataTable\Filter\Average', array($metric, 7));
            $lastDate   = Range::factory('week', $pastDate);
            $lastDate   = $lastDate->getRangeString();
        } else {
            $lastDate   = $this->model->getLastDate($date, $period, 1);
            $lastReport = $this->model->requestReport($idSite, $period, $lastDate, $reportUniqueId, $metric, $segment);
        }

        $insight = new InsightReport();
        return $insight->generateInsight($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser);
    }

    public function getInsights(
        $idSite, $period, $date, $reportUniqueId, $segment = false, $limitIncreaser = 5, $limitDecreaser = 5,
        $filterBy = '', $minImpactPercent = 2, $minGrowthPercent = 20,
        $comparedToXPeriods = 1, $orderBy = 'absolute')
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';

        $reportMetadata = $this->model->getReportByUniqueId($idSite, $reportUniqueId);
        $lastDate       = $this->model->getLastDate($date, $period, $comparedToXPeriods);
        $currentReport  = $this->model->requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment);
        $lastReport     = $this->model->requestReport($idSite, $period, $lastDate, $reportUniqueId, $metric, $segment);
        $totalValue     = $this->model->getRelevantTotalValue($currentReport, $idSite, $period, $date, $metric);

        $minMoversPercent = -1;
        $minNewPercent = -1;
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
        return $insight->generateInsight($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $minMoversPercent, $minNewPercent, $minDisappearedPercent, $minGrowthPercent, $orderBy, $limitIncreaser, $limitDecreaser);
    }
}
