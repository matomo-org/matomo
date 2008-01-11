<?php

class Piwik_ViewDataTable_Html extends Piwik_ViewDataTable
{
	protected $columnsToDisplay = array();
	
	public $arrayDataTable; // phpArray
	
	function init($currentControllerAction, 
						$moduleNameAndMethod,						
						$actionToLoadTheSubTable = null )
	{
		parent::init($currentControllerAction, 
						$moduleNameAndMethod,						
						$actionToLoadTheSubTable);
		$this->dataTableTemplate = 'Home/templates/datatable.tpl';
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
		
//		$i=0;while($i<1500000){ $j=$i*$i;$i++;}
		
		$this->loadDataTableFromAPI();
	
		// We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace(
									$this->dataTable, 
									'label', 
									'urldecode'
								);
		
		
		$view = new Piwik_View($this->dataTableTemplate);
		
		$view->id 			= $this->getUniqIdTable();
		
		// We get the PHP array converted from the DataTable
		$phpArray = $this->getPHPArrayFromDataTable();
		
		
		$view->arrayDataTable 	= $phpArray;
		$view->method = $this->method;
		
		$columns = $this->getColumnsToDisplay($phpArray);
		$view->dataTableColumns = $columns;
		
		$nbColumns = count($columns);
		// case no data in the array we use the number of columns set to be displayed 
		if($nbColumns == 0)
		{
			$nbColumns = count($this->columnsToDisplay);
		}
		
		$view->nbColumns = $nbColumns;
		
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		
		$this->view = $view;
	}

	protected function getPHPArrayFromDataTable( )
	{
		$renderer = Piwik_DataTable_Renderer::factory('php');
		$renderer->setTable($this->dataTable);
		$renderer->setSerialize( false );
		$phpArray = $renderer->render();
		return $phpArray;
	}

	
	
	public function setColumnsToDisplay( $arrayIds)
	{
		$this->columnsToDisplay = $arrayIds;
	}
	
	protected function getColumnsToDisplay($phpArray)
	{
		
		$dataTableColumns = array();
		if(count($phpArray) > 0)
		{
			// build column information
			$id = 0;
			foreach($phpArray[0]['columns'] as $columnName => $row)
			{
				if( $this->isColumnToDisplay( $id, $columnName) )
				{
					$dataTableColumns[]	= array('id' => $id, 'name' => $columnName);
				}
				$id++;
			}
		}
		return $dataTableColumns;
	}

	protected function isColumnToDisplay( $idColumn )
	{
		// we return true
		// - we didn't set any column to display (means we display all the columns)
		// - the column has been set as to display
		if( count($this->columnsToDisplay) == 0
			|| in_array($idColumn, $this->columnsToDisplay))
		{
			return true;
		}
		return false;
	}

	public function setSortedColumn( $columnId, $order = 'desc')
	{
		$this->variablesDefault['filter_sort_column']= $columnId;
		$this->variablesDefault['filter_sort_order']= $order;
	}
	

	public function setSearchRecursive()
	{
		$this->variablesDefault['search_recursive'] = true;
	}
	
	
	public function setRecursiveLoadDataTableIfSearchingForPattern()
	{
		try{
			$requestValue = Piwik_Common::getRequestVar('filter_column_recursive');
			$requestValue = Piwik_Common::getRequestVar('filter_pattern_recursive');
			// if the 2 variables are set we are searching for something.
			// we have to load all the children subtables in this case
			
			$this->recursiveDataTableLoad = true;
			return true;
		}
		catch(Exception $e) {
			$this->recursiveDataTableLoad = false;
			return false;
		}
	}
}