<?php
class Piwik_ArchiveProcessing_Day extends Piwik_ArchiveProcessing
{
	static protected $actionCategoryDelimiter = null;
	
	function __construct()
	{
		$this->setCategoryDelimiter( Zend_Registry::get('config')->General->action_category_delimiter);
	}
	public function setCategoryDelimiter($delimiter)
	{
		self::$actionCategoryDelimiter = $delimiter;
	}
	
	/**
	 * Reads the log and compute the essential reports.
	 * All the non essential reports are computed inside plugins.
	 * 
	 * One record is either a numeric value or a BLOB which is 
	 * usually a compressed serialized DataTable.
	 *  
	 * At the end of the process we add a fake record called 'done' that flags
	 * the archive as being valid.
	 * 
	 * 
	 * 
	 */
	protected function compute()
	{		
		$db = Zend_Registry::get('db');
		
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors, 
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions, 
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count 
					FROM ".$this->logTable."
					WHERE visit_server_date = ?
						AND idsite = ?
					GROUP BY visit_server_date
				 ";
		$row = $db->fetchRow($query, array($this->strDateStart,$this->idsite ) );
		
		if($row === false)
		{
			throw new Exception("TODO to implement when no visit");
		}
	
		foreach($row as $name => $value)
		{
			$record = new Archive_Processing_Record_Numeric($name, $value);
		}

/*
		$query = "SELECT count(distinct l.idaction) as nb_uniq_actions 
				 FROM ".$this->logTable." as v 
					LEFT JOIN ".$this->logActionTable." as l USING (idvisit)
				 WHERE v.visit_server_date = ?
				 	AND v.idsite = ?
				 LIMIT 1";
		$row = $db->fetchRow($query, array( $this->strDateStart, $this->idsite ) );
		$record = new Archive_Processing_Numeric_Record('nb_uniq_actions', $row['nb_uniq_actions']);
		*/
		
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors_returning,
							count(*) as nb_visits_returning, 
							sum(visit_total_actions) as nb_actions_returning,
							max(visit_total_actions) as max_actions_returning, 
							sum(visit_total_time) as sum_visit_length_returning,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count_returning
				 	FROM ".$this->logTable."
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 		AND visitor_returning = 1
				 	GROUP BY visitor_returning";
		$row = $db->fetchRow($query, array( $this->strDateStart, $this->idsite ) );
		
		foreach($row as $name => $value)
		{
			$record = new Archive_Processing_Record_Numeric($name, $value);
		}
		
		
		/**
		 * referers 
		 */
//		$this->computeReferer();
		
		/**
		 * actions
		 */
		$this->computeActions();
		
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
	
	/**
	 * Compute all the actions along with their hierarchies.
	 * 
	 * For each action we process the "interest statistics" : 
	 * visits, unique visitors, bouce count, sum visit length.
	 * 
	 * 
	 */
	protected function computeActions()
	{
		$this->actionsTablesByType = array();
		$db = Zend_Registry::get('db');
		$timer = new Piwik_Timer;
		
		/*
		 * Actions global information
		 */
		$query = "SELECT 	name,
							type,
							count(distinct idvisit) as nb_visits, 
							count(*) as nb_hits							
				 	FROM (".$this->logTable." 
						LEFT JOIN ".$this->logVisitActionTable." USING (idvisit))
							LEFT JOIN ".$this->logActionTable." USING (idaction)
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY idaction
					";
		$query = $db->query($query, array( $this->strDateStart, $this->idsite ));
				
		$modified = $this->updateActionsTableWithRowQuery($query);

		Piwik::log("$modified rows for all actions");
		
		
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
							
				 	FROM ".$this->logTable." 
						LEFT JOIN ".$this->logActionTable." ON (visit_entry_idaction = idaction)
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY visit_entry_idaction
					";
		$query = $db->query($query, array( $this->strDateStart, $this->idsite ));
				
		$modified = $this->updateActionsTableWithRowQuery($query);
		
		Piwik::log("$modified rows for entry actions");
		
		
		/*
		 * Exit actions
		 */
		$query = "SELECT 	name,
							type,
							count(distinct visitor_idcookie) as exit_nb_unique_visitor,
							count(*) as exit_nb_visits,
							sum(case visit_total_actions when 1 then 1 else 0 end) as exit_bounce_count
							
				 	FROM ".$this->logTable." 
						LEFT JOIN ".$this->logActionTable." ON (visit_exit_idaction = idaction)
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY visit_exit_idaction
					";
		$query = $db->query($query, array( $this->strDateStart, $this->idsite ));
				
		$modified = $this->updateActionsTableWithRowQuery($query);
		
		Piwik::log("$modified rows for exit actions");
		
		
		require_once "LogStats/Action.php";
		$data = $this->generateDataTable($this->actionsTablesByType[Piwik_LogStats_Action::TYPE_ACTION]);
		$s = $data->getSerialized();
//		var_export($s);
		print(" serialized has ".count($s)." elements");
		
		var_export($this->actionsTablesByType);
		
		// go through all the categories and compute recursively the category statistics
		// depending on all the subcategories stats
		
	}
	
	protected function updateActionsTableWithRowQuery($query)
	{
		$rowsProcessed = 0;
		
		while( $row = $query->fetch() )
		{
			// split the actions by category
			$aActions = $this->getActionCategoryFromName($row['name']);
			
			$currentTable =& $this->actionsTablesByType[$row['type']];
			
			// go at the level of this subcategory
			foreach($aActions as $actionCategory)
			{
				$currentTable =& $currentTable[$actionCategory];
			}
			
			// add the row to the matching sub category subtable
			if(!($currentTable instanceof Piwik_DataTable_Row))
			{
				$currentTable = new Piwik_DataTable_Row(
					array(	Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => (string)$actionCategory,
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
	
	protected function generateDataTable( $table )
	{
		$dataTableToReturn = new Piwik_DataTable;
		
		foreach($table as $label => $maybeDatatableRow)
		{
			// case the aInfo is a subtable-like array
			// it means that we have to go recursively and process it
			// then we build the row that is an aggregate of all the children
			// and we associate this row to the subtable
			if( !($maybeDatatableRow instanceof Piwik_DataTable_Row) )
			{
				$subTable = $this->generateDataTable($maybeDatatableRow);
				$row = new Piwik_DataTable_Row_ActionTableSummary( $subTable );
				$row->addColumn('label', $label);
			}
			// if aInfo is a simple Row we build it
			else
			{
				$row = $maybeDatatableRow;
			}
			
			$dataTableToReturn->addDataTableRow($row);
		}
		
		return $dataTableToReturn;
	}
	
	protected function computeReferer()
	{
		$db = Zend_Registry::get('db');
		$query = "SELECT 	referer_type, 
							referer_name, 
							referer_keyword,
							referer_url,
							count(distinct visitor_idcookie) as nb_uniq_visitors,
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions,
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count
				 	FROM ".$this->logTable."
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY referer_type, referer_name, referer_keyword";
		$query = $db->query($query, array( $this->strDateStart, $this->idsite ));
				
		$timer = new Piwik_Timer;
		while($rowBefore = $query->fetch() )
		{
			$row = array(
				Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> $rowBefore['nb_uniq_visitors'], 
				Piwik_Archive::INDEX_NB_VISITS 			=> $rowBefore['nb_visits'], 
				Piwik_Archive::INDEX_NB_ACTIONS 		=> $rowBefore['nb_actions'], 
				Piwik_Archive::INDEX_MAX_ACTIONS 		=> $rowBefore['max_actions'], 
				Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> $rowBefore['sum_visit_length'], 
				Piwik_Archive::INDEX_BOUNCE_COUNT 		=> $rowBefore['bounce_count'],
				'referer_type' 							=> $rowBefore['referer_type'],
				'referer_name' 							=> $rowBefore['referer_name'], 
				'referer_keyword'						=> $rowBefore['referer_keyword'],
				'referer_url'							=> $rowBefore['referer_url'],
			);
			
			switch($row['referer_type'])
			{
				case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
				
					if(!isset($interestBySearchEngine[$row['referer_name']])) $interestBySearchEngine[$row['referer_name']]= $this->getNewInterestRow();
					if(!isset($interestByKeyword[$row['referer_keyword']])) $interestByKeyword[$row['referer_keyword']]= $this->getNewInterestRow();
					if(!isset($keywordBySearchEngine[$row['referer_name']][$row['referer_keyword']])) $keywordBySearchEngine[$row['referer_name']][$row['referer_keyword']]= $this->getNewInterestRow();
					if(!isset($searchEngineByKeyword[$row['referer_keyword']][$row['referer_name']])) $searchEngineByKeyword[$row['referer_keyword']][$row['referer_name']]= $this->getNewInterestRow();
					
					$this->updateInterestStats( $row, $interestBySearchEngine[$row['referer_name']]);
					$this->updateInterestStats( $row, $interestByKeyword[$row['referer_keyword']]);
					$this->updateInterestStats( $row, $keywordBySearchEngine[$row['referer_name']][$row['referer_keyword']]);
					$this->updateInterestStats( $row, $searchEngineByKeyword[$row['referer_keyword']][$row['referer_name']]);
				break;
				
				case Piwik_Common::REFERER_TYPE_WEBSITE:
				case Piwik_Common::REFERER_TYPE_PARTNER:
				
					// for a website we remove the HOST from the url, to save some bytes in the DB
					// for partners URLs we keep the full URL as the partner's name can be an alias and
					// so is not necessarily the hostname of the URL...
					if($row['referer_type']==Piwik_Common::REFERER_TYPE_WEBSITE
						&& !empty($row['referer_url']))
					{
						$row['referer_url'] = Piwik_Common::getPathAndQueryFromUrl($row['referer_url']);
					}
					
					if(!isset($interestByWebsite[$row['referer_type']][$row['referer_name']])) $interestByWebsite[$row['referer_type']][$row['referer_name']]= $this->getNewInterestRow();
					$this->updateInterestStats( $row, $interestByWebsite[$row['referer_type']][$row['referer_name']]);
					
					if(!isset($urlByWebsite[$row['referer_type']][$row['referer_name']][$row['referer_url']])) $urlByWebsite[$row['referer_type']][$row['referer_name']][$row['referer_url']]= $this->getNewInterestRow();
					$this->updateInterestStats( $row, $urlByWebsite[$row['referer_type']][$row['referer_name']][$row['referer_url']]);
				
				break;
				
				case Piwik_Common::REFERER_TYPE_NEWSLETTER:
					if(!isset($interestByNewsletter[$row['referer_name']])) $interestByNewsletter[$row['referer_name']]= $this->getNewInterestRow();
					$this->updateInterestStats( $row, $interestByNewsletter[$row['referer_name']]);
					
				break;
				
				case Piwik_Common::REFERER_TYPE_CAMPAIGN:
					if(!empty($row['referer_keyword']))
					{
						if(!isset($keywordByCampaign[$row['referer_name']][$row['referer_keyword']])) $keywordByCampaign[$row['referer_name']][$row['referer_keyword']]= $this->getNewInterestRow();
						$this->updateInterestStats( $row, $keywordByCampaign[$row['referer_name']][$row['referer_keyword']]);
					}
					if(!isset($interestByCampaign[$row['referer_name']])) $interestByCampaign[$row['referer_name']]= $this->getNewInterestRow();
					$this->updateInterestStats( $row, $interestByCampaign[$row['referer_name']]);
				break;
			}
			
			if(!isset($interestByType[$row['referer_type']] )) $interestByType[$row['referer_type']] = $this->getNewInterestRow();
			$this->updateInterestStats($row, $interestByType[$row['referer_type']]);
		}
		echo "after loop = ". $timer;
		
//		Piwik::log("By search engine:");
//		Piwik::log($interestBySearchEngine);
//		Piwik::log("By keyword:");
//		Piwik::log($interestByKeyword);
//		Piwik::log("Kwd by search engine:");
//		Piwik::log($keywordBySearchEngine);
//		Piwik::log("Search engine by keyword:");
//		Piwik::log($searchEngineByKeyword);
//		

//		Piwik::log("By campaign:");
//		Piwik::log($interestByCampaign);
//		Piwik::log("Kwd by campaign:");
//		Piwik::log($keywordByCampaign);


//		Piwik::log("By referer type:");
//		Piwik::log($interestByType);

//		Piwik::log("By website:");
//		Piwik::log($interestByWebsite[Piwik_Common::REFERER_TYPE_WEBSITE]);
//		Piwik::log("Urls by website:");
//		Piwik::log($urlByWebsite[Piwik_Common::REFERER_TYPE_WEBSITE]);
//		Piwik::log("By partner website:");
//		Piwik::log($interestByWebsite[Piwik_Common::REFERER_TYPE_PARTNER]);
//		Piwik::log("Urls by partner website:");
//		Piwik::log($urlByWebsite[Piwik_Common::REFERER_TYPE_PARTNER]);
		

		$data = $this->getDataTableSerialized($interestByType);
		$record = new Archive_Processing_Record_Blob_Array('referer_type', $data);
		
		$data = $this->getDataTablesSerialized($keywordBySearchEngine, $interestBySearchEngine);
		$record = new Archive_Processing_Record_Blob_Array('referer_keyword_by_searchengine', $data);
		var_dump($data);
		$data = $this->getDataTablesSerialized($searchEngineByKeyword, $interestByKeyword);
		$record = new Archive_Processing_Record_Blob_Array('referer_searchengine_by_keyword', $data);
		
		$data = $this->getDataTablesSerialized($keywordByCampaign, $interestByCampaign);
		$record = new Archive_Processing_Record_Blob_Array('referer_keyword_by_campaign', $data);
		
		$data = $this->getDataTablesSerialized($urlByWebsite[Piwik_Common::REFERER_TYPE_WEBSITE], $interestByWebsite[Piwik_Common::REFERER_TYPE_WEBSITE]);
		$record = new Archive_Processing_Record_Blob_Array('referer_url_by_website', $data);
		
		$data = $this->getDataTablesSerialized($urlByWebsite[Piwik_Common::REFERER_TYPE_PARTNER], $interestByWebsite[Piwik_Common::REFERER_TYPE_PARTNER]);
		$record = new Archive_Processing_Record_Blob_Array('referer_url_by_partner', $data);
			
		echo "after serialization = ". $timer;
	}
	
	protected function getDataTableSerialized( $arrayLevel0 )
	{
		$table = new Piwik_DataTable(true);
		$table->loadFromArrayLabelIsKey($arrayLevel0);
		$toReturn = $table->getSerialized();
		return $toReturn;
	}
	
	
	protected function getDataTablesSerialized( $arrayLevel0, $subArrayLevel1ByKey)
	{
		$tablesByLabel = array();

		foreach($arrayLevel0 as $label => $aAllRowsForThisLabel)
		{
			$table = new Piwik_DataTable;
			$table->loadFromArrayLabelIsKey($aAllRowsForThisLabel);
			$tablesByLabel[$label] = $table;
		}
		$parentTableLevel0 = new Piwik_DataTable;
		$parentTableLevel0->loadFromArrayLabelIsKey($subArrayLevel1ByKey, $tablesByLabel);

//		$render = new Piwik_DataTable_Renderer_Console( $parentTableLevel0 );
		$toReturn = $parentTableLevel0->getSerialized();

		return $toReturn;
	}
	protected function getNewInterestRow()
	{
		return array(	Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 0, 
						Piwik_Archive::INDEX_NB_VISITS 			=> 0, 
						Piwik_Archive::INDEX_NB_ACTIONS 		=> 0, 
						Piwik_Archive::INDEX_MAX_ACTIONS 		=> 0, 
						Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> 0, 
						Piwik_Archive::INDEX_BOUNCE_COUNT 		=> 0
						);
	}
	
	protected function updateInterestStats( $newRowToAdd, &$oldRowToUpdate)
	{		
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_UNIQ_VISITORS]	+= $newRowToAdd[Piwik_Archive::INDEX_NB_UNIQ_VISITORS];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_VISITS] 		+= $newRowToAdd[Piwik_Archive::INDEX_NB_VISITS];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_ACTIONS] 		+= $newRowToAdd[Piwik_Archive::INDEX_NB_ACTIONS];
		$oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS] 		 = max($newRowToAdd[Piwik_Archive::INDEX_MAX_ACTIONS], $oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS]);
		$oldRowToUpdate[Piwik_Archive::INDEX_SUM_VISIT_LENGTH]	+= $newRowToAdd[Piwik_Archive::INDEX_SUM_VISIT_LENGTH];
		$oldRowToUpdate[Piwik_Archive::INDEX_BOUNCE_COUNT] 		+= $newRowToAdd[Piwik_Archive::INDEX_BOUNCE_COUNT];
		
		return $oldRowToUpdate;
	}
}
?>
