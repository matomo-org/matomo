<?php
require_once "ViewDataTable/GenerateGraphData.php";
/**
 * Piwik_ViewDataTable_GenerateGraphData for the pie chart, using Piwik_Visualization_Chart_Pie
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartPie extends Piwik_ViewDataTable_GenerateGraphData
{
	protected $graphLimit = 4;
	
	protected function getViewDataTableId()
	{
		return 'generateDataChartPie';
	}
	
	function __construct()
	{
		require_once "Visualization/Chart/Pie.php";
		$this->view = new Piwik_Visualization_Chart_Pie;
	}
}
