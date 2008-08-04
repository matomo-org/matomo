<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: GenerateGraphData.php 579 2008-07-27 00:32:59Z matt $
 * 
 * @package Piwik_ViewDataTable
 */

/**
 * Reads data from the API and prepares data to give to the renderer Piwik_Visualization_Chart.
 * This class is used to generate the data for the FLASH charts. It is given as a parameter of the SWF file.
 * You can set the number of elements to appear in the graph using: setGraphLimit();
 * Example:
 * <pre>
 * 	function getWebsites( $fetch = false)
 * 	{
 * 		$view = Piwik_ViewDataTable::factory();
 * 		$view->init( $this->pluginName, 'getWebsites', 'Referers.getWebsites', 'getUrlsFromWebsiteId' );
 * 		$view->setColumnsToDisplay( array('label','nb_visits') );
 *		$view->setLimit(10);
 * 		$view->setGraphLimit(12);
 * 		return $this->renderView($view, $fetch);
 * 	}
 * </pre>
 *  
 * @package Piwik_ViewDataTable
 *
 */
abstract class Piwik_ViewDataTable_GenerateGraphData extends Piwik_ViewDataTable
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
	 * Number of elements to display in the graph.
	 *
	 * @var int
	 */
	protected $graphLimit = 5;
	
	/**
	 * Sets the number max of elements to display (number of pie slice, vertical bars, etc.)
	 * If the data has more elements than $limit then the last part of the data will be the sum of all the remaining data.
	 *
	 * @param int $limit
	 */
	function setGraphLimit( $limit )
	{
		$this->graphLimit = $limit;
	}
	/**
	 * Returns numbers of elemnts to display in the graph
	 *
	 * @return int
	 */
	function getGraphLimit()
	{
		return $this->graphLimit;
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
	
		$this->setLimit(-1);
		
		// we load the data with the filters applied
		$this->loadDataTableFromAPI();
		$offsetStartSummary = $this->getGraphLimit() - 1;
		$this->dataTable->queueFilter('Piwik_DataTable_Filter_AddSummaryRow', array($offsetStartSummary, Piwik_Translate('General_Others')));
		$this->dataAvailable = $this->dataTable->getRowsCount() != 0;
		
		if(!$this->dataAvailable)
		{
			$this->view->customizeGraph();
			$this->view->title(Piwik_Translate('General_NoDataForGraph'), '{font-size: 25px;}');
		}
		else
		{
			$data = $this->generateDataFromDataTable();
			$this->view->setData($data);
			$this->view->customizeGraph();
		}
	}
	
	/**
	 * Returns a format friendly array from the dataTable 
	 *
	 * @return array
	 */
	protected function generateDataFromDataTable()
	{
		$this->dataTable->applyQueuedFilters();
		
		// We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace(
									$this->dataTable, 
									'label', 
									'urldecode'
								);
		$data = array();
		foreach($this->dataTable->getRows() as $row)
		{
			$label = $row->getColumn('label');
			$value = $row->getColumn('nb_uniq_visitors');
			
			// case no unique visitors
			if($value === false)
			{
				$value = $row->getColumn('nb_visits');
			}
			
			$data[] = array(
				'label' => $label,
				'value' => $value,
				'url' 	=> $row->getMetadata('url'),
			);
		}
		return $data;
	}
}



