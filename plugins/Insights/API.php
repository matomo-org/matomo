<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights;

use Piwik\API\Request as ApiRequest;
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * Provides API methods for insight and mover/shaker comparisons between report periods.
 *
 * @method static \Piwik\Plugins\Insights\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Include only 'movers' which are existing in the current and past report.
     */
    public const FILTER_BY_MOVERS = 'movers';

    /**
     * Include only 'new' rows which were not existing in the past report.
     */
    public const FILTER_BY_NEW = 'new';

    /**
     * Include only 'disappeared' rows which were existing in the past report but no longer in the current report.
     */
    public const FILTER_BY_DISAPPEARED = 'disappeared';

    /**
     * @var Model
     */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return array<string, array<string, scalar>>
     */
    private function getOverviewReports(): array
    {
        $reports = [];

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
     *
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @return bool Whether a previous comparison period exists for the requested date/period combination.
     */
    public function canGenerateInsights(string $date, string $period): bool
    {
        Piwik::checkUserHasSomeViewAccess();

        try {
            $lastDate = $this->model->getLastDate($date, $period, 1);
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
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable\Map Insight tables for every report included in the overview.
     */
    public function getInsightsOverview(int $idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $defaultParams = array(
            'limitIncreaser' => 3,
            'limitDecreaser' => 3,
            'minImpactPercent' => 1,
            'minGrowthPercent' => 25,
        );

        return $this->generateOverviewReport('getInsights', $idSite, $period, $date, $segment, $defaultParams);
    }

    /**
     * Detects the movers and shakers for a set of reports. Plugins can add their own reports to be included in this
     * overview by listening to the {@hook Insights.addReportToOverview} event.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable\Map Movers-and-shakers tables for every report included in the overview.
     */
    public function getMoversAndShakersOverview(int $idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $defaultParams = array(
            'limitIncreaser' => 4,
            'limitDecreaser' => 4,
        );

        return $this->generateOverviewReport('getMoversAndShakers', $idSite, $period, $date, $segment, $defaultParams);
    }

    /**
     * @param string|null|false $segment
     * @param array<string, scalar> $defaultParams
     */
    private function generateOverviewReport(string $method, int $idSite, string $period, string $date, $segment, array $defaultParams): DataTable\Map
    {
        $tableManager = DataTable\Manager::getInstance();

        $map = new DataTable\Map();

        foreach ($this->getOverviewReports() as $reportId => $reportParams) {
            if (!empty($reportParams)) {
                foreach ($defaultParams as $key => $defaultParam) {
                    if (!array_key_exists($key, $reportParams)) {
                        $reportParams[$key] = $defaultParam;
                    }
                }
            }

            $firstTableId     = $tableManager->getMostRecentTableId();
            /** @var DataTable $table */
            $table            = $this->requestApiMethod($method, $idSite, $period, $date, $reportId, $segment, $reportParams);
            $reportTableIds[] = $table->getId();
            $tableManager->deleteTablesExceptIgnored($reportTableIds, $firstTableId);

            $map->addTable($table, $table->getMetadata('reportName'));
        }

        return $map;
    }

    /**
     * Detects the movers and shakers of a given date / report combination. A mover and shakers has an higher impact
     * than other rows on average. For instance if a sites pageviews increase by 10% a page that increased by 40% at the
     * same time contributed significantly more to the success than the average of 10%.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string $reportUniqueId Report identifier, for example `Actions_getPageUrls`.
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int $comparedToXPeriods Number of past periods to compare against.
     * @param int $limitIncreaser Maximum number of positive movers to include. `0` excludes them.
     * @param int $limitDecreaser Maximum number of negative movers to include. `0` excludes them.
     * @return DataTable Movers-and-shakers rows for the requested report.
     */
    public function getMoversAndShakers(
        int $idSite,
        string $period,
        string $date,
        string $reportUniqueId,
        $segment = false,
        $comparedToXPeriods = 1,
        $limitIncreaser = 4,
        $limitDecreaser = 4
    ) {
        Piwik::checkUserHasViewAccess([$idSite]);

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
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string $reportUniqueId Report identifier, for example `Actions_getPageUrls`.
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int $limitIncreaser Maximum number of positive movers to include. `0` excludes them.
     * @param int $limitDecreaser Maximum number of negative movers to include. `0` excludes them.
     * @param ''|'movers'|'new'|'disappeared' $filterBy Optional filter for mover type.
     * @param int $minImpactPercent Minimum impact threshold in percent.
     * @param int $minGrowthPercent Minimum growth threshold in percent compared to the previous period.
     * @param int $comparedToXPeriods Number of past periods to compare against.
     * @param 'absolute'|'relative'|'importance' $orderBy Row ordering mode.
     * @return DataTable Insight rows for the requested report.
     */
    public function getInsights(
        int $idSite,
        string $period,
        string $date,
        string $reportUniqueId,
        $segment = false,
        $limitIncreaser = 5,
        $limitDecreaser = 5,
        string $filterBy = '',
        $minImpactPercent = 2,
        $minGrowthPercent = 20,
        $comparedToXPeriods = 1,
        string $orderBy = 'absolute'
    ) {
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

    /**
     * @param mixed $report
     */
    private function checkReportIsValid($report): void
    {
        if (!($report instanceof DataTable)) {
            throw new \Exception('Insight can be only generated for reports returning a dataTable');
        }
    }

    /**
     * @param string|null|false $segment
     * @param array<string, scalar> $additionalParams
     * @return DataTable|DataTable\Map
     */
    private function requestApiMethod(string $method, int $idSite, string $period, string $date, string $reportId, $segment, $additionalParams)
    {
        $params = array(
            'idSite' => $idSite,
            'date'   => $date,
            'period' => $period,
            'format' => 'original',
            'reportUniqueId' => $reportId,
            'totals' => 0,
        );

        if (!empty($segment)) {
            $params['segment'] = $segment;
        }

        if (!empty($additionalParams)) {
            foreach ($additionalParams as $key => $value) {
                $params[$key] = $value;
            }
        }

        return ApiRequest::processRequest('Insights.' . $method, $params, $default = []);
    }
}
