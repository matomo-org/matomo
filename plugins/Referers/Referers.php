<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Referers
 */
	
/**
 * @package Piwik_Referers
 */
class Piwik_Referers extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'Referers',
			'description' => 'Reports the Referers data: Search Engines, Keywords, Websites, Campaign Tracking, Direct Entry. ',
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

	public function setCategoryDelimiter($delimiter)
	{
		self::$actionCategoryDelimiter = $delimiter;
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'Referers', 'getKeywords', Piwik_Translate('Referers_WidgetKeywords'));
		Piwik_AddWidget( 'Referers', 'getCampaigns', Piwik_Translate('Referers_WidgetCampaigns'));
		Piwik_AddWidget( 'Referers', 'getWebsites', Piwik_Translate('Referers_WidgetExternalWebsites'));
		Piwik_AddWidget( 'Referers', 'getSearchEngines', Piwik_Translate('Referers_WidgetSearchEngines'));
		Piwik_AddWidget( 'Referers', 'getRefererType', Piwik_Translate('Referers_WidgetOverview'));
	}
	
	function addMenus()
	{
		Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuEvolution', array('module' => 'Referers'));
		Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuSearchEngines', array('module' => 'Referers', 'action' => 'getSearchEnginesAndKeywords'));
		Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuWebsites', array('module' => 'Referers', 'action' => 'getWebsites'));
		Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuCampaigns', array('module' => 'Referers', 'action' => 'getCampaigns'));
		//Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuEvolution', array('module' => 'Referers', 'action' => 'getSearchEnginesEvolution'));
	}
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
			'Referers_type',
			'Referers_keywordBySearchEngine',
			'Referers_searchEngineByKeyword',
			'Referers_keywordByCampaign',
			'Referers_urlByWebsite',
		);
		
		$maximumRowsInDataTableLevelZero = 500;
		$maximumRowsInSubDataTable = 50;
		$nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum, $maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable);
		
		$mappingFromArchiveName = array(
			'Referers_distinctSearchEngines' => 
						array( 	'typeCountToUse' => 'level0',
								'nameTableToUse' => 'Referers_keywordBySearchEngine',
							),
			'Referers_distinctKeywords' => 
						array( 	'typeCountToUse' => 'level0',
								'nameTableToUse' => 'Referers_searchEngineByKeyword',
							),
			'Referers_distinctCampaigns' => 
						array( 	'typeCountToUse' => 'level0',
								'nameTableToUse' => 'Referers_keywordByCampaign',
							),
			'Referers_distinctWebsites' => 
						array( 	'typeCountToUse' => 'level0',
								'nameTableToUse' => 'Referers_urlByWebsite',
							),
			'Referers_distinctWebsitesUrls' => 
						array( 	'typeCountToUse' => 'recursive',
								'nameTableToUse' => 'Referers_urlByWebsite',
							),
		);

		foreach($mappingFromArchiveName as $name => $infoMapping)
		{
			$typeCountToUse = $infoMapping['typeCountToUse'];
			$nameTableToUse = $infoMapping['nameTableToUse'];
			
			if($typeCountToUse == 'recursive')
			{
				
				$countValue = $nameToCount[$nameTableToUse]['recursive']
								- $nameToCount[$nameTableToUse]['level0'];
			}
			else
			{
				$countValue = $nameToCount[$nameTableToUse]['level0'];
			}
			
			$record = new Piwik_ArchiveProcessing_Record_Numeric(
													$name, 
													$countValue
												);
		}
	}
	
	
	public function archiveDay( $notification )
	{
		/**
		 * @var Piwik_ArchiveProcessing_Day 
		 */
		$archiveProcessing = $notification->getNotificationObject();
		
		$this->archiveDayAggregateVisits($archiveProcessing);
		$this->archiveDayAggregateGoals($archiveProcessing);
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}
	
	protected function archiveDayAggregateVisits($archiveProcessing)
	{
		$query = "SELECT 	referer_type, 
							referer_name, 
							referer_keyword,
							referer_url,
							count(distinct visitor_idcookie) as nb_uniq_visitors,
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions,
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count,
							sum(case visit_goal_converted when 1 then 1 else 0 end) as nb_visits_converted
				 	FROM ".$archiveProcessing->logTable."
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 	GROUP BY referer_type, referer_name, referer_url, referer_keyword
				 	ORDER BY nb_visits DESC";
		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ));

		$this->interestBySearchEngine =
			$this->interestByKeyword =
			$this->interestBySearchEngineAndKeyword =
			$this->interestByKeywordAndSearchEngine =
			$this->interestByWebsite =
			$this->interestByWebsiteAndUrl =
			$this->interestByCampaignAndKeyword =
			$this->interestByCampaign =
			$this->interestByType = 
			$this->distinctUrls = array();
		
		while($row = $query->fetch() )
		{
			if(empty($row['referer_type']))
			{
				$row['referer_type'] = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
			}
			else
			{
				switch($row['referer_type'])
				{
					case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
					
						if(!isset($this->interestBySearchEngine[$row['referer_name']])) $this->interestBySearchEngine[$row['referer_name']]= $archiveProcessing->getNewInterestRow();
						if(!isset($this->interestByKeyword[$row['referer_keyword']])) $this->interestByKeyword[$row['referer_keyword']]= $archiveProcessing->getNewInterestRow();
						if(!isset($this->interestBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']])) $this->interestBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']]= $archiveProcessing->getNewInterestRow();
						if(!isset($this->interestByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']])) $this->interestByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']]= $archiveProcessing->getNewInterestRow();
					
						$archiveProcessing->updateInterestStats( $row, $this->interestBySearchEngine[$row['referer_name']]);
						$archiveProcessing->updateInterestStats( $row, $this->interestByKeyword[$row['referer_keyword']]);
						$archiveProcessing->updateInterestStats( $row, $this->interestBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']]);
						$archiveProcessing->updateInterestStats( $row, $this->interestByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']]);
					break;
					
					case Piwik_Common::REFERER_TYPE_WEBSITE:
						
						if(!isset($this->interestByWebsite[$row['referer_name']])) $this->interestByWebsite[$row['referer_name']]= $archiveProcessing->getNewInterestRow();
						$archiveProcessing->updateInterestStats( $row, $this->interestByWebsite[$row['referer_name']]);
						
						if(!isset($this->interestByWebsiteAndUrl[$row['referer_name']][$row['referer_url']])) $this->interestByWebsiteAndUrl[$row['referer_name']][$row['referer_url']]= $archiveProcessing->getNewInterestRow();
						$archiveProcessing->updateInterestStats( $row, $this->interestByWebsiteAndUrl[$row['referer_name']][$row['referer_url']]);
					
						if(!isset($this->distinctUrls[$row['referer_url']]))
						{
							$this->distinctUrls[$row['referer_url']] = true;
						}
						
					break;
	
					case Piwik_Common::REFERER_TYPE_CAMPAIGN:
						if(!empty($row['referer_keyword']))
						{
							if(!isset($this->interestByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']])) $this->interestByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']]= $archiveProcessing->getNewInterestRow();
							$archiveProcessing->updateInterestStats( $row, $this->interestByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']]);
						}
						if(!isset($this->interestByCampaign[$row['referer_name']])) $this->interestByCampaign[$row['referer_name']]= $archiveProcessing->getNewInterestRow();
						$archiveProcessing->updateInterestStats( $row, $this->interestByCampaign[$row['referer_name']]);
					break;
					
					case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
						// direct entry are aggregated below in $this->interestByType array
					break;
					
					default:
						throw new Exception("Non expected referer_type = " . $row['referer_type']);
					break;
				}
			}
			if(!isset($this->interestByType[$row['referer_type']] )) $this->interestByType[$row['referer_type']] = $archiveProcessing->getNewInterestRow();
			$archiveProcessing->updateInterestStats($row, $this->interestByType[$row['referer_type']]);
		}
	}
	
	protected function archiveDayAggregateGoals($archiveProcessing)
	{
		$query = $archiveProcessing->queryConversionsBySegment("referer_type,referer_name,referer_keyword");
		while($row = $query->fetch() )
		{
			if(empty($row['referer_type']))
			{
				$row['referer_type'] = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
			}
			else
			{
				switch($row['referer_type'])
				{
					case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
						if(!isset($this->interestBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
						if(!isset($this->interestByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
						
						$archiveProcessing->updateGoalStats( $row, $this->interestBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
						$archiveProcessing->updateGoalStats( $row, $this->interestByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
					break;

					case Piwik_Common::REFERER_TYPE_WEBSITE:
						if(!isset($this->interestByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
						$archiveProcessing->updateGoalStats( $row, $this->interestByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
					break;

					case Piwik_Common::REFERER_TYPE_CAMPAIGN:
						if(!empty($row['referer_keyword']))
						{
							if(!isset($this->interestByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
							$archiveProcessing->updateGoalStats( $row, $this->interestByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
						}
						if(!isset($this->interestByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
						$archiveProcessing->updateGoalStats( $row, $this->interestByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
					break;

					default:
						throw new Exception("Non expected referer_type = " . $row['referer_type']);
					break;
				}
			}
			if(!isset($this->interestByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] )) $this->interestByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
			$archiveProcessing->updateGoalStats($row, $this->interestByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
		}
	
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByType);
		$archiveProcessing->enrichConversionsByLabelArray($this->interestBySearchEngine);
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByKeyword);
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByWebsite);
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByCampaign);
		$archiveProcessing->enrichConversionsByLabelArrayHasTwoLevels($this->interestByCampaignAndKeyword);
	}
	
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$numericRecords = array(
			'Referers_distinctSearchEngines'	=> count($this->interestBySearchEngineAndKeyword),
			'Referers_distinctKeywords' 		=> count($this->interestByKeywordAndSearchEngine),
			'Referers_distinctCampaigns'		=> count($this->interestByCampaign),
			'Referers_distinctWebsites'			=> count($this->interestByWebsite),
			'Referers_distinctWebsitesUrls'		=> count($this->distinctUrls),
		);
		
		foreach($numericRecords as $name => $value)
		{
			$record = new Piwik_ArchiveProcessing_Record_Numeric($name, $value);
		}
		
		$data = $archiveProcessing->getDataTableSerialized($this->interestByType);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray('Referers_type', $data);
		
		$maximumRowsInDataTableLevelZero = 500;
		$maximumRowsInSubDataTable = 50;
		
		$blobRecords = array(
			'Referers_keywordBySearchEngine' => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->interestBySearchEngineAndKeyword, $this->interestBySearchEngine),
			'Referers_searchEngineByKeyword' => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->interestByKeywordAndSearchEngine, $this->interestByKeyword),
			'Referers_keywordByCampaign' => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->interestByCampaignAndKeyword, $this->interestByCampaign),
			'Referers_urlByWebsite' => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->interestByWebsiteAndUrl, $this->interestByWebsite),
		);
		
		foreach($blobRecords as $recordName => $table )
		{
			$dataToRecord = $table->getSerialized($maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable);
			$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $dataToRecord);
		}
	}
}
