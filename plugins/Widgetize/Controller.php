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
		$view->url1 = '?module=Widgetize&action=iframe&moduleToWidgetize=Home&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=iframe&moduleToWidgetize=Home&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday&viewDataTable=cloud&showDataTableFooter=0';
		
		echo $view->render();
	}

	
	function testJsInclude1()
	{
		$view = new Piwik_View('Widgetize/templates/test_jsinclude.tpl');
		$view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=Home&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		echo $view->render();
	}
	
	function testJsInclude2()
	{
		$view = new Piwik_View('Widgetize/templates/test_jsinclude2.tpl');
		$view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=Home&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=Home&actionToWidgetize=getCountry&idSite=1&period=day&date=yesterday&viewDataTable=cloud&showDataTableFooter=0';
		echo $view->render();
	}
	
	
	// the code loaded by the script src=
	function js()
	{
		$controllerName = Piwik_Common::getRequestVar('moduleToWidgetize');
		$actionName = Piwik_Common::getRequestVar('actionToWidgetize');
		$parameters = array ( $fetch = true );
		$outputDataTable='';
		
		$outputDataTable = Piwik_FrontController::getInstance()->dispatch( $controllerName, $actionName, $parameters);
		
		$view = new Piwik_View('Widgetize/templates/js.tpl');
		$content = $outputDataTable;
//		$content = str_replace(
//								array( "<script",    "</script",   "'", "\n", "\t"), 
//								array( "<scr'+'ipt", "<\/scr'+'ipt",  "\'", '', ''), 
//								$outputDataTable
//							);
		
//		echo $content;exit;
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