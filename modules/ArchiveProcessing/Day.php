<?php
class Piwik_ArchiveProcessing_Day extends Piwik_ArchiveProcessing
{
	function __construct()
	{
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
		$this->logTable = Piwik::prefixTable('log_visit');
		$this->logActionTable = Piwik::prefixTable('log_link_visit_action');
		
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
			Piwik::log("No visits for this day!");
			//TODO to implement no visit 
			return;
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
		 
		$this->computeReferer();
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
				
					if(!isset($interestBySearchEngine[$row['referer_name']])) $interestBySearchEngine[$row['referer_name']]=array();
					if(!isset($interestByKeyword[$row['referer_keyword']])) $interestByKeyword[$row['referer_keyword']]=array();
					if(!isset($keywordBySearchEngine[$row['referer_name']][$row['referer_keyword']])) $keywordBySearchEngine[$row['referer_name']][$row['referer_keyword']]=array();
					if(!isset($searchEngineByKeyword[$row['referer_keyword']][$row['referer_name']])) $searchEngineByKeyword[$row['referer_keyword']][$row['referer_name']]=array();
					
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
					
					if(!isset($interestByWebsite[$row['referer_type']][$row['referer_name']])) $interestByWebsite[$row['referer_type']][$row['referer_name']]=array();
					$this->updateInterestStats( $row, $interestByWebsite[$row['referer_type']][$row['referer_name']]);
					
					if(!isset($urlByWebsite[$row['referer_type']][$row['referer_name']][$row['referer_url']])) $urlByWebsite[$row['referer_type']][$row['referer_name']][$row['referer_url']]=array();
					$this->updateInterestStats( $row, $urlByWebsite[$row['referer_type']][$row['referer_name']][$row['referer_url']]);
				
				break;
				
				case Piwik_Common::REFERER_TYPE_NEWSLETTER:
					if(!isset($interestByNewsletter[$row['referer_name']])) $interestByNewsletter[$row['referer_name']]=array();
					$this->updateInterestStats( $row, $interestByNewsletter[$row['referer_name']]);
					
				break;
				
				case Piwik_Common::REFERER_TYPE_CAMPAIGN:
					if(!empty($row['referer_keyword']))
					{
						if(!isset($keywordByCampaign[$row['referer_name']][$row['referer_keyword']])) $keywordByCampaign[$row['referer_name']][$row['referer_keyword']]=array();
						$this->updateInterestStats( $row, $keywordByCampaign[$row['referer_name']][$row['referer_keyword']]);
					}
					if(!isset($interestByCampaign[$row['referer_name']])) $interestByCampaign[$row['referer_name']]=array();
					$this->updateInterestStats( $row, $interestByCampaign[$row['referer_name']]);
				break;
			}
			
			if(!isset($interestByType[$row['referer_type']] )) $interestByType[$row['referer_type']] =array();
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
		$record = new Archive_Processing_Record_Blob_Array('referer_type', $data);
		var_dump($data);
		$data = $this->getDataTablesSerialized($searchEngineByKeyword, $interestByKeyword);
		$record = new Archive_Processing_Record_Blob_Array('referer_type', $data);
		
		$data = $this->getDataTablesSerialized($keywordByCampaign, $interestByCampaign);
		$record = new Archive_Processing_Record_Blob_Array('referer_type', $data);
		
		$data = $this->getDataTablesSerialized($urlByWebsite[Piwik_Common::REFERER_TYPE_WEBSITE], $interestByWebsite[Piwik_Common::REFERER_TYPE_WEBSITE]);
		$record = new Archive_Processing_Record_Blob_Array('referer_type', $data);
		
		$data = $this->getDataTablesSerialized($urlByWebsite[Piwik_Common::REFERER_TYPE_PARTNER], $interestByWebsite[Piwik_Common::REFERER_TYPE_PARTNER]);
		$record = new Archive_Processing_Record_Blob_Array('referer_type', $data);
			
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
	
	protected function updateInterestStats( $newRowToAdd, &$oldRowToUpdate)
	{
		if(!isset($oldRowToUpdate[Piwik_Archive::INDEX_NB_UNIQ_VISITORS]))
		{
			$oldRowToUpdate=array(	Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 0, 
									Piwik_Archive::INDEX_NB_VISITS 			=> 0, 
									Piwik_Archive::INDEX_NB_ACTIONS 		=> 0, 
									Piwik_Archive::INDEX_MAX_ACTIONS 		=> 0, 
									Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> 0, 
									Piwik_Archive::INDEX_BOUNCE_COUNT 		=> 0
						);
		}
		
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
