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
