<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 *
 * @package Piwik_Goals
 */
class Piwik_Goals extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('Goals_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
			'TrackerPlugin' => true, // this plugin must be loaded during the stats logging
		);
		return $info;
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'AssetManager.getJsFiles' => 'getJsFiles',
			'AssetManager.getCssFiles' => 'getCssFiles',
			'Common.fetchWebsiteAttributes' => 'fetchGoalsFromDb',
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'API.getReportMetadata.end' => 'getReportMetadata',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenus',
			'SitesManager.deleteSite' => 'deleteSiteGoals',
		);
		return $hooks;
	}

	/**
	 * Delete goals recorded for this site
	 */
	function deleteSiteGoals($notification )
	{
		$idSite = &$notification->getNotificationObject();
		Piwik_Query("DELETE FROM ".Piwik_Common::prefixTable('goal') . " WHERE idsite = ? ", array($idSite));
	}
	
	/**
	 * Returns the Metadata for the Goals plugin API.
	 * The API returns general Goal metrics: conv, conv rate and revenue globally
	 * and for each goal.
	 *
	 * Also, this will update metadata of all other reports that have Goal segmentation
	 */
	public function getReportMetadata($notification)
	{
		$idSites = $notification->getNotificationInfo();
		$reports = &$notification->getNotificationObject();
	
		// Processed in AddColumnsProcessedMetricsGoal
		// These metrics will also be available for some reports, for each goal
		// Example: Conversion rate for Goal 2 for the keyword 'piwik'
		$goalProcessedMetrics = array(
    		'revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
    	);
		
		$goalMetrics = array(
			'nb_conversions' => Piwik_Translate('Goals_ColumnConversions'),
			'conversion_rate' => Piwik_Translate('General_ColumnConversionRate'),
			'revenue' => Piwik_Translate('Goals_ColumnRevenue')
		);

		// General Goal metrics: conversions, conv rate, revenue
		$reports[] = array(
			'category' => Piwik_Translate('Goals_Goals'),
			'name' => Piwik_Translate('Goals_Goals'),
			'module' => 'Goals',
			'action' => 'get',
			'metrics' => $goalMetrics,
			'processedMetrics' => array(),
        	'order' => 1
		);
		
		/*
		 * Add the metricsGoal and processedMetricsGoal entry
		 * to all reports that have Goal segmentation
		 */
		$reportsWithGoals = array();
		Piwik_PostEvent('Goals.getReportsWithGoalMetrics', $reportsWithGoals);
		foreach($reportsWithGoals as $reportWithGoals)
		{
			// Select this report from the API metadata array
			// and add the Goal metrics to it
			foreach($reports as &$apiReportToUpdate)
			{
				if($apiReportToUpdate['module'] == $reportWithGoals['module']
					&& $apiReportToUpdate['action'] == $reportWithGoals['action'])
				{
					$apiReportToUpdate['metricsGoal'] = $goalMetrics;
					$apiReportToUpdate['processedMetricsGoal'] = $goalProcessedMetrics;
					break;
				}
			}
		}
		
		// If only one website is selected, we add the Goal metrics
		if(count($idSites) == 1)
		{
			$goals = Piwik_Goals_API::getInstance()->getGoals(reset($idSites));
			foreach($goals as $goal)
			{
				// Add the general Goal metrics: ie. total Goal conversions,
				// Goal conv rate or Goal total revenue.
				// This API call requires a custom parameter
				$reports[] = array(
					'category' => Piwik_Translate('Goals_Goals'),
					'name' => Piwik_Translate('Goals_GoalX', $goal['name']),
					'module' => 'Goals',
					'action' => 'get',
					'parameters' => array('idGoal' => $goal['idgoal']),
					'metrics' => $goalMetrics,
					'processedMetrics' => false,
        			'order' => 10 + $goal['idgoal']
				);
			}
		}
	}
	
	static public function getReportsWithGoalMetrics()
	{
		$dimensions = array();
		Piwik_PostEvent('Goals.getReportsWithGoalMetrics', $dimensions);
		$dimensionsByGroup = array();
		foreach($dimensions as $dimension)
		{
			$group = $dimension['category'];
			unset($dimension['category']);
			$dimensionsByGroup[$group][] = $dimension;
		}
		return $dimensionsByGroup;
	}
	
	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = "plugins/Goals/templates/GoalForm.js";
	}

	function getCssFiles( $notification )
	{
		$cssFiles = &$notification->getNotificationObject();
		$cssFiles[] = "plugins/Goals/templates/goals.css";
	}
	
	function fetchGoalsFromDb($notification)
	{
		$idsite = $notification->getNotificationInfo();
		
		// add the 'goal' entry in the website array
		$array =& $notification->getNotificationObject();
		$array['goals'] = Piwik_Goals_API::getInstance()->getGoals($idsite);
	}
	
	function addWidgets()
	{
		Piwik_AddWidget('Goals_Goals', 'Goals_GoalsOverview', 'Goals', 'widgetGoalsOverview');
		$goals = Piwik_Tracker_GoalManager::getGoalDefinitions(Piwik_Common::getRequestVar('idSite', null, 'int'));
		if(count($goals) > 0)
		{
			foreach($goals as $goal)
			{
        		Piwik_AddWidget('Goals_Goals', Piwik_Common::sanitizeInputValue($goal['name']), 'Goals', 'widgetGoalReport', array('idGoal' => $goal['idgoal']));
			}
		}
	}
	
	function addMenus()
	{
		$goals = Piwik_Tracker_GoalManager::getGoalDefinitions(Piwik_Common::getRequestVar('idSite', null, 'int'));
		if(count($goals)==0)
		{
			Piwik_AddMenu('Goals_Goals', '', array('module' => 'Goals', 'action' => 'addNewGoal'), true, 25);
			Piwik_AddMenu('Goals_Goals', 'Goals_AddNewGoal', array('module' => 'Goals', 'action' => 'addNewGoal'));
		}
		else
		{
			Piwik_AddMenu('Goals_Goals', '', array('module' => 'Goals', 'action' => 'index'), true, 25);
			Piwik_AddMenu('Goals_Goals', 'Goals_Overview', array('module' => 'Goals', 'action' => 'index'), true, 1);
			foreach($goals as $goal)
			{
				Piwik_AddMenu('Goals_Goals', str_replace('%', '%%', Piwik_TranslationWriter::clean($goal['name'])), array('module' => 'Goals', 'action' => 'goalReport', 'idGoal' => $goal['idgoal']));
			}
		}
	}
	
	/**
	 * @param string $recordName 'nb_conversions'
	 * @param int $idGoal idGoal to return the metrics for, or false to return overall
	 * @return string Archive record name
	 */
	static public function getRecordName($recordName, $idGoal = false)
	{
		$idGoalStr = '';
		if($idGoal !== false)
		{
			$idGoalStr = $idGoal . "_";
		}
		return 'Goal_' . $idGoalStr . $recordName;
	}
	
	/**
	 * Hooks on Period archiving.
	 * Sums up Goal conversions stats, and processes overall conversion rate
	 *
	 * @param Piwik_Event_Notification $notification
	 * @return void
	 */
	function archivePeriod($notification )
	{
		/**
		 * @var Piwik_ArchiveProcessing 
		 */
		$archiveProcessing = $notification->getNotificationObject();
		
		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;
		
		/*
		 * Archive Ecommerce Items
		 */
		if($this->shouldArchiveEcommerceItems($archiveProcessing))
		{
			$dataTableToSum = $this->dimensions;
			foreach($this->dimensions as $recordName)
			{
				$dataTableToSum[] = self::getItemRecordNameAbandonedCart($recordName);
			}
			$nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum);
		}
				
		/*
		 *  Archive General Goal metrics
		 */
		$goalIdsToSum = Piwik_Tracker_GoalManager::getGoalIds($archiveProcessing->idsite);
		if(Piwik::isEcommerceEnabled($archiveProcessing->idsite))
		{
			$goalIdsToSum[] = Piwik_Tracker_GoalManager::IDGOAL_ORDER;
			$goalIdsToSum[] = Piwik_Tracker_GoalManager::IDGOAL_CART;
			// Overall goal metrics
			$goalIdsToSum[] = false;
		}
		$fieldsToSum = array();
		foreach($goalIdsToSum as $goalId)
		{
			$metricsToSum = Piwik_Goals::getGoalColumns($goalId);
			unset($metricsToSum[array_search('conversion_rate', $metricsToSum)]);
			foreach($metricsToSum as $metricName)
			{
				$fieldsToSum[] = self::getRecordName($metricName, $goalId);
			}
		}
		$records = $archiveProcessing->archiveNumericValuesSum($fieldsToSum);
		
		// also recording conversion_rate for each goal
		foreach($goalIdsToSum as $goalId)
		{
			$nb_conversions = $records[self::getRecordName('nb_visits_converted', $goalId)];
			$conversion_rate = $this->getConversionRate($nb_conversions, $archiveProcessing);
			$archiveProcessing->insertNumericRecord(self::getRecordName('conversion_rate', $goalId), $conversion_rate);
		} 
	}
	
	static public function getGoalColumns($idGoal)
	{
		$columns = array(
					'nb_conversions',
					'nb_visits_converted',
					'conversion_rate', 
					'revenue',
		);
		if($idGoal === false)
		{
			return $columns;
		}
		// Orders
		if($idGoal === Piwik_Tracker_GoalManager::IDGOAL_ORDER)
		{
			$columns = array_merge($columns, array(
				'revenue_subtotal',
				'revenue_tax',
				'revenue_shipping',
				'revenue_discount',
			));
		}
		// Abandoned carts & orders
		if($idGoal <= Piwik_Tracker_GoalManager::IDGOAL_ORDER) 
		{
			$columns[] = 'items';
		}
		return $columns;
	}
	
	/**
	 * Hooks on the Daily archiving.
	 * Will process Goal stats overall and for each Goal.
	 * Also processes the New VS Returning visitors conversion stats.
	 *
	 * @param Piwik_Event_Notification $notification
	 * @return void
	 */
	function archiveDay( $notification )
	{
		/**
		 * @var Piwik_ArchiveProcessing_Day
		 */
		$archiveProcessing = $notification->getNotificationObject();
		
		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;
		
		$this->archiveGeneralGoalMetrics($archiveProcessing);
		$this->archiveEcommerceItems($archiveProcessing);
	}
	
	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	function archiveGeneralGoalMetrics($archiveProcessing)
	{
		$query = $archiveProcessing->queryConversionsByDimension('');
		
		if($query === false) { return; }
		
		$goals = array();
		// Get a standard empty goal row
		$overall = $archiveProcessing->getNewGoalRow( $idGoal = 1);
		while($row = $query->fetch() )
		{
			if(!isset($goals[$row['idgoal']])) $goals[$row['idgoal']] = $archiveProcessing->getNewGoalRow($row['idgoal']);
			$archiveProcessing->updateGoalStats($row, $goals[$row['idgoal']]);
			
			// We don't want to sum Abandoned cart metrics in the overall revenue/conversions/converted visits 
			// since it is a "negative conversion"
			if($row['idgoal'] != Piwik_Tracker_GoalManager::IDGOAL_CART)
			{
				$archiveProcessing->updateGoalStats($row, $overall);
			}
		}
		// Stats by goal, for all visitors
		foreach($goals as $idgoal => $values)
		{
			foreach($values as $metricId => $value)
			{
				$metricName = Piwik_Archive::$mappingFromIdToNameGoal[$metricId];
				$recordName = self::getRecordName($metricName, $idgoal);
				$archiveProcessing->insertNumericRecord($recordName, $value);
			}
			
			$conversion_rate = $this->getConversionRate($values[Piwik_Archive::INDEX_GOAL_NB_VISITS_CONVERTED], $archiveProcessing);
			$recordName = self::getRecordName('conversion_rate', $idgoal);
			$archiveProcessing->insertNumericRecord($recordName, $conversion_rate);
		}
		
		
		// Stats for all goals
		$totalAllGoals = array(
			self::getRecordName('conversion_rate')	=> $this->getConversionRate($archiveProcessing->getNumberOfVisitsConverted(), $archiveProcessing),
			self::getRecordName('nb_conversions')	=> $overall[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS],
			self::getRecordName('nb_visits_converted')	=> $archiveProcessing->getNumberOfVisitsConverted(),
			self::getRecordName('revenue') 			=> $overall[Piwik_Archive::INDEX_GOAL_REVENUE],
		);
		foreach($totalAllGoals as $recordName => $value)
		{
			$archiveProcessing->insertNumericRecord($recordName, $value);
		}
	}
	
	protected $dimensions = array(
		'idaction_sku' => 'Goals_ItemsSku', 
		'idaction_name' => 'Goals_ItemsName', 
		'idaction_category'  => 'Goals_ItemsCategory'
	);
	
	protected function shouldArchiveEcommerceItems($archiveProcessing)
	{
	    if(!Piwik::isEcommerceEnabled($archiveProcessing->idsite)
	    	// Per item doesn't support segment
	    	// Also, when querying Goal metrics for visitorType==returning, we wouldnt want to trigger an extra request 
	    	// event if it did support segment 
	    	// (if this is implented, we should have shouldProcessReportsForPlugin() support partial archiving based on which metric is requested) 
	    	|| !$archiveProcessing->getSegment()->isEmpty())
	    {
	    	return false;
	    }
	    return true;
	}
	
	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	function archiveEcommerceItems($archiveProcessing)
	{
		if(!$this->shouldArchiveEcommerceItems($archiveProcessing))
		{
			return false;
		}
		$dimensionsNotSet = array(
			'idaction_sku' => Piwik_Translate('General_NotDefined', Piwik_Translate('Goals_ProductSKU')), // Note: this should never happen 
			'idaction_name' => Piwik_Translate('General_NotDefined', Piwik_Translate('Goals_ProductName')), 
			'idaction_category'  => Piwik_Translate('General_NotDefined', Piwik_Translate('Goals_ProductCategory'))
		);
		$items = array();
		foreach($this->dimensions as $dimension => $recordName)
		{
			$query = $archiveProcessing->queryEcommerceItems($dimension);
			if($query == false) { return; }
			
			while($row = $query->fetch())
			{
				$label = $row['label'];
				$ecommerceType = $row['type'];
				
				if(empty($label))
				{
					$label = $dimensionsNotSet[$dimension];
				}
				// For carts, idorder = 0. To count abandoned carts, we must count visits with an abandoned cart
				if($ecommerceType == Piwik_Tracker_GoalManager::IDGOAL_CART)
				{
					$row[Piwik_Archive::INDEX_ECOMMERCE_ORDERS] = $row[Piwik_Archive::INDEX_NB_VISITS];
				}
				unset($row[Piwik_Archive::INDEX_NB_VISITS]);
				unset($row['label']);
				unset($row['type']);
				if($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE] == round($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE]))
				{
					$row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE] = round($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE]);
				}
				if($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE] == round($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE]))
				{
					$row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE] = round($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE]);
				}
				$items[$dimension][$ecommerceType][$label] = $row;
			}
		}
		
		foreach($this->dimensions as $dimension => $recordName)
		{
			foreach(array(Piwik_Tracker_GoalManager::IDGOAL_CART, Piwik_Tracker_GoalManager::IDGOAL_ORDER) as $ecommerceType)
			{
				if(!isset($items[$dimension][$ecommerceType]))
				{
					continue;
				}
				$recordNameInsert = $recordName;
				if($ecommerceType == Piwik_Tracker_GoalManager::IDGOAL_CART)
				{
					$recordNameInsert = self::getItemRecordNameAbandonedCart($recordName);
				}
				$table = $archiveProcessing->getDataTableFromArray($items[$dimension][$ecommerceType]);
				$archiveProcessing->insertBlobRecord($recordNameInsert, $table->getSerialized());
			}
		}
	}
	
	static public function getItemRecordNameAbandonedCart($recordName)
	{
		return $recordName . '_Cart';
	}
	
	function getConversionRate($count, $archiveProcessing)
	{
		$visits = $archiveProcessing->getNumberOfVisits();
		return round(100 * $count / $visits, Piwik_Tracker_GoalManager::REVENUE_PRECISION);
	}

}
