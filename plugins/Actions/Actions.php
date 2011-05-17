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
	static protected $limitLevelSubCategory = 10; // must be less than Piwik_DataTable::MAXIMUM_DEPTH_LEVEL_ALLOWED
	protected $maximumRowsInDataTableLevelZero;
	protected $maximumRowsInSubDataTable;
	protected $columnToSortByBeforeTruncation;
	
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
	
	function getListHooksRegistered()
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
	    
	public function getSegmentsMetadata($notification)
	{
		$segments =& $notification->getNotificationObject();
		$sqlFilter = array($this, 'getIdActionFromString');
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnEntryPageURL',
	        'segment' => 'entryPageUrl',
	        'sqlSegment' => 'visit_entry_idaction_url',
        	'sqlFilter' => $sqlFilter,
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnEntryPageTitle',
	        'segment' => 'entryPageTitle',
	        'sqlSegment' => 'visit_entry_idaction_name',
        	'sqlFilter' => $sqlFilter,
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnExitPageURL',
	        'segment' => 'exitPageUrl',
	        'sqlSegment' => 'visit_exit_idaction_url',
        	'sqlFilter' => $sqlFilter,
        );
        $segments[] = array(
	        'type' => 'dimension',
	        'category' => 'Actions_Actions',
	        'name' => 'Actions_ColumnExitPageTitle',
	        'segment' => 'exitPageTitle',
	        'sqlSegment' => 'visit_exit_idaction_name',
        	'sqlFilter' => $sqlFilter,
        );
	}

	function getIdActionFromString($string, $sqlField)
	{
		// Field is visit_*_idaction_url or visit_*_idaction_name
		$actionType = strpos($sqlField, '_name') === false
							? Piwik_Tracker_Action::TYPE_ACTION_URL
							: Piwik_Tracker_Action::TYPE_ACTION_NAME;
							 
		$sql = Piwik_Tracker_Action::getSqlSelectActionId();
		$bind = array($string, $string, $actionType);
		
		$idAction = Zend_Registry::get('db')->fetchOne($sql, $bind);
		return $idAction;
	}
	
	public function getReportMetadata($notification)
	{
		$reports = &$notification->getNotificationObject();
        
		$metrics = array(
            'nb_visits' => Piwik_Translate('General_ColumnUniquePageviews'),
            'nb_hits' => Piwik_Translate('General_ColumnPageviews'),
            'entry_nb_visits' => Piwik_Translate('General_ColumnEntrances'),
            'avg_time_on_page' => Piwik_Translate('General_ColumnAverageTimeOnPage'),
            'bounce_rate' => Piwik_Translate('General_ColumnBounceRate'),
            'exit_nb_visits' => Piwik_Translate('General_ColumnExits'),
            'exit_rate' => Piwik_Translate('General_ColumnExitRate'),
			// 'entry_bounce_count' => Piwik_Translate('General_ColumnBounces'),
    	);
    	
		$documentation = array(
			'nb_hits' => 'General_ColumnPageviewsDocumentation',
			'nb_visits' => 'General_ColumnUniquePageviewsDocumentation',
    		'bounce_rate' => 'General_ColumnPageBounceRateDocumentation',
    		'avg_time_on_page' => 'General_ColumnAverageTimeOnPageDocumentation',
			'exit_rate' => 'General_ColumnExitRateDocumentation'
		);
		$documentation = array_map('Piwik_Translate', $documentation);

		// Page views URLs and Page titles have the full set of metrics
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuPages'),
			'module' => 'Actions',
			'action' => 'getPageUrls',
    		'dimension' => Piwik_Translate('Actions_ColumnPageURL'),
			'metrics' => $metrics,
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_PagesReportDocumentation', '<br />')
					.'<br />'.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'order' => 1,
		);

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
			'order' => 3,
		);
		
		// Outlinks and downloads only report basic metrics
		$metrics = array(	'nb_hits' => Piwik_Translate('General_ColumnPageviews'),
            				'nb_visits',
    	);
    	
    	$documentation = array(
			'nb_hits' => Piwik_Translate('Actions_ColumnClicksDocumentation'),
			'nb_visits' => Piwik_Translate('Actions_ColumnUniqueClicksDocumentation')
		);
    	
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuOutlinks'),
			'module' => 'Actions',
			'action' => 'getOutlinks',
			'dimension' => Piwik_Translate('Actions_ColumnClickedURL'),
			'metrics' => $metrics,
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_OutlinksReportDocumentation').' '
					.Piwik_Translate('Actions_OutlinkDocumentation').'<br />'
					.Piwik_Translate('General_UsePlusMinusIconsDocumentation'),
			'processedMetrics' => false,
			'order' => 5,
		);
		$reports[] = array(
			'category' => Piwik_Translate('Actions_Actions'),
			'name' => Piwik_Translate('Actions_SubmenuDownloads'),
			'module' => 'Actions',
			'action' => 'getDownloads',
			'dimension' => Piwik_Translate('Actions_ColumnDownloadURL'),
			'metrics' => $metrics,
			'metricsDocumentation' => $documentation,
			'documentation' => Piwik_Translate('Actions_DownloadsReportDocumentation', '<br />'),
			'processedMetrics' => false,
			'order' => 7,
		);
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPagesEntry', 'Actions', 'getEntryPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPagesExit', 'Actions', 'getExitPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPages', 'Actions', 'getPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPageTitles', 'Actions', 'getPageTitles');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuOutlinks', 'Actions', 'getOutlinks');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuDownloads', 'Actions', 'getDownloads');
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
		// for BC, we read the old style delimiter first (see #1067)
		$actionDelimiter = Zend_Registry::get('config')->General->action_category_delimiter;
		if(empty($actionDelimiter))
		{
    		self::$actionUrlCategoryDelimiter =  Zend_Registry::get('config')->General->action_url_category_delimiter;
    		self::$actionTitleCategoryDelimiter =  Zend_Registry::get('config')->General->action_title_category_delimiter;
		}
		else
		{
			self::$actionUrlCategoryDelimiter = self::$actionTitleCategoryDelimiter = $actionDelimiter;
		}
		
		self::$defaultActionName = Zend_Registry::get('config')->General->action_default_name;
		$this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
		$this->maximumRowsInDataTableLevelZero = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_actions;
		$this->maximumRowsInSubDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_subtable_actions;
	}
	
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
	}
	
	/**
	 * Compute all the actions along with their hierarchies.
	 *
	 * For each action we process the "interest statistics" :
	 * visits, unique visitors, bouce count, sum visit length.
	 *
	 *
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

		/*
		 * Handling a custom segment when processing Page reports
		 */
		$segment = $archiveProcessing->getSegment();
		$segmentSql = $segment->getSql($archiveProcessing->getSegmentsAvailableForActions(), 'log_link_visit_action' );
		$sqlJoinVisitTable = $segmentSql['sql_join_visits'];
		$sqlSegmentWhere = '';
		if(!$segment->isEmpty())
		{
			$sqlSegmentWhere = ' AND '.$segmentSql['sql'];
		}
		$sqlBind = $segmentSql['bind'];

		/*
		 * Page URLs and Page names, general stats
		 */
		$queryString = "SELECT name,
							type,
							idaction,
							count(distinct log_link_visit_action.idvisit) as `". Piwik_Archive::INDEX_NB_VISITS ."`,
							count(distinct log_link_visit_action.idvisitor) as `". Piwik_Archive::INDEX_NB_UNIQ_VISITORS ."`,
							count(*) as `". Piwik_Archive::INDEX_PAGE_NB_HITS ."`
					FROM ".Piwik_Common::prefixTable('log_link_visit_action')." as log_link_visit_action
							LEFT JOIN ".Piwik_Common::prefixTable('log_action')." as log_action 
							ON (log_link_visit_action.%s = idaction)
							$sqlJoinVisitTable
					WHERE server_time >= ?
						AND server_time <= ?
						AND log_link_visit_action.idsite = ?
						AND %s IS NOT NULL
				 		$sqlSegmentWhere
					GROUP BY idaction
					ORDER BY `". Piwik_Archive::INDEX_PAGE_NB_HITS ."` DESC";
		$this->archiveDayQueryProcess($queryString, "idaction_url", $sqlBind, $archiveProcessing);
		$this->archiveDayQueryProcess($queryString, "idaction_name", $sqlBind, $archiveProcessing);

		
		/*
		 * Entry actions for Page URLs and Page names
		 */
		$sqlSegmentWhereVisit = str_replace("log_link_visit_action.", "log_visit.", $sqlSegmentWhere);
		$queryString = "SELECT %s as idaction,
							count(distinct idvisitor) as `". Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS ."`,
							count(*) as `". Piwik_Archive::INDEX_PAGE_ENTRY_NB_VISITS ."`,
							sum(visit_total_actions) as `". Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS ."`,
							sum(visit_total_time) as `". Piwik_Archive::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH ."`,
							sum(case visit_total_actions when 1 then 1 else 0 end) as `". Piwik_Archive::INDEX_PAGE_ENTRY_BOUNCE_COUNT ."`
					FROM ".Piwik_Common::prefixTable('log_visit')." AS log_visit
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND idsite = ?
				 		AND %s > 0
				 		$sqlSegmentWhereVisit
					GROUP BY %s, idaction";
		$this->archiveDayQueryProcess($queryString, "visit_entry_idaction_url", $sqlBind, $archiveProcessing);
		$this->archiveDayQueryProcess($queryString, "visit_entry_idaction_name", $sqlBind, $archiveProcessing);
		
		/*
		 * Exit actions
		 */
		$queryString = "SELECT %s as idaction,
							count(distinct idvisitor) as `". Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS ."`,
							count(*) as `". Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS ."`
				 	FROM ".Piwik_Common::prefixTable('log_visit')." AS log_visit
				 	WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
				 		AND idsite = ?
				 		AND %s > 0
				 		$sqlSegmentWhereVisit
				 	GROUP BY %s, idaction";
		$this->archiveDayQueryProcess($queryString, "visit_exit_idaction_url", $sqlBind, $archiveProcessing);
		$this->archiveDayQueryProcess($queryString, "visit_exit_idaction_name", $sqlBind, $archiveProcessing);
		
		/*
		 * Time per action
		 */
		$queryString = "SELECT %s as idaction,
							sum(time_spent_ref_action) as `".Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT."`
					FROM ".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action
							$sqlJoinVisitTable
					WHERE server_time >= ?
						AND server_time <= ?
				 		AND log_link_visit_action.idsite = ?
				 		AND time_spent_ref_action > 0
				 		AND %s > 0
				 		$sqlSegmentWhere
				 	GROUP BY %s, idaction";
		$this->archiveDayQueryProcess($queryString, "idaction_url_ref", $sqlBind, $archiveProcessing);
		$this->archiveDayQueryProcess($queryString, "idaction_name_ref", $sqlBind, $archiveProcessing);

		// Empty static cache
		self::$cacheParsedAction = array();
		
		// Record the final datasets
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}

	protected function archiveDayQueryProcess($queryString, $sprintfParameter, $bind, $archiveProcessing)
	{
		$queryString = str_replace("%s", $sprintfParameter, $queryString);
		$bind = array_merge(array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ), $bind);
		$resultSet = $archiveProcessing->db->query($queryString, $bind);
		$modified = $this->updateActionsTableWithRowQuery($resultSet, $sprintfParameter);
		return $modified;
	}
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_ACTION_URL]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_actions_url', $s);
		destroy($dataTable);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_DOWNLOAD]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized($this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_downloads', $s);
		destroy($dataTable);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_OUTLINK]);
		$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_outlink', $s);
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
		foreach($dataTable->getRows() as $row)
		{
			if(($idSubtable = $row->getIdSubDataTable()) !== null)
			{
				foreach(self::$invalidSummedColumnNameToDeleteFromDayArchive as $name)
				{
					$row->deleteColumn($name);
				}
				$this->deleteInvalidSummedColumnsFromDataTable(Piwik_DataTable_Manager::getInstance()->getTable($idSubtable));
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
	 * @return array of exploded elements from $name
	 */
	static public function getActionExplodedNames($name, $type)
	{
		$matches = array();
		$isUrl = false;
		$name = str_replace("\n", "", $name);
		preg_match('@^http[s]?://([^/]+)[/]?([^#]*)[#]?(.*)$@i', $name, $matches);

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
			
			if( empty($name) || substr($name, -1) == '/' )
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

		$split = explode($categoryDelimiter, $name, self::$limitLevelSubCategory);
		
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
    			// in some unknown case, the type field is NULL, as reported in #1082 - we ignore this page view
    			if(empty($actionType))
    			{
    				self::$cacheParsedAction[$row['idaction']] = false;
    				continue;
    			}
    
    			$currentTable = $this->parseActionNameCategoriesInDataTable($actionName, $actionType);
    			
				self::$cacheParsedAction[$row['idaction']] = $currentTable;
			}
			else
			{
				if(!isset(self::$cacheParsedAction[$row['idaction']]))
				{
					// This can happen when
					// - We select an entry page ID that was only seen yesterday, so wasn't selected in the first query
					// - We count time spent on a page, when this page was only seen yesterday
					continue;
				}
				$currentTable = self::$cacheParsedAction[$row['idaction']];
				// Action processed as "to skip" for some reasons
				if($currentTable === false)
				{
					continue;
				}
			}
			
			unset($row['name']);
			unset($row['type']);
			unset($row['idaction']);
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
	 * @return Piwik_DataTable
	 */
	protected function parseActionNameCategoriesInDataTable($actionName, $actionType)
	{
		// we work on the root table of the given TYPE (either ACTION_URL or DOWNLOAD or OUTLINK etc.)
		$currentTable =& $this->actionsTablesByType[$actionType];

		// go to the level of the subcategory
		$actionExplodedNames = $this->getActionExplodedNames($actionName, $actionType);
		$end = count($actionExplodedNames)-1;
		for($level = 0 ; $level < $end; $level++)
		{
			$actionCategory = $actionExplodedNames[$level];
			$currentTable =& $currentTable[$actionCategory];
		}
		$actionShortName = $actionExplodedNames[$end];

		// currentTable is now the array element corresponding the the action
		// at this point we may be for example at the 4th level of depth in the hierarchy
		$currentTable =& $currentTable[$actionShortName];
		
		// add the row to the matching sub category subtable
		if(!($currentTable instanceof Piwik_DataTable_Row))
		{
			$defaultColumnsNewRow = array(
    									'label' => (string)$actionShortName,
    									Piwik_Archive::INDEX_NB_VISITS => 0,
    									Piwik_Archive::INDEX_NB_UNIQ_VISITORS => 0,
    									Piwik_Archive::INDEX_PAGE_NB_HITS => 0,
    									Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT => 0,
        	);
			if( $actionType == Piwik_Tracker_Action::TYPE_ACTION_NAME )
			{
				$currentTable = new Piwik_DataTable_Row(array(
						Piwik_DataTable_Row::COLUMNS => $defaultColumnsNewRow,
					));
			}
			else
			{
				$currentTable = new Piwik_DataTable_Row(array(
						Piwik_DataTable_Row::COLUMNS => $defaultColumnsNewRow,
						Piwik_DataTable_Row::METADATA => array('url' => (string)$actionName),
					));
			}
		}
		return $currentTable;
	}
}

