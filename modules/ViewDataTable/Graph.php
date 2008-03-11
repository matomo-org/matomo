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

/**
 * 
 * @package Piwik_ViewDataTable
 *
 */
abstract class Piwik_ViewDataTable_Graph extends Piwik_ViewDataTable
{	
	protected $width = '100%'; 
	protected $height = 250;
	protected $graphType = 'standard';
	
	
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod );
		$this->dataTableTemplate = 'Home/templates/graph.tpl';
//		var_dump($currentControllerName);
//		var_dump($currentControllerAction);
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
		$this->disableSearchBox();
		$this->parametersToModify = array( 
						'viewDataTable' => $this->valueParameterViewDataTable,
						// in the case this controller is being executed by another controller
						// eg. when being widgetized in an IFRAME
						// we need to put in the URL of the graph data the real module and action
						'module' => $currentControllerName, 
						'action' => $currentControllerAction,
		);
	}
	
	public function setParametersToModify($array)
	{
		$this->parametersToModify = array_merge($this->parametersToModify, $array);
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
		
		$view = new Piwik_View($this->dataTableTemplate);
		$this->id = $this->getUniqIdTable();
		$view->id = $this->id;
		$view->method = $this->method;
		$view->graphType = $this->graphType;

		$this->parametersToModify['action'] = $this->currentControllerAction;
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($this->parametersToModify);
		$view->jsInvocationTag = $this->getFlashInvocationCode($url);
//		print($url);exit;
		$view->urlData = $url;
		
		$view->formId = "formEmbed".$this->id;
		$view->codeEmbed = $this->codeEmbed;
		
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->showFooter = $this->showFooter;
		$this->view = $view;
	}
	
	//TODO change $use_swfobject = true
	public function getFlashInvocationCode(
			$url = 'libs/open-flash-chart/data-files/nodata.txt',
			$use_swfobject = true  )
	{ 
		$width = $this->width; 
		$height = $this->height; 

		$libPathInPiwik = 'libs/open-flash-chart/';
		$currentPath = Piwik_Url::getCurrentUrlWithoutFileName();
		$pathToLibraryOpenChart = $currentPath . $libPathInPiwik;
		
		$url = $currentPath . $url;
	    // escape the & and stuff:
	    $url = urlencode($url);
		
		$obj_id = $this->id . "Chart";
	    $div_name = $this->id . "FlashContent";
	    	   
	    $return = ''; 
	    if( $use_swfobject )
	    {
	    	// Using library for auto-enabling Flash object on IE, disabled-Javascript proof
		    $return .=  '
				<div id="'. $div_name .'"></div>
				<script type="text/javascript">
				var so = new SWFObject("'.$pathToLibraryOpenChart.'open-flash-chart.swf", "'.$obj_id.'_swf", "'. $width . '", "' . $height . '", "9", "#FFFFFF");
				so.addVariable("data", "'. $url . '");
				so.addParam("allowScriptAccess", "sameDomain");
				so.write("'. $div_name .'");
				</script>
				<noscript>
				';
		}
		$urlGraph = $pathToLibraryOpenChart."open-flash-chart.swf?data=" . $url;
		
		$this->codeEmbed .= "<div><object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0' width='" . $width . "' height='" . $height . "' id='". $obj_id ."' >".
							"<param name='movie' value='".$urlGraph."' />".
							"<param name='allowScriptAccess' value='sameDomain' /> ".
							"<embed src='$urlGraph' allowScriptAccess='sameDomain' quality='high' bgcolor='#FFFFFF' width='". $width ."' height='". $height ."' name='open-flash-chart' type='application/x-shockwave-flash' id='". $obj_id ."' />".
							"</object></div>";
		$return .= $this->codeEmbed;
		
		if ( $use_swfobject ) {
			$return .= '</noscript>';
		}
		
		return $return;
	}
}

/**
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Graph_ChartEvolution extends Piwik_ViewDataTable_Graph
{
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartEvolution';
		$this->width='100%';
		$this->height=150;
		// used for the CSS class to apply to the DIV containing the graph
		$this->graphType = 'evolution';		
	}
	
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod );
		
		$this->parametersToModify['date'] = 'last30';
		$this->doNotShowFooter();
	}
}
/**
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Graph_ChartPie extends Piwik_ViewDataTable_Graph
{
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartPie';
	}
}

/**
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Graph_ChartVerticalBar extends Piwik_ViewDataTable_Graph
{
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartVerticalBar';
	}
}
