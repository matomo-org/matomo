<?php

abstract class Piwik_ViewDataTable_GenerateGraphData extends Piwik_ViewDataTable
{
	
	function init($currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerAction, 
						$moduleNameAndMethod );
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
		$this->disableSearchBox();
	}
	
	protected $graphLimit;
	
	function setGraphLimit( $limit )
	{
		$this->graphLimit = $limit;
	}
	
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
	
		
		$this->setLimit($this->getGraphLimit());
		
		// we load the data with the filters applied
		$this->loadDataTableFromAPI();
		
//		echo $this->dataTable;
		$this->dataAvailable = $this->dataTable->getRowsCount() != 0;
		
		if(!$this->dataAvailable)
		{
			$this->view->title("No data for this graph", '{font-size: 25px;}');
		}
		else
		{
//			echo $this->dataTable;
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
				$value = $row->getColumn('nb_unique_visitors');
				// case no unique visitors
				if($value === false)
				{
					$value = $row->getColumn('nb_visits');
				}
				$data[] = array(
					'label' => $label,
					'value' => $value,
					'url' 	=> $row->getDetail('url'),
				);
			}
			$this->view->setData($data);
			$this->view->customizeGraph();
		}
	}
}

class Piwik_ViewDataTable_GenerateGraphData_ChartPie extends Piwik_ViewDataTable_GenerateGraphData
{
	function __construct()
	{
		require_once "Visualization/ChartPie.php";
		$this->view = new Piwik_Visualization_ChartPie;
	}
}
class Piwik_ViewDataTable_GenerateGraphData_ChartVerticalBar extends Piwik_ViewDataTable_GenerateGraphData
{
	function __construct()
	{
		require_once "Visualization/ChartVerticalBar.php";
		$this->view = new Piwik_Visualization_ChartVerticalBar;
	}
}
