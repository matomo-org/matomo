<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    public function index()
    {
        $view = new Piwik_View('@Widgetize/index');
        $view->availableWidgets = Piwik_Common::json_encode(Piwik_GetWidgetsList());
        $this->setGeneralVariablesView($view);
        echo $view->render();
    }

    public function testJsInclude1()
    {
        $view = new Piwik_View('@Widgetize/testJsInclude1');
        $view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
        $view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=API&actionToWidgetize=index&method=ExamplePlugin.getGoldenRatio&format=original';
        echo $view->render();
    }

    public function testJsInclude2()
    {
        $view = new Piwik_View('@Widgetize/testJsInclude2');
        $view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
        $view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&idSite=1&period=day&date=yesterday&viewDataTable=cloud&show_footer=0';
        $view->url3 = '?module=Widgetize&action=js&moduleToWidgetize=Referers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&viewDataTable=table&show_footer=0';
        echo $view->render();
    }

    public function iframe()
    {
        Piwik_API_Request::reloadAuthUsingTokenAuth();
        $this->init();
        $controllerName = Piwik_Common::getRequestVar('moduleToWidgetize');
        $actionName = Piwik_Common::getRequestVar('actionToWidgetize');
        $parameters = array($fetch = true);
        $outputDataTable = Piwik_FrontController::getInstance()->fetchDispatch($controllerName, $actionName, $parameters);
        if ($controllerName == 'Dashboard' && $actionName == 'index') {
            $view = new Piwik_View('@Widgetize/iframe_empty');
        } else {
            $view = new Piwik_View('@Widgetize/iframe');
        }
        $this->setGeneralVariablesView($view);
        $view->setXFrameOptions('allow');
        $view->content = $outputDataTable;
        echo $view->render();
    }
}
