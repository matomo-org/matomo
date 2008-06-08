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
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartPie';
	}
}