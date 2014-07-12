<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Widgetize;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\FrontController;
use Piwik\View;
use Piwik\WidgetsList;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@Widgetize/index');
        $view->availableWidgets = Common::json_encode(WidgetsList::get());
        $this->setGeneralVariablesView($view);
        return $view->render();
    }

    public function testJsInclude1()
    {
        $view = new View('@Widgetize/testJsInclude1');
        $view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
        $view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=API&actionToWidgetize=index&method=ExamplePlugin.getGoldenRatio&format=original';
        return $view->render();
    }

    public function testJsInclude2()
    {
        $view = new View('@Widgetize/testJsInclude2');
        $view->url1 = '?module=Widgetize&action=js&moduleToWidgetize=UserSettings&actionToWidgetize=getBrowser&idSite=1&period=day&date=yesterday';
        $view->url2 = '?module=Widgetize&action=js&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&idSite=1&period=day&date=yesterday&viewDataTable=cloud&show_footer=0';
        $view->url3 = '?module=Widgetize&action=js&moduleToWidgetize=Referrers&actionToWidgetize=getKeywords&idSite=1&period=day&date=yesterday&viewDataTable=table&show_footer=0';
        return $view->render();
    }

    public function iframe()
    {
        Request::reloadAuthUsingTokenAuth();
        $this->init();

        $controllerName = Common::getRequestVar('moduleToWidgetize');
        $actionName     = Common::getRequestVar('actionToWidgetize');

        if ($controllerName == 'Dashboard' && $actionName == 'index') {
            $view = new View('@Widgetize/iframe_empty');
        } else {
            $view = new View('@Widgetize/iframe');
        }

        $this->setGeneralVariablesView($view);
        $view->setXFrameOptions('allow');
        $view->content = FrontController::getInstance()->fetchDispatch($controllerName, $actionName);

        return $view->render();
    }
}
