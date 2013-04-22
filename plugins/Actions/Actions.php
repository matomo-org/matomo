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
 * Actions plugin
 *
 * Reports about the page views, the outlinks and downloads.
 *
 * @package Piwik_Actions
 */
class Piwik_Actions extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('Actions_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
        return $info;
    }


    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenus',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
        );
        return $hooks;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
        $sqlFilter = array($this, 'getIdActionFromSegment');

        // entry and exit pages of visit
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Actions_Actions',
            'name'       => 'Actions_ColumnEntryPageURL',
            'segment'    => 'entryPageUrl',
            'sqlSegment' => 'log_visit.visit_entry_idaction_url',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Actions_Actions',
            'name'       => 'Actions_ColumnEntryPageTitle',
            'segment'    => 'entryPageTitle',
            'sqlSegment' => 'log_visit.visit_entry_idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Actions_Actions',
            'name'       => 'Actions_ColumnExitPageURL',
            'segment'    => 'exitPageUrl',
            'sqlSegment' => 'log_visit.visit_exit_idaction_url',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Actions_Actions',
            'name'       => 'Actions_ColumnExitPageTitle',
            'segment'    => 'exitPageTitle',
            'sqlSegment' => 'log_visit.visit_exit_idaction_name',
            'sqlFilter'  => $sqlFilter,
        );

        // single pages
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Actions_Actions',
            'name'           => 'Actions_ColumnPageURL',
            'segment'        => 'pageUrl',
            'sqlSegment'     => 'log_link_visit_action.idaction_url',
            'sqlFilter'      => $sqlFilter,
            'acceptedValues' => "All these segments must be URL encoded, for example: " . urlencode('http://example.com/path/page?query'),
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Actions_Actions',
            'name'       => 'Actions_ColumnPageName',
            'segment'    => 'pageTitle',
            'sqlSegment' => 'log_link_visit_action.idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Actions_Actions',
            'name'       => 'Actions_SiteSearchKeyword',
            'segment'    => 'siteSearchKeyword',
            'sqlSegment' => 'log_link_visit_action.idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
    }

    /**
     * Convert segment expression to an action ID or an SQL expression.
     *
     * This method is used as a sqlFilter-callback for the segments of this plugin.
     * Usually, these callbacks only return a value that should be compared to the
     * column in the database. In this case, that doesn't work since multiple IDs
     * can match an expression (e.g. "pageUrl=@foo").
     * @param string $valueToMatch
     * @param string $sqlField
     * @param string $matchType
     * @throws Exception
     * @return array|int|string
     */
    public function getIdActionFromSegment($valueToMatch, $sqlField, $matchType, $segmentName)
    {
        $actionType = $this->guessActionTypeFromSegment($segmentName);

        if ($actionType == Piwik_Tracker_Action::TYPE_ACTION_URL) {
            // for urls trim protocol and www because it is not recorded in the db
            $valueToMatch = preg_replace('@^http[s]?://(www\.)?@i', '', $valueToMatch);
        }

        $valueToMatch = Piwik_Common::sanitizeInputValue(Piwik_Common::unsanitizeInputValue($valueToMatch));

        // exact matches work by returning the id directly
        if ($matchType == Piwik_SegmentExpression::MATCH_EQUAL
            || $matchType == Piwik_SegmentExpression::MATCH_NOT_EQUAL
        ) {
            $sql = Piwik_Tracker_Action::getSqlSelectActionId();
            $bind = array($valueToMatch, $valueToMatch, $actionType);
            $idAction = Piwik_FetchOne($sql, $bind);
            // if the action is not found, we hack -100 to ensure it tries to match against an integer
            // otherwise binding idaction_name to "false" returns some rows for some reasons (in case &segment=pageTitle==Větrnásssssss)
            if (empty($idAction)) {
                $idAction = -100;
            }
            return $idAction;
        }

        // now, we handle the cases =@ (contains) and !@ (does not contain)

        // build the expression based on the match type
        $sql = 'SELECT idaction FROM ' . Piwik_Common::prefixTable('log_action') . ' WHERE ';
        $sqlMatchType = 'AND type = ' . $actionType;
        switch ($matchType) {
            case '=@':
                // use concat to make sure, no %s occurs because some plugins use %s in their sql
                $sql .= '( name LIKE CONCAT(\'%\', ?, \'%\') ' . $sqlMatchType . ' )';
                break;
            case '!@':
                $sql .= '( name NOT LIKE CONCAT(\'%\', ?, \'%\') ' . $sqlMatchType . ' )';
                break;
            default:
                throw new Exception("This match type $matchType is not available for action-segments.");
                break;
        }

        return array(
            // mark that the returned value is an sql-expression instead of a literal value
            'SQL'  => $sql,
            'bind' => $valueToMatch,
        );
    }

    /**
     * Returns metadata for available reports
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();

        $reports[] = array(
            'category'             => Piwik_Translate('Actions_Actions'),
            'name'                 => Piwik_Translate('Actions_Actions') . ' - ' . Piwik_Translate('General_MainMetrics'),
            'module'               => 'Actions',
            'action'               => 'get',
            'metrics'              => array(
                'nb_pageviews'        => Piwik_Translate('General_ColumnPageviews'),
                'nb_uniq_pageviews'   => Piwik_Translate('General_ColumnUniquePageviews'),
                'nb_downloads'        => Piwik_Translate('Actions_ColumnDownloads'),
                'nb_uniq_downloads'   => Piwik_Translate('Actions_ColumnUniqueDownloads'),
                'nb_outlinks'         => Piwik_Translate('Actions_ColumnOutlinks'),
                'nb_uniq_outlinks'    => Piwik_Translate('Actions_ColumnUniqueOutlinks'),
                'nb_searches'         => Piwik_Translate('Actions_ColumnSearches'),
                'nb_keywords'         => Piwik_Translate('Actions_ColumnSiteSearchKeywords'),
				'avg_time_generation' => Piwik_Translate('General_ColumnAverageGenerationTime'),
            ),
            'metricsDocumentation' => array(
                'nb_pageviews'        => Piwik_Translate('General_ColumnPageviewsDocumentation'),
                'nb_uniq_pageviews'   => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
                'nb_downloads'        => Piwik_Translate('Actions_ColumnClicksDocumentation'),
                'nb_uniq_downloads'   => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation'),
                'nb_outlinks'         => Piwik_Translate('Actions_ColumnClicksDocumentation'),
                'nb_uniq_outlinks'    => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation'),
                'nb_searches'         => Piwik_Translate('Actions_ColumnSearchesDocumentation'),
				'avg_time_generation' => Piwik_Translate('General_ColumnAverageGenerationTimeDocumentation'),
//				'nb_keywords' => Piwik_Translate('Actions_ColumnSiteSearchKeywords'),
            ),
            'processedMetrics'     => false,
            'order'                => 1
        );

        $metrics = array(
            'nb_hits'             => Piwik_Translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik_Translate('General_ColumnUniquePageviews'),
            'bounce_rate'         => Piwik_Translate('General_ColumnBounceRate'),
            'avg_time_on_page'    => Piwik_Translate('General_ColumnAverageTimeOnPage'),
            'exit_rate'           => Piwik_Translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik_Translate('General_ColumnAverageGenerationTime')
        );

        $documentation = array(
            'nb_hits'             => Piwik_Translate('General_ColumnPageviewsDocumentation'),
            'nb_visits'           => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
            'bounce_rate'         => Piwik_Translate('General_ColumnPageBounceRateDocumentation'),
            'avg_time_on_page'    => Piwik_Translate('General_ColumnAverageTimeOnPageDocumentation'),
            'exit_rate'           => Piwik_Translate('General_ColumnExitRateDocumentation'),
            'avg_time_generation' => Piwik_Translate('General_ColumnAverageGenerationTimeDocumentation'),
        );

        // pages report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_PageUrls'),
            'module'                => 'Actions',
            'action'                => 'getPageUrls',
            'dimension'             => Piwik_Translate('Actions_ColumnPageURL'),
            'metrics'               => $metrics,
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik_Translate('Actions_PagesReportDocumentation', '<br />')
                . '<br />' . Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getPageUrls',
            'order'                 => 2
        );

        // entry pages report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_SubmenuPagesEntry'),
            'module'                => 'Actions',
            'action'                => 'getEntryPageUrls',
            'dimension'             => Piwik_Translate('Actions_ColumnPageURL'),
            'metrics'               => array(
                'entry_nb_visits'    => Piwik_Translate('General_ColumnEntrances'),
                'entry_bounce_count' => Piwik_Translate('General_ColumnBounces'),
                'bounce_rate'        => Piwik_Translate('General_ColumnBounceRate'),
            ),
            'metricsDocumentation'  => array(
                'entry_nb_visits'    => Piwik_Translate('General_ColumnEntrancesDocumentation'),
                'entry_bounce_count' => Piwik_Translate('General_ColumnBouncesDocumentation'),
                'bounce_rate'        => Piwik_Translate('General_ColumnBounceRateForPageDocumentation')
            ),
            'documentation'         => Piwik_Translate('Actions_EntryPagesReportDocumentation', '<br />')
                . ' ' . Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getEntryPageUrls',
            'order'                 => 3
        );

        // exit pages report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_SubmenuPagesExit'),
            'module'                => 'Actions',
            'action'                => 'getExitPageUrls',
            'dimension'             => Piwik_Translate('Actions_ColumnPageURL'),
            'metrics'               => array(
                'exit_nb_visits' => Piwik_Translate('General_ColumnExits'),
                'nb_visits'      => Piwik_Translate('General_ColumnUniquePageviews'),
                'exit_rate'      => Piwik_Translate('General_ColumnExitRate')
            ),
            'metricsDocumentation'  => array(
                'exit_nb_visits' => Piwik_Translate('General_ColumnExitsDocumentation'),
                'nb_visits'      => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
                'exit_rate'      => Piwik_Translate('General_ColumnExitRateDocumentation')
            ),
            'documentation'         => Piwik_Translate('Actions_ExitPagesReportDocumentation', '<br />')
                . ' ' . Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getExitPageUrls',
            'order'                 => 4
        );

        // page titles report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_SubmenuPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getPageTitles',
            'dimension'             => Piwik_Translate('Actions_ColumnPageName'),
            'metrics'               => $metrics,
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik_Translate('Actions_PageTitlesReportDocumentation', array('<br />', htmlentities('<title>'))),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getPageTitles',
            'order'                 => 5,

        );

        // entry page titles report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_EntryPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getEntryPageTitles',
            'dimension'             => Piwik_Translate('Actions_ColumnPageName'),
            'metrics'               => array(
                'entry_nb_visits'    => Piwik_Translate('General_ColumnEntrances'),
                'entry_bounce_count' => Piwik_Translate('General_ColumnBounces'),
                'bounce_rate'        => Piwik_Translate('General_ColumnBounceRate'),
            ),
            'metricsDocumentation'  => array(
                'entry_nb_visits'    => Piwik_Translate('General_ColumnEntrancesDocumentation'),
                'entry_bounce_count' => Piwik_Translate('General_ColumnBouncesDocumentation'),
                'bounce_rate'        => Piwik_Translate('General_ColumnBounceRateForPageDocumentation')
            ),
            'documentation'         => Piwik_Translate('Actions_ExitPageTitlesReportDocumentation', '<br />')
                . ' ' . Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getEntryPageTitles',
            'order'                 => 6
        );

        // exit page titles report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_ExitPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getExitPageTitles',
            'dimension'             => Piwik_Translate('Actions_ColumnPageName'),
            'metrics'               => array(
                'exit_nb_visits' => Piwik_Translate('General_ColumnExits'),
                'nb_visits'      => Piwik_Translate('General_ColumnUniquePageviews'),
                'exit_rate'      => Piwik_Translate('General_ColumnExitRate')
            ),
            'metricsDocumentation'  => array(
                'exit_nb_visits' => Piwik_Translate('General_ColumnExitsDocumentation'),
                'nb_visits'      => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
                'exit_rate'      => Piwik_Translate('General_ColumnExitRateDocumentation')
            ),
            'documentation'         => Piwik_Translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
                . ' ' . Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getExitPageTitles',
            'order'                 => 7
        );

        $documentation = array(
            'nb_visits' => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation'),
            'nb_hits'   => Piwik_Translate('Actions_ColumnClicksDocumentation')
        );

        // outlinks report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_SubmenuOutlinks'),
            'module'                => 'Actions',
            'action'                => 'getOutlinks',
            'dimension'             => Piwik_Translate('Actions_ColumnClickedURL'),
            'metrics'               => array(
                'nb_visits' => Piwik_Translate('Actions_ColumnUniqueClicks'),
                'nb_hits'   => Piwik_Translate('Actions_ColumnClicks')
            ),
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik_Translate('Actions_OutlinksReportDocumentation') . ' '
                . Piwik_Translate('Actions_OutlinkDocumentation') . '<br />'
                . Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getOutlinks',
            'order'                 => 8,
        );

        // downloads report
        $reports[] = array(
            'category'              => Piwik_Translate('Actions_Actions'),
            'name'                  => Piwik_Translate('Actions_SubmenuDownloads'),
            'module'                => 'Actions',
            'action'                => 'getDownloads',
            'dimension'             => Piwik_Translate('Actions_ColumnDownloadURL'),
            'metrics'               => array(
                'nb_visits' => Piwik_Translate('Actions_ColumnUniqueDownloads'),
                'nb_hits'   => Piwik_Translate('Actions_ColumnDownloads')
            ),
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik_Translate('Actions_DownloadsReportDocumentation', '<br />'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getDownloads',
            'order'                 => 9,
        );

        if ($this->isSiteSearchEnabled()) {
            // Search Keywords
            $reports[] = array(
                'category'             => Piwik_Translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik_Translate('Actions_WidgetSearchKeywords'),
                'module'               => 'Actions',
                'action'               => 'getSiteSearchKeywords',
                'dimension'            => Piwik_Translate('Actions_ColumnSearchKeyword'),
                'metrics'              => array(
                    'nb_visits'           => Piwik_Translate('Actions_ColumnSearches'),
                    'nb_pages_per_search' => Piwik_Translate('Actions_ColumnPagesPerSearch'),
                    'exit_rate'           => Piwik_Translate('Actions_ColumnSearchExits'),
                ),
                'metricsDocumentation' => array(
                    'nb_visits'           => Piwik_Translate('Actions_ColumnSearchesDocumentation'),
                    'nb_pages_per_search' => Piwik_Translate('Actions_ColumnPagesPerSearchDocumentation'),
                    'exit_rate'           => Piwik_Translate('Actions_ColumnSearchExitsDocumentation'),
                ),
                'documentation'        => Piwik_Translate('Actions_SiteSearchKeywordsDocumentation') . '<br/><br/>' . Piwik_Translate('Actions_SiteSearchIntro') . '<br/><br/>'
                    . '<a href="http://piwik.org/docs/site-search/" target="_blank">' . Piwik_Translate('Actions_LearnMoreAboutSiteSearchLink') . '</a>',
                'processedMetrics'     => false,
                'order'                => 15
            );
            // No Result Search Keywords
            $reports[] = array(
                'category'             => Piwik_Translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik_Translate('Actions_WidgetSearchNoResultKeywords'),
                'module'               => 'Actions',
                'action'               => 'getSiteSearchNoResultKeywords',
                'dimension'            => Piwik_Translate('Actions_ColumnNoResultKeyword'),
                'metrics'              => array(
                    'nb_visits' => Piwik_Translate('Actions_ColumnSearches'),
                    'exit_rate' => Piwik_Translate('Actions_ColumnSearchExits'),
                ),
                'metricsDocumentation' => array(
                    'nb_visits' => Piwik_Translate('Actions_ColumnSearchesDocumentation'),
                    'exit_rate' => Piwik_Translate('Actions_ColumnSearchExitsDocumentation'),
                ),
                'documentation'        => Piwik_Translate('Actions_SiteSearchIntro') . '<br /><br />' . Piwik_Translate('Actions_SiteSearchKeywordsNoResultDocumentation'),
                'processedMetrics'     => false,
                'order'                => 16
            );

            if (self::isCustomVariablesPluginsEnabled()) {
                // Search Categories
                $reports[] = array(
                    'category'             => Piwik_Translate('Actions_SubmenuSitesearch'),
                    'name'                 => Piwik_Translate('Actions_WidgetSearchCategories'),
                    'module'               => 'Actions',
                    'action'               => 'getSiteSearchCategories',
                    'dimension'            => Piwik_Translate('Actions_ColumnSearchCategory'),
                    'metrics'              => array(
                        'nb_visits'           => Piwik_Translate('Actions_ColumnSearches'),
                        'nb_pages_per_search' => Piwik_Translate('Actions_ColumnPagesPerSearch'),
                        'exit_rate'           => Piwik_Translate('Actions_ColumnSearchExits'),
                    ),
                    'metricsDocumentation' => array(
                        'nb_visits'           => Piwik_Translate('Actions_ColumnSearchesDocumentation'),
                        'nb_pages_per_search' => Piwik_Translate('Actions_ColumnPagesPerSearchDocumentation'),
                        'exit_rate'           => Piwik_Translate('Actions_ColumnSearchExitsDocumentation'),
                    ),
                    'documentation'        => Piwik_Translate('Actions_SiteSearchCategories1') . '<br/>' . Piwik_Translate('Actions_SiteSearchCategories2'),
                    'processedMetrics'     => false,
                    'order'                => 17
                );
            }

            $documentation = Piwik_Translate('Actions_SiteSearchFollowingPagesDoc') . '<br/>' . Piwik_Translate('General_UsePlusMinusIconsDocumentation');
            // Pages URLs following Search
            $reports[] = array(
                'category'             => Piwik_Translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik_Translate('Actions_WidgetPageUrlsFollowingSearch'),
                'module'               => 'Actions',
                'action'               => 'getPageUrlsFollowingSiteSearch',
                'dimension'            => Piwik_Translate('General_ColumnDestinationPage'),
                'metrics'              => array(
                    'nb_hits_following_search' => Piwik_Translate('General_ColumnViewedAfterSearch'),
                    'nb_hits'                  => Piwik_Translate('General_ColumnTotalPageviews'),
                ),
                'metricsDocumentation' => array(
                    'nb_hits_following_search' => Piwik_Translate('General_ColumnViewedAfterSearchDocumentation'),
                    'nb_hits'                  => Piwik_Translate('General_ColumnPageviewsDocumentation'),
                ),
                'documentation'        => $documentation,
                'processedMetrics'     => false,
                'order'                => 18
            );
            // Pages Titles following Search
            $reports[] = array(
                'category'             => Piwik_Translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik_Translate('Actions_WidgetPageTitlesFollowingSearch'),
                'module'               => 'Actions',
                'action'               => 'getPageTitlesFollowingSiteSearch',
                'dimension'            => Piwik_Translate('General_ColumnDestinationPage'),
                'metrics'              => array(
                    'nb_hits_following_search' => Piwik_Translate('General_ColumnViewedAfterSearch'),
                    'nb_hits'                  => Piwik_Translate('General_ColumnTotalPageviews'),
                ),
                'metricsDocumentation' => array(
                    'nb_hits_following_search' => Piwik_Translate('General_ColumnViewedAfterSearchDocumentation'),
                    'nb_hits'                  => Piwik_Translate('General_ColumnPageviewsDocumentation'),
                ),
                'documentation'        => $documentation,
                'processedMetrics'     => false,
                'order'                => 19
            );
        }
    }

    function addWidgets()
    {
        Piwik_AddWidget('Actions_Actions', 'Actions_SubmenuPages', 'Actions', 'getPageUrls');
        Piwik_AddWidget('Actions_Actions', 'Actions_WidgetPageTitles', 'Actions', 'getPageTitles');
        Piwik_AddWidget('Actions_Actions', 'Actions_SubmenuOutlinks', 'Actions', 'getOutlinks');
        Piwik_AddWidget('Actions_Actions', 'Actions_SubmenuDownloads', 'Actions', 'getDownloads');
        Piwik_AddWidget('Actions_Actions', 'Actions_WidgetPagesEntry', 'Actions', 'getEntryPageUrls');
        Piwik_AddWidget('Actions_Actions', 'Actions_WidgetPagesExit', 'Actions', 'getExitPageUrls');
        Piwik_AddWidget('Actions_Actions', 'Actions_WidgetEntryPageTitles', 'Actions', 'getEntryPageTitles');
        Piwik_AddWidget('Actions_Actions', 'Actions_WidgetExitPageTitles', 'Actions', 'getExitPageTitles');

        if ($this->isSiteSearchEnabled()) {
            Piwik_AddWidget('Actions_SubmenuSitesearch', 'Actions_WidgetSearchKeywords', 'Actions', 'getSiteSearchKeywords');

            if (self::isCustomVariablesPluginsEnabled()) {
                Piwik_AddWidget('Actions_SubmenuSitesearch', 'Actions_WidgetSearchCategories', 'Actions', 'getSiteSearchCategories');
            }
            Piwik_AddWidget('Actions_SubmenuSitesearch', 'Actions_WidgetSearchNoResultKeywords', 'Actions', 'getSiteSearchNoResultKeywords');
            Piwik_AddWidget('Actions_SubmenuSitesearch', 'Actions_WidgetPageUrlsFollowingSearch', 'Actions', 'getPageUrlsFollowingSiteSearch');
            Piwik_AddWidget('Actions_SubmenuSitesearch', 'Actions_WidgetPageTitlesFollowingSearch', 'Actions', 'getPageTitlesFollowingSiteSearch');
        }
    }

    function addMenus()
    {
        Piwik_AddMenu('Actions_Actions', '', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 15);
        Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPages', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 1);
        Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPagesEntry', array('module' => 'Actions', 'action' => 'indexEntryPageUrls'), true, 2);
        Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPagesExit', array('module' => 'Actions', 'action' => 'indexExitPageUrls'), true, 3);
        Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPageTitles', array('module' => 'Actions', 'action' => 'indexPageTitles'), true, 4);
        Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuOutlinks', array('module' => 'Actions', 'action' => 'indexOutlinks'), true, 6);
        Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuDownloads', array('module' => 'Actions', 'action' => 'indexDownloads'), true, 7);

        if ($this->isSiteSearchEnabled()) {
            Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuSitesearch', array('module' => 'Actions', 'action' => 'indexSiteSearch'), true, 5);
        }
    }

    protected function isSiteSearchEnabled()
    {
        $idSite = Piwik_Common::getRequestVar('idSite', 0, 'int');
        if ($idSite == 0) {
            return false;
        }
        return Piwik_Site::isSiteSearchEnabledFor($idSite);
    }


    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    function archivePeriod($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $actionsArchiving = new Piwik_Actions_Archiving($archiveProcessing->idsite);
        return $actionsArchiving->archivePeriod($archiveProcessing);
    }

    /**
     * Compute all the actions along with their hierarchies.
     *
     * For each action we process the "interest statistics" :
     * visits, unique visitors, bounce count, sum visit length.
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function archiveDay($notification)
    {
        /* @var $archiveProcessing Piwik_ArchiveProcessing_Day */
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $actionsArchiving = new Piwik_Actions_Archiving($archiveProcessing->idsite);
        return $actionsArchiving->archiveDay($archiveProcessing);
    }

    static public function checkCustomVariablesPluginEnabled()
    {
        if (!self::isCustomVariablesPluginsEnabled()) {
            throw new Exception("To Track Site Search Categories, please ask the Piwik Administrator to enable the 'Custom Variables' plugin in Settings > Plugins.");
        }
    }

    static protected function isCustomVariablesPluginsEnabled()
    {
        return Piwik_PluginsManager::getInstance()->isPluginActivated('CustomVariables');
    }

    /**
     * @param $segmentName
     * @return int
     * @throws Exception
     */
    protected function guessActionTypeFromSegment($segmentName)
    {
        if (stripos($segmentName, 'pageurl') !== false) {
            $actionType = Piwik_Tracker_Action::TYPE_ACTION_URL;
            return $actionType;
        } elseif (stripos($segmentName, 'pagetitle') !== false) {
            $actionType = Piwik_Tracker_Action::TYPE_ACTION_NAME;
            return $actionType;
        } elseif (stripos($segmentName, 'sitesearch') !== false) {
            $actionType = Piwik_Tracker_Action::TYPE_SITE_SEARCH;
            return $actionType;
        } else {
            throw new Exception(" The segment $segmentName has an unexpected value.");
        }
    }
}

