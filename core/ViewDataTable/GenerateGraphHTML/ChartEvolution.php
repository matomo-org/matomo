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
		
		$this->setParametersToModify(array('date' => Piwik_Common::getRequestVar('date', 'last30', 'string')));
		$this->doNotShowFooter();
	}
	
	/**
	 * Sets the columns that will be displayed on output evolution chart
	 * By default all columns are displayed ($columnsNames = array() will display all columns)
	 * 
	 * @param array $columnsNames Array of column names eg. array('nb_visits','nb_hits')
	 */
	public function setColumnsToDisplay( $columnsNames)
	{
		$this->setParametersToModify( array('columns' => $columnsNames) );
	}
}