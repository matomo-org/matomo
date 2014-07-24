<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Exception;
use Piwik\API\Request;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\API as APICustomVariables;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

/**
 * The Actions API lets you request reports for all your Visitor Actions: Page URLs, Page titles (Piwik Events),
 * File Downloads and Clicks on external websites.
 *
 * For example, "getPageTitles" will return all your page titles along with standard <a href='http://piwik.org/docs/analytics-api/reference/#toc-metric-definitions' target='_blank'>Actions metrics</a> for each row.
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
        $archive = Archive::build($idSite, $period, $date, $segment);

        $metrics = Archiver::$actionsAggregateMetrics;
        $metrics['Actions_avg_time_generation'] = 'avg_time_generation';

        // get requested columns
        $columns = Piwik::getArrayFromApiParameter($columns);
        if (!empty($columns)) {
            // get the columns that are available and requested
            $columns = array_intersect($columns, array_values($metrics));
            $columns = array_values($columns); // make sure indexes are right
            $nameReplace = array();
            foreach ($columns as $i => $column) {
                $fullColumn = array_search($column, $metrics);
                $columns[$i] = $fullColumn;
                $nameReplace[$fullColumn] = $column;
            }

            if (false !== ($avgGenerationTimeRequested = array_search('Actions_avg_time_generation', $columns))) {
                unset($columns[$avgGenerationTimeRequested]);
                $avgGenerationTimeRequested = true;
            }
        } else {
            // get all columns
            unset($metrics['Actions_avg_time_generation']);
            $columns = array_keys($metrics);
            $nameReplace = & $metrics;
            $avgGenerationTimeRequested = true;
        }

        if ($avgGenerationTimeRequested) {
            $tempColumns = array(
                Archiver::METRIC_SUM_TIME_RECORD_NAME,
                Archiver::METRIC_HITS_TIMED_RECORD_NAME,
            );
            $columns = array_merge($columns, $tempColumns);
            $columns = array_unique($columns);

            $nameReplace[Archiver::METRIC_SUM_TIME_RECORD_NAME] = 'sum_time_generation';
            $nameReplace[Archiver::METRIC_HITS_TIMED_RECORD_NAME] = 'nb_hits_with_time_generation';
        }

        $table = $archive->getDataTableFromNumeric($columns);

        // replace labels (remove Actions_)
        $table->filter('ReplaceColumnNames', array($nameReplace));

        // compute avg generation time
        if ($avgGenerationTimeRequested) {
            $table->filter('ColumnCallbackAddColumnQuotient', array('avg_time_generation', 'sum_time_generation', 'nb_hits_with_time_generation', 3));
            $table->deleteColumns(array('sum_time_generation', 'nb_hits_with_time_generation'));
        }

        return $table;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param bool $segment
     * @param bool $expanded
     * @param bool|int $idSubtable
     * @param bool|int $depth
     *
     * @return DataTable|DataTable\Map
     */
    public function getPageUrls($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false,
                                $depth = false)
    {
        $dataTable = $this->getDataTableFromArchive('Actions_actions_url', $idSite, $period, $date, $segment, $expanded, $idSubtable, $depth);
        $this->filterPageDatatable($dataTable);
        $this->filterActionsDataTable($dataTable, $expanded);
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
        $dataTable->filter('ColumnCallbackDeleteRow', array(
            'nb_hits_following_search',
            function ($value) {
                return $value <= 0;
            }
        ));
    }

    /**
     * Returns a DataTable with analytics information for every unique entry page URL, for
     * the specified site, period & segment.
     */
    public function getEntryPageUrls($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterNonEntryActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique exit page URL, for
     * the specified site, period & segment.
     */
    public function getExitPageUrls($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        $dataTable = $this->getPageUrls($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterNonExitActions($dataTable);
        return $dataTable;
    }

    public function getPageUrl($pageUrl, $idSite, $period, $date, $segment = false)
    {
        $callBackParameters = array('Actions_actions_url', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false);
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageUrl, Action::TYPE_PAGE_URL);
        $this->filterPageDatatable($dataTable);
        $this->filterActionsDataTable($dataTable);
        return $dataTable;
    }

    public function getPageTitles($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        $dataTable = $this->getDataTableFromArchive('Actions_actions', $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterPageDatatable($dataTable);
        $this->filterActionsDataTable($dataTable, $expanded);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique entry page title
     * for the given site, time period & segment.
     */
    public function getEntryPageTitles($idSite, $period, $date, $segment = false, $expanded = false,
                                       $idSubtable = false)
    {
        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterNonEntryActions($dataTable);
        return $dataTable;
    }

    /**
     * Returns a DataTable with analytics information for every unique exit page title
     * for the given site, time period & segment.
     */
    public function getExitPageTitles($idSite, $period, $date, $segment = false, $expanded = false,
                                      $idSubtable = false)
    {
        $dataTable = $this->getPageTitles($idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterNonExitActions($dataTable);
        return $dataTable;
    }

    public function getPageTitle($pageName, $idSite, $period, $date, $segment = false)
    {
        $callBackParameters = array('Actions_actions', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false);
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $pageName, Action::TYPE_PAGE_TITLE);
        $this->filterPageDatatable($dataTable);
        $this->filterActionsDataTable($dataTable);
        return $dataTable;
    }

    public function getDownloads($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        $dataTable = $this->getDataTableFromArchive('Actions_downloads', $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterActionsDataTable($dataTable, $expanded);
        return $dataTable;
    }

    public function getDownload($downloadUrl, $idSite, $period, $date, $segment = false)
    {
        $callBackParameters = array('Actions_downloads', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false);
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $downloadUrl, Action::TYPE_DOWNLOAD);
        $this->filterActionsDataTable($dataTable);
        return $dataTable;
    }

    public function getOutlinks($idSite, $period, $date, $segment = false, $expanded = false, $idSubtable = false)
    {
        $dataTable = $this->getDataTableFromArchive('Actions_outlink', $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $this->filterActionsDataTable($dataTable, $expanded);
        return $dataTable;
    }

    public function getOutlink($outlinkUrl, $idSite, $period, $date, $segment = false)
    {
        $callBackParameters = array('Actions_outlink', $idSite, $period, $date, $segment, $expanded = false, $idSubtable = false);
        $dataTable = $this->getFilterPageDatatableSearch($callBackParameters, $outlinkUrl, Action::TYPE_OUTLINK);
        $this->filterActionsDataTable($dataTable);
        return $dataTable;
    }

    public function getSiteSearchKeywords($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getSiteSearchKeywordsRaw($idSite, $period, $date, $segment);
        $dataTable->deleteColumn(Metrics::INDEX_SITE_SEARCH_HAS_NO_RESULT);
        $this->filterPageDatatable($dataTable);
        $this->filterActionsDataTable($dataTable);
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
        $dataTable->filter('ColumnCallbackAddColumnQuotient', array('nb_pages_per_search', $columnToRead, 'nb_visits', $precision = 1));
    }

    protected function getSiteSearchKeywordsRaw($idSite, $period, $date, $segment)
    {
        $dataTable = $this->getDataTableFromArchive('Actions_sitesearch', $idSite, $period, $date, $segment, $expanded = false);
        return $dataTable;
    }

    public function getSiteSearchNoResultKeywords($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getSiteSearchKeywordsRaw($idSite, $period, $date, $segment);
        // Delete all rows that have some results
        $dataTable->filter('ColumnCallbackDeleteRow',
            array(
                Metrics::INDEX_SITE_SEARCH_HAS_NO_RESULT,
                function ($value) {
                    return $value < 1;
                }
            ));
        $dataTable->deleteRow(DataTable::ID_SUMMARY_ROW);
        $dataTable->deleteColumn(Metrics::INDEX_SITE_SEARCH_HAS_NO_RESULT);
        $this->filterPageDatatable($dataTable);
        $this->filterActionsDataTable($dataTable);
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
        Actions::checkCustomVariablesPluginEnabled();
        $customVariables = APICustomVariables::getInstance()->getCustomVariables($idSite, $period, $date, $segment, $expanded = false, $_leavePiwikCoreVariables = true);

        $customVarNameToLookFor = ActionSiteSearch::CVAR_KEY_SEARCH_CATEGORY;

        $dataTable = new DataTable();
        // Handle case where date=last30&period=day
        // FIXMEA: this logic should really be refactored somewhere, this is ugly!
        if ($customVariables instanceof DataTable\Map) {
            $dataTable = $customVariables->getEmptyClone();

            $customVariableDatatables = $customVariables->getDataTables();
            foreach ($customVariableDatatables as $key => $customVariableTableForDate) {
                // we do not enter the IF, in the case idSite=1,3 AND period=day&date=datefrom,dateto,
                if ($customVariableTableForDate instanceof DataTable
                    && $customVariableTableForDate->getMetadata(Archive\DataTableFactory::TABLE_METADATA_PERIOD_INDEX)
                ) {
                    $row = $customVariableTableForDate->getRowFromLabel($customVarNameToLookFor);
                    if ($row) {
                        $dateRewrite = $customVariableTableForDate->getMetadata(Archive\DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart()->toString();
                        $idSubtable = $row->getIdSubDataTable();
                        $categories = APICustomVariables::getInstance()->getCustomVariablesValuesFromNameId($idSite, $period, $dateRewrite, $idSubtable, $segment);
                        $dataTable->addTable($categories, $key);
                    }
                }
            }
        } elseif ($customVariables instanceof DataTable) {
            $row = $customVariables->getRowFromLabel($customVarNameToLookFor);
            if ($row) {
                $idSubtable = $row->getIdSubDataTable();
                $dataTable = APICustomVariables::getInstance()->getCustomVariablesValuesFromNameId($idSite, $period, $date, $idSubtable, $segment);
            }
        }
        $this->filterActionsDataTable($dataTable);
        $this->addPagesPerSearchColumn($dataTable, $columnToRead = 'nb_actions');
        return $dataTable;
    }

    /**
     * Will search in the DataTable for a Label matching the searched string
     * and return only the matching row, or an empty datatable
     */
    protected function getFilterPageDatatableSearch($callBackParameters, $search, $actionType, $table = false,
                                                    $searchTree = false)
    {
        if ($searchTree === false) {
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
        }

        if ($table === false) {
            // fetch the data table
            $table = call_user_func_array(array($this, 'getDataTableFromArchive'), $callBackParameters);

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
            return new DataTable;
        }

        // filter regular data table
        if ($table instanceof DataTable) {
            // search for the first part of the tree search
            $search = array_shift($searchTree);
            $row = $table->getRowFromLabel($search);
            if ($row === false) {
                // not found
                $result = new DataTable;
                $result->setAllTableMetadata($table->getAllTableMetadata());
                return $result;
            }

            // end of tree search reached
            if (count($searchTree) == 0) {
                $result = new DataTable();
                $result->addRow($row);
                $result->setAllTableMetadata($table->getAllTableMetadata());
                return $result;
            }

            // match found on this level and more levels remaining: go deeper
            $idSubTable = $row->getIdSubDataTable();
            $callBackParameters[6] = $idSubTable;

            /**
             * @var \Piwik\Period $period
             */
            $period = $table->getMetadata('period');
            if (!empty($period)) {
                $callBackParameters[3] = $period->getDateStart() . ',' . $period->getDateEnd();
            }

            $table = call_user_func_array(array($this, 'getDataTableFromArchive'), $callBackParameters);
            return $this->doFilterPageDatatableSearch($callBackParameters, $table, $searchTree);
        }

        throw new Exception("For this API function, DataTable " . get_class($table) . " is not supported");
    }

    /**
     * Common filters for Page URLs and Page Titles
     *
     * @param DataTable|DataTable\Simple|DataTable\Map $dataTable
     */
    protected function filterPageDatatable($dataTable)
    {
        $columnsToRemove = array('bounce_rate');
        $dataTable->queueFilter('ColumnDelete', array($columnsToRemove));

        // Average time on page = total time on page / number visits on that page
        $dataTable->queueFilter('ColumnCallbackAddColumnQuotient',
            array('avg_time_on_page',
                  'sum_time_spent',
                  'nb_visits',
                  0)
        );

        // Bounce rate = single page visits on this page / visits started on this page
        $dataTable->queueFilter('ColumnCallbackAddColumnPercentage',
            array('bounce_rate',
                  'entry_bounce_count',
                  'entry_nb_visits',
                  0));

        // % Exit = Number of visits that finished on this page / visits on this page
        $dataTable->queueFilter('ColumnCallbackAddColumnPercentage',
            array('exit_rate',
                  'exit_nb_visits',
                  'nb_visits',
                  0)
        );

        // Handle performance analytics
        $hasTimeGeneration = (array_sum($dataTable->getColumn(Metrics::INDEX_PAGE_SUM_TIME_GENERATION)) > 0);
        if ($hasTimeGeneration) {
            // Average generation time = total generation time / number of pageviews
            $precisionAvgTimeGeneration = 3;
            $dataTable->queueFilter('ColumnCallbackAddColumnQuotient',
                array('avg_time_generation',
                      'sum_time_generation',
                      'nb_hits_with_time_generation',
                      $precisionAvgTimeGeneration)
            );
            $dataTable->queueFilter('ColumnDelete', array(array('sum_time_generation')));
        } else {
            // No generation time: remove it from the API output and add it to empty_columns metadata, so that
            // the columns can also be removed from the view
            $dataTable->filter('ColumnDelete', array(array(
                                                         Metrics::INDEX_PAGE_SUM_TIME_GENERATION,
                                                         Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION,
                                                         Metrics::INDEX_PAGE_MIN_TIME_GENERATION,
                                                         Metrics::INDEX_PAGE_MAX_TIME_GENERATION
                                                     )));

            if ($dataTable instanceof DataTable) {
                $emptyColumns = $dataTable->getMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME);
                if (!is_array($emptyColumns)) {
                    $emptyColumns = array();
                }
                $emptyColumns[] = 'sum_time_generation';
                $emptyColumns[] = 'avg_time_generation';
                $emptyColumns[] = 'min_time_generation';
                $emptyColumns[] = 'max_time_generation';
                $dataTable->setMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME, $emptyColumns);
            }
        }
    }

    /**
     * Common filters for all Actions API
     *
     * @param DataTable|DataTable\Simple|DataTable\Map $dataTable
     * @param bool $expanded
     */
    protected function filterActionsDataTable($dataTable, $expanded = false)
    {
        // Must be applied before Sort in this case, since the DataTable can contain both int and strings indexes
        // (in the transition period between pre 1.2 and post 1.2 datatable structure)
        $dataTable->filter('ReplaceColumnNames');
        $dataTable->filter('Sort', array('nb_visits', 'desc', $naturalSort = false, $expanded));

        $dataTable->queueFilter('ReplaceSummaryRowLabel');
    }

    /**
     * Removes DataTable rows referencing actions that were never the first action of a visit.
     *
     * @param DataTable $dataTable
     */
    private function filterNonEntryActions($dataTable)
    {
        $dataTable->filter('ColumnCallbackDeleteRow',
            array('entry_nb_visits',
                  function ($visits) {
                      return !strlen($visits);
                  }
            )
        );
    }

    /**
     * Removes DataTable rows referencing actions that were never the last action of a visit.
     *
     * @param DataTable $dataTable
     */
    private function filterNonExitActions($dataTable)
    {
        $dataTable->filter('ColumnCallbackDeleteRow',
            array('exit_nb_visits',
                  function ($visits) {
                      return !strlen($visits);
                  })
        );
    }

    protected function getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded = false, $idSubtable = null, $depth = null)
    {
        $skipAggregationOfSubTables = false;
        if ($period == 'range'
            && empty($idSubtable)
            && empty($expanded)
            && !Request::shouldLoadFlatten()
        ) {
            $skipAggregationOfSubTables = false;
        }
        return Archive::getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable, $skipAggregationOfSubTables, $depth);
    }
}
