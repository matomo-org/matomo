<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ViewDataTable
 */

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
		$this->view = new Piwik_Visualization_Chart_Pie();
	}
}
