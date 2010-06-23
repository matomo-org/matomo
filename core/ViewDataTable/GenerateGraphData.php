<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
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
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
abstract class Piwik_ViewDataTable_GenerateGraphData extends Piwik_ViewDataTable
{	
	/**
	 * Number of elements to display in the graph.
	 * @var int
	 */
	protected $graphLimit = null;
	protected $yAxisUnit = '';
	
	public function setAxisYUnit($unit)
	{
		$this->yAxisUnit = $unit;
	}
	
	/**
	 * Sets the number max of elements to display (number of pie slice, vertical bars, etc.)
	 * If the data has more elements than $limit then the last part of the data will be the sum of all the remaining data.
	 *
	 * @param int $limit
	 */
	public function setGraphLimit( $limit )
	{
		$this->graphLimit = $limit;
	}
	
	/**
	 * Returns numbers of elemnts to display in the graph
	 *
	 * @return int
	 */
	public function getGraphLimit()
	{
		return $this->graphLimit;
	}

	protected $displayPercentageInTooltip = true;
	
	/**
	 * The percentage in tooltips is computed based on the sum of all values for the plotted column.
	 * If the sum of the column in the data set is not the number of elements in the data set,
	 * for example when plotting visits that have a given plugin enabled: 
	 * one visit can have several plugins, hence the sum is much greater than the number of visits.
	 * In this case displaying the percentage doesn't make sense.
	 */
	public function disallowPercentageInGraphTooltip()
	{
		$this->displayPercentageInTooltip = false;
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;

		@header( "Content-type: application/json" );

		// the queued filters will be manually applied later. This is to ensure that filtering using search
		// will be done on the table before the labels are enhanced (see ReplaceColumnNames)
		$this->disableQueuedFilters();

		// throws exception if no view access
		$this->loadDataTableFromAPI();
		
		$graphLimit = $this->getGraphLimit();
		if(!empty($graphLimit))
		{
			$offsetStartSummary = $this->getGraphLimit() - 1;
			$this->dataTable->filter('AddSummaryRow', 
										array($offsetStartSummary, 
										Piwik_Translate('General_Others'), 
										Piwik_Archive::INDEX_NB_VISITS
										)
									);
		}
		$this->isDataAvailable = $this->dataTable->getRowsCount() != 0;

		if(!$this->isDataAvailable)
		{
			$this->view->setTitle(html_entity_decode(Piwik_Translate('General_NoDataForGraph'), ENT_COMPAT, 'UTF-8'), '{font-size: 25px;}');
		}
		else
		{
			$this->initChartObjectData();
		}
		$this->view->customizeChartProperties();
	}

	protected function initChartObjectData()
	{
		$this->dataTable->applyQueuedFilters();

		// We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
		$this->dataTable->filter('ColumnCallbackReplace', array('label','urldecode'));

		$xLabels = $this->dataTable->getColumn('label');
		$columnNames = parent::getColumnsToDisplay();
		if(($labelColumnFound = array_search('label',$columnNames)) !== false)
		{
			unset($columnNames[$labelColumnFound]);
		}
		
		$columnNameToTranslation = $columnNameToValue = array();
		foreach($columnNames as $columnName)
		{
			$columnNameToTranslation[$columnName] = $this->getColumnTranslation($columnName);
			$columnNameToValue[$columnName] = $this->dataTable->getColumn($columnName);
		}
		$this->view->setAxisXLabels($xLabels);
		$this->view->setAxisYValues($columnNameToValue);
		$this->view->setAxisYLabels($columnNameToTranslation);
		$this->view->setAxisYUnit($this->yAxisUnit);
		$this->view->setDisplayPercentageInTooltip($this->displayPercentageInTooltip);
	}
}
