<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Widgetize;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Session\SessionInitializer;
use Piwik\Url;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@Widgetize/index');
        $this->setGeneralVariablesView($view);
        return $view->render();
    }

    public function iframe()
    {
        $this->initWidgetAuth();
        $this->init();

        $controllerName = Common::getRequestVar('moduleToWidgetize');
        $actionName     = Common::getRequestVar('actionToWidgetize');

        if ($controllerName == 'API') {
            throw new \Exception("Widgetizing API requests is not supported for security reasons. Please change query parameter 'moduleToWidgetize'.");
        }

        if ($controllerName == 'Widgetize') {
            throw new \Exception("Please set 'moduleToWidgetize' to a valid value.");
        }

        if ($controllerName == 'CoreHome' && $actionName == 'index') {
            $message = 'CoreHome cannot be widgetized. '  . 
                'You can enable it to be embedded directly into an iframe (passing module=CoreHome instead of module=Widgetize) ' .
                'instead by enabling the \'enable_framed_pages\' setting in your config. ' .
                'See https://matomo.org/faq/how-to/faq_193/ for more info.';
            throw new \Exception($message);
        }

        $shouldEmbedEmpty = false;

        /**
         * Triggered to detect whether a widgetized report should be wrapped in the widgetized HTML or whether only
         * the rendered output of the controller/action should be printed. Set `$shouldEmbedEmpty` to `true` if
         * your widget renders the full HTML itself.
         *
         * **Example**
         *
         *     public function embedIframeEmpty(&$shouldEmbedEmpty, $controllerName, $actionName)
         *     {
         *         if ($controllerName == 'Dashboard' && $actionName == 'index') {
         *             $shouldEmbedEmpty = true;
         *         }
         *     }
         *
         * @param string &$shouldEmbedEmpty Defines whether the iframe should be embedded empty or wrapped within the widgetized html.
         * @param string $controllerName    The name of the controller that will be executed.
         * @param string $actionName        The name of the action within the controller that will be executed.
         */
        Piwik::postEvent('Widgetize.shouldEmbedIframeEmpty', array(&$shouldEmbedEmpty, $controllerName, $actionName));

        if ($shouldEmbedEmpty) {
            $view = new View('@Widgetize/iframe_empty');
        } else {
            $view = new View('@Widgetize/iframe');
        }

        $this->setGeneralVariablesView($view);
        $view->setXFrameOptions('allow');
        $view->content = FrontController::getInstance()->fetchDispatch($controllerName, $actionName);

        return $view->render();
    }

    private function initWidgetAuth()
    {
        $token_auth = Common::getRequestVar('token_auth', '', 'string');

        if (!empty($token_auth)) {
            $auth = StaticContainer::get('Piwik\Auth');
            $auth->setTokenAuth($token_auth);
            $auth->setPassword(null);
            $auth->setPasswordHash(null);
            $auth->setLogin(null);

            $sessionInitializer = new SessionInitializer();
            $sessionInitializer->initSession($auth);

            $url = preg_replace('/&token_auth=[^&]{20,38}|$/i', '', Url::getCurrentUrl());
            if ($url) {
                Url::redirectToUrl($url);
                return;
            }
        }
    }
}
