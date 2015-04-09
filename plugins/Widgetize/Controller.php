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
        $view->availableWidgets = json_encode(WidgetsList::get());
        $this->setGeneralVariablesView($view);
        return $view->render();
    }

    public function iframe()
    {
        Request::reloadAuthUsingTokenAuth();
        $this->init();

        $controllerName = Common::getRequestVar('moduleToWidgetize');
        $actionName     = Common::getRequestVar('actionToWidgetize');

        if($controllerName == 'API') {
            throw new \Exception("Widgetizing API requests is not supported for security reasons. Please change query parameter 'moduleToWidgetize'.");
        }

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
