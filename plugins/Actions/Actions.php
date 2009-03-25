<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Actions
 */
	
/**
 * 
 * @package Piwik_Actions
 */
class Piwik_Actions extends Piwik_Plugin
{
	static protected $actionCategoryDelimiter = null;
	static protected $limitLevelSubCategory = 10;
	
	public function getInformation()
	{
		$info = array(
			'name' => 'Actions',
			'description' => 'Reports about the page views, the outlinks and downloads. Outlinks and Downloads tracking is automatic!',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
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
		self::$actionCategoryDelimiter =  Zend_Registry::get('config')->General->action_category_delimiter;
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'Actions', 'getActions', Piwik_Translate('Actions_SubmenuPages'));
		Piwik_AddWidget( 'Actions', 'getDownloads', Piwik_Translate('Actions_SubmenuDownloads'));
		Piwik_AddWidget( 'Actions', 'getOutlinks', Piwik_Translate('Actions_SubmenuOutlinks'));
	}
	
	function addMenus()
	{
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuPages', array('module' => 'Actions', 'action' => 'getActions'));
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuOutlinks', array('module' => 'Actions', 'action' => 'getOutlinks'));
		Piwik_AddMenu('Actions_Actions', 'Actions_SubmenuDownloads', array('module' => 'Actions', 'action' => 'getDownloads'));		
	}
		
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'Actions_actions',
				'Actions_downloads',
				'Actions_outlink',
		);
		
		$maximumRowsInDataTableLevelZero = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_actions;
		$maximumRowsInSubDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_subtable_actions;
		$archiveProcessing->archiveDataTable($dataTableToSum, $maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable);
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
		$archiveProcessing = $notification->getNotificationObject();
		
		require_once "Tracker/Action.php";
		
		$this->actionsTablesByType = array(
			Piwik_Tracker_Action::TYPE_ACTION => array(),
			Piwik_Tracker_Action::TYPE_DOWNLOAD => array(),
			Piwik_Tracker_Action::TYPE_OUTLINK => array(),
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
		 * Actions global information
		 */
		$query = "SELECT 	name,
							type,
							count(distinct t1.idvisit) as nb_visits, 
							count(distinct visitor_idcookie) as nb_uniq_visitors,
							count(*) as nb_hits							
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".$archiveProcessing->logVisitActionTable." as t2 USING (idvisit))
							LEFT JOIN ".$archiveProcessing->logActionTable." as t3 USING (idaction)
					WHERE visit_server_date = ?
						AND idsite = ?
					GROUP BY t3.idaction
					ORDER BY nb_hits DESC";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ));
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
						JOIN ".$archiveProcessing->logActionTable." ON (visit_entry_idaction = idaction)
					WHERE visit_server_date = ?
						AND idsite = ?
					GROUP BY visit_entry_idaction
					";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);
		

		/*
		 * Exit actions
		 */
		$query = "SELECT 	name,
							type,
							count(distinct visitor_idcookie) as exit_nb_uniq_visitors,
							count(*) as exit_nb_visits,
							sum(case visit_total_actions when 1 then 1 else 0 end) as exit_bounce_count
				 	FROM ".$archiveProcessing->logTable." 
						JOIN ".$archiveProcessing->logActionTable." ON (visit_exit_idaction = idaction)
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY visit_exit_idaction
					";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);
		
		/*
		 * Time per action
		 */
		$query = "SELECT 	name,
							type,
							sum(time_spent_ref_action) as sum_time_spent
					FROM (".$archiveProcessing->logTable." log_visit 
						JOIN ".$archiveProcessing->logVisitActionTable." log_link_visit_action USING (idvisit))
							JOIN ".$archiveProcessing->logActionTable."  log_action ON (log_action.idaction = log_link_visit_action.idaction_ref)				 	
					WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY idaction_ref
				";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ));
		$modified = $this->updateActionsTableWithRowQuery($query);
		
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}

	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$maximumRowsInDataTableLevelZero = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_actions;
		$maximumRowsInSubDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_subtable_actions;

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_ACTION]);
		$s = $dataTable->getSerialized( $maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable );
		$archiveProcessing->insertBlobRecord('Actions_actions', $s);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_DOWNLOAD]);
		$s = $dataTable->getSerialized($maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable );
		$archiveProcessing->insertBlobRecord('Actions_downloads', $s);
		
		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_Tracker_Action::TYPE_OUTLINK]);
		$s = $dataTable->getSerialized( $maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable );
		$archiveProcessing->insertBlobRecord('Actions_outlink', $s);
		
		unset($this->actionsTablesByType);
	}
	
	static public function getHostAndPageNameFromUrl($url)
	{
		$matches = $split_arr = array();
		if(preg_match("#://[^/]+(/)#",$url, $matches, PREG_OFFSET_CAPTURE))
		{
			$host = substr($url, 0, $matches[1][1]);
			return array($host, substr($url,strlen($host)));
		}
		return array($url, "/");
	}
	
	static public function getActionExplodedNames($name, $type)
	{
		if($type == Piwik_Tracker_Action::TYPE_DOWNLOAD
			|| $type == Piwik_Tracker_Action::TYPE_OUTLINK)
		{
			return self::getHostAndPageNameFromUrl($name);
		}
		if(empty(self::$actionCategoryDelimiter))
		{
			return array($name);
		}
		return explode(	self::$actionCategoryDelimiter, 
						$name, 
						self::$limitLevelSubCategory);
	}
	
	
	protected function updateActionsTableWithRowQuery($query)
	{
		$rowsProcessed = 0;
		while( $row = $query->fetch() )
		{
			$actionExplodedNames = $this->getActionExplodedNames($row['name'], $row['type']);

			// we work on the root table of the given TYPE (either ACTION or DOWNLOAD or OUTLINK etc.)
			$currentTable =& $this->actionsTablesByType[$row['type']];
			
			// go to the level of the subcategory
			$end = count($actionExplodedNames)-1;
			for($level = 0 ; $level < $end; $level++)
			{
				$actionCategory = $actionExplodedNames[$level];
				$currentTable =& $currentTable[$actionCategory];
			}
			$actionName = $actionExplodedNames[$end];
			
			// we are careful to prefix the pageName with some value 
			// so that if a page has the same name as a category 
			// we don't merge both entries 
			if($row['type'] == Piwik_Tracker_Action::TYPE_ACTION)
			{
				$actionName = '/' . $actionName;
			}
						
			// currentTable is now the array element corresponding the the action
			// at this point we may be for example at the 4th level of depth in the hierarchy
			$currentTable =& $currentTable[$actionName];
			
			// add the row to the matching sub category subtable
			if(!($currentTable instanceof Piwik_DataTable_Row))
			{
				$currentTable = new Piwik_DataTable_Row(array(	
							Piwik_DataTable_Row::COLUMNS => array('label' => (string)$actionName),
							Piwik_DataTable_Row::METADATA => array('url' => (string)$row['name']),
						));
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
				//  a given exit_idaction 
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

