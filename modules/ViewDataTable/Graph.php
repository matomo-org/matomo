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
	protected $width = 400; 
	protected $height = 250; 
	
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod );
		$this->dataTableTemplate = 'Home/templates/graph.tpl';
		
		$this->disableOffsetInformation();
		$this->disableExcludeLowPopulation();
		$this->disableSearchBox();
		$this->parametersToModify = array( 'viewDataTable' => $this->valueParameterViewDataTable);
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
			$use_swfobject = false  )
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
	    // I think we may use swfobject for all browsers, not JUST for IE...
	    //
	    //$ie = strstr(getenv('HTTP_USER_AGENT'), 'MSIE');
	    
	   
	    $return = ''; 
	    if( $use_swfobject )
	    {
	    	// Using library for auto-enabling Flash object on IE, disabled-Javascript proof
		    $return .=  '
				<div id="'. $div_name .'"></div>
				<script type="text/javascript">
				var so = new SWFObject("'.$pathToLibraryOpenChart.'open-flash-chart.swf", "chart", "'. $width . '", "' . $height . '", "9", "#FFFFFF");
				so.addVariable("data", "'. $url . '");
				so.addParam("allowScriptAccess", "sameDomain");
				so.write("'. $div_name .'");
				</script>
				<noscript>
				';
		}
		$this->codeEmbed = "<embed src='".$pathToLibraryOpenChart."open-flash-chart.swf?data=" . $url ."' quality='high' bgcolor='#FFFFFF' width='". $width ."' height='". $height ."' name='open-flash-chart' align='middle' allowScriptAccess='sameDomain' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' id='". $obj_id ."'/>";
		
		$return .= '
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" 
			width="' . $width . '" height="' . $height . '" id="'. $obj_id .'" align="middle">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="movie" value="'.$pathToLibraryOpenChart.'open-flash-chart.swf?width='. $width .'&height='. $height . '&data='. $url .'" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#FFFFFF" />
			'.$this->codeEmbed.'
			</object>';
	
		if ( $use_swfobject ) {
			$return .= '</noscript>';
		}
		
		// doesn't work embed because needs to be in BODY
//		$return = '<script type="text/javascript" src="libs/swfobject/swfobject.js"></script> ' . $return;
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
		$this->width=700;
		$this->height=150;
		
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
