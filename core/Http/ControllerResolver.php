<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

use DI\FactoryInterface;
use Exception;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugin\WidgetsProvider;

/**
 * Resolves the controller that will handle the request.
 *
 * A controller is a PHP callable.
 */
class ControllerResolver
{
    /**
     * @var FactoryInterface
     */
    private $abstractFactory;

    /**
     * @var WidgetsProvider
     */
    private $widgets;

    public function __construct(FactoryInterface $abstractFactory, WidgetsProvider $widgets)
    {
        $this->abstractFactory = $abstractFactory;
        $this->widgets = $widgets;
    }

    /**
     * @param string $module
     * @param string|null $action
     * @param array $parameters
     * @throws Exception Controller not found.
     * @return callable The controller is a PHP callable.
     */
    public function getController($module, $action, array &$parameters)
    {
        $controller = $this->createPluginController($module, $action);
        if ($controller) {
            return $controller;
        }

        $controller = $this->createWidgetController($module, $action, $parameters);
        if ($controller) {
            return $controller;
        }

        $controller = $this->createReportController($module, $action, $parameters);
        if ($controller) {
            return $controller;
        }

        throw new Exception(sprintf("Action '%s' not found in the module '%s'", $action, $module));
    }

    private function createPluginController($module, $action)
    {
        $controllerClass = "Piwik\\Plugins\\$module\\Controller";
        if (!class_exists($controllerClass)) {
            return null;
        }

        /** @var $controller Controller */
        $controller = $this->abstractFactory->make($controllerClass);

        $action = $action ?: $controller->getDefaultAction();

        if (!is_callable(array($controller, $action)) || !in_array($action, get_class_methods($controller))) {
            return null;
        }

        return array($controller, $action);
    }

    private function createWidgetController($module, $action, array &$parameters)
    {
        $widget = $this->widgets->factory($module, $action);

        if (!$widget) {
            return;
        }

        $parameters['widget'] = $widget;

        return array($this->createCoreHomeController(), 'renderWidget');
    }

    private function createReportController($module, $action, array &$parameters)
    {
        $report = ReportsProvider::factory($module, $action);

        if (!$report) {
            return null;
        }

        $parameters['report'] = $report;

        return array($this->createCoreHomeController(), 'renderReportWidget');
    }

    private function createCoreHomeController()
    {
        return $this->abstractFactory->make('Piwik\Plugins\CoreHome\Controller');
    }
}
