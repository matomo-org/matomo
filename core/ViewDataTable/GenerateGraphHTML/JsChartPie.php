<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: ChartVerticalBar.php 2968 2010-08-20 15:26:33Z vipsoft $
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Generates a pie chart using jqplot
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */

class Piwik_ViewDataTable_GenerateGraphHTML_JsChartPie
		extends Piwik_ViewDataTable_GenerateGraphHTML_ChartPie
{
	
	protected function buildView()
	{
		$this->dataTableTemplate = 'CoreHome/templates/graph_jqplot.tpl';
		
		$view = parent::buildView();
		$view->jqPlotType = $this->getViewDataTableId();
		return $view;
	}
	
}