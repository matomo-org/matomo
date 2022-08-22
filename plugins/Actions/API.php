<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions;

use Exception;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Piwik;
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
 * For example, "getPageTitles" will return all your page titles along with standard <a href='http://matomo.org/docs/analytics-api/reference/#toc-metric-definitions' rel='noreferrer' target='_blank'>Actions metrics</a> for each row.
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
     * Returns the list of metrics (pages, downloads, outlinks)
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @param bool|array $columns
     * @return DataTable
     */
    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

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
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param bool $segment
     * @param bool $expanded
     * @param bool|int $idSubtable
     * @param bool|int $depth
     * @param bool|int $flat
     *
     * @return DataTable|DataTable\Map
     */
    public function getPageUrls(
        $idSite,
        $period,
        $date,
        $segment = false,
        $expanded = false,
        $idSubtable = false,
        $depth = false,
        $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_actions_url', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable, $depth);

        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_URL);

        if ($flat) {
            $dataTable->filter(function (DataTable $dataTable) {
                foreach ($dataTable->getRows() as $row) {
                    $label = $row->getColumn('label');
                    if (substr($label, 0, 1) !== '/' && $label != Piwik::translate('General_NotDefined', Piwik::translate('Actions_ColumnPageURL'))) {
                        $row->setColumn('label', '/' . $label);
                    }
                }
            });
        }

        return $dataTable;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param bool $segment
     * @param bool $expanded
     * @param bool $idSubtable
     *
     * @return DataTable|DataTable\Map
     */
    public function getPageUrlsFollowingSiteSearch($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->keepPagesFollowingSearch($dataTable);
        return $dataTable;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param bool $segment
     * @param bool $expanded
     * @param bool $idSubtable
     *
     * @return DataTable|DataTable\Map
     */
    public function getPageTitlesFollowingSiteSearch($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->keepPagesFollowingSearch($dataTable);
        return $dataTable;
    }

    /**
     * @param DataTable $dataTable
     */
    protected function keepPagesFollowingSearch($dataTable)
    {
        // Keep only pages which are following site search
        $dataTable->filter('ColumnCallbackDeleteRow', [
            PiwikMetrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS,
            function ($value) {
                return $value <= 0;
            }
        ]);
    }

    /**
     * Returns a DataTable with analytics information for every unique entry page URL, for
     * the specified site, period & segment.
     */
    public function getEntryPageUrls($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false,
                                     $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable, false, $flat);
        $this->filterNonEntryActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique exit page URL, for
     * the specified site, period & segment.
     */
    public function getExitPageUrls($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false,
                                    $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable, false, $flat);
        $this->filterNonExitActions($dataTable);
        return $dataTable;
    }

    public function getPageUrl($pageUrl, $idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_actions_url', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageUrl, Action::TYPE_PAGE_URL);
        $this->addPageProcessedMetrics($dataTable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_URL);
        return $dataTable;
    }

    public function getPageTitles($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_actions', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);

        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_TITLE);

        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique entry page title
     * for the given site, time period & segment.
     */
    public function getEntryPageTitles(
        $idSite,
        $period,
        $date,
        $segment = false,
        $expanded = false,
        $idSubtable = false,
        $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable, $flat);
        $this->filterNonEntryActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique exit page title
     * for the given site, time period & segment.
     */
    public function getExitPageTitles(
        $idSite,
        $period,
        $date,
        $segment = false,
        $expanded = false,
        $idSubtable = false,
        $flat = false
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable, $flat);
        $this->filterNonExitActions($dataTable);
        return $dataTable;
    }

    public function getPageTitle($pageName, $idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_actions', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageName, Action::TYPE_PAGE_TITLE);
        $this->addPageProcessedMetrics($dataTable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_PAGE_TITLE);
        return $dataTable;
    }

    public function getDownloads($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_downloads', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_DOWNLOAD);
        return $dataTable;
    }

    public function getDownload($downloadUrl, $idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_downloads', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $downloadUrl, Action::TYPE_DOWNLOAD);
        $this->filterActionsDataTable($dataTable, Action::TYPE_DOWNLOAD);
        return $dataTable;
    }

    public function getOutlinks($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_outlink', $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $this->filterActionsDataTable($dataTable, Action::TYPE_OUTLINK);
        return $dataTable;
    }

    public function getOutlink($outlinkUrl, $idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $callBackParameters = ['Actions_outlink', $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null];
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $outlinkUrl, Action::TYPE_OUTLINK);
        $this->filterActionsDataTable($dataTable, Action::TYPE_OUTLINK);
        return $dataTable;
    }

    public function getSiteSearchKeywords($idSite, $period, $date, $segment = false)
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
    protected function addPagesPerSearchColumn($dataTable, $columnToRead = 'nb_hits')
    {
        $dataTable->filter('ColumnCallbackAddColumnQuotient', ['nb_pages_per_search', $columnToRead, 'nb_visits', $precision = 1]);
    }

    protected function getSiteSearchKeywordsRaw($idSite, $period, $date, $segment)
    {
        $dataTable = Archive::createDataTableFromArchive('Actions_sitesearch', $idSite, $period, $date, $segment, $expanded = false);
        return $dataTable;
    }

    public function getSiteSearchNoResultKeywords($idSite, $period, $date, $segment = false)
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
                }
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
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param bool $segment
     *
     * @return DataTable|DataTable\Map
     */
    public function getSiteSearchCategories($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive('Actions_SiteSearchCategories', $idSite, $period, $date, $segment);

        $dataTable->queueFilter('ColumnDelete', 'nb_uniq_visitors');
        $this->filterActionsDataTable($dataTable, $isPageTitleType = false);
        $dataTable->filter('ReplaceColumnNames');
        $dataTable->filter('AddSegmentValue');
        $this->addPagesPerSearchColumn($dataTable, $columnToRead = 'nb_actions');

        return $dataTable;
    }

    /**
     * Will search in the DataTable for a Label matching the searched string
     * and return only the matching row, or an empty datatable
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

        // fetch the data table
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
     * This looks very similar to LabelFilter.php should it be refactored somehow? FIXME
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
             * @var \Piwik\Period $period
             */
            $period = $table->getMetadata('period');
            if (!empty($period)) {
                $callBackParameters[3] = $period->getDateStart() . ',' . $period->getDateEnd();
            }

            $table = call_user_func_array('\Piwik\Archive::createDataTableFromArchive', $callBackParameters);
            return $this->doFilterPageDatatableSearch($callBackParameters, $table, $searchTree);
        }

        throw new Exception("For this API function, DataTable " . get_class($table) . " is not supported");
    }

    /**
     * Common filters for all Actions API
     *
     * @param DataTable|DataTable\Simple|DataTable\Map $dataTable
     * @param bool $isPageTitleType Whether we are handling page title or regular URL
     */
    private function filterActionsDataTable($dataTable, $isPageTitleType)
    {
        // Must be applied before Sort in this case, since the DataTable can contain both int and strings indexes
        // (in the transition period between pre 1.2 and post 1.2 datatable structure)
        $dataTable->filter('Piwik\Plugins\Actions\DataTable\Filter\Actions', [$isPageTitleType]);
        $dataTable->filter('Piwik\Plugins\Goals\DataTable\Filter\CalculateConversionPageRate');

        return $dataTable;
    }

    /**
     * Removes DataTable rows referencing actions that were never the first action of a visit.
     *
     * @param DataTable $dataTable
     */
    private function filterNonEntryActions($dataTable)
    {
        $dataTable->filter(
            'ColumnCallbackDeleteRow',
            [
                PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS,
                function ($visits) {
                    return !strlen($visits);
                }
            ]
        );
    }

    /**
     * Removes DataTable rows referencing actions that were never the last action of a visit.
     *
     * @param DataTable $dataTable
     */
    private function filterNonExitActions($dataTable)
    {
        $dataTable->filter(
            'ColumnCallbackDeleteRow',
            [
                PiwikMetrics::INDEX_PAGE_EXIT_NB_VISITS,
                function ($visits) {
                    return !strlen($visits);
                }
            ]
        );
    }

    private function addPageProcessedMetrics(DataTable\DataTableInterface $dataTable)
    {
        $dataTable->filter(function (DataTable $table) {
            $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];
            $extraProcessedMetrics[] = new AverageTimeOnPage();
            $extraProcessedMetrics[] = new BounceRate();
            $extraProcessedMetrics[] = new ExitRate();
            $extraProcessedMetrics[] = new AveragePageGenerationTime();

            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        });
    }
}
