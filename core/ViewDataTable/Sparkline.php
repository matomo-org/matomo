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
 * Reads the requested DataTable from the API and prepare data for the Sparkline view.
 * 
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_Sparkline extends Piwik_ViewDataTable
{
	protected function getViewDataTableId()
	{
		return 'sparkline';
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
	
		$this->loadDataTableFromAPI();
		
		$this->isDataAvailable = $this->dataTable->getRowsCount() != 0;
		if(!$this->isDataAvailable)
		{
			throw new Exception(Piwik_TranslateException('General_NoDataForGraph'));
		}
		$values = $this->getValuesFromDataTable($this->dataTable);
		$graph = new Piwik_Visualization_Sparkline();
		$graph->setValues($values);
		$graph->main();
		
		$this->view = $graph;
	}
	
	protected function getValuesFromDataTableArray( $dataTableArray, $columnToPlot )
	{
		$dataTableArray->applyQueuedFilters();
		$values = array();
		foreach($dataTableArray->getArray() as $table)
		{
			if($table->getRowsCount() > 1)
			{
				throw new Exception("Expecting only one row per DataTable");
			}
			$value = 0;
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
	
	protected function getValuesFromDataTable( $dataTable )
	{
		$columns = $this->getColumnsToDisplay();
		$columnToPlot = false;
		if(!empty($columns))
		{
			$columnToPlot = $columns[0];
		}
		
		// a Piwik_DataTable_Array is returned when using the normal code path to request data from Archives, in all core plugins
		// however plugins can also return simple datatable, hence why the sparkline can accept both data types
		if($this->dataTable instanceof Piwik_DataTable_Array)
		{
			$values = $this->getValuesFromDataTableArray($dataTable, $columnToPlot);
		}
		elseif($this->dataTable instanceof Piwik_DataTable)
		{
			$values = $this->dataTable->getColumn($columnToPlot);
		}
		return $values;
	}
}
