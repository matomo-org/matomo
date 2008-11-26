<?php
require_once "ViewDataTable/GenerateGraphHTML.php";
/**
 * 
 * Generates HTML embed for the vertical bar chart
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphHTML_ChartVerticalBar extends Piwik_ViewDataTable_GenerateGraphHTML
{
	protected function getViewDataTableId()
	{
		return 'graphVerticalBar';
	}
	
	protected function getViewDataTableIdToLoad()
	{
		return 'generateDataChartVerticalBar';
	}
}
