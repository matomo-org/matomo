<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ViewDataTable
 */

require_once "Visualization/Cloud.php";

/**
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Cloud extends Piwik_ViewDataTable
{
	protected $displayLogoInsteadOfLabel = false;
	function init($currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerAction, 
						$moduleNameAndMethod );
		$this->dataTableTemplate = 'Home/templates/cloud.tpl';
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
	}
	
	public function main()
	{
		$this->setLimit( 30 );
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
		$cloud = new Piwik_Visualization_Cloud($words);
		$cloudValues  = $cloud->render('array');
		
		foreach($cloudValues as &$value)
		{
			$value['logoWidth'] = round(max(16, $value['percent']));
		}
//		var_dump($cloudValues);exit;
//		var_dump($labelDetails);exit;
		$view->labelDetails = $labelDetails;
		$view->cloudValues = $cloudValues;
		
		$view->showFooter = $this->showFooter;
		$this->view = $view;
	}
}
