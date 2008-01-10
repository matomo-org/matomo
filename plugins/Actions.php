<?php
	
class Piwik_Actions extends Piwik_Plugin
{
	static protected $actionCategoryDelimiter = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->setCategoryDelimiter( Zend_Registry::get('config')->General->action_category_delimiter);
	}

	public function setCategoryDelimiter($delimiter)
	{
		self::$actionCategoryDelimiter = $delimiter;
	}
	

	public function getInformation()
	{
		$info = array(
			'name' => 'Actions',
			'description' => 'Computes all reports about the actions',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
	}
	
	function uninstall()
	{
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archiveMonth',
		);
		return $hooks;
	}
	
	function archiveMonth( $notification )
	{
		$this->archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'Actions_actions',
				'Actions_downloads',
				'Actions_outlink',
		);
		
		$this->archiveProcessing->archiveDataTable($dataTableToSum);
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
		
		require_once "LogStats/Action.php";
		
		$this->actionsTablesByType = array(
			Piwik_LogStats_Action::TYPE_ACTION => array(),
			Piwik_LogStats_Action::TYPE_DOWNLOAD => array(),
			Piwik_LogStats_Action::TYPE_OUTLINK => array(),
		);
		
		/*
		 * Actions global information
		 */
		$query = "SELECT 	name,
							type,
							count(distinct idvisit) as nb_visits, 
							count(*) as nb_hits							
				 	FROM (".$archiveProcessing->logTable." 
						LEFT JOIN ".$archiveProcessing->logVisitActionTable." USING (idvisit))
							LEFT JOIN ".$archiveProcessing->logActionTable." USING (idaction)
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY idaction ";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ));
				
		$modified = $this->updateActionsTableWithRowQuery($query);

		
		/*
		 * Entry actions
		 */
		$query = "SELECT 	name,
							type,
							count(distinct visitor_idcookie) as entry_nb_unique_visitor, 
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
							count(distinct visitor_idcookie) as exit_nb_unique_visitor,
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
		
		
		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_LogStats_Action::TYPE_ACTION]);
		$s = $dataTable->getSerialized();
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array('Actions_actions', $s);
		
		
		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_LogStats_Action::TYPE_DOWNLOAD]);
		$s = $dataTable->getSerialized();
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array('Actions_downloads', $s);
		
		
		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTablesByType[Piwik_LogStats_Action::TYPE_OUTLINK]);
		$s = $dataTable->getSerialized();
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array('Actions_outlink', $s);
		
		unset($this->actionsTablesByType);
	}
	
	static public function getActionCategoryFromName($name)
	{
		// case the name is an URL we dont clean the name the same way
		if(Piwik_Common::isUrl($name))
		{
			$split = array($name);
		}
		else
		{
			$split = explode(self::$actionCategoryDelimiter, $name);
		}
		return $split;
	}
	
	
	protected function updateActionsTableWithRowQuery($query)
	{
		$rowsProcessed = 0;
		
		while( $row = $query->fetch() )
		{
			// split the actions by category
			$aActions = $this->getActionCategoryFromName($row['name']);
			
			// we work on the root table of the given TYPE (either ACTION or DOWNLOAD or OUTLINK etc.)
			$currentTable =& $this->actionsTablesByType[$row['type']];
			
			// go to the level of the subcategory
			$end = count($aActions)-1;
			for($level = 0 ; $level < $end; $level++)
			{
				$actionCategory = $aActions[$level];
				$currentTable =& $currentTable[$actionCategory];
			}
			$actionNameBefore = $aActions[$end];
			
			// create a new element in the array for the page
			// we are careful to prefix the pageName with some value so that if a page has the same name
			// as a category we don't overwrite or do other silly things
			
			// we know that the concatenation of a space and the name of the action
			// will always be unique as all the action names have been trimmed before reaching this point
			$actionName = '/' . $actionNameBefore;
			
			// currentTable is now the array element corresponding the the action
			// at this point we may be for example at the 4th level of depth in the hierarchy
			$currentTable =& $currentTable[$actionName];
			
			// add the row to the matching sub category subtable
			if(!($currentTable instanceof Piwik_DataTable_Row))
			{
				$currentTable = new Piwik_DataTable_Row(
					array(	Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => (string)$actionName,
								)
						)
					);
			}
			
			foreach($row as $name => $value)
			{
				// we don't add this information as it not pertinent
				// name is already set as the label // and it has been cleaned from the categories and extracted from the initial string
				// type is used to partition the different actions type in different table. Adding the info to the row would be a duplicate. 
				if($name != 'name' && $name != 'type')
				{
					$name = $this->getIdColumn($name);
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

	static protected $nameToIdMapping = array(
			'nb_visits'	 				=> 1,
			'nb_hits'					=> 2,
			'entry_nb_unique_visitor'	=> 3,
			'entry_nb_visits'			=> 4,
			'entry_nb_actions'			=> 5,
			'entry_sum_visit_length'	=> 6,
			'entry_bounce_count'		=> 7,
			'exit_nb_unique_visitor'	=> 8,
			'exit_nb_visits'			=> 9,
			'exit_bounce_count'			=> 10,
			'sum_time_spent'			=> 11,
			
		);
	static public function getColumnsMap()
	{
		return array_flip(self::$nameToIdMapping);
	}
	
	protected function getIdColumn( $name )
	{
		return self::$nameToIdMapping[$name];
	}
}

