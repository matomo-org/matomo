<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_CustomVariables
 */

/**
 * @package Piwik_CustomVariables
 */
class Piwik_CustomVariables extends Piwik_Plugin
{
	public $archiveProcessing;
	protected $columnToSortByBeforeTruncation;
	protected $maximumRowsInDataTableLevelZero;
	protected $maximumRowsInSubDataTable;

	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('CustomVariables_PluginDescription'),
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
			'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
			'API.getReportMetadata' => 'getReportMetadata',
		    'API.getSegmentsMetadata' => 'getSegmentsMetadata',
		);
		return $hooks;
	}

	function addWidgets()
	{
		Piwik_AddWidget( 'General_Visitors', 'CustomVariables_CustomVariables', 'CustomVariables', 'getCustomVariables');
	}

	function addMenus()
	{
		Piwik_AddMenu('General_Visitors', 'CustomVariables_CustomVariables', array('module' => 'CustomVariables', 'action' => 'index'), $display = true, $order = 50);
	}

	public function getReportMetadata($notification)
	{
		$reports = &$notification->getNotificationObject();
		$reports = array_merge($reports, array(
		array(
        			'category'  => Piwik_Translate('General_Visitors'),
        			'name'   => Piwik_Translate('CustomVariables_CustomVariables'),
        			'module' => 'CustomVariables',
        			'action' => 'getCustomVariables',
        			'dimension' => Piwik_Translate('CustomVariables_ColumnCustomVariableName'),
        			'documentation' => Piwik_Translate('CustomVariables_CustomVariablesReportDocumentation', array('<br />', '<a href="http://piwik.org/docs/custom-variables/" target="_blank">', '</a>')),
        			'order' => 10
		),
		));
	}

	public function getSegmentsMetadata($notification)
	{
		$segments =& $notification->getNotificationObject();
		for($i=1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++)
		{
			$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'CustomVariables_CustomVariables',
		        'name' => Piwik_Translate('CustomVariables_ColumnCustomVariableName').' '.$i
			.' ('.Piwik_Translate('CustomVariables_ScopeVisit').')',
		        'segment' => 'customVariableName'.$i,
		        'sqlSegment' => 'log_visit.custom_var_k'.$i,
			);
			$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'CustomVariables_CustomVariables',
		        'name' => Piwik_Translate('CustomVariables_ColumnCustomVariableValue').' '.$i
			.' ('.Piwik_Translate('CustomVariables_ScopeVisit').')',
		        'segment' => 'customVariableValue'.$i,
		        'sqlSegment' => 'log_visit.custom_var_v'.$i,
			);
			$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'CustomVariables_CustomVariables',
		        'name' => Piwik_Translate('CustomVariables_ColumnCustomVariableName').' '.$i
			.' ('.Piwik_Translate('CustomVariables_ScopePage').')',
		        'segment' => 'customVariablePageName'.$i,
		        'sqlSegment' => 'log_link_visit_action.custom_var_k'.$i,
			);
			$segments[] = array(
		        'type' => 'dimension',
		        'category' => 'CustomVariables_CustomVariables',
		        'name' => Piwik_Translate('CustomVariables_ColumnCustomVariableValue').' '.$i
			.' ('.Piwik_Translate('CustomVariables_ScopePage').')',
		        'segment' => 'customVariablePageValue'.$i,
		        'sqlSegment' => 'log_link_visit_action.custom_var_v'.$i,
			);
		}
	}

	/**
	 * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
	 */
	function getReportsWithGoalMetrics( $notification )
	{
		$dimensions =& $notification->getNotificationObject();
		$dimensions = array_merge($dimensions, array(
		array(	'category'  => Piwik_Translate('General_Visit'),
            			'name'   => Piwik_Translate('CustomVariables_CustomVariables'),
            			'module' => 'CustomVariables',
            			'action' => 'getCustomVariables',
		),
		));
	}

	function __construct()
	{
		$this->maximumRowsInDataTableLevelZero = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_referers;
		$this->maximumRowsInSubDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_subtable_referers;
	}

	protected $interestByCustomVariables = array();
	protected $interestByCustomVariablesAndValue = array();

	/**
	 * Hooks on daily archive to trigger various log processing
	 *
	 * @param Piwik_Event_Notification $notification
	 * @return void
	 */
	public function archiveDay( $notification )
	{
		$this->interestByCustomVariables = $this->interestByCustomVariablesAndValue = array();

		/**
		 * @var Piwik_ArchiveProcessing_Day
		 */
		$this->archiveProcessing = $notification->getNotificationObject();

		if(!$this->archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

		$this->archiveDayAggregate($this->archiveProcessing);
		$this->archiveDayRecordInDatabase($this->archiveProcessing);
		destroy($this->interestByCustomVariables);
		destroy($this->interestByCustomVariablesAndValue);
	}

	const LABEL_CUSTOM_VALUE_NOT_DEFINED = "Value not defined";
	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 * @return void
	 */
	protected function archiveDayAggregate(Piwik_ArchiveProcessing_Day $archiveProcessing)
	{
		for($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++ )
		{
			$keyField = "custom_var_k".$i;
			$valueField = "custom_var_v".$i;
			$dimensions = array($keyField, $valueField);
			$where = "%s.$keyField != ''";
			 
			// Custom Vars names and values metrics for visits
			$query = $archiveProcessing->queryVisitsByDimension($dimensions, $where);
			 
			while($row = $query->fetch() )
			{
				// Handle case custom var value is empty
				$row[$valueField] = $this->cleanCustomVarValue($row[$valueField]);
								
				// Aggregate
				if(!isset($this->interestByCustomVariables[$row[$keyField]])) $this->interestByCustomVariables[$row[$keyField]]= $archiveProcessing->getNewInterestRow();
				if(!isset($this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]])) $this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]] = $archiveProcessing->getNewInterestRow();
				$archiveProcessing->updateInterestStats( $row, $this->interestByCustomVariables[$row[$keyField]]);
				$archiveProcessing->updateInterestStats( $row, $this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]]);
			}
			 
			// Custom Vars names and values metrics for page views
			$query = $archiveProcessing->queryActionsByDimension($dimensions, $where);
			$onlyMetricsAvailableInActionsTable = true;
			while($row = $query->fetch() )
			{
				// Handle case custom var value is empty
				$row[$valueField] = $this->cleanCustomVarValue($row[$valueField]);
								
				$label = $row[$valueField];
				
				// when custom variable value is a JSON array of categories
				// possibly JSON value
				$mustInsertCustomVariableValue = true;
				if($row[$keyField] == '_pkc'
					&& $label[0] == '[' && $label[1] == '"') 
				{
					// In case categories were truncated, try closing the array
					if(substr($label, -2) != '"]') {
						$label .= '"]';
					}
					$decoded = @json_decode($label);
					if(is_array($decoded))
					{
						$count = 0;
						foreach($decoded as $category)
						{
							if(empty($category) 
								|| $count >= Piwik_Tracker_GoalManager::MAXIMUM_PRODUCT_CATEGORIES) {
								continue;
							}
							if(!isset($this->interestByCustomVariablesAndValue[$row[$keyField]][$category])) {
								$this->interestByCustomVariablesAndValue[$row[$keyField]][$category] = $archiveProcessing->getNewInterestRow($onlyMetricsAvailableInActionsTable);
							}
							$archiveProcessing->updateInterestStats( $row, $this->interestByCustomVariablesAndValue[$row[$keyField]][$category], $onlyMetricsAvailableInActionsTable);
							$mustInsertCustomVariableValue = false;
							$count++;
						}
					}
				} // end multi categories hack
				
				if($mustInsertCustomVariableValue)
				{
					if(!isset($this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]])) $this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]] = $archiveProcessing->getNewInterestRow($onlyMetricsAvailableInActionsTable);
					$archiveProcessing->updateInterestStats( $row, $this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]], $onlyMetricsAvailableInActionsTable);
				}
				
				// Hack: when tracking Ecommerce product page view, we do not wish 
				// to track the "price" in the Custom Variable name report, only in the values report
				unset($row[Piwik_Archive::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED]);
				
				if(!isset($this->interestByCustomVariables[$row[$keyField]])) $this->interestByCustomVariables[$row[$keyField]]= $archiveProcessing->getNewInterestRow($onlyMetricsAvailableInActionsTable);
				$archiveProcessing->updateInterestStats( $row, $this->interestByCustomVariables[$row[$keyField]], $onlyMetricsAvailableInActionsTable);
			}
			 
			// Custom Vars names and values metrics for Goals
			$query = $archiveProcessing->queryConversionsByDimension($dimensions, $where);

			if($query !== false)
			{
				while($row = $query->fetch() )
				{
					// Handle case custom var value is empty
					$row[$valueField] = $this->cleanCustomVarValue($row[$valueField]);
					
					if(!isset($this->interestByCustomVariables[$row[$keyField]][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByCustomVariables[$row[$keyField]][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow($row['idgoal']);
					if(!isset($this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow($row['idgoal']);

					$archiveProcessing->updateGoalStats( $row, $this->interestByCustomVariables[$row[$keyField]][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
					$archiveProcessing->updateGoalStats( $row, $this->interestByCustomVariablesAndValue[$row[$keyField]][$row[$valueField]][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
				}
			}
		}
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByCustomVariables);
		$archiveProcessing->enrichConversionsByLabelArrayHasTwoLevels($this->interestByCustomVariablesAndValue);
		 
		//    	var_dump($this->interestByCustomVariables);
    	//var_dump($this->interestByCustomVariablesAndValue);
	}

	protected function cleanCustomVarValue($value)
	{
		if(!empty($value))
		{
			return $value;
		} 
		return self::LABEL_CUSTOM_VALUE_NOT_DEFINED;
	}
	
	/**
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 * @return void
	 */
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$recordName = 'CustomVariables_valueByName';
		$table = $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->interestByCustomVariablesAndValue, $this->interestByCustomVariables);

		$blob = $table->getSerialized($this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable);
		$archiveProcessing->insertBlobRecord($recordName, $blob);
		destroy($table);
	}

	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();

		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

		$dataTableToSum = 'CustomVariables_valueByName';
		$nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum, null, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable);
	}
}
