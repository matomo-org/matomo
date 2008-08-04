<?php
require_once "ViewDataTable/GenerateGraphHTML.php";
/**
 * Generates HTML embed for the Evolution graph
 *  
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphHTML
{
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartEvolution';
		$this->width='100%';
		$this->height=150;
		// used for the CSS class to apply to the DIV containing the graph
		$this->graphType = 'evolution';		
	}
	
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod );
		
		$this->setParametersToModify(array('date' => 'last30'));
		$this->doNotShowFooter();
	}
}