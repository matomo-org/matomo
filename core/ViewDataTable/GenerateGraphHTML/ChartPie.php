<?php
require_once "ViewDataTable/GenerateGraphHTML.php";
/**
 * Generates HTML embed for the Pie chart
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphHTML_ChartPie extends Piwik_ViewDataTable_GenerateGraphHTML
{
	protected function getViewDataTableId()
	{
		return 'graphPie';
	}
	
	protected function getViewDataTableIdToLoad()
	{
		return 'generateDataChartPie';
	}
}
