<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Widgetize;

use Piwik\API\Request;
use Piwik\Request\AuthenticationToken;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\API\WidgetMetadata;
use Piwik\Url;
use Piwik\View;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetsList;

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
        // also called by FrontController, we call it explicitly as a safety measure in case something changes in the future
        if (StaticContainer::get(AuthenticationToken::class)->getAuthToken()) {
            Request::checkTokenAuthIsNotLimited('Widgetize', 'iframe');
        }

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
                'See ' . Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/faq_193/') . ' for more info.';
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

        $clientWidget = $this->findClientWidgetMetadata($controllerName, $actionName);
        if (!empty($clientWidget)) {
            $widgetView = new View('@Widgetize/clientWidget');
            $widgetView->widget = $clientWidget;
            $view->content = $widgetView->render();
        } else {
            $view->content = FrontController::getInstance()->fetchDispatch($controllerName, $actionName);

            // Only actions that return HTML may be embedded into the widgetized iframe document.
            $this->assertDispatchedContentIsEmbeddable($controllerName, $actionName);
        }

        return $view->render();
    }

    private function assertDispatchedContentIsEmbeddable(string $module, string $action): void
    {
        $contentType = $this->getDispatchedContentType();

        // An empty content type means the action did not set one explicitly and the (HTML) default applies.
        // Only actions returning HTML (or no explicit content type) can be embedded into the iframe document.
        if (
            $contentType !== ''
            && stripos($contentType, 'text/html') === false
            && stripos($contentType, 'application/xhtml') === false
        ) {
            throw new \Exception(sprintf(
                "%s.%s cannot be widgetized: only actions returning HTML can be embedded.",
                $module,
                $action
            ));
        }
    }

    private function getDispatchedContentType(): string
    {
        // In CLI / test mode no real headers are emitted, but Common::sendHeader() records them instead.
        if (Common::isPhpCliMode()) {
            return (string) (Common::$headersSentInTests['Content-Type'] ?? '');
        }

        foreach (headers_list() as $header) {
            if (stripos($header, 'content-type:') === 0) {
                return trim(substr($header, strlen('content-type:')));
            }
        }

        return '';
    }

    private function findClientWidgetMetadata(string $module, string $action): ?array
    {
        $widgetsList = WidgetsList::get();

        foreach ($widgetsList->getWidgetConfigs() as $config) {
            if ($config->getModule() !== $module || $config->getAction() !== $action) {
                continue;
            }

            if (empty($config->getClientSideComponent())) {
                return null;
            }

            return $this->buildClientWidgetMetadata($config);
        }

        return null;
    }

    private function buildClientWidgetMetadata(WidgetConfig $config): ?array
    {
        if (!$config->isWidgetizeable()) {
            return null;
        }

        $config->checkIsEnabled();

        $metadata = new WidgetMetadata();
        return $metadata->buildWidgetMetadata($config);
    }
}
