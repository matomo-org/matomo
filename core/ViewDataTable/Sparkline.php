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
 */
class Piwik_ViewDataTable_Sparkline extends Piwik_ViewDataTable
{
	protected function getViewDataTableId()
	{
		return 'sparkline';
	}

	protected $columnsToDisplay = array();
	
	public function setColumnsToDisplay($columns)
	{
		$this->columnsToDisplay = $columns;
	}
	
	public function getColumnsToDisplay()
	{
		return $this->columnsToDisplay;
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
		
		$this->isDataAvailable = $this->dataTable->getRowsCount() != 0;
		if(!$this->isDataAvailable)
		{
			throw new Exception(Piwik_Translate('General_NoDataForGraph'));
		}
		$values = $this->getValuesFromDataTable($this->dataTable);
		
		$graph = new Piwik_Visualization_Sparkline;
		$graph->setValues($values);
		$graph->main();
		
		$this->view = $graph;
	}
	
	protected function getValuesFromDataTable( Piwik_DataTable_Array $dataTableArray)
	{
		$dataTableArray->applyQueuedFilters();
		
		$columns = $this->getColumnsToDisplay();
		$columnToPlot = false;
		if(!empty($columns))
		{
			$columnToPlot = $columns[0];
		}
		$values = array();
		foreach($dataTableArray->getArray() as $keyName => $table)
		{
			$value = 0;
			if($table->getRowsCount() > 1)
			{
				throw new Exception("Expecting only one row per DataTable");
			}
			$onlyRow = $table->getFirstRow();
			if($onlyRow !== false)
			{
				if(!empty($columnToPlot))
				{
					$value = $onlyRow->getColumn($columnToPlot);
				}
				// if not specified, we load by default the first column found
				// eg. case of getLastDistinctCountriesGraph
				else
				{
					$columns = $onlyRow->getColumns();
					$value = current($columns);
				}
			}
			$values[] = $value;
		}
		return $values;
	}
}
