<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Cloud.php 581 2008-07-27 23:07:52Z matt $
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
	protected $displayLogoInsteadOfLabel = false;

	protected function getViewDataTableId()
	{
		return 'cloud';
	}
		
	/**
	 * @see Piwik_ViewDataTable::init()
	 */
	function init($currentControllerName,
						$currentControllerAction, 
						$apiMethodToRequestDataTable )
	{
		parent::init($currentControllerName,
						$currentControllerAction, 
						$apiMethodToRequestDataTable );
		$this->dataTableTemplate = 'CoreHome/templates/cloud.tpl';
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
		$this->view = $this->buildView();
	}
	
	protected function buildView()
	{
		$view = new Piwik_View($this->dataTableTemplate);
		
		$words = $labelMetadata = array();
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
				$logo =  $row->getMetadata('logo');
			}
			
			$labelMetadata[$label] = array( 
				'logo' => $logo,
				'url' => $row->getMetadata('url'),
				'hits' => $value
				);
		}
		$cloud = new Piwik_Visualization_Cloud($words);
		$cloudValues  = $cloud->render('array');
		
		foreach($cloudValues as &$value)
		{
			$value['logoWidth'] = round(max(16, $value['percent']));
		}
		$view->labelMetadata = $labelMetadata;
		$view->cloudValues = $cloudValues;
		
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->properties = $this->getViewProperties();
		return $view;
	}
}
