<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Actions
 */

/**
 * Actions controller
 *
 * @package Piwik_Actions
 */
class Piwik_Actions_Controller extends Piwik_Controller
{
    const ACTIONS_REPORT_ROWS_DISPLAY = 100;

    protected function getPageUrlsView($currentAction, $controllerActionSubtable, $apiAction)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, $currentAction, $apiAction, $controllerActionSubtable);
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnPageURL'));
        return $view;
    }

    /**
     * PAGES
     * @param bool $fetch
     * @return string
     */

    public function indexPageUrls($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Actions_SubmenuPages'),
            $this->getPageUrls(true), $fetch);
    }

    public function getPageUrls($fetch = false)
    {
        $view = $this->getPageUrlsView(__FUNCTION__, 'getPageUrls', 'Actions.getPageUrls');
        $this->configureViewPages($view);
        $this->configureViewActions($view);
        return $this->renderView($view, $fetch);
    }

    protected function configureViewPages($view)
    {
        $view->setColumnsToDisplay(array('label', 'nb_hits', 'nb_visits', 'bounce_rate', 'avg_time_on_page', 'exit_rate', 'avg_time_generation'));
    }

    /**
     * ENTRY PAGES
     * @param bool $fetch
     * @return string|void
     */
    public function indexEntryPageUrls($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Actions_SubmenuPagesEntry'),
            $this->getEntryPageUrls(true), $fetch);
    }

    public function getEntryPageUrls($fetch = false)
    {
        $view = $this->getPageUrlsView(__FUNCTION__, 'getEntryPageUrls', 'Actions.getEntryPageUrls');
        $this->configureViewEntryPageUrls($view);
        $this->configureViewActions($view);
        return $this->renderView($view, $fetch);
    }

    protected function configureViewEntryPageUrls($view)
    {
        $view->setSortedColumn('entry_nb_visits');
        $view->setColumnsToDisplay(array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate'));
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnEntryPageURL'));
        $view->setColumnTranslation('entry_bounce_count', Piwik_Translate('General_ColumnBounces'));
        $view->setColumnTranslation('entry_nb_visits', Piwik_Translate('General_ColumnEntrances'));
        $view->addRelatedReports(Piwik_Translate('Actions_SubmenuPagesEntry'), array(
                                                                                    'Actions.getEntryPageTitles' => Piwik_Translate('Actions_EntryPageTitles')
                                                                               ));
        $view->setReportUrl('Actions', $this->getEntryPageUrlActionForLink());
    }

    /*
     * EXIT PAGES
     */
    public function indexExitPageUrls($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Actions_SubmenuPagesExit'),
            $this->getExitPageUrls(true), $fetch);
    }

    public function getExitPageUrls($fetch = false)
    {
        $view = $this->getPageUrlsView(__FUNCTION__, 'getExitPageUrls', 'Actions.getExitPageUrls');
        $this->configureViewExitPageUrls($view);
        $this->configureViewActions($view);
        return $this->renderView($view, $fetch);
    }

    protected function configureViewExitPageUrls($view)
    {
        $view->setSortedColumn('exit_nb_visits');
        $view->setColumnsToDisplay(array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate'));
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnExitPageURL'));
        $view->setColumnTranslation('exit_nb_visits', Piwik_Translate('General_ColumnExits'));
        $view->addRelatedReports(Piwik_Translate('Actions_SubmenuPagesExit'), array(
                                                                                   'Actions.getExitPageTitles' => Piwik_Translate('Actions_ExitPageTitles')
                                                                              ));
        $view->setReportUrl('Actions', $this->getExitPageUrlActionForLink());
    }

    /*
     * SITE SEARCH
     */
    public function indexSiteSearch()
    {
        $view = Piwik_View::factory('indexSiteSearch');

        $view->keywords = $this->getSiteSearchKeywords(true);
        $view->noResultKeywords = $this->getSiteSearchNoResultKeywords(true);
        $view->pagesUrlsFollowingSiteSearch = $this->getPageUrlsFollowingSiteSearch(true);

        $categoryTrackingEnabled = Piwik_PluginsManager::getInstance()->isPluginActivated('CustomVariables');
        if ($categoryTrackingEnabled) {
            $view->categories = $this->getSiteSearchCategories(true);
        }

        echo $view->render();
    }

    public function getSiteSearchKeywords($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getSiteSearchKeywords');
        $this->configureViewSiteSearchKeywords($view);
        return $this->renderView($view, $fetch);
    }

    public function getSiteSearchNoResultKeywords($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getSiteSearchNoResultKeywords');
        $this->configureViewSiteSearchKeywords($view);
        $view->setColumnsToDisplay(array('label', 'nb_visits', 'exit_rate'));
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnNoResultKeyword'));
        return $this->renderView($view, $fetch);
    }

    public function configureViewSiteSearchKeywords(Piwik_ViewDataTable $view)
    {
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnSearchKeyword'));
        $view->setColumnsToDisplay(array('label', 'nb_visits', 'nb_pages_per_search', 'exit_rate'));
        $view->setColumnTranslation('nb_visits', Piwik_Translate('Actions_ColumnSearches'));
        $view->setColumnTranslation('exit_rate', str_replace("% ", "%&nbsp;", Piwik_Translate('Actions_ColumnSearchExits')));
        $view->setColumnTranslation('nb_pages_per_search', Piwik_Translate('Actions_ColumnPagesPerSearch'));
        $view->disableShowBarChart();
        $view->disableShowAllColumns();
    }

    public function getSiteSearchCategories($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getSiteSearchCategories');
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnSearchCategory'));
        $view->setColumnTranslation('nb_visits', Piwik_Translate('Actions_ColumnSearches'));
        $view->setColumnsToDisplay(array('label', 'nb_visits', 'nb_pages_per_search'));
        $view->setColumnTranslation('nb_pages_per_search', Piwik_Translate('Actions_ColumnPagesPerSearch'));
        $view->disableShowAllColumns();
        $view->disableShowBarChart();
        $view->disableRowEvolution();
        return $this->renderView($view, $fetch);
    }


    public function getPageUrlsFollowingSiteSearch($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getPageUrlsFollowingSiteSearch', 'getPageUrlsFollowingSiteSearch');
        $view->addRelatedReports(Piwik_Translate('Actions_WidgetPageUrlsFollowingSearch'), array(
                                                                                                'Actions.getPageTitlesFollowingSiteSearch' => Piwik_Translate('Actions_WidgetPageTitlesFollowingSearch'),
                                                                                           ));
        $view = $this->configureViewPagesFollowingSiteSearch($view);
        return $this->renderView($view, $fetch);
    }

    public function getPageTitlesFollowingSiteSearch($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getPageTitlesFollowingSiteSearch', 'getPageTitlesFollowingSiteSearch');
        $view->addRelatedReports(Piwik_Translate('Actions_WidgetPageTitlesFollowingSearch'), array(
                                                                                                  'Actions.getPageUrlsFollowingSiteSearch' => Piwik_Translate('Actions_WidgetPageUrlsFollowingSearch'),
                                                                                             ));
        $view = $this->configureViewPagesFollowingSiteSearch($view);
        return $this->renderView($view, $fetch);
    }

    public function configureViewPagesFollowingSiteSearch($view)
    {
        $view->setColumnsToDisplay(array('label', 'nb_hits_following_search', 'nb_hits'));
        $view->setColumnTranslation('nb_hits_following_search', Piwik_Translate('General_ColumnViewedAfterSearch'));
        $view->setColumnTranslation('label', Piwik_Translate('General_ColumnDestinationPage'));
        $view->setSortedColumn('nb_hits_following_search');
        $view->setColumnTranslation('nb_hits', Piwik_Translate('General_ColumnTotalPageviews'));
        $view->disableExcludeLowPopulation();
        $view = $this->configureViewActions($view, $doSetTranslations = false);
        return $view;
    }

    /*
     * PAGE TITLES
     */
    public function indexPageTitles($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Actions_SubmenuPageTitles'),
            $this->getPageTitles(true), $fetch);
    }

    public function getPageTitles($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName,
            __FUNCTION__,
            'Actions.getPageTitles',
            'getPageTitlesSubDataTable');
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnPageName'));
        $view->addRelatedReports(Piwik_Translate('Actions_SubmenuPageTitles'), array(
                                                                                    'Actions.getEntryPageTitles' => Piwik_Translate('Actions_EntryPageTitles'),
                                                                                    'Actions.getExitPageTitles'  => Piwik_Translate('Actions_ExitPageTitles'),
                                                                               ));
        $view->setReportUrl('Actions', $this->getPageTitlesActionForLink());
        $this->configureViewPages($view);
        $this->configureViewActions($view);
        return $this->renderView($view, $fetch);
    }

    public function getPageTitlesSubDataTable($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName,
            __FUNCTION__,
            'Actions.getPageTitles',
            'getPageTitlesSubDataTable');
        $this->configureViewPages($view);
        $this->configureViewActions($view);
        return $this->renderView($view, $fetch);
    }

    /**
     * Echos or returns a report displaying analytics data for every unique entry
     * page title.
     *
     * @param bool $fetch True to return the view as a string, false to echo it.
     * @return string
     */
    public function getEntryPageTitles($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getEntryPageTitles', __FUNCTION__);
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnEntryPageTitle'));
        $view->setColumnTranslation('entry_bounce_count', Piwik_Translate('General_ColumnBounces'));
        $view->setColumnTranslation('entry_nb_visits', Piwik_Translate('General_ColumnEntrances'));
        $view->setColumnsToDisplay(array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate'));

        $entryPageUrlAction = $this->getEntryPageUrlActionForLink();
        $view->addRelatedReports(Piwik_Translate('Actions_EntryPageTitles'), array(
                                                                                  'Actions.getPageTitles'       => Piwik_Translate('Actions_SubmenuPageTitles'),
                                                                                  "Actions.$entryPageUrlAction" => Piwik_Translate('Actions_SubmenuPagesEntry'),
                                                                             ));

        $this->configureViewActions($view);

        return $this->renderView($view, $fetch);
    }

    /**
     * Echos or returns a report displaying analytics data for every unique exit
     * page title.
     *
     * @param bool $fetch True to return the view as a string, false to echo it.
     * @return string
     */
    public function getExitPageTitles($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Actions.getExitPageTitles', __FUNCTION__);
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnExitPageTitle'));
        $view->setColumnTranslation('exit_nb_visits', Piwik_Translate('General_ColumnExits'));
        $view->setColumnsToDisplay(array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate'));

        $exitPageUrlAction = $this->getExitPageUrlActionForLink();
        $view->addRelatedReports(Piwik_Translate('Actions_ExitPageTitles'), array(
                                                                                 'Actions.getPageTitles'      => Piwik_Translate('Actions_SubmenuPageTitles'),
                                                                                 "Actions.$exitPageUrlAction" => Piwik_Translate('Actions_SubmenuPagesExit'),
                                                                            ));

        $this->configureViewActions($view);

        return $this->renderView($view, $fetch);
    }

    /*
     * DOWNLOADS
     */

    public function indexDownloads($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Actions_SubmenuDownloads'),
            $this->getDownloads(true), $fetch);
    }

    public function getDownloads($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName,
            __FUNCTION__,
            'Actions.getDownloads',
            'getDownloadsSubDataTable');

        $this->configureViewDownloads($view);
        return $this->renderView($view, $fetch);
    }

    public function getDownloadsSubDataTable($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName,
            __FUNCTION__,
            'Actions.getDownloads',
            'getDownloadsSubDataTable');
        $this->configureViewDownloads($view);
        return $this->renderView($view, $fetch);
    }


    /*
     * OUTLINKS
     */

    public function indexOutlinks($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Actions_SubmenuOutlinks'),
            $this->getOutlinks(true), $fetch);
    }

    public function getOutlinks($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName,
            __FUNCTION__,
            'Actions.getOutlinks',
            'getOutlinksSubDataTable');
        $this->configureViewOutlinks($view);
        return $this->renderView($view, $fetch);
    }

    public function getOutlinksSubDataTable($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName,
            __FUNCTION__,
            'Actions.getOutlinks',
            'getOutlinksSubDataTable');
        $this->configureViewOutlinks($view);
        return $this->renderView($view, $fetch);
    }

    /*
     * Page titles & Page URLs reports
     */
    protected function configureViewActions($view, $doSetTranslations = true)
    {
        if ($doSetTranslations) {
            $view->setColumnTranslation('nb_hits', Piwik_Translate('General_ColumnPageviews'));
            $view->setColumnTranslation('nb_visits', Piwik_Translate('General_ColumnUniquePageviews'));
            $view->setColumnTranslation('avg_time_on_page', Piwik_Translate('General_ColumnAverageTimeOnPage'));
            $view->setColumnTranslation('bounce_rate', Piwik_Translate('General_ColumnBounceRate'));
            $view->setColumnTranslation('exit_rate', Piwik_Translate('General_ColumnExitRate'));
            $view->setColumnTranslation('avg_time_generation', Piwik_Translate('General_ColumnAverageGenerationTime'));
            
			$view->queueFilter('ColumnCallbackReplace', array('avg_time_on_page', array('Piwik', 'getPrettyTimeFromSeconds')));
			
			$avgTimeCallback = create_function('$time', 'return $time ? Piwik::getPrettyTimeFromSeconds($time, true, true, false) : "-";');
            $view->queueFilter('ColumnCallbackReplace', array('avg_time_generation', $avgTimeCallback));
			
			$tooltipCallback = create_function('$hits, $min, $max', '
			    return $hits ? 
			        Piwik_Translate("Actions_AvgGenerationTimeTooltip", array(
			            $hits, "<br />", 
			            Piwik::getPrettyTimeFromSeconds($min), 
			            Piwik::getPrettyTimeFromSeconds($max)
			        ))
			        : false;');
			$view->queueFilter('ColumnCallbackAddMetadata', array(
                array('nb_hits_with_time_generation', 'min_time_generation', 'max_time_generation'),
                'avg_time_generation_tooltip', $tooltipCallback));
        }

        if (Piwik_Common::getRequestVar('enable_filter_excludelowpop', '0', 'string') != '0') {
            // computing minimum value to exclude
            $visitsInfo = Piwik_VisitsSummary_Controller::getVisitsSummary();
            $visitsInfo = $visitsInfo->getFirstRow();
            $nbActions = $visitsInfo->getColumn('nb_actions');
            $nbActionsLowPopulationThreshold = floor(0.02 * $nbActions); // 2 percent of the total number of actions
            // we remove 1 to make sure some actions/downloads are displayed in the case we have a very few of them
            // and each of them has 1 or 2 hits...
            $nbActionsLowPopulationThreshold = min($visitsInfo->getColumn('max_actions') - 1, $nbActionsLowPopulationThreshold - 1);

            $view->setExcludeLowPopulation('nb_hits', $nbActionsLowPopulationThreshold);
        }

        $this->configureGenericViewActions($view);
        return $view;
    }

    /*
     * Downloads report
     */
    protected function configureViewDownloads($view)
    {
        $view->setColumnsToDisplay(array('label', 'nb_visits', 'nb_hits'));
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnDownloadURL'));
        $view->setColumnTranslation('nb_visits', Piwik_Translate('Actions_ColumnUniqueDownloads'));
        $view->setColumnTranslation('nb_hits', Piwik_Translate('Actions_ColumnDownloads'));
        $view->disableExcludeLowPopulation();
        $this->configureGenericViewActions($view);
    }

    /*
     * Outlinks report
     */
    protected function configureViewOutlinks($view)
    {
        $view->setColumnsToDisplay(array('label', 'nb_visits', 'nb_hits'));
        $view->setColumnTranslation('label', Piwik_Translate('Actions_ColumnClickedURL'));
        $view->setColumnTranslation('nb_visits', Piwik_Translate('Actions_ColumnUniqueClicks'));
        $view->setColumnTranslation('nb_hits', Piwik_Translate('Actions_ColumnClicks'));
        $view->disableExcludeLowPopulation();
        $this->configureGenericViewActions($view);
    }

    /*
     * Common to all Actions reports, how to use the custom Actions Datatable html
     */
    protected function configureGenericViewActions($view)
    {
        $view->setTemplate('CoreHome/templates/datatable_actions.tpl');
        if (Piwik_Common::getRequestVar('idSubtable', -1) != -1) {
            $view->setTemplate('CoreHome/templates/datatable_actions_subdatable.tpl');
        }
        $currentlySearching = $view->setSearchRecursive();
        if ($currentlySearching) {
            $view->setTemplate('CoreHome/templates/datatable_actions_recursive.tpl');
        }
        // disable Footer icons
        $view->disableShowAllViewsIcons();
        $view->disableShowAllColumns();

        $view->setLimit(self::ACTIONS_REPORT_ROWS_DISPLAY);

        // if the flat parameter is not provided, make sure it is set to 0 in the URL,
        // so users can see that they can set it to 1 (see #3365)
        if (Piwik_Common::getRequestVar('flat', false) === false) {
            $view->setCustomParameter('flat', 0);
        }

        $view->main();
        // we need to rewrite the phpArray so it contains all the recursive arrays
        if ($currentlySearching) {
            $phpArrayRecursive = $this->getArrayFromRecursiveDataTable($view->getDataTable());
            $view->getView()->arrayDataTable = $phpArrayRecursive;
        }
    }

    protected function getArrayFromRecursiveDataTable($dataTable, $depth = 0)
    {
        $table = array();
        foreach ($dataTable->getRows() as $row) {
            $phpArray = array();
            if (($idSubtable = $row->getIdSubDataTable()) !== null) {
                $subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubtable);

                if ($subTable->getRowsCount() > 0) {
                    $phpArray = $this->getArrayFromRecursiveDataTable($subTable, $depth + 1);
                }
            }

            $newRow = array(
                'level'          => $depth,
                'columns'        => $row->getColumns(),
                'metadata'       => $row->getMetadata(),
                'idsubdatatable' => $row->getIdSubDataTable()
            );
            $table[] = $newRow;
            if (count($phpArray) > 0) {
                $table = array_merge($table, $phpArray);
            }
        }
        return $table;
    }

    /** Returns action to use when linking to the exit page URLs report. */
    private function getExitPageUrlActionForLink()
    {
        // link to the page not, just the report, but only if not a widget
        return Piwik_Common::getRequestVar('widget', 0) == 0 ? 'indexExitPageUrls' : 'getExitPageUrls';
    }


    /** Returns action to use when linking to the entry page URLs report. */
    private function getEntryPageUrlActionForLink()
    {
        // link to the page not, just the report, but only if not a widget
        return Piwik_Common::getRequestVar('widget', 0) == 0 ? 'indexEntryPageUrls' : 'getEntryPageUrls';
    }

    /** Returns action to use when linking to the page titles report. */
    private function getPageTitlesActionForLink()
    {
        // link to the page not, just the report, but only if not a widget
        return Piwik_Common::getRequestVar('widget', 0) == 0 ? 'indexPageTitles' : 'getPageTitles';
    }
}
