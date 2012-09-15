<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
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
	static protected $actionUrlCategoryDelimiter = null;
	static protected $actionTitleCategoryDelimiter = null;
	static protected $defaultActionName = null;
	static protected $defaultActionNameWhenNotDefined = null;
	static protected $defaultActionUrlWhenNotDefined = null;
	protected $maximumRowsInDataTableLevelZero;
	protected $maximumRowsInSubDataTable;
	protected $columnToSortByBeforeTruncation;
	
	const OTHERS_ROW_KEY = '';
	
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('Actions_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
		return $info;
	}
	
	public function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenus',
			'API.getReportMetadata' => 'getReportMetadata',
		    'API.getSegmentsMetadata' => 'getSegmentsMetadata',
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
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnEntryPageURL',
	        'segment' => 'entryPageUrl',
	        'sqlSegment' => 'log_visit.visit_entry_idaction_url',
        	'sqlFilter' => $sqlFilter,
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnEntryPageTitle',
	        'segment' => 'entryPageTitle',
	        'sqlSegment' => 'log_visit.visit_entry_idaction_name',
        	'sqlFilter' => $sqlFilter,
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnExitPageURL',
	        'segment' => 'exitPageUrl',
	        'sqlSegment' => 'log_visit.visit_exit_idaction_url',
        	'sqlFilter' => $sqlFilter,
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnExitPageTitle',
	        'segment' => 'exitPageTitle',
	        'sqlSegment' => 'log_visit.visit_exit_idaction_name',
        	'sqlFilter' => $sqlFilter,
        );
        
        // single pages
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnPageURL',
	        'segment' => 'pageUrl',
	        'sqlSegment' => 'log_link_visit_action.idaction_url',
        	'sqlFilter' => $sqlFilter,
        	'acceptedValues' => "All these segments must be URL encoded, for example: ".urlencode('http://example.com/path/page?query'),
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnPageName',
	        'segment' => 'pageTitle',
	        'sqlSegment' => 'log_link_visit_action.idaction_name',
        	'sqlFilter' => $sqlFilter,
        );
	}

	/**
	 * Convert segment expression to an action ID or an SQL expression.
	 *
	 * This method is used as a sqlFilter-callback for the segments of this plugin.
	 * Usually, these callbacks only return a value that should be compared to the
	 * column in the database. In this case, that doesn't work since multiple IDs
	 * can match an expression (e.g. "pageUrl=@foo").
	 * @param string $string
	 * @param string $sqlField
	 * @param string $matchType
	 * @throws Exception
	 * @return array|int|string
	 */
	function getIdActionFromSegment($string, $sqlField, $matchType='==')
	{
		// Field is visit_*_idaction_url or visit_*_idaction_name
		$actionType = strpos($sqlField, '_name') === false
							? Piwik_Tracker_Action::TYPE_ACTION_URL
							: Piwik_Tracker_Action::TYPE_ACTION_NAME;
		
		if ($actionType == Piwik_Tracker_Action::TYPE_ACTION_URL)
		{
			// for urls trim protocol and www because it is not recorded in the db
			$string = preg_replace('@^http[s]?://(www\.)?@i', '', $string);
		}
		
        // exact matches work by returning the id directly
        if ($matchType == Piwik_SegmentExpression::MATCH_EQUAL 
			|| $matchType == Piwik_SegmentExpression::MATCH_NOT_EQUAL)
        {
            $sql = Piwik_Tracker_Action::getSqlSelectActionId();
            $bind = array($string, $string, $actionType);
            $idAction = Piwik_FetchOne($sql, $bind);
            // if the action is not found, we hack -100 to ensure it tries to match against an integer
            // otherwise binding idaction_name to "false" returns some rows for some reasons (in case &segment=pageTitle==Větrnásssssss)
            if(empty($idAction))
            {
                $idAction = -100;
            }
            return $idAction;
        }
        
        // now, we handle the cases =@ (contains) and !@ (does not contain)
                
        // build the expression based on the match type
        $sql = 'SELECT idaction FROM '.Piwik_Common::prefixTable('log_action').' WHERE ';
        switch ($matchType)
        {
            case '=@':
                // use concat to make sure, no %s occurs because some plugins use %s in their sql
                $sql .= '( name LIKE CONCAT("%", ?, "%") AND type = '.$actionType.' )';
                break;
            case '!@':
                $sql .= '( name NOT LIKE CONCAT("%", ?, "%") AND type = '.$actionType.' )';
                break;
            default:
                throw new Exception("This match type is not available for action-segments.");
                break;
        }
        
        return array(
            // mark that the returned value is an sql-expression instead of a literal value
            'SQL' => $sql,
        	'bind' => $string
        );
	}

	/**
	 * Returns metadata for available reports
	 *
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function getReportMetadata($notification)
	{
		$reports = &$notification->getNotificationObject();
        
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_Actions') . ' - ' . Piwik_Translate('General_MainMetrics'),
			'module' => 'Actions',
			'action' => 'get',
			'metrics' => array(
				'nb_pageviews' => Piwik_Translate('General_ColumnPageviews'),
				'nb_uniq_pageviews' => Piwik_Translate('General_ColumnUniquePageviews'),
				'nb_downloads' => Piwik_Translate('Actions_ColumnDownloads'),
				'nb_uniq_downloads' => Piwik_Translate('Actions_ColumnUniqueDownloads'),
				'nb_outlinks' => Piwik_Translate('Actions_ColumnOutlinks'),
				'nb_uniq_outlinks' => Piwik_Translate('Actions_ColumnUniqueOutlinks')
			),
			'metricsDocumentation' => array(
				'nb_pageviews' => Piwik_Translate('General_ColumnPageviewsDocumentation'),
				'nb_uniq_pageviews' => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
				'nb_downloads' => Piwik_Translate('Actions_ColumnClicksDocumentation'),
				'nb_uniq_downloads' => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation'),
				'nb_outlinks' => Piwik_Translate('Actions_ColumnClicksDocumentation'),
				'nb_uniq_outlinks' => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation')
			),
			'processedMetrics' => false,
			'order' => 1
		);
		
		$metrics = array(
			'nb_hits' => Piwik_Translate('General_ColumnPageviews'),
			'nb_visits' => Piwik_Translate('General_ColumnUniquePageviews'),
			'bounce_rate' => Piwik_Translate('General_ColumnBounceRate'),
			'avg_time_on_page' => Piwik_Translate('General_ColumnAverageTimeOnPage'),
			'exit_rate' => Piwik_Translate('General_ColumnExitRate')
		);
		
		$documentation = array(
			'nb_hits' => Piwik_Translate('General_ColumnPageviewsDocumentation'),
			'nb_visits' => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
			'bounce_rate' => Piwik_Translate('General_ColumnPageBounceRateDocumentation'),
			'avg_time_on_page' => Piwik_Translate('General_ColumnAverageTimeOnPageDocumentation'),
			'exit_rate' => Piwik_Translate('General_ColumnExitRateDocumentation')
		);
		
		// pages report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_PageUrls'),
			'module' => 'Actions',
			'action' => 'getPageUrls',
    		'dimension' => Piwik_Translate('Actions_ColumnPageURL'),
			'metrics' => $metrics,
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_PagesReportDocumentation', '<br />')
					.'<br />'.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getPageUrls',
			'order' => 2
		);
		
		// entry pages report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuPagesEntry'),
			'module' => 'Actions',
			'action' => 'getEntryPageUrls',
    		'dimension' => Piwik_Translate('Actions_ColumnPageURL'),
			'metrics' => array(
				'entry_nb_visits' => Piwik_Translate('General_ColumnEntrances'),
				'entry_bounce_count' => Piwik_Translate('General_ColumnBounces'),
				'bounce_rate' => Piwik_Translate('General_ColumnBounceRate'),
			),
			'metricsDocumentation' => array(
				'entry_nb_visits' => Piwik_Translate('General_ColumnEntrancesDocumentation'),
				'entry_bounce_count' => Piwik_Translate('General_ColumnBouncesDocumentation'),
				'bounce_rate' => Piwik_Translate('General_ColumnBounceRateForPageDocumentation')
			),
			'documentation' => Piwik_Translate('Actions_EntryPagesReportDocumentation', '<br />')
					.' '.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getEntryPageUrls',
			'order' => 3
		);
		
		// exit pages report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuPagesExit'),
			'module' => 'Actions',
			'action' => 'getExitPageUrls',
    		'dimension' => Piwik_Translate('Actions_ColumnPageURL'),
			'metrics' => array(
				'exit_nb_visits' => Piwik_Translate('General_ColumnExits'),
				'nb_visits' => Piwik_Translate('General_ColumnUniquePageviews'),
				'exit_rate' => Piwik_Translate('General_ColumnExitRate')
			),
			'metricsDocumentation' => array(
				'exit_nb_visits' => Piwik_Translate('General_ColumnExitsDocumentation'),
				'nb_visits' => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
				'exit_rate' => Piwik_Translate('General_ColumnExitRateDocumentation')
			),
			'documentation' => Piwik_Translate('Actions_ExitPagesReportDocumentation', '<br />')
					.' '.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getExitPageUrls',
			'order' => 4
		);
		
		// page titles report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuPageTitles'),
			'module' => 'Actions',
			'action' => 'getPageTitles',
			'dimension' => Piwik_Translate('Actions_ColumnPageName'),
			'metrics' => $metrics,
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_PageTitlesReportDocumentation', array('<br />', htmlentities('<title>'))),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getPageTitles',
			'order' => 5,
			
		);
		
		// entry page titles report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_EntryPageTitles'),
			'module' => 'Actions',
			'action' => 'getEntryPageTitles',
			'dimension' => Piwik_Translate('Actions_ColumnPageName'),
			'metrics' => array(
				'entry_nb_visits' => Piwik_Translate('General_ColumnEntrances'),
				'entry_bounce_count' => Piwik_Translate('General_ColumnBounces'),
				'bounce_rate' => Piwik_Translate('General_ColumnBounceRate'),
			),
			'metricsDocumentation' => array(
				'entry_nb_visits' => Piwik_Translate('General_ColumnEntrancesDocumentation'),
				'entry_bounce_count' => Piwik_Translate('General_ColumnBouncesDocumentation'),
				'bounce_rate' => Piwik_Translate('General_ColumnBounceRateForPageDocumentation')
			),
			'documentation' => Piwik_Translate('Actions_ExitPageTitlesReportDocumentation', '<br />')
					.' '.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getEntryPageTitles',
			'order' => 6
		);
		
		// exit page titles report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_ExitPageTitles'),
			'module' => 'Actions',
			'action' => 'getExitPageTitles',
			'dimension' => Piwik_Translate('Actions_ColumnPageName'),
			'metrics' => array(
				'exit_nb_visits' => Piwik_Translate('General_ColumnExits'),
				'nb_visits' => Piwik_Translate('General_ColumnUniquePageviews'),
				'exit_rate' => Piwik_Translate('General_ColumnExitRate')
			),
			'metricsDocumentation' => array(
				'exit_nb_visits' => Piwik_Translate('General_ColumnExitsDocumentation'),
				'nb_visits' => Piwik_Translate('General_ColumnUniquePageviewsDocumentation'),
				'exit_rate' => Piwik_Translate('General_ColumnExitRateDocumentation')
			),
			'documentation' => Piwik_Translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
					.' '.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getExitPageTitles',
			'order' => 7
		);
    	
		$documentation = array(
			'nb_visits' => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation'),
			'nb_hits' => Piwik_Translate('Actions_ColumnClicksDocumentation')
		);
		
		// outlinks report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuOutlinks'),
			'module' => 'Actions',
			'action' => 'getOutlinks',
			'dimension' => Piwik_Translate('Actions_ColumnClickedURL'),
			'metrics' => array(
				'nb_visits' => Piwik_Translate('Actions_ColumnUniqueClicks'),
				'nb_hits' => Piwik_Translate('Actions_ColumnClicks')
			),
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_OutlinksReportDocumentation').' '
					.Piwik_Translate('Actions_OutlinkDocumentation').'<br />'
					.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getOutlinks',
			'order' => 8,
		);
		
		// downloads report
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuDownloads'),
			'module' => 'Actions',
			'action' => 'getDownloads',
			'dimension' => Piwik_Translate('Actions_ColumnDownloadURL'),
			'metrics' => array(
				'nb_visits' => Piwik_Translate('Actions_ColumnUniqueDownloads'),
				'nb_hits' => Piwik_Translate('Actions_ColumnDownloads')
			),
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_DownloadsReportDocumentation', '<br />'),
			'processedMetrics' => false,
			'actionToLoadSubTables' => 'getDownloads',
			'order' => 9,
		);
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPages', 'Actions', 'getPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_WidgetPageTitles', 'Actions', 'getPageTitles');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuOutlinks', 'Actions', 'getOutlinks');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuDownloads', 'Actions', 'getDownloads');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_WidgetPagesEntry', 'Actions', 'getEntryPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_WidgetPagesExit', 'Actions', 'getExitPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_WidgetEntryPageTitles', 'Actions', 'getEntryPageTitles' );
		Piwik_AddWidget( 'Actions_Actions', 'Actions_WidgetExitPageTitles', 'Actions', 'getExitPageTitles' );
	}
	
	function addMenus()
	{
		Piwik_AddMenu('Actions_Actions', '', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 15);
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPages', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 1);
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPagesEntry', array('module' => 'Actions', 'action' => 'indexEntryPageUrls'), true, 2);
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPagesExit', array('module' => 'Actions', 'action' => 'indexExitPageUrls'), true, 3);
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPageTitles', array('module' => 'Actions', 'action' => 'indexPageTitles'), true, 4);
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuOutlinks', array('module' => 'Actions', 'action' => 'indexOutlinks'), true, 5);
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuDownloads', array('module' => 'Actions', 'action' => 'indexDownloads'), true, 6);
	}
	
	static protected $invalidSummedColumnNameToRenamedNameForPeriodArchive = array(
		Piwik_Archive::INDEX_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS,
	);
	
	protected static $invalidSummedColumnNameToDeleteFromDayArchive = array(
		Piwik_Archive::INDEX_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
	);
	
	public function __construct()
	{
		$this->reloadConfig();
	}
	
	public function reloadConfig()
	{
		// for BC, we read the old style delimiter first (see #1067)
		$actionDelimiter = @Piwik_Config::getInstance()->General['action_category_delimiter'];
		if(empty($actionDelimiter))
		{
			self::$actionUrlCategoryDelimiter = Piwik_Config::getInstance()->General['action_url_category_delimiter'];
			self::$actionTitleCategoryDelimiter = Piwik_Config::getInstance()->General['action_title_category_delimiter'];
		}
		else
		{
			self::$actionUrlCategoryDelimiter = self::$actionTitleCategoryDelimiter = $actionDelimiter;
		}
		
		self::$defaultActionName = Piwik_Config::getInstance()->General['action_default_name'];
		$this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
		$this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_actions'];
		$this->maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_actions'];
		$rankingQueryLimit = Piwik_Config::getInstance()->General['archiving_ranking_query_row_limit'];

		// Piwik_DataTable::MAXIMUM_DEPTH_LEVEL_ALLOWED must be greater than the category level limit
		Piwik_DataTable::setMaximumDepthLevelAllowedAtLeast(self::getSubCategoryLevelLimit() + 1);
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 * @return mixed
	 */
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;
		
		$dataTableToSum = array(
				'Actions_actions',
				'Actions_downloads',
				'Actions_outlink',
				'Actions_actions_url',
		);
		$archiveProcessing->archiveDataTable($dataTableToSum, self::$invalidSummedColumnNameToRenamedNameForPeriodArchive, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);
		
		$archiveProcessing->archiveNumericValuesSum(array(
			'Actions_nb_pageviews',
			'Actions_nb_uniq_pageviews',
			'Actions_nb_downloads',
			'Actions_nb_uniq_downloads',
			'Actions_nb_outlinks',
			'Actions_nb_uniq_outlinks'
		));
	}
	
	/**
	 * Compute all the actions along with their hierarchies.
	 *
	 * For each action we process the "interest statistics" :
	 * visits, unique visitors, bounce count, sum visit length.
	 *
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function archiveDay( $notification )
	{
		/* @var $archiveProcessing Piwik_ArchiveProcessing_Day */
		$archiveProcessing = $notification->getNotificationObject();
		
		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;
		
		$this->actionsTablesByType = array(
			Piwik_Tracker_Action::TYPE_ACTION_URL => array(),
			Piwik_Tracker_Action::TYPE_DOWNLOAD => array(),
			Piwik_Tracker_Action::TYPE_OUTLINK => array(),
			Piwik_Tracker_Action::TYPE_ACTION_NAME => array(),
		);
		
		// This row is used in the case where an action is know as an exit_action
		// but this action was not properly recorded when it was hit in the first place
		// so we add this fake row information to make sure there is a nb_hits, etc. column for every action
		$this->defaultRow = new Piwik_DataTable_Row(array(
							Piwik_DataTable_Row::COLUMNS => array(
											Piwik_Archive::INDEX_NB_VISITS => 1,
											Piwik_Archive::INDEX_NB_UNIQ_VISITORS => 1,
											Piwik_Archive::INDEX_PAGE_NB_HITS => 1,
										)));
		
		$rankingQueryLimit = self::getRankingQueryLimit();
		$rankingQuery = false;
		
		/*
		 * Page URLs and Page names, general stats
		 */
		
		$select = "log_action.name,
				log_action.type,
				log_action.idaction,
				log_action.url_prefix,
				count(distinct log_link_visit_action.idvisit) as `". Piwik_Archive::INDEX_NB_VISITS ."`,
				count(distinct log_link_visit_action.idvisitor) as `". Piwik_Archive::INDEX_NB_UNIQ_VISITORS ."`,
				count(*) as `". Piwik_Archive::INDEX_PAGE_NB_HITS ."`";
		
		$from = array(
			"log_link_visit_action",
			array(
				"table" => "log_action",
				"joinOn" => "log_link_visit_action.%s = log_action.idaction"
			)
		);
		
		$where = "log_link_visit_action.server_time >= ?
				AND log_link_visit_action.server_time <= ?
				AND log_link_visit_action.idsite = ?
				AND log_link_visit_action.%s IS NOT NULL";
			
		$groupBy = "log_action.idaction";
		$orderBy = "`". Piwik_Archive::INDEX_PAGE_NB_HITS ."` DESC, name ASC";
		
		if ($rankingQueryLimit > 0)
		{
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn(array('idaction', 'name'));
			$rankingQuery->addColumn(array('url_prefix', Piwik_Archive::INDEX_NB_UNIQ_VISITORS));
			$rankingQuery->addColumn(array(Piwik_Archive::INDEX_PAGE_NB_HITS, Piwik_Archive::INDEX_NB_VISITS), 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));
		}
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"idaction_url", $archiveProcessing, $rankingQuery);
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"idaction_name", $archiveProcessing, $rankingQuery);
		
		/*
		 * Entry actions for Page URLs and Page names
		 */
		if ($rankingQueryLimit > 0)
		{
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn('idaction');
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS);
			$rankingQuery->addColumn(array(Piwik_Archive::INDEX_PAGE_ENTRY_NB_VISITS,
										   Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS,
										   Piwik_Archive::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH,
										   Piwik_Archive::INDEX_PAGE_ENTRY_BOUNCE_COUNT), 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));
			
			$extraSelects = 'log_action.type, log_action.name,';
			$from = array(
				"log_visit",
				array(
					"table" => "log_action",
					"joinOn" => "log_visit.%s = log_action.idaction"
				)
			);
			$orderBy = "`".Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS."` DESC, log_action.name ASC";
		}
		else
		{
			$extraSelects = false;
			$from = "log_visit";
			$orderBy = false;
		}
		
		$select = "log_visit.%s as idaction, $extraSelects
				count(distinct log_visit.idvisitor) as `". Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS ."`,
				count(*) as `". Piwik_Archive::INDEX_PAGE_ENTRY_NB_VISITS ."`,
				sum(log_visit.visit_total_actions) as `". Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS ."`,
				sum(log_visit.visit_total_time) as `". Piwik_Archive::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH ."`,
				sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `". Piwik_Archive::INDEX_PAGE_ENTRY_BOUNCE_COUNT ."`";
		
		$where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite = ?
		 		AND log_visit.%s > 0";
		
		$groupBy = "log_visit.%s, idaction";
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"visit_entry_idaction_url", $archiveProcessing, $rankingQuery);
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"visit_entry_idaction_name", $archiveProcessing, $rankingQuery);
		
		/*
		 * Exit actions
		 */
		if ($rankingQueryLimit > 0)
		{
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn('idaction');
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS);
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS, 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));
			
			$extraSelects = 'log_action.type, log_action.name,';
			$from = array(
				"log_visit",
				array(
					"table" => "log_action",
					"joinOn" => "log_visit.%s = log_action.idaction"
				)
			);
			$orderBy = "`".Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS."` DESC, log_action.name ASC";
		}
		else
		{
			$extraSelects = false;
			$from = "log_visit";
			$orderBy = false;
		}
		
		$select = "log_visit.%s as idaction, $extraSelects
				count(distinct log_visit.idvisitor) as `". Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS ."`,
				count(*) as `". Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS ."`";
		
		$where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
		 		AND log_visit.idsite = ?
		 		AND log_visit.%s > 0";
		
		$groupBy = "log_visit.%s, idaction";
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"visit_exit_idaction_url", $archiveProcessing, $rankingQuery);
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"visit_exit_idaction_name", $archiveProcessing, $rankingQuery);
		
		/*
		 * Time per action
		 */
		if ($rankingQueryLimit > 0)
		{
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn('idaction');
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT, 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));
			
			$extraSelects = "log_action.type, log_action.name, count(*) as `".Piwik_Archive::INDEX_PAGE_NB_HITS."`,";
			$from = array(
				"log_link_visit_action",
				array(
					"table" => "log_action",
					"joinOn" => "log_link_visit_action.%s = log_action.idaction"
				)
			);
			$orderBy = "`".Piwik_Archive::INDEX_PAGE_NB_HITS."` DESC, log_action.name ASC";
		}
		else
		{
			$extraSelects = false;
			$from = "log_link_visit_action";
			$orderBy = false;
		}
		
		$select = "log_link_visit_action.%s as idaction, $extraSelects
				sum(log_link_visit_action.time_spent_ref_action) as `".Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT."`";
		
		$where = "log_link_visit_action.server_time >= ?
				AND log_link_visit_action.server_time <= ?
		 		AND log_link_visit_action.idsite = ?
		 		AND log_link_visit_action.time_spent_ref_action > 0
		 		AND log_link_visit_action.%s > 0";
		
		$groupBy = "log_link_visit_action.%s, idaction";
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"idaction_url_ref", $archiveProcessing, $rankingQuery);
		
		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
				"idaction_name_ref", $archiveProcessing, $rankingQuery);

		// Empty static cache
		self::$cacheParsedAction = array();
		
		// Record the final datasets
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}

	/**
	 * @param $select
	 * @param $from
	 * @param $where
	 * @param $orderBy
	 * @param $groupBy
	 * @param $sprintfField
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 * @param Piwik_RankingQuery|false $rankingQuery
	 * @return int
	 */
	protected function archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			$sprintfField, $archiveProcessing, $rankingQuery = false)
	{
		// idaction field needs to be set in select clause before calling getSelectQuery().
		// if a complex segmentation join is needed, the field needs to be propagated
		// to the outer select. therefore, $segment needs to know about it. 
		$select = sprintf($select, $sprintfField);
		
		// get query with segmentation
		$bind = array();
		$query = $archiveProcessing->getSegment()->getSelectQuery(
					$select, $from, $where, $bind, $orderBy, $groupBy);
		
		// replace the rest of the %s
		$querySql = str_replace("%s", $sprintfField, $query['sql']);
		
		// apply ranking query
		if ($rankingQuery)
		{
			$querySql = $rankingQuery->generateQuery($querySql);
		}
		
		// extend bindings
		$bind = array_merge(array($archiveProcessing->getStartDatetimeUTC(),
					$archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite), $query['bind']);
		
		// get result
		$resultSet = $archiveProcessing->db->query($querySql, $bind);
		$modified = $this->updateActionsTableWithRowQuery($resultSet, $sprintfField);
		return $modified;
	}
	
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_ACTION_URL]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_actions_url', $s);
		$archiveProcessing->insertNumericRecord('Actions_nb_pageviews', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS)));
		$archiveProcessing->insertNumericRecord('Actions_nb_uniq_pageviews', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS)));
		destroy($dataTable);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_DOWNLOAD]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized($this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_downloads', $s);
		$archiveProcessing->insertNumericRecord('Actions_nb_downloads', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS)));
		$archiveProcessing->insertNumericRecord('Actions_nb_uniq_downloads', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS)));
		destroy($dataTable);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_OUTLINK]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_outlink', $s);
		$archiveProcessing->insertNumericRecord('Actions_nb_outlinks', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS)));
		$archiveProcessing->insertNumericRecord('Actions_nb_uniq_outlinks', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS)));
		destroy($dataTable);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_ACTION_NAME]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_actions', $s);
		destroy($dataTable);

		destroy($this->actionsTablesByType);
	}
	
	protected function deleteInvalidSummedColumnsFromDataTable($dataTable)
	{
		foreach($dataTable->getRows() as $id => $row)
		{
			if(($idSubtable = $row->getIdSubDataTable()) !== null || $id === Piwik_DataTable::ID_SUMMARY_ROW)
			{
				foreach(self::$invalidSummedColumnNameToDeleteFromDayArchive as $name)
				{
					$row->deleteColumn($name);
				}
				
				if ($idSubtable !== null)
				{
					$subtable = Piwik_DataTable_Manager::getInstance()->getTable($idSubtable);
					$this->deleteInvalidSummedColumnsFromDataTable($subtable);
				}
			}
		}
	}
	
	/**
	 * Explodes action name into an array of elements.
	 *
	 * for downloads:
	 *  we explode link http://piwik.org/some/path/piwik.zip into an array( 'piwik.org', '/some/path/piwik.zip' );
	 *
	 * for outlinks:
	 *  we explode link http://dev.piwik.org/some/path into an array( 'dev.piwik.org', '/some/path' );
	 *
	 * for action urls:
	 *  we explode link http://piwik.org/some/path into an array( 'some', 'path' );
	 *
	 * for action names:
	 *   we explode name 'Piwik / Category 1 / Category 2' into an array('Piwik', 'Category 1', 'Category 2');
	 *
	 * @param string action name
	 * @param int action type
	 * @param int url prefix (only used for TYPE_ACTION_URL)
	 * @return array of exploded elements from $name
	 */
	static public function getActionExplodedNames($name, $type, $urlPrefix=null)
	{
		$matches = array();
		$isUrl = false;
		$name = str_replace("\n", "", $name);
		
		$urlRegexAfterDomain = '([^/]+)[/]?([^#]*)[#]?(.*)';
		if ($urlPrefix === null)
		{
			// match url with protocol (used for outlinks / downloads)
			$urlRegex = '@^http[s]?://'.$urlRegexAfterDomain.'$@i';
		}
		else
		{
			// the name is a url that does not contain protocol and www anymore
			// we know that normalization has been done on db level because $urlPrefix is set
			$urlRegex = '@^'.$urlRegexAfterDomain.'$@i';
		}
		
		preg_match($urlRegex, $name, $matches);
		if( count($matches) )
		{
			$isUrl = true;
			$urlHost = $matches[1];
			$urlPath = $matches[2];
			$urlAnchor = $matches[3];
		}
		
		if($type == Piwik_Tracker_Action::TYPE_DOWNLOAD
			|| $type == Piwik_Tracker_Action::TYPE_OUTLINK)
		{
			if( $isUrl )
			{
				return array(trim($urlHost), '/' . trim($urlPath));
			}
		}

		if( $isUrl )
		{
			$name = $urlPath;
			
			if( $name === '' || substr($name, -1) == '/' )
			{
				$name .= self::$defaultActionName;
			}
		}
		
	    if($type == Piwik_Tracker_Action::TYPE_ACTION_NAME)
	    {
	    	$categoryDelimiter = self::$actionTitleCategoryDelimiter;
	    }
	    else
	    {
	    	$categoryDelimiter = self::$actionUrlCategoryDelimiter;
	    }
	    
		if(empty($categoryDelimiter))
		{
			return array( trim($name) );
		}

		$split = explode($categoryDelimiter, $name, self::getSubCategoryLevelLimit());
		
		// trim every category and remove empty categories
		$split = array_map('trim', $split);
		$split = array_filter($split, 'strlen');

		// forces array key to start at 0
		$split = array_values($split);
		
		if( empty($split) )
		{
			$defaultName = self::getUnknownActionName($type);
			return array( trim($defaultName) );
		}

		$lastPageName = end($split);
		// we are careful to prefix the page URL / name with some value
		// so that if a page has the same name as a category
		// we don't merge both entries
		if($type != Piwik_Tracker_Action::TYPE_ACTION_NAME )
		{
			$lastPageName = '/' . $lastPageName;
		}
		else
		{
			$lastPageName = ' ' . $lastPageName;
		}
		$split[count($split)-1] = $lastPageName;
		return array_values( $split );
	}
	
	static protected function getUnknownActionName($type)
	{
		if(empty(self::$defaultActionNameWhenNotDefined))
		{
			self::$defaultActionNameWhenNotDefined = Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageName'));
			self::$defaultActionUrlWhenNotDefined = Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageURL'));
		}
	    if($type == Piwik_Tracker_Action::TYPE_ACTION_NAME) {
	        return self::$defaultActionNameWhenNotDefined;
	    } 
	    return self::$defaultActionUrlWhenNotDefined;
	}
	
	const CACHE_PARSED_INDEX_NAME = 0;
	const CACHE_PARSED_INDEX_TYPE = 1;
	static $cacheParsedAction = array();
	
	/**
	 * Get cached action row by id & type. If $idAction is set to -1, the 'Others' row
	 * for the specific action type will be returned.
	 * 
	 * @param int $idAction
	 * @param int $actionType
	 * @return Piwik_DataTable_Row|false
	 */
	private static function getCachedActionRow( $idAction, $actionType )
	{
		$cacheLabel = self::getCachedActionRowKey($idAction, $actionType);
		
		if (!isset(self::$cacheParsedAction[$cacheLabel]))
		{
			// This can happen when
			// - We select an entry page ID that was only seen yesterday, so wasn't selected in the first query
			// - We count time spent on a page, when this page was only seen yesterday
			return false;
		}
		
		return self::$cacheParsedAction[$cacheLabel];
	}
	
	/**
	 * Set cached action row for an id & type.
	 * 
	 * @param int $idAction
	 * @param int $actionType
	 * @param Piwik_DataTable_Row
	 */
	private static function setCachedActionRow( $idAction, $actionType, $actionRow )
	{
		$cacheLabel = self::getCachedActionRowKey($idAction, $actionType);
		self::$cacheParsedAction[$cacheLabel] = $actionRow;
	}
	
	/**
	 * Gets the key for the cache of action rows from an action ID and type.
	 * 
	 * @param int $idAction
	 * @param int $actionType
	 * @return string|int
	 */
	private static function getCachedActionRowKey( $idAction, $actionType )
	{
		return $idAction == Piwik_DataTable::LABEL_SUMMARY_ROW ? $actionType.'_others' : $idAction;
	}
	
	/**
	 * @param Zend_Db_Statement|PDOStatement $query
	 * @param string|bool $fieldQueried
	 * @return int
	 */
	protected function updateActionsTableWithRowQuery($query, $fieldQueried = false)
	{
		$rowsProcessed = 0;
		while( $row = $query->fetch() )
		{
			if(empty($row['idaction']))
			{
				$row['type'] = ($fieldQueried == 'idaction_url' ? Piwik_Tracker_Action::TYPE_ACTION_URL : Piwik_Tracker_Action::TYPE_ACTION_NAME);
				// This will be replaced with 'X not defined' later 
				$row['name'] = '';
				// Yes, this is kind of a hack, so we don't mix 'page url not defined' with 'page title not defined' etc.
				$row['idaction'] = -$row['type'];
			}
			// Only the first query will contain the name and type of actions, for performance reasons
			if(isset($row['name'])
				&& isset($row['type']))
			{
				$actionName = $row['name'];
				$actionType = $row['type'];
				$urlPrefix = $row['url_prefix'];
				$idaction = $row['idaction'];
				
    			// in some unknown case, the type field is NULL, as reported in #1082 - we ignore this page view
    			if(empty($actionType))
    			{
    				if ($idaction != Piwik_DataTable::LABEL_SUMMARY_ROW)
    				{
	    				self::setCachedActionRow($idaction, $actionType, false);
    				}
    				
    				continue;
    			}
    
    			$currentTable = $this->parseActionNameCategoriesInDataTable($actionName, $actionType, $urlPrefix);
    			
    			self::setCachedActionRow($idaction, $actionType, $currentTable);
			}
			else
			{
				$currentTable = self::getCachedActionRow($row['idaction'], $row['type']);
				
				// Action processed as "to skip" for some reasons
				if($currentTable === false)
				{
					continue;
				}
			}
			
			unset($row['name']);
			unset($row['type']);
			unset($row['idaction']);
			unset($row['url_prefix']);
			
			if (is_null($currentTable))
			{
				continue;
			}
			
			foreach($row as $name => $value)
			{
				// in some edge cases, we have twice the same action name with 2 different idaction
				// this happens when 2 visitors visit the same new page at the same time, there is a SELECT and an INSERT for each new page,
				// and in between the two the other visitor comes.
				// here we handle the case where there is already a row for this action name, if this is the case we add the value
				if(($alreadyValue = $currentTable->getColumn($name)) !== false)
				{
					$currentTable->setColumn($name, $alreadyValue+$value);
				}
				else
				{
					$currentTable->addColumn($name, $value);
				}
			}
			
			// if the exit_action was not recorded properly in the log_link_visit_action
			// there would be an error message when getting the nb_hits column
			// we must fake the record and add the columns
			if($currentTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS) === false)
			{
				// to test this code: delete the entries in log_link_action_visit for
				//  a given exit_idaction_url
				foreach($this->defaultRow->getColumns() as $name => $value)
				{
					$currentTable->addColumn($name, $value);
				}
			}
			$rowsProcessed++;
		}

		// just to make sure php copies the last $currentTable in the $parentTable array
		$currentTable =& $this->actionsTablesByType;
		return $rowsProcessed;
	}
	
	/**
	 * Given a page name and type, builds a recursive datatable where
	 * each level of the tree is a category, based on the page name split by a delimiter (slash / by default)
	 *
	 * @param string $actionName
	 * @param int $actionType
	 * @param int $urlPrefix
	 * @return Piwik_DataTable
	 */
	protected function parseActionNameCategoriesInDataTable($actionName, $actionType, $urlPrefix=null)
	{
		// we work on the root table of the given TYPE (either ACTION_URL or DOWNLOAD or OUTLINK etc.)
		$currentTable =& $this->actionsTablesByType[$actionType];
		
		// check for ranking query cut-off
		if ($actionName == Piwik_DataTable::LABEL_SUMMARY_ROW)
		{
			return $this->createOthersRow($currentTable, $actionType);
		}
		
		// go to the level of the subcategory
		$actionExplodedNames = $this->getActionExplodedNames($actionName, $actionType, $urlPrefix);
		$end = count($actionExplodedNames)-1;
		$maxRows = $this->maximumRowsInDataTableLevelZero;
		for($level = 0 ; $level < $end; $level++)
		{
			$actionCategory = $actionExplodedNames[$level];
			
			$othersRow = $this->getOthersRowIfTableFull($currentTable, $actionCategory, $actionType, $maxRows);
			if ($othersRow)
			{
				return $othersRow;
			}
			
			$currentTable =& $currentTable[$actionCategory];
			$maxRows = $this->maximumRowsInSubDataTable;
		}
		$actionShortName = $actionExplodedNames[$end];
		
		$othersRow = $this->getOthersRowIfTableFull($currentTable, $actionShortName, $actionType, $maxRows);
		if ($othersRow)
		{
			return $othersRow;
		}
		
		// currentTable is now the array element corresponding the the action
		// at this point we may be for example at the 4th level of depth in the hierarchy
		$currentTable =& $currentTable[$actionShortName];
		
		// add the row to the matching sub category subtable
		if(!($currentTable instanceof Piwik_DataTable_Row))
		{
			$currentTable = $this->createActionsTableRow(
				(string)$actionShortName, $actionType, $actionName, $urlPrefix);
		}
		return $currentTable;
	}
	
	/**
	 * Checks if the given table is full (has the maximum number of rows allowed in config)
	 * and if so, returns an 'Others' summary row. Returns false if the table is not full.
	 * 
	 * @param array $currentTable Array of Piwik_DataTable_Rows.
	 * @param string $actionCategory The current table key.
	 * @param int $actionType The action type.
	 * @param int $maxRows The maximum number of rows allowed in $currentTable.
	 * @return Piwik_DataTable_Row|false
	 */
	private function getOthersRowIfTableFull( &$currentTable, $actionCategory, $actionType, $maxRows )
	{
		if (!isset($currentTable[$actionCategory]))
		{
			if (count($currentTable) == $maxRows - 1)
			{
				return $this->createOthersRow($currentTable, $actionType);
			}
			else if (count($currentTable) >= $maxRows)
			{
				return $currentTable[self::OTHERS_ROW_KEY]; // return existing 'others' row
			}
		}
		
		return false;
	}
	
	/**
	 * Create an 'Others' action row. The row created by this function is used
	 * as the summary row in a truncated DataTable.
	 * 
	 * @param array $currentTable The array of rows to add the 'Others' row to.
	 * @param int $actionType The type of actions the row will hold stats for.
	 * @return Piwik_DataTable_Row
	 */
	private function createOthersRow( &$currentTable, $actionType )
	{
		// create other row and return it
		$othersRow = $this->createActionsTableRow(Piwik_DataTable::LABEL_SUMMARY_ROW, $actionType);
		$othersRow->setMetadata('issummaryrow', true);
		
		$currentTable[self::OTHERS_ROW_KEY] = $othersRow;
		return $othersRow;
	}
	
	/**
	 * Creates a new empty row for DataTables created by this plugin.
	 * 
	 * @param string $label The row label.
	 * @param int $actionType The action type of the action the row will describe.
	 * @param string $actionName
	 * @param string $urlPrefix
	 * @return Piwik_DataTable_Row
	 */
	private function createActionsTableRow( $label, $actionType, $actionName = null, $urlPrefix = null )
	{
		$defaultColumnsNewRow = array(
									'label' => $label,
									Piwik_Archive::INDEX_NB_VISITS => 0,
									Piwik_Archive::INDEX_NB_UNIQ_VISITORS => 0,
									Piwik_Archive::INDEX_PAGE_NB_HITS => 0,
									Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT => 0,
    	);
		if( $actionType == Piwik_Tracker_Action::TYPE_ACTION_NAME )
		{
			return new Piwik_DataTable_Row(array(
					Piwik_DataTable_Row::COLUMNS => $defaultColumnsNewRow,
				));
		}
		else
		{
			$metadata = array();
			if (!is_null($actionName))
			{
				$url = Piwik_Tracker_Action::reconstructNormalizedUrl((string)$actionName, $urlPrefix);
				$metadata['url'] = $url;
			}
			
			return new Piwik_DataTable_Row(array(
					Piwik_DataTable_Row::COLUMNS => $defaultColumnsNewRow,
					Piwik_DataTable_Row::METADATA => $metadata,
				));
		}
	}
	
	/**
	 * Returns the configured sub-category level limit.
	 * 
	 * @return int
	 */
	public static function getSubCategoryLevelLimit()
	{
		return Piwik_Config::getInstance()->General['action_category_level_limit'];
	}
	
	/**
	 * Returns the limit to use with RankingQuery for this plugin.
	 * 
	 * @return int
	 */
	public static function getRankingQueryLimit()
	{
		$configGeneral = Piwik_Config::getInstance()->General;
		$configLimit = $configGeneral['archiving_ranking_query_row_limit'];
		return $configLimit == 0 ? 0 : max(
			$configLimit,
			$configGeneral['datatable_archiving_maximum_rows_actions'],
			$configGeneral['datatable_archiving_maximum_rows_subtable_actions']
		);
	}
}

