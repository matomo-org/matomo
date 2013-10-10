<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Actions
 */
namespace Piwik\Plugins\Actions;

use Piwik\API\Request;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;
use Piwik\Menu\MenuMain;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\SegmentExpression;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\ViewDataTable;
use Piwik\WidgetsList;

/**
 * Actions plugin
 *
 * Reports about the page views, the outlinks and downloads.
 *
 * @package Actions
 */
class Actions extends \Piwik\Plugin
{
    const ACTIONS_REPORT_ROWS_DISPLAY = 100;

    private $columnTranslations;

    public function __construct()
    {
        parent::__construct();

        $this->columnTranslations = array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime'),
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'Menu.Reporting.addItems'                  => 'addMenus',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
            'AssetManager.getStylesheetFiles'          => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'          => 'getJsFiles'
        );
        return $hooks;
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Actions/stylesheets/dataTableActions.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Actions/javascripts/actionsDataTable.js";
    }

    public function getSegmentsMetadata(&$segments)
    {
        $sqlFilter = array($this, 'getIdActionFromSegment');

        // entry and exit pages of visit
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnEntryPageURL',
            'segment'    => 'entryPageUrl',
            'sqlSegment' => 'log_visit.visit_entry_idaction_url',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnEntryPageTitle',
            'segment'    => 'entryPageTitle',
            'sqlSegment' => 'log_visit.visit_entry_idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnExitPageURL',
            'segment'    => 'exitPageUrl',
            'sqlSegment' => 'log_visit.visit_exit_idaction_url',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnExitPageTitle',
            'segment'    => 'exitPageTitle',
            'sqlSegment' => 'log_visit.visit_exit_idaction_name',
            'sqlFilter'  => $sqlFilter,
        );

        // single pages
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'General_Actions',
            'name'           => 'Actions_ColumnPageURL',
            'segment'        => 'pageUrl',
            'sqlSegment'     => 'log_link_visit_action.idaction_url',
            'sqlFilter'      => $sqlFilter,
            'acceptedValues' => "All these segments must be URL encoded, for example: " . urlencode('http://example.com/path/page?query'),
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnPageName',
            'segment'    => 'pageTitle',
            'sqlSegment' => 'log_link_visit_action.idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
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
     * @param string $segmentName
     * @throws \Exception
     * @return array|int|string
     */
    public function getIdActionFromSegment($valueToMatch, $sqlField, $matchType, $segmentName)
    {
        $actionType = $this->guessActionTypeFromSegment($segmentName);

        if ($actionType == Action::TYPE_ACTION_URL) {
            // for urls trim protocol and www because it is not recorded in the db
            $valueToMatch = preg_replace('@^http[s]?://(www\.)?@i', '', $valueToMatch);
        }

        $valueToMatch = Common::sanitizeInputValue(Common::unsanitizeInputValue($valueToMatch));

        // exact matches work by returning the id directly
        if ($matchType == SegmentExpression::MATCH_EQUAL
            || $matchType == SegmentExpression::MATCH_NOT_EQUAL
        ) {
            $sql = Action::getSqlSelectActionId();
            $bind = array($valueToMatch, $valueToMatch, $actionType);
            $idAction = Db::fetchOne($sql, $bind);
            // if the action is not found, we hack -100 to ensure it tries to match against an integer
            // otherwise binding idaction_name to "false" returns some rows for some reasons (in case &segment=pageTitle==Větrnásssssss)
            if (empty($idAction)) {
                $idAction = -100;
            }
            return $idAction;
        }

        // now, we handle the cases =@ (contains) and !@ (does not contain)

        // build the expression based on the match type
        $sql = 'SELECT idaction FROM ' . Common::prefixTable('log_action') . ' WHERE ';
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
                throw new \Exception("This match type $matchType is not available for action-segments.");
                break;
        }

        return array(
            // mark that the returned value is an sql-expression instead of a literal value
            'SQL'  => $sql,
            'bind' => $valueToMatch,
        );
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'             => Piwik::translate('General_Actions'),
            'name'                 => Piwik::translate('General_Actions') . ' - ' . Piwik::translate('General_MainMetrics'),
            'module'               => 'Actions',
            'action'               => 'get',
            'metrics'              => array(
                'nb_pageviews'        => Piwik::translate('General_ColumnPageviews'),
                'nb_uniq_pageviews'   => Piwik::translate('General_ColumnUniquePageviews'),
                'nb_downloads'        => Piwik::translate('General_Downloads'),
                'nb_uniq_downloads'   => Piwik::translate('Actions_ColumnUniqueDownloads'),
                'nb_outlinks'         => Piwik::translate('General_Outlinks'),
                'nb_uniq_outlinks'    => Piwik::translate('Actions_ColumnUniqueOutlinks'),
                'nb_searches'         => Piwik::translate('Actions_ColumnSearches'),
                'nb_keywords'         => Piwik::translate('Actions_ColumnSiteSearchKeywords'),
                'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime'),
            ),
            'metricsDocumentation' => array(
                'nb_pageviews'        => Piwik::translate('General_ColumnPageviewsDocumentation'),
                'nb_uniq_pageviews'   => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
                'nb_downloads'        => Piwik::translate('Actions_ColumnClicksDocumentation'),
                'nb_uniq_downloads'   => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
                'nb_outlinks'         => Piwik::translate('Actions_ColumnClicksDocumentation'),
                'nb_uniq_outlinks'    => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
                'nb_searches'         => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTimeDocumentation'),
//				'nb_keywords' => Piwik::translate('Actions_ColumnSiteSearchKeywords'),
            ),
            'processedMetrics'     => false,
            'order'                => 1
        );

        $metrics = array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime')
        );

        $documentation = array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviewsDocumentation'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
            'bounce_rate'         => Piwik::translate('General_ColumnPageBounceRateDocumentation'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPageDocumentation'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRateDocumentation'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTimeDocumentation'),
        );

        // pages report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_PageUrls'),
            'module'                => 'Actions',
            'action'                => 'getPageUrls',
            'dimension'             => Piwik::translate('Actions_ColumnPageURL'),
            'metrics'               => $metrics,
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_PagesReportDocumentation', '<br />')
                . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getPageUrls',
            'order'                 => 2
        );

        // entry pages report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_SubmenuPagesEntry'),
            'module'                => 'Actions',
            'action'                => 'getEntryPageUrls',
            'dimension'             => Piwik::translate('Actions_ColumnPageURL'),
            'metrics'               => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRate'),
            ),
            'metricsDocumentation'  => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrancesDocumentation'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBouncesDocumentation'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRateForPageDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_EntryPagesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getEntryPageUrls',
            'order'                 => 3
        );

        // exit pages report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_SubmenuPagesExit'),
            'module'                => 'Actions',
            'action'                => 'getExitPageUrls',
            'dimension'             => Piwik::translate('Actions_ColumnPageURL'),
            'metrics'               => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviews'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRate')
            ),
            'metricsDocumentation'  => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExitsDocumentation'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRateDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_ExitPagesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getExitPageUrls',
            'order'                 => 4
        );

        // page titles report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_SubmenuPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getPageTitles',
            'dimension'             => Piwik::translate('Actions_ColumnPageName'),
            'metrics'               => $metrics,
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_PageTitlesReportDocumentation', array('<br />', htmlentities('<title>'))),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getPageTitles',
            'order'                 => 5,

        );

        // entry page titles report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_EntryPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getEntryPageTitles',
            'dimension'             => Piwik::translate('Actions_ColumnPageName'),
            'metrics'               => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRate'),
            ),
            'metricsDocumentation'  => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrancesDocumentation'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBouncesDocumentation'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRateForPageDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_ExitPageTitlesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getEntryPageTitles',
            'order'                 => 6
        );

        // exit page titles report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_ExitPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getExitPageTitles',
            'dimension'             => Piwik::translate('Actions_ColumnPageName'),
            'metrics'               => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviews'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRate')
            ),
            'metricsDocumentation'  => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExitsDocumentation'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRateDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getExitPageTitles',
            'order'                 => 7
        );

        $documentation = array(
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
            'nb_hits'   => Piwik::translate('Actions_ColumnClicksDocumentation')
        );

        // outlinks report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('General_Outlinks'),
            'module'                => 'Actions',
            'action'                => 'getOutlinks',
            'dimension'             => Piwik::translate('Actions_ColumnClickedURL'),
            'metrics'               => array(
                'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicks'),
                'nb_hits'   => Piwik::translate('Actions_ColumnClicks')
            ),
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_OutlinksReportDocumentation') . ' '
                . Piwik::translate('Actions_OutlinkDocumentation') . '<br />'
                . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getOutlinks',
            'order'                 => 8,
        );

        // downloads report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('General_Downloads'),
            'module'                => 'Actions',
            'action'                => 'getDownloads',
            'dimension'             => Piwik::translate('Actions_ColumnDownloadURL'),
            'metrics'               => array(
                'nb_visits' => Piwik::translate('Actions_ColumnUniqueDownloads'),
                'nb_hits'   => Piwik::translate('General_Downloads')
            ),
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_DownloadsReportDocumentation', '<br />'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getDownloads',
            'order'                 => 9,
        );

        if ($this->isSiteSearchEnabled()) {
            // Search Keywords
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetSearchKeywords'),
                'module'               => 'Actions',
                'action'               => 'getSiteSearchKeywords',
                'dimension'            => Piwik::translate('General_ColumnKeyword'),
                'metrics'              => array(
                    'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
                    'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch'),
                    'exit_rate'           => Piwik::translate('Actions_ColumnSearchExits'),
                ),
                'metricsDocumentation' => array(
                    'nb_visits'           => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                    'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearchDocumentation'),
                    'exit_rate'           => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
                ),
                'documentation'        => Piwik::translate('Actions_SiteSearchKeywordsDocumentation') . '<br/><br/>' . Piwik::translate('Actions_SiteSearchIntro') . '<br/><br/>'
                    . '<a href="http://piwik.org/docs/site-search/" target="_blank">' . Piwik::translate('Actions_LearnMoreAboutSiteSearchLink') . '</a>',
                'processedMetrics'     => false,
                'order'                => 15
            );
            // No Result Search Keywords
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetSearchNoResultKeywords'),
                'module'               => 'Actions',
                'action'               => 'getSiteSearchNoResultKeywords',
                'dimension'            => Piwik::translate('Actions_ColumnNoResultKeyword'),
                'metrics'              => array(
                    'nb_visits' => Piwik::translate('Actions_ColumnSearches'),
                    'exit_rate' => Piwik::translate('Actions_ColumnSearchExits'),
                ),
                'metricsDocumentation' => array(
                    'nb_visits' => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                    'exit_rate' => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
                ),
                'documentation'        => Piwik::translate('Actions_SiteSearchIntro') . '<br /><br />' . Piwik::translate('Actions_SiteSearchKeywordsNoResultDocumentation'),
                'processedMetrics'     => false,
                'order'                => 16
            );

            if (self::isCustomVariablesPluginsEnabled()) {
                // Search Categories
                $reports[] = array(
                    'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                    'name'                 => Piwik::translate('Actions_WidgetSearchCategories'),
                    'module'               => 'Actions',
                    'action'               => 'getSiteSearchCategories',
                    'dimension'            => Piwik::translate('Actions_ColumnSearchCategory'),
                    'metrics'              => array(
                        'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
                        'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch'),
                        'exit_rate'           => Piwik::translate('Actions_ColumnSearchExits'),
                    ),
                    'metricsDocumentation' => array(
                        'nb_visits'           => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                        'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearchDocumentation'),
                        'exit_rate'           => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
                    ),
                    'documentation'        => Piwik::translate('Actions_SiteSearchCategories1') . '<br/>' . Piwik::translate('Actions_SiteSearchCategories2'),
                    'processedMetrics'     => false,
                    'order'                => 17
                );
            }

            $documentation = Piwik::translate('Actions_SiteSearchFollowingPagesDoc') . '<br/>' . Piwik::translate('General_UsePlusMinusIconsDocumentation');
            // Pages URLs following Search
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetPageUrlsFollowingSearch'),
                'module'               => 'Actions',
                'action'               => 'getPageUrlsFollowingSiteSearch',
                'dimension'            => Piwik::translate('General_ColumnDestinationPage'),
                'metrics'              => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearch'),
                    'nb_hits'                  => Piwik::translate('General_ColumnTotalPageviews'),
                ),
                'metricsDocumentation' => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearchDocumentation'),
                    'nb_hits'                  => Piwik::translate('General_ColumnPageviewsDocumentation'),
                ),
                'documentation'        => $documentation,
                'processedMetrics'     => false,
                'order'                => 18
            );
            // Pages Titles following Search
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetPageTitlesFollowingSearch'),
                'module'               => 'Actions',
                'action'               => 'getPageTitlesFollowingSiteSearch',
                'dimension'            => Piwik::translate('General_ColumnDestinationPage'),
                'metrics'              => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearch'),
                    'nb_hits'                  => Piwik::translate('General_ColumnTotalPageviews'),
                ),
                'metricsDocumentation' => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearchDocumentation'),
                    'nb_hits'                  => Piwik::translate('General_ColumnPageviewsDocumentation'),
                ),
                'documentation'        => $documentation,
                'processedMetrics'     => false,
                'order'                => 19
            );
        }
    }

    function addWidgets()
    {
        WidgetsList::add('General_Actions', 'General_Pages', 'Actions', 'getPageUrls');
        WidgetsList::add('General_Actions', 'Actions_WidgetPageTitles', 'Actions', 'getPageTitles');
        WidgetsList::add('General_Actions', 'General_Outlinks', 'Actions', 'getOutlinks');
        WidgetsList::add('General_Actions', 'General_Downloads', 'Actions', 'getDownloads');
        WidgetsList::add('General_Actions', 'Actions_WidgetPagesEntry', 'Actions', 'getEntryPageUrls');
        WidgetsList::add('General_Actions', 'Actions_WidgetPagesExit', 'Actions', 'getExitPageUrls');
        WidgetsList::add('General_Actions', 'Actions_WidgetEntryPageTitles', 'Actions', 'getEntryPageTitles');
        WidgetsList::add('General_Actions', 'Actions_WidgetExitPageTitles', 'Actions', 'getExitPageTitles');

        if ($this->isSiteSearchEnabled()) {
            WidgetsList::add('Actions_SubmenuSitesearch', 'Actions_WidgetSearchKeywords', 'Actions', 'getSiteSearchKeywords');

            if (self::isCustomVariablesPluginsEnabled()) {
                WidgetsList::add('Actions_SubmenuSitesearch', 'Actions_WidgetSearchCategories', 'Actions', 'getSiteSearchCategories');
            }
            WidgetsList::add('Actions_SubmenuSitesearch', 'Actions_WidgetSearchNoResultKeywords', 'Actions', 'getSiteSearchNoResultKeywords');
            WidgetsList::add('Actions_SubmenuSitesearch', 'Actions_WidgetPageUrlsFollowingSearch', 'Actions', 'getPageUrlsFollowingSiteSearch');
            WidgetsList::add('Actions_SubmenuSitesearch', 'Actions_WidgetPageTitlesFollowingSearch', 'Actions', 'getPageTitlesFollowingSiteSearch');
        }
    }

    function addMenus()
    {
        MenuMain::getInstance()->add('General_Actions', '', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 15);
        MenuMain::getInstance()->add('General_Actions', 'General_Pages', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 1);
        MenuMain::getInstance()->add('General_Actions', 'Actions_SubmenuPagesEntry', array('module' => 'Actions', 'action' => 'indexEntryPageUrls'), true, 2);
        MenuMain::getInstance()->add('General_Actions', 'Actions_SubmenuPagesExit', array('module' => 'Actions', 'action' => 'indexExitPageUrls'), true, 3);
        MenuMain::getInstance()->add('General_Actions', 'Actions_SubmenuPageTitles', array('module' => 'Actions', 'action' => 'indexPageTitles'), true, 4);
        MenuMain::getInstance()->add('General_Actions', 'General_Outlinks', array('module' => 'Actions', 'action' => 'indexOutlinks'), true, 6);
        MenuMain::getInstance()->add('General_Actions', 'General_Downloads', array('module' => 'Actions', 'action' => 'indexDownloads'), true, 7);

        if ($this->isSiteSearchEnabled()) {
            MenuMain::getInstance()->add('General_Actions', 'Actions_SubmenuSitesearch', array('module' => 'Actions', 'action' => 'indexSiteSearch'), true, 5);
        }
    }

    protected function isSiteSearchEnabled()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite == 0) {
            return false;
        }
        return Site::isSiteSearchEnabledFor($idSite);
    }

    /**
     * Compute all the actions along with their hierarchies.
     *
     * For each action we process the "interest statistics" :
     * visits, unique visitors, bounce count, sum visit length.
     */
    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    static public function checkCustomVariablesPluginEnabled()
    {
        if (!self::isCustomVariablesPluginsEnabled()) {
            throw new \Exception("To Track Site Search Categories, please ask the Piwik Administrator to enable the 'Custom Variables' plugin in Settings > Plugins.");
        }
    }

    static protected function isCustomVariablesPluginsEnabled()
    {
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables');
    }

    /**
     * @param $segmentName
     * @return int
     * @throws \Exception
     */
    protected function guessActionTypeFromSegment($segmentName)
    {
        if (stripos($segmentName, 'pageurl') !== false) {
            $actionType = Action::TYPE_ACTION_URL;
            return $actionType;
        } elseif (stripos($segmentName, 'pagetitle') !== false) {
            $actionType = Action::TYPE_ACTION_NAME;
            return $actionType;
        } elseif (stripos($segmentName, 'sitesearch') !== false) {
            $actionType = Action::TYPE_SITE_SEARCH;
            return $actionType;
        } else {
            throw new \Exception(" The segment $segmentName has an unexpected value.");
        }
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['Actions.getPageUrls'] = $this->getDisplayPropertiesForPageUrls();
        $properties['Actions.getEntryPageUrls'] = $this->getDisplayPropertiesForEntryPageUrls();
        $properties['Actions.getExitPageUrls'] = $this->getDisplayPropertiesForExitPageUrls();
        $properties['Actions.getSiteSearchKeywords'] = $this->getDisplayPropertiesForSiteSearchKeywords();
        $properties['Actions.getSiteSearchNoResultKeywords'] = $this->getDisplayPropertiesForSiteSearchNoResultKeywords();
        $properties['Actions.getSiteSearchCategories'] = $this->getDisplayPropertiesForSiteSearchCategories();
        $properties['Actions.getPageUrlsFollowingSiteSearch'] = $this->getDisplayPropertiesForGetPageUrlsOrTitlesFollowingSiteSearch(false);
        $properties['Actions.getPageTitlesFollowingSiteSearch'] = $this->getDisplayPropertiesForGetPageUrlsOrTitlesFollowingSiteSearch(true);
        $properties['Actions.getPageTitles'] = $this->getDisplayPropertiesForGetPageTitles();
        $properties['Actions.getEntryPageTitles'] = $this->getDisplayPropertiesForGetEntryPageTitles();
        $properties['Actions.getExitPageTitles'] = $this->getDisplayPropertiesForGetExitPageTitles();
        $properties['Actions.getDownloads'] = $this->getDisplayPropertiesForGetDownloads();
        $properties['Actions.getOutlinks'] = $this->getDisplayPropertiesForGetOutlinks();
    }

    private function addBaseDisplayProperties(&$result)
    {
        $result['datatable_js_type'] = 'ActionsDataTable';
        $result['visualization_properties']['table']['show_embedded_subtable'] = true;
        $result['search_recursive'] = true;
        $result['show_all_views_icons'] = false;
        $result['show_table_all_columns'] = false;
        $result['filter_limit'] = self::ACTIONS_REPORT_ROWS_DISPLAY;

        // if the flat parameter is not provided, make sure it is set to 0 in the URL,
        // so users can see that they can set it to 1 (see #3365)
        $result['custom_parameters'] = array('flat' => 0);

        if (ViewDataTable::shouldLoadExpanded()) {
            $result['visualization_properties']['table']['show_expanded'] = true;

            $result['filters'][] = function ($dataTable) {
                Actions::setDataTableRowLevels($dataTable);
            };
        }

        $result['filters'][] = function ($dataTable, $view) {
            if ($view->getViewDataTableId() == 'table') {
                $view->datatable_css_class = 'dataTableActions';
            }
        };

        return $result;
    }

    /**
     * @param \Piwik\DataTable $dataTable
     * @param int $level
     */
    public static function setDataTableRowLevels($dataTable, $level = 0)
    {
        foreach ($dataTable->getRows() as $row) {
            $row->setMetadata('css_class', 'level' . $level);

            $subtable = $row->getSubtable();
            if ($subtable) {
                self::setDataTableRowLevels($subtable, $level + 1);
            }
        }
    }

    private function addExcludeLowPopDisplayProperties(&$result)
    {
        if (Common::getRequestVar('enable_filter_excludelowpop', '0', 'string') != '0') {
            $result['filter_excludelowpop'] = 'nb_hits';
            $result['filter_excludelowpop_value'] = function () {
                // computing minimum value to exclude (2 percent of the total number of actions)
                $visitsInfo = \Piwik\Plugins\VisitsSummary\Controller::getVisitsSummary()->getFirstRow();
                $nbActions = $visitsInfo->getColumn('nb_actions');
                $nbActionsLowPopulationThreshold = floor(0.02 * $nbActions);

                // we remove 1 to make sure some actions/downloads are displayed in the case we have a very few of them
                // and each of them has 1 or 2 hits...
                return min($visitsInfo->getColumn('max_actions') - 1, $nbActionsLowPopulationThreshold - 1);
            };
        }
    }

    private function addPageDisplayProperties(&$result)
    {
        // add common translations
        $result['translations'] += array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime'),
        );

        // prettify avg_time_on_page column
        $getPrettyTimeFromSeconds = '\Piwik\MetricsFormatter::getPrettyTimeFromSeconds';
        $result['filters'][] = array('ColumnCallbackReplace', array('avg_time_on_page', $getPrettyTimeFromSeconds));

        // prettify avg_time_generation column
        $avgTimeCallback = function ($time) {
            return $time ? MetricsFormatter::getPrettyTimeFromSeconds($time, true, true, false) : "-";
        };
        $result['filters'][] = array('ColumnCallbackReplace', array('avg_time_generation', $avgTimeCallback));

        // add avg_generation_time tooltip
        $tooltipCallback = function ($hits, $min, $max) {
            if (!$hits) {
                return false;
            }

            return Piwik::translate("Actions_AvgGenerationTimeTooltip", array(
                                                                            $hits,
                                                                            "<br />",
                                                                            MetricsFormatter::getPrettyTimeFromSeconds($min),
                                                                            MetricsFormatter::getPrettyTimeFromSeconds($max)
                                                                       ));
        };
        $result['filters'][] = array('ColumnCallbackAddMetadata',
                                     array(
                                         array('nb_hits_with_time_generation', 'min_time_generation', 'max_time_generation'),
                                         'avg_time_generation_tooltip',
                                         $tooltipCallback
                                     )
        );

        $this->addExcludeLowPopDisplayProperties($result);
    }

    public function getDisplayPropertiesForPageUrls()
    {
        $result = array(
            'translations'       => array('label' => Piwik::translate('Actions_ColumnPageURL')),
            'columns_to_display' => array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                          'avg_time_on_page', 'exit_rate', 'avg_time_generation'),
        );

        $this->addPageDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForEntryPageUrls()
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);
        $reportUrl = Request::getCurrentUrlWithoutGenericFilters(array(
                                                                      'module' => 'Actions',
                                                                      'action' => $widget === false ? 'indexEntryPageUrls' : 'getEntryPageUrls'
                                                                 ));

        $result = array(
            'translations'       => array('label'              => Piwik::translate('Actions_ColumnEntryPageURL'),
                                          'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
                                          'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances')),
            'columns_to_display' => array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate'),
            'filter_sort_column' => 'entry_nb_visits',
            'filter_sort_order'  => 'desc',
            'title'              => Piwik::translate('Actions_SubmenuPagesEntry'),
            'related_reports'    => array(
                'Actions.getEntryPageTitles' => Piwik::translate('Actions_EntryPageTitles')
            ),
            'self_url'           => $reportUrl
        );

        $this->addPageDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForExitPageUrls()
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);
        $reportUrl = Request::getCurrentUrlWithoutGenericFilters(array(
                                                                      'module' => 'Actions',
                                                                      'action' => $widget === false ? 'indexExitPageUrls' : 'getExitPageUrls'
                                                                 ));

        $result = array(
            'translations'       => array('label'          => Piwik::translate('Actions_ColumnExitPageURL'),
                                          'exit_nb_visits' => Piwik::translate('General_ColumnExits')),
            'columns_to_display' => array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate'),
            'filter_sort_column' => 'exit_nb_visits',
            'filter_sort_order'  => 'desc',
            'title'              => Piwik::translate('Actions_SubmenuPagesExit'),
            'related_reports'    => array(
                'Actions.getExitPageTitles' => Piwik::translate('Actions_ExitPageTitles')
            ),
            'self_url'           => $reportUrl,
        );

        $this->addPageDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    private function addSiteSearchDisplayProperties(&$result)
    {
        $result['translations'] += array(
            'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
            'exit_rate'           => str_replace("% ", "%&nbsp;", Piwik::translate('Actions_ColumnSearchExits')),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch')
        );
        $result['show_bar_chart'] = false;
        $result['show_table_all_columns'] = false;
    }

    public function getDisplayPropertiesForSiteSearchKeywords()
    {
        $result = array(
            'translations'       => array('label' => Piwik::translate('General_ColumnKeyword')),
            'columns_to_display' => array('label', 'nb_visits', 'nb_pages_per_search', 'exit_rate'),
        );

        $this->addSiteSearchDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForSiteSearchNoResultKeywords()
    {
        $result = array(
            'translations'       => array('label', Piwik::translate('Actions_ColumnNoResultKeyword')),
            'columns_to_display' => array('label', 'nb_visits', 'exit_rate')
        );

        $this->addSiteSearchDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForSiteSearchCategories()
    {
        return array(
            'translations'             => array(
                'label'               => Piwik::translate('Actions_ColumnSearchCategory'),
                'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
                'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch')
            ),
            'columns_to_display'       => array('label', 'nb_visits', 'nb_pages_per_search'),
            'show_table_all_columns'   => false,
            'show_bar_chart'           => false,
            'visualization_properties' => array(
                'table' => array(
                    'disable_row_evolution' => false,
                )
            )
        );
    }

    public function getDisplayPropertiesForGetPageUrlsOrTitlesFollowingSiteSearch($isTitle)
    {
        $title = $isTitle ? Piwik::translate('Actions_WidgetPageTitlesFollowingSearch')
            : Piwik::translate('Actions_WidgetPageUrlsFollowingSearch');

        $relatedReports = array(
            'Actions.getPageTitlesFollowingSiteSearch' => Piwik::translate('Actions_WidgetPageTitlesFollowingSearch'),
            'Actions.getPageUrlsFollowingSiteSearch'   => Piwik::translate('Actions_WidgetPageUrlsFollowingSearch'),
        );

        $result = array(
            'translations'                => array(
                'label'                    => Piwik::translate('General_ColumnDestinationPage'),
                'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearch'),
                'nb_hits'                  => Piwik::translate('General_ColumnTotalPageviews')
            ),
            'columns_to_display'          => array('label', 'nb_hits_following_search', 'nb_hits'),
            'filter_sort_column'          => 'nb_hits_following_search',
            'filter_sort_order'           => 'desc',
            'show_exclude_low_population' => false,
            'title'                       => $title,
            'related_reports'             => $relatedReports
        );

        $this->addExcludeLowPopDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForGetPageTitles()
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);
        $reportUrl = Request::getCurrentUrlWithoutGenericFilters(array(
                                                                      'module' => 'Actions',
                                                                      'action' => $widget === false ? 'indexPageTitles' : 'getPageTitles'
                                                                 ));

        $result = array(
            'translations'       => array('label' => Piwik::translate('Actions_ColumnPageName')),
            'columns_to_display' => array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                          'avg_time_on_page', 'exit_rate', 'avg_time_generation'),
            'title'              => Piwik::translate('Actions_SubmenuPageTitles'),
            'related_reports'    => array(
                'Actions.getEntryPageTitles' => Piwik::translate('Actions_EntryPageTitles'),
                'Actions.getExitPageTitles'  => Piwik::translate('Actions_ExitPageTitles'),
            ),
            'self_url'           => $reportUrl
        );

        $this->addPageDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForGetEntryPageTitles()
    {
        $entryPageUrlAction =
            Common::getRequestVar('widget', false) === false ? 'indexEntryPageUrls' : 'getEntryPageUrls';

        $result = array(
            'translations'       => array(
                'label'              => Piwik::translate('Actions_ColumnEntryPageTitle'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'),
            ),
            'columns_to_display' => array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate'),
            'title'              => Piwik::translate('Actions_EntryPageTitles'),
            'related_reports'    => array(
                'Actions.getPageTitles'       => Piwik::translate('Actions_SubmenuPageTitles'),
                "Actions.$entryPageUrlAction" => Piwik::translate('Actions_SubmenuPagesEntry')
            ),
        );

        $this->addPageDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForGetExitPageTitles()
    {
        $exitPageUrlAction =
            Common::getRequestVar('widget', false) === false ? 'indexExitPageUrls' : 'getExitPageUrls';

        $result = array(
            'translations'       => array(
                'label'          => Piwik::translate('Actions_ColumnExitPageTitle'),
                'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
            ),
            'columns_to_display' => array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate'),
            'title'              => Piwik::translate('Actions_ExitPageTitles'),
            'related_reports'    => array(
                'Actions.getPageTitles'      => Piwik::translate('Actions_SubmenuPageTitles'),
                "Actions.$exitPageUrlAction" => Piwik::translate('Actions_SubmenuPagesExit'),
            ),
        );

        $this->addPageDisplayProperties($result);
        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForGetDownloads()
    {
        $result = array(
            'translations'                => array(
                'label'     => Piwik::translate('Actions_ColumnDownloadURL'),
                'nb_visits' => Piwik::translate('Actions_ColumnUniqueDownloads'),
                'nb_hits'   => Piwik::translate('General_Downloads'),
            ),
            'columns_to_display'          => array('label', 'nb_visits', 'nb_hits'),
            'show_exclude_low_population' => false
        );

        $this->addBaseDisplayProperties($result);

        return $result;
    }

    public function getDisplayPropertiesForGetOutlinks()
    {
        $result = array(
            'translations'                => array(
                'label'     => Piwik::translate('Actions_ColumnClickedURL'),
                'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicks'),
                'nb_hits'   => Piwik::translate('Actions_ColumnClicks'),
            ),
            'columns_to_display'          => array('label', 'nb_visits', 'nb_hits'),
            'show_exclude_low_population' => false
        );

        $this->addBaseDisplayProperties($result);

        return $result;
    }
}

