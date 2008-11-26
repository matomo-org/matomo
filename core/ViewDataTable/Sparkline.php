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
			throw new Exception(Piwik_Translate('General_NoDataForGraph'));
		}
		$data = $this->generateDataFromDataTableArray($this->dataTable);
		
		$graph = new Piwik_Visualization_Sparkline;
		$graph->setData($data);
		$graph->main();
		
		$this->view = $graph;
	}
	
	/**
	 * Given a Piwik_DataTable_Array made of DataTable_Simple rows, returns a php array with the structure:
	 * array(
	 * 	array( label => X, value => Y),
	 * 	array( label => A, value => B),
	 * ...
	 * )
	 *
	 * This is used for example for the evolution graph (last 30 days visits) or the sparklines.
	 * 
	 * @param Piwik_DataTable_Array $dataTableArray
	 * @return array
	 */
	protected function generateDataFromDataTableArray( Piwik_DataTable_Array $dataTableArray)
	{
		$data = array();
		foreach($dataTableArray->getArray() as $keyName => $table)
		{
			if($table instanceof Piwik_DataTable_Array)
			{
				throw new Exception("Operation not supported (yet)");
			}
			$value = false;
			
			$onlyRow = $table->getFirstRow();
			if($onlyRow !== false)
			{
				$value = $onlyRow->getColumn('value');
				if($value == false)
				{
					// TEMP
					// quite a hack, useful in the case at this point we do have a normal row with nb_visits, nb_actions, nb_uniq_visitors, etc.
					// instead of the dataTable_Simple row (label, value) 
					// to do it properly we'd need to
					// - create a filter that removes columns
					// - apply this filter to keep only the column called nb_uniq_visitors
					// - rename this column as 'value'
					// and at this point the getcolumn('value') would have worked
					// this code is executed eg. when displaying a sparkline for the last 30 days displaying the number of unique visitors coming from search engines
					
					//TODO solution: use a filter rename column etc.
					
					// another solution would be to add a method to the Referers API giving directly the integer 'visits from search engines'
					// and we would build automatically the dataTable_array of datatatble_simple from these integers
					// but we'd have to add this integer to be recorded during archiving etc.
					$value = $onlyRow->getColumn('nb_uniq_visitors');
				}
			}

			if($value === false)
			{
				$value = 0;
			}
			$data[] = array(
					'label' => $keyName,
					'value' => $value
				);
		}
		return $data;
	}
}
