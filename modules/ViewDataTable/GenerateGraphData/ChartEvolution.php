<?php
require_once "ViewDataTable/GenerateGraphData.php";
/**
 * Piwik_ViewDataTable_GenerateGraphData for the Evolution graph (eg. Last 30 days visits) using Piwik_Visualization_ChartEvolution
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphData
{
	function __construct()
	{
		require_once "Visualization/ChartEvolution.php";
		$this->view = new Piwik_Visualization_ChartEvolution;
	}
	
	protected function generateDataFromDataTable()
	{
		return $this->generateDataFromDataTableArray($this->dataTable);
	}
}
