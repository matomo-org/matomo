<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Widgetize
 */

/**
 * 
 * @package Piwik_Widgetize
 */
class Piwik_Widgetize_Controller extends Piwik_Controller
{
	function index()
	{
		$view = Piwik_View::factory('index');
		$view->availableWidgets = json_encode(Piwik_GetWidgetsList());
		$this->setGeneralVariablesView($view);
		echo $view->render();
	}

	function testJsInclude1()
	{
		$view = Piwik_View::factory('test_jsinclude');
		$view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=API&actionToWidgetize=index&method=ExamplePlugin.getGoldenRatio&format=original';
		echo $view->render();
	}
	
	function testJsInclude2()
	{
		$view = Piwik_View::factory('test_jsinclude2');
		$view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
		$view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&idSite=1&period=day&date=yesterday&viewDataTable=cloud&show_footer=0';
		$view->url3 = '?module=Widgetize&action=js&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&viewDataTable=table&show_footer=0';
		echo $view->render();
	}
	
	function testClearspring()
	{
		$view = Piwik_View::factory('test_widget');
		$view->url1 = Piwik_Url::getCurrentUrlWithoutQueryString().'?module=Widgetize&action=iframe&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&filter_limit=5&token_auth='.Piwik::getCurrentUserTokenAuth();
		$view->url2 = Piwik_Url::getCurrentUrlWithoutQueryString().'?module=Widgetize&action=iframe&moduleToWidgetize=VisitTime&actionToWidgetize=getVisitInformationPerServerTime&idSite=1&period=day&date=yesterday&viewDataTable=graphVerticalBar&show_footer=0&token_auth='.Piwik::getCurrentUserTokenAuth();
		$view->url3 = Piwik_Url::getCurrentUrlWithoutQueryString().'?module=Widgetize&action=iframe&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&viewDataTable=cloud&show_footer=1&filter_limit=15&show_search=false&token_auth='.Piwik::getCurrentUserTokenAuth();
		echo $view->render();
	}
	
	function js()
	{
		$controllerName = Piwik_Common::getRequestVar('moduleToWidgetize');
		$actionName = Piwik_Common::getRequestVar('actionToWidgetize');
		$parameters = array ( $fetch = true );
		$content = Piwik_FrontController::getInstance()->fetchDispatch( $controllerName, $actionName, $parameters);
		$view = Piwik_View::factory('js');
		$view->piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();
		$content = str_replace(array("\t","\n","\r\n","\r"), "", $content);
		$view->content = $content;
		echo $view->render();
	}

	function iframe()
	{		
		$controllerName = Piwik_Common::getRequestVar('moduleToWidgetize');
		$actionName = Piwik_Common::getRequestVar('actionToWidgetize');
		$parameters = array ( $fetch = true );
		$outputDataTable = Piwik_FrontController::getInstance()->fetchDispatch( $controllerName, $actionName, $parameters);
		$view = Piwik_View::factory('iframe');
		$view->content = $outputDataTable;
		echo $view->render();
	}
}
