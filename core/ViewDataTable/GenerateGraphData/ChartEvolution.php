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
	
	protected function guessUnitFromRequestedColumnNames($requestedColumnNames)
	{
		$nameToUnit = array(
			'_rate' => '%',
			'_revenue' => Piwik::getCurrency(),
		);
		foreach($requestedColumnNames as $columnName)
		{
			foreach($nameToUnit as $pattern => $type)
			{
				if(strpos($columnName, $pattern) !== false)
				{
					return $type;
				}
			}
		}
		return false;
	}
	
	protected function initChartObjectData()
	{
		// if the loaded datatable is a simple DataTable, it is most likely a plugin plotting some custom data
		// we don't expect plugin developers to return a well defined Piwik_DataTable_Array 
		if($this->dataTable instanceof Piwik_DataTable)
		{
			return parent::initChartObjectData();
		}
		
		$this->dataTable->applyQueuedFilters();
		if(!($this->dataTable instanceof Piwik_DataTable_Array))
		{
			throw new Exception("Expecting a DataTable_Array with custom format to draw an evolution chart");
		}
		
		// the X label is extracted from the 'period' object in the table's metadata
		$xLabels = $uniqueIdsDataTable = array();
		foreach($this->dataTable->metadata as $idDataTable => $metadataDataTable)
		{
			//eg. "Aug 2009"
			$xLabels[] = $metadataDataTable['period']->getLocalizedShortString();
			// we keep track of all unique data table that we need to set a Y value for
			$uniqueIdsDataTable[] = $idDataTable;
		}
		
		$requestedColumnNames = $this->getColumnsToDisplay();
		$yAxisLabelToValue = array();
		foreach($this->dataTable->getArray() as $idDataTable => $dataTable)
		{
			foreach($dataTable->getRows() as $row)
			{
				$rowLabel = $row->getColumn('label');
				foreach($requestedColumnNames as $requestedColumnName)
				{
					$metricLabel = $this->getColumnTranslation($requestedColumnName);
					if($rowLabel !== false)
					{
						// eg. "Yahoo! (Visits)"
						$yAxisLabel = "$rowLabel ($metricLabel)";
					}
					else
					{
						// eg. "Visits"
						$yAxisLabel = $metricLabel;
					}
					if(($columnValue = $row->getColumn($requestedColumnName)) !== false)
					{
						$yAxisLabelToValue[$yAxisLabel][$idDataTable] = $columnValue;
					} 
				}
			}
		}
		
		// make sure all column values are set to at least zero (no gap in the graph) 
		$yAxisLabelToValueCleaned = array();
		$yAxisLabels = array();
		foreach($uniqueIdsDataTable as $uniqueIdDataTable)
		{
			foreach($yAxisLabelToValue as $yAxisLabel => $idDataTableToColumnValue)
			{
				$yAxisLabels[$yAxisLabel] = $yAxisLabel;
				if(isset($idDataTableToColumnValue[$uniqueIdDataTable]))
				{
					$columnValue = $idDataTableToColumnValue[$uniqueIdDataTable];
				}
				else
				{
					$columnValue = 0;
				}
				$yAxisLabelToValueCleaned[$yAxisLabel][] = $columnValue;
			}
		}
		
		$unit = $this->yAxisUnit;
		if(empty($unit))
		{
			$unit = $this->guessUnitFromRequestedColumnNames($requestedColumnNames);
		}
		
		$this->view->setAxisXLabels($xLabels);
		$this->view->setAxisYValues($yAxisLabelToValueCleaned);
		$this->view->setAxisYLabels($yAxisLabels);
		$this->view->setAxisYUnit($unit);
		
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
						'?' .
						Piwik_Url::getQueryStringFromParameters( array(
							'module' => 'CoreHome',
							'action' => 'index',
							'idSite' => Piwik_Common::getRequestVar('idSite'),
							'period' => $period->getLabel(),
							'date' => $dateInUrl,
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
