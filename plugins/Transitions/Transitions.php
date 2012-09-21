<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Transitions
 */
	
/**
 * @package Piwik_Transitions
 */
class Piwik_Transitions extends Piwik_Plugin
{
	
	private $limitBeforeGrouping = 5;
	
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('Transitions_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	function getListHooksRegistered()
	{
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'AssetManager.getJsFiles' => 'getJsFiles'
		);
	}
	    
	public function getCssFiles($notification)
	{
		$cssFiles = &$notification->getNotificationObject();
		$cssFiles[] = 'plugins/Transitions/templates/transitions.css';
	}
	
	public function getJsFiles($notification)
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = 'plugins/Transitions/templates/transitions.js';
	}

	/**
	 * Get information about external referrers (i.e. search engines, websites & campaigns)
	 * 
	 * @param $idaction
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 * @return Piwik_DataTable
	 */
	public function queryExternalReferrers($idaction, Piwik_ArchiveProcessing_Day $archiveProcessing,
				$limitBeforeGrouping = false)
	{
		$rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
		
		// we generate a single column that contains the interesting data for each referrer.
		// the reason we cannot group by referer_* becomes clear when we look at search engine keywords.
		// referer_url contains the url from the search engine, referer_keyword the keyword we want to
		// group by. when we group by both, we don't get a single column for the keyword but instead
		// one column per keyword + search engine url. this way, we could not get the top keywords using
		// the ranking query.
		$dimension = 'referrer_data';
		$rankingQuery->addLabelColumn('referrer_data');
		$select = '
			CASE referer_type
				WHEN '.Piwik_Common::REFERER_TYPE_DIRECT_ENTRY.' THEN ""
				WHEN '.Piwik_Common::REFERER_TYPE_SEARCH_ENGINE.' THEN referer_keyword
				WHEN '.Piwik_Common::REFERER_TYPE_WEBSITE.' THEN referer_url
				WHEN '.Piwik_Common::REFERER_TYPE_CAMPAIGN.' THEN CONCAT(referer_name, " ", referer_keyword)
			END AS referrer_data,
			referer_type';
		
		// get one limited group per referrer type
		$rankingQuery->partitionResultIntoMultipleGroups('referer_type', array(
				Piwik_Common::REFERER_TYPE_DIRECT_ENTRY,
				Piwik_Common::REFERER_TYPE_SEARCH_ENGINE,
				Piwik_Common::REFERER_TYPE_WEBSITE,
				Piwik_Common::REFERER_TYPE_CAMPAIGN
			));
		
		$orderBy = '`'.Piwik_Archive::INDEX_NB_VISITS.'` DESC';
		$where = 'visit_entry_idaction_url = '.intval($idaction);
		
		$metrics = array(Piwik_Archive::INDEX_NB_VISITS);
		$data = $archiveProcessing->queryVisitsByDimension($dimension, $where, $metrics, $orderBy,
					$rankingQuery, $select, $selectGeneratesLabelColumn = true);
		
		$referrerData = array();
		$referrerSubData = array();
		
		foreach ($data as $referrerType => &$subData)
		{
			$referrerData[$referrerType] = array(Piwik_Archive::INDEX_NB_VISITS => 0);
			if ($referrerType != Piwik_Common::REFERER_TYPE_DIRECT_ENTRY)
			{
				$referrerSubData[$referrerType] = array();
			}
			
			foreach ($subData as &$row)
			{
				if ($referrerType == Piwik_Common::REFERER_TYPE_SEARCH_ENGINE && empty($row['referrer_data']))
				{
					$row['referrer_data'] = Piwik_Referers::LABEL_KEYWORD_NOT_DEFINED;
				}
				
				$referrerData[$referrerType][Piwik_Archive::INDEX_NB_VISITS] += $row[Piwik_Archive::INDEX_NB_VISITS];
				
				$label = $row['referrer_data'];
				if ($label)
				{
					$referrerSubData[$referrerType][$label] = array(
						Piwik_Archive::INDEX_NB_VISITS => $row[Piwik_Archive::INDEX_NB_VISITS]
					);
				}
			}
		}
		
		return $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($referrerSubData, $referrerData);
	}

	/**
	 * Get information about internal referrers (previous pages & loops, i.e. page refreshes)
	 * 
	 * @param $idaction
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 * @return array(previousPages:Piwik_DataTable, loops:integer)
	 */
	public function queryInternalReferrers($idaction, Piwik_ArchiveProcessing_Day $archiveProcessing,
			$limitBeforeGrouping = false)
	{
		$dimension = 'idaction_url_ref';
		
		$rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
		$rankingQuery->addLabelColumn(array('name', 'url_prefix'));
		$rankingQuery->setColumnToMarkExcludedRows('is_self');
		
		$addSelect = '
			log_action.name, log_action.url_prefix,
			CASE WHEN log_link_visit_action.idaction_url_ref = '.intval($idaction).' THEN 1 ELSE 0 END AS is_self';
		
		$where = '
			log_link_visit_action.idaction_url = '.intval($idaction).' AND
			log_action.type = '.Piwik_Tracker_Action::TYPE_ACTION_URL;
		
		$orderBy = '`'.Piwik_Archive::INDEX_NB_ACTIONS.'` DESC';
		
		$metrics = array(Piwik_Archive::INDEX_NB_ACTIONS);
		$data = $archiveProcessing->queryActionsByDimension(array($dimension), $where, $metrics, $orderBy,
					$rankingQuery, $dimension, $addSelect);
		
		$previousPagesDataTable = new Piwik_DataTable;
		foreach ($data['result'] as &$page)
		{
			$previousPagesDataTable->addRow(new Piwik_DataTable_Row(array(
				Piwik_DataTable_Row::COLUMNS => array(
					'label' => Piwik_Tracker_Action::reconstructNormalizedUrl($page['name'], $page['url_prefix']),
					Piwik_Archive::INDEX_NB_ACTIONS => intval($page[Piwik_Archive::INDEX_NB_ACTIONS])
				)
			)));
		}
		
		$loops = 0;
		if (count($data['excludedFromLimit']))
		{
			$loops = intval($data['excludedFromLimit'][0][Piwik_Archive::INDEX_NB_ACTIONS]);
		}
		
		return array(
			'previousPages' => $previousPagesDataTable,
			'loops' => $loops
		);
	}
	
	/**
	 * Get information about the following actions (following pages, outlinks, downloads)
	 * 
	 * @param $idaction
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 * @return array(followingPages:Piwik_DataTable, outlinks:Piwik_DataTable, downloads:Piwik_DataTable)
	 */
	public function queryFollowingActions($idaction, Piwik_ArchiveProcessing_Day $archiveProcessing,
				$limitBeforeGrouping = false)
	{
		static $types = array(
			Piwik_Tracker_Action::TYPE_ACTION_URL => 'followingPages',
			Piwik_Tracker_Action::TYPE_OUTLINK => 'outlinks',
			Piwik_Tracker_Action::TYPE_DOWNLOAD => 'downloads'
		);
		
		$dimension = 'idaction_url';
				
		$rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
		$rankingQuery->addLabelColumn(array('name', 'url_prefix'));
		$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($types));
		
		$addSelect = 'log_action.name, log_action.url_prefix, log_action.type';
		
		$where = '
			log_link_visit_action.idaction_url_ref = '.intval($idaction).' AND 
			log_link_visit_action.idaction_url != '.intval($idaction);
		
		$orderBy = '`'.Piwik_Archive::INDEX_NB_ACTIONS.'` DESC';
		
		$metrics = array(Piwik_Archive::INDEX_NB_ACTIONS);
		$data = $archiveProcessing->queryActionsByDimension(array($dimension), $where, $metrics, $orderBy, 
					$rankingQuery, $dimension, $addSelect);
		
		$dataTables = array();
		foreach ($types as $type => $recordName)
		{
			$dataTable = new Piwik_DataTable;
			if (isset($data[$type]))
			{
				foreach ($data[$type] as &$record)
				{
					$dataTable->addRow(new Piwik_DataTable_Row(array(
						Piwik_DataTable_Row::COLUMNS => array(
							'label' => $type == Piwik_Tracker_Action::TYPE_ACTION_URL ?
									Piwik_Tracker_Action::reconstructNormalizedUrl($record['name'], $record['url_prefix']) :
									$record['name'],
							Piwik_Archive::INDEX_NB_ACTIONS => intval($record[Piwik_Archive::INDEX_NB_ACTIONS])
						)
					)));
				}
			}
			$dataTables[$recordName] = $dataTable;
		}
		
		return $dataTables;
	}
	
}