<?php
require_once "ViewDataTable/GenerateGraphData.php";
/**
 * Piwik_ViewDataTable_GenerateGraphData for the pie chart, using Piwik_Visualization_ChartPie
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartPie extends Piwik_ViewDataTable_GenerateGraphData
{
	function __construct()
	{
		require_once "Visualization/ChartPie.php";
		$this->view = new Piwik_Visualization_ChartPie;
	}
}