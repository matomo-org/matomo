<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary;

use Piwik\Archive;
use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\VisitsSummary\Reports\Get;
use Piwik\SettingsPiwik;
use Piwik\Url;

/**
 * VisitsSummary API lets you access the core web analytics metrics (visits, unique visitors,
 * count of actions (page views & downloads & clicks on outlinks), time on site, bounces and converted visits.
 *
 * @method static \Piwik\Plugins\VisitsSummary\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the VisitsSummary overview report for the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param string|string[]|false $columns Specific metrics to include, or `false` to return the default set.
     * @return DataTable|DataTable\Map VisitsSummary metrics for the requested period.
     */
    public function get($idSite, string $period, string $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);

        $requestedColumns = Piwik::getArrayFromApiParameter($columns);
        $columnsToFetch = $requestedColumns;
        $isEstimated = SettingsPiwik::isUniqueVisitorsEstimated($period);
        if ($isEstimated && array_intersect(['nb_uniq_visitors', 'nb_users', PluginsArchiver::UNIQUES_ESTIMATED_METRIC], $requestedColumns)) {
            // the filter below needs these, and views read the flag before the columns that weren't requested go
            $columnsToFetch = array_merge($requestedColumns, ['nb_visits', 'nb_uniq_visitors', PluginsArchiver::UNIQUES_ESTIMATED_METRIC]);
        }

        /** @var Get $report */
        $report = ReportsProvider::factory('VisitsSummary', 'get');
        $columns = $report->getMetricsRequiredForReport($this->getCoreColumns($period), $columnsToFetch);

        $dataTable = $archive->getDataTableFromNumeric($columns);

        if ($isEstimated) {
            $dataTable->filter(function (DataTable $table) {
                $row = $table->getFirstRow();
                if (!$row) {
                    return;
                }
                // archives built before the period was estimated have no flag, and were counted exactly
                $row->setColumn(PluginsArchiver::UNIQUES_ESTIMATED_METRIC, (int) !empty($row->getColumn(PluginsArchiver::UNIQUES_ESTIMATED_METRIC)));

                // no unique visitors for a period with visits means they weren't estimated or counted (eg a year whose
                // days have no sketches, or a Roll-Up), so leave them out rather than show 0
                if ($row->getColumn('nb_visits') && !$row->getColumn('nb_uniq_visitors')) {
                    $row->deleteColumn('nb_uniq_visitors');
                    $row->deleteColumn('nb_users');
                }
            });
        }

        if (!empty($requestedColumns)) {
            $dataTable->queueFilter('ColumnDelete', [$columnsToRemove = [], $requestedColumns]);
        }

        return $dataTable;
    }

    /**
     * @return string[]
     */
    protected function getCoreColumns(string $period): array
    {
        $columns = [
            'nb_visits',
            'nb_actions',
            'nb_visits_converted',
            'bounce_count',
            'sum_visit_length',
            'max_actions',
        ];
        if (SettingsPiwik::isUniqueVisitorsTotalAvailable($period)) {
            $columns = array_merge(['nb_uniq_visitors', 'nb_users'], $columns);
        }
        if (SettingsPiwik::isUniqueVisitorsEstimated($period)) {
            $columns[] = PluginsArchiver::UNIQUES_ESTIMATED_METRIC;
        }
        return $columns;
    }

    /**
     * @param int|string|int[] $idSite
     * @param string|false|null $segment
     * @param string|string[] $toFetch
     * @return DataTable|DataTable\Map
     */
    protected function getNumeric($idSite, string $period, string $date, $segment, $toFetch)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        return $archive->getDataTableFromNumeric($toFetch);
    }

    /**
     * Returns the number of visits in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of visits.
     */
    public function getVisits($idSite, string $period, string $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'nb_visits');
    }

    /**
     * Returns the number of unique visitors in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of unique visitors.
     */
    public function getUniqueVisitors($idSite, string $period, string $date, $segment = false)
    {
        return $this->getUniqueMetric($idSite, $period, $date, $segment, 'nb_uniq_visitors');
    }

    /**
     * Returns the number of users in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of users.
     */
    public function getUsers($idSite, string $period, string $date, $segment = false)
    {
        return $this->getUniqueMetric($idSite, $period, $date, $segment, 'nb_users');
    }

    /**
     * Returns the number of actions in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of actions.
     */
    public function getActions($idSite, string $period, string $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'nb_actions');
    }

    /**
     * Returns the maximum number of actions in a single visit for the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the maximum actions value.
     */
    public function getMaxActions($idSite, string $period, string $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'max_actions');
    }

    /**
     * Returns the number of bounces in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the bounce count.
     */
    public function getBounceCount($idSite, string $period, string $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'bounce_count');
    }

    /**
     * Returns the number of converted visits in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing converted visits.
     */
    public function getVisitsConverted($idSite, string $period, string $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'nb_visits_converted');
    }

    /**
     * Returns the total visit duration in seconds for the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing total visit duration.
     */
    public function getSumVisitsLength($idSite, string $period, string $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'sum_visit_length');
    }

    /**
     * Returns the total visit duration formatted as human-readable text.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing Human-readable visit duration values.
     */
    public function getSumVisitsLengthPretty($idSite, string $period, string $date, $segment = false)
    {
        $formatter = new Formatter();

        $table = $this->getSumVisitsLength($idSite, $period, $date, $segment);
        $table->filter(
            'ColumnCallbackReplace',
            ['sum_visit_length', [$formatter, 'getPrettyTimeFromSeconds'], [true]]
        );
        return $table;
    }

    /**
     * @param int|string|int[] $idSite
     * @param string|false|null $segment
     * @return DataTable|DataTable\Map
     */
    private function getUniqueMetric($idSite, string $period, string $date, $segment, string $metric)
    {
        $this->checkUniqueIsEnabledOrFail($period, $metric);
        if (!SettingsPiwik::isUniqueVisitorsEstimated($period)) {
            return $this->getNumeric($idSite, $period, $date, $segment, $metric);
        }

        // get() leaves the value out of periods that could neither be estimated nor counted
        return $this->get($idSite, $period, $date, $segment, [$metric]);
    }

    private function checkUniqueIsEnabledOrFail(string $period, string $metric): void
    {
        if (!SettingsPiwik::isUniqueVisitorsTotalAvailable($period)) {
            throw new \Exception(
                'The metric ' . $metric . ' is not enabled for the requested period. ' .
                'Please see this FAQ: ' . Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/faq_113/')
            );
        }
    }
}
