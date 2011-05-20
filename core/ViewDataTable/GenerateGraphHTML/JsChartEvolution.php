<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Generates an evolution chart using jqplot
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */

class Piwik_ViewDataTable_GenerateGraphHTML_JsChartEvolution
		extends Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution
{
	
	protected function buildView()
	{
		$this->dataTableTemplate = 'CoreHome/templates/graph_jqplot.tpl';
		$this->height = 170;
		
		$view = parent::buildView();
		$view->jqPlotType = $this->getViewDataTableId();
		return $view;
	}
	
}