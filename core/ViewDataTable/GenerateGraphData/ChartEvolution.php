<?php
require_once "ViewDataTable/GenerateGraphData.php";
/**
 * Piwik_ViewDataTable_GenerateGraphData for the Evolution graph (eg. Last 30 days visits) using Piwik_Visualization_Chart_Evolution
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphData
{
	protected function getViewDataTableId()
	{
		return 'generateDataChartEvolution';
	}
	
	function __construct()
	{
		require_once "Visualization/Chart/Evolution.php";
		$this->view = new Piwik_Visualization_Chart_Evolution;
	}
	
	protected function generateDataFromDataTable()
	{
		$this->dataTable->applyQueuedFilters();
		if(!($this->dataTable instanceof Piwik_DataTable_Array))
		{
			throw new Exception("Expecting a DataTable_Array with custom format to draw an evolution chart");
		}
		$xLabels = $uniqueIdsDataTable = array();
		foreach($this->dataTable->metadata as $idDataTable => $metadataDataTable)
		{
			$xLabels[] = $metadataDataTable['period']->getLocalizedShortString();
			$uniqueIdsDataTable[] = $idDataTable;
		}
		
		// list of column names requested to be plotted, we only need to forward these to the Graph object
		$columnNameRequested = $this->getColumnsToDisplay();
		
		$columnNameToValue = array();
		foreach($this->dataTable->getArray() as $idDataTable => $dataTable)
		{
			if($dataTable->getRowsCount() > 1)
			{
				throw new Exception("Expecting only one row per DataTable");
			}
			$row = $dataTable->getFirstRow();
			if($row !== false)
			{
				foreach($row->getColumns() as $columnName => $columnValue)
				{
					if(array_search($columnName, $columnNameRequested) !== false)
					{
						$columnNameToValue[$columnName][$idDataTable] = $columnValue;
					} 
				}
			}
		}
		
		
		// make sure all column values are set (at least zero) in order for all unique idDataTable
		$columnNameToValueCleaned = array();
		foreach($uniqueIdsDataTable as $uniqueIdDataTable)
		{
			foreach($columnNameToValue as $columnName => $idDataTableToColumnValue)
			{
				if(isset($idDataTableToColumnValue[$uniqueIdDataTable]))
				{
					$columnValue = $idDataTableToColumnValue[$uniqueIdDataTable];
				}
				else
				{
					$columnValue = 0;
				}
				$columnNameToValueCleaned[$columnName][] = $columnValue;
			}
		}
		$columnNames = array_keys($columnNameToValueCleaned);
		$columnNameToTranslation = array();
		$columnNameToType = array();
		$nameToType = array(
			'_rate' => '%',
			'_revenue' => Piwik::getCurrency(),
		);
		foreach($columnNames as $columnName)
		{
			$columnNameToTranslation[$columnName] = $this->getColumnTranslation($columnName);
			$columnNameToType[$columnName] = false;
			foreach($nameToType as $pattern => $type)
			{
				if(strpos($columnName, $pattern) !== false)
				{
					$columnNameToType[$columnName] = $type;
					break;
				}
			}
		}
		$this->view->setAxisXLabels($xLabels);
		$this->view->setAxisYValues($columnNameToValueCleaned);
		$this->view->setAxisYLabels($columnNameToTranslation);
		$this->view->setAxisYValuesTypes($columnNameToType);
		
		$firstDatatable = reset($this->dataTable->metadata);
		$period = $firstDatatable['period'];
		switch($period->getLabel()) {
			case 'day': $steps = 7; break;
			case 'week': $steps = 10; break;
			case 'month': $steps = 6; break;
			case 'year': $steps = 2; break;
			default: $steps = 10; break;
		}
		$this->view->setXSteps($steps);
		
		if($this->isLinkEnabled())
		{
			$axisXOnClick = array();
			foreach($this->dataTable->metadata as $idDataTable => $metadataDataTable)
			{
				$period = $metadataDataTable['period'];
				$dateInUrl = $period->getDateStart();
				$link = Piwik_Url::getCurrentUrlWithoutQueryString() . 
						Piwik_Url::getCurrentQueryStringWithParametersModified( array(
							'date' => $dateInUrl,
							'module' => 'CoreHome',
							'action' => 'index',
							'viewDataTable' => null, // we reset the viewDataTable parameter (useless in the link)
							'idGoal' => null, // we reset idGoal
							'columns' => null, 
				));
				$axisXOnClick[] = $link;
			}
			$this->view->setAxisXOnClick($axisXOnClick);
		}
	}

	private function isLinkEnabled() 
	{
		static $linkEnabled;
		if(!isset($linkEnabled)) 
		{
			$linkEnabled = !Piwik_Common::getRequestVar('disableLink', 0, 'int');
		}
		return $linkEnabled;
	}
}
