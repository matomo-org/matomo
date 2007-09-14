<?php

class Piwik_ViewDataTable_GenerateGraphData extends Piwik_ViewDataTable
{
	function __construct($typeViewRequested)
	{
		parent::__construct($typeViewRequested);
	}
	
	function init($currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerAction, 
						$moduleNameAndMethod );
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
		$this->disableSearchBox();
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
	
		switch($this->typeViewRequested)
		{
			case 'generateDataChartPie':
				require_once "Visualization/ChartPie.php";
				$view = new Piwik_Visualization_ChartPie;			
			break;
			
			default:
			case 'generateDataChartVerticalBar':
				require_once "Visualization/ChartVerticalBar.php";
				$view = new Piwik_Visualization_ChartVerticalBar;
			break;
			
		}

		$this->setDefaultLimit( $view->getDefaultLimit() );
		
	
		$this->loadDataTableFromAPI();
		// We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace(
									$this->dataTable, 
									'label', 
									'urldecode'
								);

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
		$view->setData($data);
		$view->customizeGraph();
		
		$this->view = $view;
		
	}
}
?>
