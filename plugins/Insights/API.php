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
use Piwik\Piwik;

/**
 * API for plugin Insights
 *
 * @method static \Piwik\Plugins\Insights\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Include only 'movers' which are existing in the current and past report.
     */
    const FILTER_BY_MOVERS = 'movers';

    /**
     * Include only 'new' rows which were not existing in the past report.
     */
    const FILTER_BY_NEW = 'new';

    /**
     * Include only 'disappeared' rows which were existing in the past report but no longer in the current report.
     */
    const FILTER_BY_DISAPPEARED = 'disappeared';

    /**
     * @var Model
     */
    private $model;

    protected function __construct()
    {
        parent::__construct();

        $this->model = new Model();
    }

    private function getOverviewReports()
    {
        $reports = array();

        /**
         * Triggered to gather all reports to be displayed in the "Insight" and "Movers And Shakers" overview reports.
         * Plugins that want to add new reports to the overview should subscribe to this event and add reports to the
         * incoming array. API parameters can be configured as an array optionally.
         *
         * **Example**
         *
         *     public function addReportToInsightsOverview(&$reports)
         *     {
         *         $reports['Actions_getPageUrls']  = array();
         *         $reports['Actions_getDownloads'] = array('flat' => 1, 'minGrowthPercent' => 60);
         *     }
         *
         * @param array &$reports An array containing a report unique id as key and an array of API parameters as
         *                        values.
         */
        Piwik::postEvent('Insights.addReportToOverview', array(&$reports));

        return $reports;
    }

    /**
     * Detects whether insights can be generated for this date/period combination or not.
     * @param string $date     eg 'today', '2012-12-12'
     * @param string $period   eg 'day' or 'week'
     *
     * @return bool
     */
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

    /**
     * Generates insights for a set of reports. Plugins can add their own reports to be included in the insights
     * overview by listening to the {@hook Insights.addReportToOverview} event.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     *
     * @return DataTable\Map   A map containing a dataTable for each insight report. See {@link getInsights()} for more
     *                         information
     */
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

    /**
     * Detects the movers and shakers for a set of reports. Plugins can add their own reports to be included in this
     * overview by listening to the {@hook Insights.addReportToOverview} event.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     *
     * @return DataTable\Map   A map containing a dataTable for each movers and shakers report. See
     *                         {@link getMoversAndShakers()} for more information
     */
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
        foreach ($this->getOverviewReports() as $reportId => $reportParams) {
            if (!empty($reportParams)) {
                foreach ($defaultParams as $key => $defaultParam) {
                    if (!array_key_exists($key, $reportParams)) {
                        $reportParams[$key] = $defaultParam;
                    }
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

    /**
     * Detects the movers and shakers of a given date / report combination. A mover and shakers has an higher impact
     * than other rows on average. For instance if a sites pageviews increase by 10% a page that increased by 40% at the
     * same time contributed significantly more to the success than the average of 10%.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $reportUniqueId   eg 'Actions_getPageUrls'. An id like 'Goals_getVisitsUntilConversion_idGoal--4' works as well.
     * @param bool|string $segment
     * @param int $comparedToXPeriods
     * @param int $limitIncreaser      Value '0' ignores all increasers
     * @param int $limitDecreaser      Value '0' ignores all decreasers
     *
     * @return DataTable
     *
     * @throws \Exception In case a report having the given ID does not exist
     * @throws \Exception In case the report exists but does not return a dataTable
     */
    public function getMoversAndShakers($idSite, $period, $date, $reportUniqueId, $segment = false,
                                        $comparedToXPeriods = 1, $limitIncreaser = 4, $limitDecreaser = 4)
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric  = 'nb_visits';
        $orderBy = InsightReport::ORDER_BY_ABSOLUTE;

        $reportMetadata = $this->model->getReportByUniqueId($idSite, $reportUniqueId);

        if (empty($reportMetadata)) {
            throw new \Exception('A report having the ID ' . $reportUniqueId .  ' does not exist');
        }

        $totalValue     = $this->model->getTotalValue($idSite, $period, $date, $metric, $segment);
        $currentReport  = $this->model->requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment);
        $this->checkReportIsValid($currentReport);

        $lastDate       = $this->model->getLastDate($date, $period, $comparedToXPeriods);
        $lastTotalValue = $this->model->getTotalValue($idSite, $period, $lastDate, $metric, $segment);
        $lastReport     = $this->model->requestReport($idSite, $period, $lastDate, $reportUniqueId, $metric, $segment);
        $this->checkReportIsValid($lastReport);

        $insight = new InsightReport();
        return $insight->generateMoverAndShaker($reportMetadata, $period, $date, $lastDate, $metric, $currentReport, $lastReport, $totalValue, $lastTotalValue, $orderBy, $limitIncreaser, $limitDecreaser);
    }

    /**
     * Generates insights by comparing the report for a given date/period with a different date and calculating the
     * difference. The API can exclude rows which growth is not good enough or did not have enough impact.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $reportUniqueId   eg 'Actions_getPageUrls'. An id like 'Goals_getVisitsUntilConversion_idGoal--4' works as well.
     * @param bool|string $segment
     * @param int $limitIncreaser      Value '0' ignores all increasers
     * @param int $limitDecreaser      Value '0' ignores all decreasers
     * @param string $filterBy         By default all rows will be ignored. If given only 'movers', 'new' or 'disappeared' will be returned.
     * @param int $minImpactPercent    The minimum impact in percent. Eg '2%' of 1000 visits means the change /
     *                                 increase / decrease has to be at least 20 visits. Usually the '2%' are based on the total
     *                                 amount of visits but for reports having way less visits the metric total is used. Eg A page
     *                                 has 1000 visits but only 100 visits having keywords. In this case a minimum impact of '2%' evaluates to 2 and not 20.
     * @param int $minGrowthPercent    The amount of percent a row has to increase or decrease at least compared to the previous period.
     *                                 If value is '20' the growth has to be either at least '+20%' or '-20%' and lower.
     * @param int $comparedToXPeriods  The report will be compared to X periods before.
     * @param string $orderBy          Orders the rows by 'absolute', 'relative' or 'importance'.
     *
     * @return DataTable
     *
     * @throws \Exception In case a report having the given ID does not exist
     * @throws \Exception In case the report exists but does not return a dataTable
     */
    public function getInsights(
        $idSite, $period, $date, $reportUniqueId, $segment = false, $limitIncreaser = 5, $limitDecreaser = 5,
        $filterBy = '', $minImpactPercent = 2, $minGrowthPercent = 20,
        $comparedToXPeriods = 1, $orderBy = 'absolute')
    {
        Piwik::checkUserHasViewAccess(array($idSite));

        $metric = 'nb_visits';

        $reportMetadata = $this->model->getReportByUniqueId($idSite, $reportUniqueId);

        if (empty($reportMetadata)) {
            throw new \Exception('A report having the ID ' . $reportUniqueId .  ' does not exist');
        }

        $totalValue     = $this->model->getTotalValue($idSite, $period, $date, $metric, $segment);
        $currentReport  = $this->model->requestReport($idSite, $period, $date, $reportUniqueId, $metric, $segment);
        $this->checkReportIsValid($currentReport);

        $lastDate       = $this->model->getLastDate($date, $period, $comparedToXPeriods);
        $lastTotalValue = $this->model->getTotalValue($idSite, $period, $lastDate, $metric, $segment);
        $lastReport     = $this->model->requestReport($idSite, $period, $lastDate, $reportUniqueId, $metric, $segment);
        $this->checkReportIsValid($lastReport);

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

    private function checkReportIsValid($report)
    {
        if (!($report instanceof DataTable)) {
            throw new \Exception('Insight can be only generated for reports returning a dataTable');
        }
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
