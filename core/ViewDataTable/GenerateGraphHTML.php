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
 * This class generates the HTML code to embed to flash graphs in the page.
 * It doesn't call the API but simply prints the html snippet.
 * 
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
abstract class Piwik_ViewDataTable_GenerateGraphHTML extends Piwik_ViewDataTable
{	
	protected $width = '100%'; 
	protected $height = 250;
	protected $graphType = 'standard';
	
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

		$this->dataTableTemplate = 'CoreHome/templates/graph.tpl';
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
		$this->disableSearchBox();
		$this->enableShowExportAsImageIcon();
		
		$this->parametersToModify = array( 
						'viewDataTable' => $this->getViewDataTableIdToLoad(),
						// in the case this controller is being executed by another controller
						// eg. when being widgetized in an IFRAME
						// we need to put in the URL of the graph data the real module and action
						'module' => $currentControllerName, 
						'action' => $currentControllerAction,
		);
	}
	
	public function enableShowExportAsImageIcon()
	{
		$this->viewProperties['show_export_as_image_icon'] = true;
	}
	
	/**
	 * Sets parameters to modify in the future generated URL
	 * @param array $array array('nameParameter' => $newValue, ...)
	 */
	public function setParametersToModify($array)
	{
		$this->parametersToModify = array_merge($this->parametersToModify, $array);
	}
	
	/**
	 * We persist the parametersToModify values in the javascript footer.
	 * This is used by the "export links" that use the "date" attribute from the json properties array in the datatable footer.
	 */
	protected function getJavascriptVariablesToSet()
	{
		return $this->parametersToModify + parent::getJavascriptVariablesToSet();
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
		
		$this->view = $this->buildView();
	}
	
	protected function buildView()
	{
		$view = new Piwik_View($this->dataTableTemplate);
		$this->uniqueIdViewDataTable = $this->getUniqueIdViewDataTable();
		$view->graphType = $this->graphType;
		$this->chartDivId = $this->uniqueIdViewDataTable . "Chart_swf";

		$this->parametersToModify['action'] = $this->currentControllerAction;
		$this->parametersToModify = array_merge($this->variablesDefault, $this->parametersToModify);
		
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($this->parametersToModify);

		$this->includeData = Zend_Registry::get('config')->General->serve_widget_and_data;
		$idSite = Piwik_Common::getRequestVar('idSite', 1);
		if(Piwik::isUserHasViewAccess($idSite) && $this->includeData)
		{
			$this->chartData = $this->getFlashData();
		}
		else
		{
			$this->chartData = null;
		}
		$view->flashParameters = $this->getFlashParameters();
		$view->urlGraphData = $url;
		$view->chartDivId = $this->chartDivId;
		$view->formEmbedId = "formEmbed".$this->uniqueIdViewDataTable;
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->properties = $this->getViewProperties();
		return $view;
	}

	protected function getFlashData()
	{
		$saveGet = $_GET;

		foreach($this->parametersToModify as $key => $val)
		{
			if (is_array($val)) {
				$_GET[$key] = unserialize(serialize($val));
			} else {
				$_GET[$key] = $val;
			}
		}
		$content = Piwik_FrontController::getInstance()->fetchDispatch( $this->currentControllerName, $this->currentControllerAction, array());

		$_GET = $saveGet;

		return str_replace(array("\r", "\n", "'", '\"'), array('', '', "\\'", '\\"'), $content);
	}

	protected function getFlashParameters()
	{
		// chart title is only set when there's no data in the graph
		$isDataAvailable = $this->chartData && !preg_match('/],\s+"title": {/', $this->chartData);

		return array(
			'width'                => $this->width,
			'height'               => $this->height,
			'ofcLibraryPath'       => 'libs/open-flash-chart/',
			'swfLibraryPath'       => 'libs/swfobject/',
			'requiredFlashVersion' => '9.0.0',
			'isDataAvailable'      => $isDataAvailable,
			'includeData'          => $this->includeData,
			'data'                 => $this->chartData,
		);
	}
}
