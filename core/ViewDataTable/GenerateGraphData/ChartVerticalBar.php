<?php
require_once "ViewDataTable/GenerateGraphData.php";
/**
 * Piwik_ViewDataTable_GenerateGraphData for the vertical bar graph, using Piwik_Visualization_ChartVerticalBar
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartVerticalBar extends Piwik_ViewDataTable_GenerateGraphData
{
	protected $graphLimit = 5;
	
	protected function getViewDataTableId()
	{
		return 'generateDataChartVerticalBar';
	}
	
	function __construct()
	{
		require_once "Visualization/ChartVerticalBar.php";
		$this->view = new Piwik_Visualization_ChartVerticalBar;
	}
}
