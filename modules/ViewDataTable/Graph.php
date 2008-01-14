<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

abstract class Piwik_ViewDataTable_Graph extends Piwik_ViewDataTable
{	
	function init($currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerAction, 
						$moduleNameAndMethod );
		$this->dataTableTemplate = 'Home/templates/graph.tpl';
		
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
		
		$view = new Piwik_View($this->dataTableTemplate);
		$this->id = $this->getUniqIdTable();
		$view->id = $this->id;
		$view->method = $this->method;

		$parametersToModify = array( 'viewDataTable' => $this->valueParameterViewDataTable);
		
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($parametersToModify);
		$view->jsInvocationTag = $this->getFlashInvocationCode($url);
//		print($url);exit;
		$view->urlData = $url;
		
		$view->formId = "formEmbed".$this->id;
		$view->codeEmbed = $this->codeEmbed;
		
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$this->view = $view;
	}
	
	protected function getCodeEmbed( $url )
	{
	}
	
	protected function getFlashInvocationCode(
			$url = 'libs/open-flash-chart/data-files/nodata.txt', 
			$width = 500, 
			$height = 250, 
			$use_swfobject = true  )
	{
		$libPathInPiwik = 'libs/open-flash-chart/';
		
		$currentPath = Piwik_Url::getCurrentUrlWithoutFileName();
		
		$pathToLibraryOpenChart = $currentPath . $libPathInPiwik;
		
		$url = $currentPath . $url;
		
		$div_name = $this->id;
		
	    $obj_id = $this->id . "_chart";
	    $div_name = $this->id . "_flashContent";
	    // I think we may use swfobject for all browsers, not JUST for IE...
	    //
	    //$ie = strstr(getenv('HTTP_USER_AGENT'), 'MSIE');
	    
	    //
	    // escape the & and stuff:
	    //
	    $url = urlencode($url);
	   
	    $return = ''; 
	    if( $use_swfobject )
	    {
	    	// Using library for auto-enabling Flash object on IE, disabled-Javascript proof
		    $return .=  '
				<div id="'. $div_name .'"></div>
				<script type="text/javascript">
				var so = new SWFObject("'.$pathToLibraryOpenChart.'open-flash-chart.swf", "ofc", "'. $width . '", "' . $height . '", "9", "#FFFFFF");
				so.addVariable("width", "' . $width . '");
				so.addVariable("height", "' . $height . '");
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
			width="' . $width . '" height="' . $height . '" id="ie_'. $obj_id .'" align="middle">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="movie" value="'.$pathToLibraryOpenChart.'open-flash-chart.swf?width='. $width .'&height='. $height . '&data='. $url .'" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#FFFFFF" />
			'.$this->codeEmbed.'
			</object>';
	
		if ( $use_swfobject ) {
			$return .= '</noscript>';
		}
		
		return $return;
	}
}

class Piwik_ViewDataTable_Graph_ChartPie extends Piwik_ViewDataTable_Graph
{
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartPie';
	}
}

class Piwik_ViewDataTable_Graph_ChartVerticalBar extends Piwik_ViewDataTable_Graph
{
	function __construct()
	{
		$this->valueParameterViewDataTable = 'generateDataChartVerticalBar';
	}
}
