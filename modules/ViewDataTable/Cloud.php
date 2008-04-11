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
 * Reads the requested DataTable from the API, and prepares the data to give 
 * to Piwik_Visualization_Cloud that will display the tag cloud (via the template cloud.tpl).
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Cloud extends Piwik_ViewDataTable
{
	//TODO test this
	protected $displayLogoInsteadOfLabel = false;
	
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
		$this->dataTableTemplate = 'Home/templates/cloud.tpl';
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
	}
	
	/**
	 * @see Piwik_ViewDataTable::main()
	 *
	 */
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
		
		$words = $labelDetails = array();
		foreach($this->dataTable->getRows() as $row)
		{
			$label = $row->getColumn('label');
			$value = $row->getColumn('nb_uniq_visitors');
			
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
		
		$view->method = $this->method;
		$view->id = $this->getUniqIdTable();
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->showFooter = $this->getShowFooter();
		$this->view = $view;
	}
}
