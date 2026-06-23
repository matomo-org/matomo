<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions;

use Exception;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugin\ReportsProvider;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

/**
 * The Actions API lets you request reports for all your Visitor Actions: Page URLs, Page titles, Events, Content Tracking,
 * File Downloads and Clicks on external websites.
 *
 * For example, "getPageTitles" will return all your page titles along with standard <a href='https://matomo.org/docs/analytics-api/reference/#toc-metric-definitions' rel='noreferrer' target='_blank'>Actions metrics</a> for each row.
 *
 * It is also possible to request data for a specific Page Title with "getPageTitle"
 * and setting the parameter pageName to the page title you wish to request.
 * Similarly, you can request metrics for a given Page URL via "getPageUrl", a Download file via "getDownload"
 * and an outlink via "getOutlink".
 *
 * Note: pageName, pageUrl, outlinkUrl, downloadUrl parameters must be URL encoded before you call the API.
 * @method static \Piwik\Plugins\Actions\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns aggregated action metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param list<string>|string|false $columns Metrics to include in the response.
     *                                    Accepts a comma-separated list or array of metric names.
     * @return DataTable|DataTable\Map Action metrics for the selected site, period, and segment.
     */
    public function get($idSite, string $period, string $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        /** @var Reports\Get $report */
        $report = ReportsProvider::factory("Actions", "get");
        $archive = Archive::build($idSite, $period, $date, $segment);

        $requestedColumns = Piwik::getArrayFromApiParameter($columns);
        $columns = $report->getMetricsRequiredForReport($allColumns = null, $requestedColumns);

        $inDbColumnNames = array_map(function ($value) {
            return 'Actions_' . $value;
        }, $columns);
        $dataTable = $archive->getDataTableFromNumeric($inDbColumnNames);

        $dataTable->deleteColumns(array_diff($requestedColumns, $columns));

        $newNameMapping = array_combine($inDbColumnNames, $columns);
        $dataTable->filter('ReplaceColumnNames', [$newNameMapping]);

        $columnsToShow = $requestedColumns ?: $report->getAllMetrics();
        $dataTable->queueFilter('ColumnDelete', [$columnsToRemove = [], $columnsToShow]);

        return $dataTable;
    }

    /**
     * Returns page URL metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param int|null|false $depth Maximum depth of subtables to include when expanding results.
     * @param bool $flat Whether to flatten the hierarchical URL report into a single table.
     * @return DataTable|DataTable\Map Page URL metrics for the requested action rows.
     */
    public function getPageUrls(
        $idSite,
        string $period,
        string $date,
        $segment = false,
        bool $expanded = false,
        $idSubtable = false,
        $depth = false,
        bool $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_actions_url', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable, $depth);

        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_URL);

        if ($flat) {
            $dataTable->filter(function (DataTable $dataTable) {
                $notDefinedUrl = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_URL);
                foreach ($dataTable->getRows() as $row) {
                    $label = (string)$row->getColumn('label');
                    if (substr($label, 0, 1) !== '/' && $label !== $notDefinedUrl) {
                        $row->setColumn('label', '/' . $label);
                    }
                }
            });
        }

        return $dataTable;
    }

    /**
     * Returns page URL metrics for pages viewed immediately after an internal site search.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @return DataTable|DataTable\Map Page URLs that followed an internal search.
     */
    public function getPageUrlsFollowingSiteSearch($idSite, string $period, string $date, $segment = false, bool $expanded = false, $idSubtable = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->keepPagesFollowingSearch($dataTable);
        return $dataTable;
    }

    /**
     * Returns page title metrics for pages viewed immediately after an internal site search.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @return DataTable|DataTable\Map Page titles that followed an internal search.
     */
    public function getPageTitlesFollowingSiteSearch($idSite, string $period, string $date, $segment = false, bool $expanded = false, $idSubtable = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->keepPagesFollowingSearch($dataTable);
        return $dataTable;
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     */
    protected function keepPagesFollowingSearch($dataTable): void
    {
        // Keep only pages which are following site search
        $dataTable->filter('ColumnCallbackDeleteRow', [
            PiwikMetrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS,
            function ($value) {
                return $value <= 0;
            },
        ]);
    }

    /**
     * Returns a DataTable with analytics information for every unique entry page URL, for
     * the specified site, period & segment.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical URL report into a single table.
     * @return DataTable|DataTable\Map Entry page URL metrics for the requested site.
     */
    public function getEntryPageUrls(
        $idSite,
        string $period,
        string $date,
        $segment = false,
        bool $expanded = false,
        $idSubtable = false,
        bool $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable, false, $flat);
        $this->filterNonEntryActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique exit page URL, for
     * the specified site, period & segment.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical URL report into a single table.
     * @return DataTable|DataTable\Map Exit page URL metrics for the requested site.
     */
    public function getExitPageUrls(
        $idSite,
        string $period,
        string $date,
        $segment = false,
        bool $expanded = false,
        $idSubtable = false,
        bool $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable, false, $flat);
        $this->filterNonExitActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns metrics for a specific page URL.
     *
     * @param string $pageUrl The URL-encoded page URL to look up.
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Metrics for the requested page URL, or an empty table if it is not found.
     */
    public function getPageUrl($pageUrl, $idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_actions_url', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageUrl, Action::TYPE_PAGE_URL);
        $this->addPageProcessedMetrics($dataTable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_URL);
        return $dataTable;
    }

    /**
     * Returns page title metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical title report into a single table.
     * @return DataTable|DataTable\Map Page title metrics for the requested action rows.
     */
    public function getPageTitles($idSite, string $period, string $date, $segment = false, bool $expanded = false, $idSubtable = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_actions', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);

        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_TITLE);

        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique entry page title
     * for the given site, time period & segment.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical title report into a single table.
     * @return DataTable|DataTable\Map Entry page title metrics for the requested site.
     */
    public function getEntryPageTitles(
        $idSite,
        string $period,
        string $date,
        $segment = false,
        bool $expanded = false,
        $idSubtable = false,
        bool $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable, $flat);
        $this->filterNonEntryActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique exit page title
     * for the given site, time period & segment.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical title report into a single table.
     * @return DataTable|DataTable\Map Exit page title metrics for the requested site.
     */
    public function getExitPageTitles(
        $idSite,
        string $period,
        string $date,
        $segment = false,
        bool $expanded = false,
        $idSubtable = false,
        bool $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable, $flat);
        $this->filterNonExitActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns metrics for a specific page title.
     *
     * @param string $pageName The URL-encoded page title to look up.
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Metrics for the requested page title, or an empty table if it is not found.
     */
    public function getPageTitle($pageName, $idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_actions', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageName, Action::TYPE_PAGE_TITLE);
        $this->addPageProcessedMetrics($dataTable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_TITLE);
        return $dataTable;
    }

    /**
     * Returns download metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical download report into a single table.
     * @return DataTable|DataTable\Map Download metrics for the requested action rows.
     */
    public function getDownloads($idSite, string $period, string $date, $segment = false, bool $expanded = false, $idSubtable = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_downloads', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_DOWNLOAD);
        return $dataTable;
    }

    /**
     * Returns metrics for a specific download URL.
     *
     * @param string $downloadUrl The URL-encoded download URL to look up.
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Metrics for the requested download URL, or an empty table if it is not found.
     */
    public function getDownload($downloadUrl, $idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_downloads', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $downloadUrl, Action::TYPE_DOWNLOAD);
        $this->filterActionsDataTable($dataTable, Action::TYPE_DOWNLOAD);
        return $dataTable;
    }

    /**
     * Returns outlink metrics for the requested site and period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether to expand all rows and include their subtables.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     * @param bool $flat Whether to flatten the hierarchical outlink report into a single table.
     * @return DataTable|DataTable\Map Outlink metrics for the requested action rows.
     */
    public function getOutlinks($idSite, string $period, string $date, $segment = false, bool $expanded = false, $idSubtable = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_outlink', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_OUTLINK);
        return $dataTable;
    }

    /**
     * Returns metrics for a specific outlink URL.
     *
     * @param string $outlinkUrl The URL-encoded outlink URL to look up.
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Metrics for the requested outlink URL, or an empty table if it is not found.
     */
    public function getOutlink($outlinkUrl, $idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_outlink', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $outlinkUrl, Action::TYPE_OUTLINK);
        $this->filterActionsDataTable($dataTable, Action::TYPE_OUTLINK);
        return $dataTable;
    }

    /**
     * Returns internal search keywords that produced search results.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Site search keywords that returned at least one result.
     */
    public function getSiteSearchKeywords($idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getSiteSearchKeywordsRaw($idSite, $period, $date, $segment);
        $dataTable->deleteColumn(PiwikMetrics::INDEX_SITE_SEARCH_HAS_NO_RESULT);
        $this->filterActionsDataTable($dataTable, Action::TYPE_SITE_SEARCH);
        $dataTable->filter('ReplaceColumnNames');
        $dataTable->filter('AddSegmentByLabel', ['siteSearchKeyword']);
        $this->addPagesPerSearchColumn($dataTable);
        return $dataTable;
    }

    /**
     * Visitors can search, and then click "next" to view more results. This is the average number of search results pages viewed for this keyword.
     *
     * @param DataTable|DataTable\Simple|DataTable\Map $dataTable
     * @param string $columnToRead
     */
    protected function addPagesPerSearchColumn($dataTable, $columnToRead = 'nb_hits'): void
    {
        $dataTable->filter('ColumnCallbackAddColumnQuotient', ['nb_pages_per_search', $columnToRead, 'nb_visits', $precision = 1]);
    }

    /**
     * Returns the raw internal site search keyword report before post-processing.
     *
     * @param int|string|int[] $idSite
     * @param string|null|false $segment
     * @return DataTable|DataTable\Map
     */
    protected function getSiteSearchKeywordsRaw($idSite, string $period, string $date, $segment)
    {
        return Archive::createDataTableFromArchive('Actions_sitesearch', $idSite, $period, $date, $segment, $expanded = false);
    }

    /**
     * Returns internal search keywords that produced no results.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Site search keywords that returned no results.
     */
    public function getSiteSearchNoResultKeywords($idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getSiteSearchKeywordsRaw($idSite, $period, $date, $segment);
        // Delete all rows that have some results
        $dataTable->filter(
            'ColumnCallbackDeleteRow',
            [
                PiwikMetrics::INDEX_SITE_SEARCH_HAS_NO_RESULT,
                function ($value) {
                    return $value < 1;
                },
            ]
        );
        $dataTable->deleteRow(DataTable::ID_SUMMARY_ROW);
        $dataTable->deleteColumn(PiwikMetrics::INDEX_SITE_SEARCH_HAS_NO_RESULT);
        $this->filterActionsDataTable($dataTable, $isPageTitleType = false);
        $dataTable->filter('AddSegmentByLabel', ['siteSearchKeyword']);
        $dataTable->filter('ReplaceColumnNames');
        $this->addPagesPerSearchColumn($dataTable);
        return $dataTable;
    }

    /**
     * Returns internal search categories used by visitors on the site.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Site search categories and their metrics.
     */
    public function getSiteSearchCategories($idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_SiteSearchCategories', $idSite, $period, $date, $segment);

        $dataTable->queueFilter('ColumnDelete', ['nb_uniq_visitors']);
        $this->filterActionsDataTable($dataTable, $isPageTitleType = false);
        $dataTable->filter('ReplaceColumnNames');
        $dataTable->filter('AddSegmentValue');
        $this->addPagesPerSearchColumn($dataTable, $columnToRead = 'nb_actions');

        return $dataTable;
    }

    /**
     * Searches an actions report for the first row whose label matches the requested value.
     *
     * @param list<mixed> $callBackParameters
     * @param string $search
     * @param int $actionType
     * @return DataTable|DataTable\Map
     */
    protected function getFilterPageDatatableSearch($callBackParameters, $search, $actionType)
    {
        // build the query parts that are searched inside the tree
        if ($actionType == Action::TYPE_PAGE_TITLE) {
            $searchedString = Common::unsanitizeInputValue($search);
        } else {
            $idSite = $callBackParameters[1];
            try {
                $searchedString = PageUrl::excludeQueryParametersFromUrl($search, $idSite);
            } catch (Exception $e) {
                $searchedString = $search;
            }
        }
        ArchivingHelper::reloadConfig();
        $searchTree = ArchivingHelper::getActionExplodedNames($searchedString, $actionType);

        /** @var DataTable|DataTable\Map $table */
        $table = call_user_func_array('\Piwik\Archive::createDataTableFromArchive', $callBackParameters);

        if ($table instanceof DataTable\Map) {
            // search an array of tables, e.g. when using date=last30
            // note that if the root is an array, we filter all children
            // if an array occurs inside the nested table, we only look for the first match (see below)
            $dataTableMap = $table->getEmptyClone();

            foreach ($table->getDataTables() as $label => $subTable) {
                $newSubTable = $this->doFilterPageDatatableSearch($callBackParameters, $subTable, $searchTree);

                $dataTableMap->addTable($newSubTable, $label);
            }

            return $dataTableMap;
        }

        return $this->doFilterPageDatatableSearch($callBackParameters, $table, $searchTree);
    }

    /**
     * Traverses an actions report tree and returns the first matching row as a table.
     *
     * @FIXME This looks very similar to LabelFilter.php should it be refactored somehow?
     *
     * @param list<mixed> $callBackParameters
     * @param DataTable|DataTable\Map $table
     * @param list<string> $searchTree
     * @return DataTable
     */
    protected function doFilterPageDatatableSearch($callBackParameters, $table, $searchTree)
    {
        // filter a data table array
        if ($table instanceof DataTable\Map) {
            foreach ($table->getDataTables() as $subTable) {
                $filteredSubTable = $this->doFilterPageDatatableSearch($callBackParameters, $subTable, $searchTree);

                if ($filteredSubTable->getRowsCount() > 0) {
                    // match found in a sub table, return and stop searching the others
                    return $filteredSubTable;
                }
            }

            // nothing found in all sub tables
            $result = new DataTable();
            $subTables = $table->getDataTables();
            if (count($subTables) > 0) {
                // use the first subtable's metadata to ensure basic metadata like `period` is available in response
                $subTable = reset($subTables);
                $result->setAllTableMetadata($subTable->getAllTableMetadata());
            }
            return $result;
        }

        // filter regular data table
        if ($table instanceof DataTable) {
            // search for the first part of the tree search
            $search = array_shift($searchTree);
            $row = $table->getRowFromLabel($search);
            if ($row === false) {
                // not found
                $result = new DataTable();
                $result->setAllTableMetadata($table->getAllTableMetadata());
                return $result;
            }

            // end of tree search reached
            if (count($searchTree) == 0) {
                $result = $table->getEmptyClone();
                $result->addRow($row);
                $result->setAllTableMetadata($table->getAllTableMetadata());
                return $result;
            }

            // match found on this level and more levels remaining: go deeper
            $idSubTable = $row->getIdSubDataTable();
            $callBackParameters[7] = $idSubTable;

            /**
             * @var \Piwik\Period|false $period
             */
            $period = $table->getMetadata('period');
            if (!empty($period)) {
                $callBackParameters[3] = $period->getDateStart() . ',' . $period->getDateEnd();
            }

            $table = call_user_func_array('\Piwik\Archive::createDataTableFromArchive', $callBackParameters);
            return $this->doFilterPageDatatableSearch($callBackParameters, $table, $searchTree);
        }
    }

    /**
     * Applies the shared post-processing filters used by Actions API reports.
     *
     * @template T of DataTable|DataTable\Map
     *
     * @param T $dataTable
     * @param int|false $actionType Action type constant used to normalize labels and metadata.
     * @return T
     */
    private function filterActionsDataTable($dataTable, $actionType)
    {
        $dataTable->filter(function ($dataTable) {
            $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, Metrics::getColumnsAggregationOperation());
        });
        // Must be applied before Sort in this case, since the DataTable can contain both int and strings indexes
        // (in the transition period between pre 1.2 and post 1.2 datatable structure)
        $dataTable->filter('Piwik\Plugins\Actions\DataTable\Filter\Actions', [$actionType]);
        $dataTable->filter('Piwik\Plugins\Goals\DataTable\Filter\CalculateConversionPageRate');
        return $dataTable;
    }

    /**
     * Removes DataTable rows referencing actions that were never the first action of a visit.
     *
     * @param DataTable|DataTable\Map $dataTable
     */
    private function filterNonEntryActions($dataTable): void
    {
        $dataTable->filter(
            'ColumnCallbackDeleteRow',
            [
                PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS,
                function ($visits) {
                    return !strlen($visits);
                },
            ]
        );
    }

    /**
     * Removes DataTable rows referencing actions that were never the last action of a visit.
     *
     * @param DataTable|DataTable\Map $dataTable
     */
    private function filterNonExitActions($dataTable): void
    {
        $dataTable->filter(
            'ColumnCallbackDeleteRow',
            [
                PiwikMetrics::INDEX_PAGE_EXIT_NB_VISITS,
                function ($visits) {
                    return !strlen($visits);
                },
            ]
        );
    }

    private function addPageProcessedMetrics(DataTable\DataTableInterface $dataTable): void
    {
        $dataTable->filter(function (DataTable $table) {
            /** @var ProcessedMetric[] $extraProcessedMetrics */
            $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];
            $extraProcessedMetrics[] = new AverageTimeOnPage();
            $extraProcessedMetrics[] = new BounceRate();
            $extraProcessedMetrics[] = new ExitRate();
            $extraProcessedMetrics[] = new AveragePageGenerationTime();

            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        });
    }
}
