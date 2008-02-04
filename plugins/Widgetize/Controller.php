<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 169 2008-01-14 05:41:15Z matt $
 * 
 * @package Piwik_SitesManager
 */


/**
 * 
 * @package Piwik_Widgetize
 */
class Piwik_Widgetize_Controller extends Piwik_Controller
{
	function index()
	{
		$view = new Piwik_View('Widgetize/templates/index.tpl');
		echo $view->render();
	}

	// display code calling the IFRAME
	function testIframe()
	{
		$view = new Piwik_View('Widgetize/templates/test_iframe.tpl');
		$view->url1 = '?module=Widgetize&action=iframe&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=iframe&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday&viewDataTable=cloud&showDataTableFooter=0';
		
		echo $view->render();
	}

	
	function testJsInclude1()
	{
		$view = new Piwik_View('Widgetize/templates/test_jsinclude.tpl');
		$view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=API&actionToWidgetize=index&method=ExamplePlugin.getGoldenRatio&format=original';
		echo $view->render();
	}
	
	function testJsInclude2()
	{
		$view = new Piwik_View('Widgetize/templates/test_jsinclude2.tpl');
		$view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&idSite=1&period=day&date=yesterday&viewDataTable=cloud&showDataTableFooter=0';
		$view->url3 = '?module=Widgetize&action=js&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&viewDataTable=table&showDataTableFooter=0';
		echo $view->render();
	}
	
	
	// display code calling the IFRAME
	function testClearspring()
	{
		$view = new Piwik_View('Widgetize/templates/test_widget.tpl');
		$view->url1 = Piwik_Url::getCurrentUrlWithoutQueryString().'?module=Widgetize&action=iframe&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&filter_limit=5';
		$view->url2 = Piwik_Url::getCurrentUrlWithoutQueryString().'?module=Widgetize&action=iframe&moduleToWidgetize=VisitTime&actionToWidgetize=getVisitInformationPerServerTime&idSite=1&period=day&date=yesterday&viewDataTable=graphVerticalBar&showDataTableFooter=0';
		$view->url3 = Piwik_Url::getCurrentUrlWithoutQueryString().'?module=Widgetize&action=iframe&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&viewDataTable=cloud&showDataTableFooter=1&filter_limit=15&show_search=false';
		
		echo $view->render();
	}
	
	// the code loaded by the script src=
	function js()
	{
		$controllerName = Piwik_Common::getRequestVar('moduleToWidgetize');
		$actionName = Piwik_Common::getRequestVar('actionToWidgetize');
		$parameters = array ( $fetch = true );
		$outputDataTable='';
		
		$content = Piwik_FrontController::getInstance()->fetchDispatch( $controllerName, $actionName, $parameters);
				
	    $view = new Piwik_View('Widgetize/templates/js.tpl');
		$view->piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();
		$view->content = $content;
		echo $view->render();
	}

	// the code loaded by the frame src=
	function iframe()
	{		
		$controllerName = Piwik_Common::getRequestVar('moduleToWidgetize');
		$actionName = Piwik_Common::getRequestVar('actionToWidgetize');
		$parameters = array ( $fetch = true );
		$outputDataTable='';
		
		$outputDataTable = Piwik_FrontController::getInstance()->dispatch( $controllerName, $actionName, $parameters);
		
		$view = new Piwik_View('Widgetize/templates/iframe.tpl');
		$view->content = $outputDataTable;
		echo $view->render();
	}
}