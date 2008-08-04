<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Html.php 404 2008-03-23 01:09:59Z matt $
 * 
 * @package Piwik_ViewDataTable
 */


require_once "Visualization/Sparkline.php";

/**
 * Reads the requested DataTable from the API and prepare data for the Sparkline view.
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Sparkline extends Piwik_ViewDataTable
{
	
	/**
	 * @see Piwik_ViewDataTable::init()
	 */
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerName, 
						$currentControllerAction, 
						$moduleNameAndMethod );
	}
	
	/**
	 * @see Piwik_ViewDataTable::main()
	 */
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
	
		// we load the data with the filters applied
		$this->loadDataTableFromAPI();
		
		$this->dataAvailable = $this->dataTable->getRowsCount() != 0;
		
		if(!$this->dataAvailable)
		{
			throw new Exception( "No data for this graph" );
		}
		else
		{
			$data = $this->generateDataFromDataTableArray($this->dataTable);
			
			$graph = new Piwik_Visualization_Sparkline;
			$graph->setData($data);
			$graph->main();
//			var_dump($data);exit;
			$this->view = $graph;
		}
	}
}
