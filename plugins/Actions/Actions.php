<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
			'name' => 'Actions',
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
		);
		return $hooks;
	}
	
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
		self::$defaultActionNameWhenNotDefined = Zend_Registry::get('config')->General->action_default_name_when_not_defined;
		self::$defaultActionUrlWhenNotDefined = Zend_Registry::get('config')->General->action_default_url_when_not_defined;
		$this->columnToSortByBeforeTruncation = 'nb_visits';
		$this->maximumRowsInDataTableLevelZero = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_actions;
		$this->maximumRowsInSubDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_subtable_actions;
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPages', 'Actions', 'getPageUrls');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPageTitles', 'Actions', 'getPageTitles');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuOutlinks', 'Actions', 'getOutlinks');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuDownloads', 'Actions', 'getDownloads');
	}
	
	function addMenus()
	{
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPages', array('module' => 'Actions', 'action' => 'getPageUrls'));
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPageTitles', array('module' => 'Actions', 'action' => 'getPageTitles'));
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuOutlinks', array('module' => 'Actions', 'action' => 'getOutlinks'));
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuDownloads', array('module' => 'Actions', 'action' => 'getDownloads'));
	}
	
	static protected $invalidSummedColumnNameToRenamedNameForPeriodArchive = array(
		'nb_uniq_visitors' => 'sum_daily_nb_uniq_visitors', 
		'entry_nb_uniq_visitors' => 'sum_daily_entry_nb_uniq_visitors', 
		'exit_nb_uniq_visitors' => 'sum_daily_exit_nb_uniq_visitors',
	);
	
	protected static $invalidSummedColumnNameToDeleteFromDayArchive = array(
		'nb_uniq_visitors',
		'entry_nb_uniq_visitors', 
		'exit_nb_uniq_visitors',
	);
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
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
		//TODO Actions should use integer based keys like other archive in piwik
		/* @var $archiveProcessing Piwik_ArchiveProcessing */
		$archiveProcessing = $notification->getNotificationObject();
		
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
											'nb_visits' => 1,
											'nb_uniq_visitors' => 1,
											'nb_hits' => 1,	
										)));

		/*
		 * Actions urls global information
		 */
		$query = "SELECT 	name,
							type,
							count(distinct t1.idvisit) as nb_visits, 
							count(distinct visitor_idcookie) as nb_uniq_visitors,
							count(*) as nb_hits							
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".$archiveProcessing->logVisitActionTable." as t2 USING (idvisit))
							LEFT JOIN ".$archiveProcessing->logActionTable." as t3 ON (t2.idaction_url = t3.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND idsite = ?
					GROUP BY t3.idaction
					ORDER BY nb_hits DESC";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);

		/*
		 * Actions names global information
		 */
		$query = "SELECT 	name,
							type,
							count(distinct t1.idvisit) as nb_visits,
							count(distinct visitor_idcookie) as nb_uniq_visitors,
							count(*) as nb_hits
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".$archiveProcessing->logVisitActionTable." as t2 USING (idvisit))
							LEFT JOIN ".$archiveProcessing->logActionTable." as t3 ON (t2.idaction_name = t3.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND idsite = ?
					GROUP BY t3.idaction
					ORDER BY nb_hits DESC";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);

		/*
		 * Entry actions
		 */
		$query = "SELECT 	name,
							type,
							count(distinct visitor_idcookie) as entry_nb_uniq_visitors, 
							count(*) as entry_nb_visits,
							sum(visit_total_actions) as entry_nb_actions,
							sum(visit_total_time) as entry_sum_visit_length,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as entry_bounce_count
					FROM ".$archiveProcessing->logTable." 
						JOIN ".$archiveProcessing->logActionTable." ON (visit_entry_idaction_url = idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND idsite = ?
					GROUP BY visit_entry_idaction_url
					";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);
		

		/*
		 * Exit actions
		 */
		$query = "SELECT 	name,
							type,
							count(distinct visitor_idcookie) as exit_nb_uniq_visitors,
							count(*) as exit_nb_visits
				 	FROM ".$archiveProcessing->logTable." 
						JOIN ".$archiveProcessing->logActionTable." ON (visit_exit_idaction_url = idaction)
				 	WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
				 		AND idsite = ?
				 	GROUP BY visit_exit_idaction_url
					";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);
		
		/*
		 * Time per action
		 */
		$query = "SELECT 	name,
							type,
							sum(time_spent_ref_action) as sum_time_spent
					FROM (".$archiveProcessing->logTable." log_visit 
						JOIN ".$archiveProcessing->logVisitActionTable." log_link_visit_action USING (idvisit))
							JOIN ".$archiveProcessing->logActionTable."  log_action ON (log_action.idaction = log_link_visit_action.idaction_url_ref)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
				 		AND idsite = ?
				 	GROUP BY idaction_url_ref
				";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);
		$this->archiveDayRecordInDatabase($archiveProcessing);
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

		unset($this->actionsTablesByType);
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
				return array($urlHost, '/' . $urlPath);
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

		if( empty($split) )
		{
		    if($type == Piwik_Tracker_Action::TYPE_ACTION_NAME) {
		        $defaultName = self::$defaultActionNameWhenNotDefined;
		    } else {
		        $defaultName = self::$defaultActionUrlWhenNotDefined;
		    }
			return array( $defaultName );
		}

		return array_values( $split );
	}
	
	protected function updateActionsTableWithRowQuery($query)
	{
		$rowsProcessed = 0;
		while( $row = $query->fetch() )
		{
			// in some unknown case, the type field is NULL, as reported in #1082 - we ignore this page view
			if(empty($row['type'])) {
				continue;
			}
			
			$actionExplodedNames = $this->getActionExplodedNames($row['name'], $row['type']);

			// we work on the root table of the given TYPE (either ACTION_URL or DOWNLOAD or OUTLINK etc.)
			$currentTable =& $this->actionsTablesByType[$row['type']];

			// go to the level of the subcategory
			$end = count($actionExplodedNames)-1;
			for($level = 0 ; $level < $end; $level++)
			{
				$actionCategory = $actionExplodedNames[$level];
				$currentTable =& $currentTable[$actionCategory];
			}
			$actionName = $actionExplodedNames[$end];

			// we are careful to prefix the page URL / name with some value
			// so that if a page has the same name as a category 
			// we don't merge both entries 
			if($row['type'] == Piwik_Tracker_Action::TYPE_ACTION_URL )
			{
				$actionName = '/' . $actionName;
			}
			else 
			{
				$actionName = ' ' . $actionName;
			}

			// currentTable is now the array element corresponding the the action
			// at this point we may be for example at the 4th level of depth in the hierarchy
			$currentTable =& $currentTable[$actionName];
			
			// add the row to the matching sub category subtable
			if(!($currentTable instanceof Piwik_DataTable_Row))
			{
				if( $row['type'] == Piwik_Tracker_Action::TYPE_ACTION_NAME )
				{
					$currentTable = new Piwik_DataTable_Row(array(
							Piwik_DataTable_Row::COLUMNS => array('label' => (string)$actionName),
						));	
				}
				else
				{
					$currentTable = new Piwik_DataTable_Row(array(
							Piwik_DataTable_Row::COLUMNS => array('label' => (string)$actionName),
							Piwik_DataTable_Row::METADATA => array('url' => (string)$row['name']),
						));
				}
			}
			
			// For pages that bounce, we don't know the time on page.
			if($row['type'] == Piwik_Tracker_Action::TYPE_ACTION_URL
				&& isset($row['nb_visits'])
				&& !isset($row['sum_time_spent']))
			{
				$row['sum_time_spent'] = Zend_Registry::get('config')->Tracker->default_time_one_page_visit * $row['nb_visits'];
			}
			
			foreach($row as $name => $value)
			{
				// we don't add this information as itnot pertinent
				// name is already set as the label // and it has been cleaned from the categories and extracted from the initial string
				// type is used to partition the different actions type in different table. Adding the info to the row would be a duplicate. 
				if($name != 'name' 
					&& $name != 'type')
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
			}
			
			// if the exit_action was not recorded properly in the log_link_visit_action
			// there would be an error message when getting the nb_hits column
			// we must fake the record and add the columns
			if($currentTable->getColumn('nb_hits') === false)
			{
				// to test this code: delete the entries in log_link_action_visit for
				//  a given exit_idaction_url
				foreach($this->defaultRow->getColumns() as $name => $value)
				{
					$currentTable->addColumn($name, $value);
				}
			}
			// simple count 
			$rowsProcessed++;
		}

		// just to make sure php copies the last $currentTable in the $parentTable array
		$currentTable =& $this->actionsTablesByType;
		return $rowsProcessed;
	}
}

