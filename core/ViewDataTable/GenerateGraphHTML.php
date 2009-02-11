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
		$this->parametersToModify = array( 
						'viewDataTable' => $this->getViewDataTableIdToLoad(),
						// in the case this controller is being executed by another controller
						// eg. when being widgetized in an IFRAME
						// we need to put in the URL of the graph data the real module and action
						'module' => $currentControllerName, 
						'action' => $currentControllerAction,
		);
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

		$this->parametersToModify['action'] = $this->currentControllerAction;
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($this->parametersToModify);
		$view->jsInvocationTag = $this->getFlashInvocationCode($url);
		$view->urlGraphData = $url;
		
		$view->formEmbedId = "formEmbed".$this->uniqueIdViewDataTable;
		$view->graphCodeEmbed = $this->graphCodeEmbed;
		
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->properties = $this->getViewProperties();
		return $view;
	}
	
	protected function getFlashInvocationCode( $url = 'libs/open-flash-chart/data-files/nodata.txt', $use_swfobject = true )
	{ 
		$width = $this->width; 
		$height = $this->height; 

		$libPathInPiwik = 'libs/open-flash-chart/';
		$currentPath = Piwik_Url::getCurrentUrlWithoutFileName();
		$pathToLibraryOpenChart = $currentPath . $libPathInPiwik;
		
		$url = Piwik_Url::getCurrentUrlWithoutQueryString() . $url;
		// escape the & and stuff:
		$url = urlencode($url);

		$obj_id = $this->uniqueIdViewDataTable . "Chart";
		$div_name = $this->uniqueIdViewDataTable . "FlashContent";
		$return = '';
		if( $use_swfobject )
		{
			// Using library for auto-enabling Flash object on IE, disabled-Javascript proof
			$return .=  '
<div id="'. $div_name .'"><div id="'. $obj_id .'_swf"><noscript>';
		}
		$urlGraph = $pathToLibraryOpenChart."open-flash-chart.swf?data=" . $url;
		
		// when the object/embed is changed, see also widgetize.js; it may require a logic update
		$this->graphCodeEmbed .= '<div><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="' . $width . '" height="' . $height . '" id="'. $obj_id .'" >
<param name="movie" value="'.$urlGraph.'" />
<param name="wmode" value="opaque" />
<param name="allowScriptAccess" value="sameDomain" />
<embed src="'.$urlGraph.'" wmode="opaque" allowScriptAccess="sameDomain" quality="high" bgcolor="#FFFFFF" width="'. $width .'" height="'. $height .'" name="open-flash-chart" type="application/x-shockwave-flash" id="'. $obj_id .'" />
</object></div>';
		$return .= $this->graphCodeEmbed;
		if( $use_swfobject )
		{
			$return .= '
</noscript></div></div>
<script type="text/javascript">
swfobject.embedSWF("'.$pathToLibraryOpenChart.'open-flash-chart.swf", "'. $obj_id .'_swf", "'. $width . '", "' . $height . '", "9.0.0", false, {"data":"'.$url.'"}, {"allowScriptAccess":"sameDomain","wmode":"opaque"}, {"bgcolor":"#FFFFFF"});
</script>';
		}
		
		return $return;
	}
}

