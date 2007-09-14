<?php
require_once "View/Cloud.php";
class Piwik_ViewDataTable_Cloud extends Piwik_View_DataTable
{
	
	protected $displayLogoInsteadOfLabel = false;
	function init($currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerAction, 
						$moduleNameAndMethod );
		$this->dataTableTemplate = 'UserSettings/templates/cloud.tpl';
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
		$this->disableSearchBox();
	}
	
	public function main()
	{
		$this->setDefaultLimit( 30 );
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
	
		$this->loadDataTableFromAPI();
	
		// We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace(
									$this->dataTable, 
									'label', 
									'urldecode'
								);
		
		
		$view = new Piwik_View($this->dataTableTemplate);
		$view->method = $this->method;
		
		$view->id 			= $this->getUniqIdTable();
		
		
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
//		echo $this->dataTable; exit;
		$words = $labelDetails = array();
		foreach($this->dataTable->getRows() as $row)
		{
			$label = $row->getColumn('label');
			$value = $row->getColumn('nb_unique_visitors');
			// case no unique visitors
			if($value === false)
			{
				$value = $row->getColumn('nb_visits');
			}
			$words[$label] = $value;
			
			$logo = false;
			if($this->displayLogoInsteadOfLabel)
			{
				$logo =  $row->getDetail('logo');
			}
			
			$labelDetails[$label] = array( 
				'logo' => $logo,
				'url' => $row->getDetail('url'),
				'hits' => $value
				);
		}
		$cloud = new Piwik_Cloud($words);
		$cloudValues  = $cloud->render('array');
		
		foreach($cloudValues as &$value)
		{
			$value['logoWidth'] = round(max(16, $value['percent']));
		}
//		var_dump($cloudValues);exit;
//		var_dump($labelDetails);exit;
		$view->labelDetails = $labelDetails;
		$view->cloudValues = $cloudValues;
		
		$this->view = $view;
	}
}
?>
