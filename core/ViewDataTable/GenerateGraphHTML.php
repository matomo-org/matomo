<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: GenerateGraphHTML.php 581 2008-07-27 23:07:52Z matt $
 * 
 * @package Piwik_ViewDataTable
 */

/**
 * This class generates the HTML code to embed to flash graphs in the page.
 * It doesn't call the API but simply prints the html snippet.
 * 
 * @package Piwik_ViewDataTable
 *
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
		$view->jsInvocationTag = $this->getFlashInvocationCode($url);
		$view->urlGraphData = $url;
		$view->chartDivId = $this->chartDivId;
		$view->formEmbedId = "formEmbed".$this->uniqueIdViewDataTable;
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->properties = $this->getViewProperties();
		return $view;
	}
	
	protected function getFlashInvocationCode( $url = 'libs/open-flash-chart/data-files/nodata.txt' )
	{ 
		$width = $this->width; 
		$height = $this->height; 

		$currentPath = dirname(Piwik_Url::getCurrentScriptName() . 'x');
		$pathToLibraryOpenChart = $currentPath . '/libs/open-flash-chart/';
		$pathToLibrarySwfObject = $currentPath . '/libs/swfobject/';
		
		$url = Piwik_Url::getCurrentUrlWithoutQueryString() . $url;
		// escape the & and stuff:
		$url = urlencode($url);

		$requiredFlashVersion = "9.0.0";
		
		// - Export as Image feature from Open Flash Chart
		// - Using library for auto-enabling Flash object on IE, disabled-Javascript proof
		$return = '
			<div id="'. $this->chartDivId .'">
				Displaying Graphs in Piwik requires Flash >= '.$requiredFlashVersion.'. <a target="_blank" href="misc/redirectToUrl.php?url=http://piwik.org/faq/troubleshooting/#faq_53">More information about displaying graphs in Piwik.</a>
			</div>
			<script type="text/javascript">
				OFC = {};
				OFC.jquery = {
				    name: "jQuery",
				    rasterize: function (src, dst) { $("#"+ dst).replaceWith(Control.OFC.image(src)) },
				    image: function(src) { return "<img title=\'Piwik Graph\' src=\'data:image/png;base64," + $("#"+src)[0].get_img_binary() + "\' />"},
				    popup: function(src) {
				        var img_win = window.open("", "Charts: Export as Image")
				        with(img_win.document) {
				            write("<html><head><title>'.Piwik_Translate('General_ExportAsImage').'<\/title><\/head><body>" + Control.OFC.image(src) + "<br><br><p>'.htmlentities(Piwik_Translate('General_SaveImageOnYourComputer')).'</p><\/body><\/html>") }
				     }
				}
				if (typeof(Control == "undefined")) {var Control = {OFC: OFC.jquery}; }
				// By default, right-clicking on OFC and choosing "save image locally" calls this function.
				function save_image() { OFC.jquery.popup("'.$this->chartDivId.'"); }
				
					swfobject.embedSWF(
						"'.$pathToLibraryOpenChart.'open-flash-chart.swf", 
						"'. $this->chartDivId .'", 
						"'. $width . '", "' . $height . '", 
						"'.$requiredFlashVersion.'", 
						"'.$pathToLibrarySwfObject.'expressInstall.swf", 
						{
							"data-file":"'.$url.'", 
							"loading":"'.htmlspecialchars(Piwik_Translate('General_Loading')).'"
						}, 
						{
							"allowScriptAccess":"sameDomain",
							"wmode":"opaque"
						}, 
						{"bgcolor":"#FFFFFF"}
					);
				</script>';
		return $return;
	}
}

