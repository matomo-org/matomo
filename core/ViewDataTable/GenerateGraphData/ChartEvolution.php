<?php
require_once "ViewDataTable/GenerateGraphData.php";
/**
 * Piwik_ViewDataTable_GenerateGraphData for the Evolution graph (eg. Last 30 days visits) using Piwik_Visualization_Chart_Evolution
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphData
{
	protected function getViewDataTableId()
	{
		return 'generateDataChartEvolution';
	}
	
	function __construct()
	{
		require_once "Visualization/Chart/Evolution.php";
		$this->view = new Piwik_Visualization_Chart_Evolution;
	}
	
	var $lineLabels = array();
	var $data = array();
	
	private function generateLine( $dataArray, $columns, $schema = "##label## ##column##" )
	{
		$data = array();
		
		foreach($dataArray as $keyName => $table)
		{
			$table->applyQueuedFilters();

			// initialize data (default values for all lines is 0)
			$dataRow = array();

			$rows = $table->getRows();

			foreach($rows as $row)
			{
				$rowLabel = $schema;

				if( strpos($rowLabel, "##label##") !== false )
				{
					$rowLabel = str_replace("##label##", $row->getColumn('label'), $rowLabel);
				}
					
				foreach($columns as $col)
				{
					$label = $rowLabel;
					
					if( strpos($label, "##column##") !== false )
					{
						$label = str_replace("##column##", $col, $label);
					}
					
					if( !isset($this->lineLabels[$label]) )
					{
						$this->lineLabels[$label] = count($this->lineLabels);
					}					
					$lineNb = $this->lineLabels[$label];
										
					$value = $row->getColumn($col);

					$dataRow['value'.$lineNb] = $value;
				}
			}
			$data[] = $dataRow;
		}
		return $data;
	}
	
	private function generateLabels( $dataArray )
	{
		$data = array();
		
		foreach($dataArray as $keyName => $table)
		{
			$table->applyQueuedFilters();
			
			$data[] = array('label' => $keyName);
		}
		
		return $data;
	}
	
	private function addArray( &$data, $newData )
	{	
		for($i = 0; $i < count($newData); $i++)
		{
			foreach($newData[$i] as $key => $value)
			{
				$data[$i][$key] = $value;
			}
		}
	}
	
	private function fillValues( &$data )
	{
		$nbLines = count($this->lineLabels);
		
		for($i = 0; $i < count($data); $i++)
		{
			for($j = 0; $j < $nbLines; $j++)
			{
				if( !isset($data[$i]['value'.$j]) )
				{
					$data[$i]['value'.$j] = 0;
				}
			}
		}
	}
	
	/*
	 * generates data for evolution graph from a numeric DataTable (DataTable that has only 'label' and 'value' columns)
	 */
	protected function generateDataFromNumericDataTable($dataArray, $siteLabel = "")
	{
		$columnsToDisplay = Piwik_Common::getRequestVar('columns', array(), 'array');
				
		// for numeric we want to have only one column name
		if( count($columnsToDisplay) != 1 )
		{
			$columnsToDisplay = array( 'nb_uniq_visitors' );
		}
		
		$label = $siteLabel . array_shift($columnsToDisplay);
		
		$this->addArray($this->data, $this->generateLabels($dataArray));
		$this->addArray($this->data, $this->generateLine($dataArray,array('value'),$label));
		$this->fillValues($this->data);
	}
	
	/*
	 * generates data for evolution graph from a DataTable that has named columns (i.e. 'nb_hits', 'nb_uniq_visitors')    
	 */
	protected function generateDataFromRegularDataTable($dataArray, $siteLabel = "")
	{	
		// get list of columns 	to display i.e. array('nb_hits','nb_uniq_visitors')						
		$columnsToDisplay = Piwik_Common::getRequestVar('columns', array(), 'array');
				
		// default column
		if( count($columnsToDisplay) == 0 )
		{
			$columnsToDisplay = array( 'nb_uniq_visitors' );
		}		
		
		$this->addArray($this->data, $this->generateLabels($dataArray));
		$this->addArray($this->data, $this->generateLine($dataArray, $columnsToDisplay, $siteLabel."##label## ##column##"));
		$this->fillValues($this->data);
	}	

	protected function handleSiteGenerateDataFromDataTable($dataArray, $siteLabel = "")
	{			
		// detect if we got numeric Datatable or regular DataTable	
		foreach($dataArray as $table) 
		{
			$row = $table->getFirstRow();
				
			if( $row != null )
			{
				$columns = $row->getColumns();

				// if we got 2 columns - 'label' and 'value' this is numeric DataTable
				if( count($columns) == 2 && isset($columns['label']) && isset($columns['value']) )
				{
					$this->generateDataFromNumericDataTable($dataArray, $siteLabel);
				}
				else
				{
					$this->generateDataFromRegularDataTable($dataArray, $siteLabel);
				}
				break;
			}
		}
	}
			
	public function generateDataFromDataTable()
	{
		$data = array();
				
		if( $this->dataTable->getRowsCount() )
		{
			$row = null;
			
			// find first table with rows
			foreach($this->dataTable->getArray() as $idsite => $table)
			{
				// detect if we got data from more than one site
				if( $table instanceof Piwik_DataTable_Array)
				{
					// multiple sites
					$site = new Piwik_Site($idsite);
					
					$this->handleSiteGenerateDataFromDataTable($table->getArray(), $site->getName()." ");
				}
				else if( $table instanceof Piwik_DataTable_Simple && $this->dataTable->getKeyName() == 'idSite')
				{
					// multiple sites (when numeric DataTable)
					$site = new Piwik_Site($idsite);
										
					$this->handleSiteGenerateDataFromDataTable($table->getFirstRow()->getColumn('value')->getArray(), $site->getName()." ");
				}
				else
				{
					// single site
					$this->handleSiteGenerateDataFromDataTable($this->dataTable->getArray());
					break;
				}				
			}			

		}		
		array_unshift($this->data, array_keys($this->lineLabels));
				
		return $this->data;
	}
}
