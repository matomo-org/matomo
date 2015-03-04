<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

use DI\FactoryInterface;
use Exception;
use Piwik\Plugin\Controller;
use Piwik\Plugin\Report;
use Piwik\Plugin\Widgets;
use Piwik\Session;

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

    public function __construct(FactoryInterface $abstractFactory)
    {
        $this->abstractFactory = $abstractFactory;
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
        // Controller action
        $controllerClass = $this->getControllerClass($module);
        if (class_exists($controllerClass)) {
            $controller = $this->createPluginController($controllerClass, $action);
            if ($controller) {
                return $controller;
            }
        }

        // Widget action
        $widget = Widgets::factory($module, $action);
        if ($widget) {
            return $this->createWidgetController($module, $action, $parameters);
        }

        // Report action
        $report = Report::factory($module, $action);
        if ($report) {
            return $this->createReportController($module, $action, $parameters);
        }

        // Report menu action
        if ($this->isReportMenuAction($action)) {
            $controller = $this->createReportMenuController($module, $action, $parameters);
            if ($controller) {
                return $controller;
            }
        }

        throw new Exception(sprintf("Action '%s' not found in the module '%s'", $action, $module));
    }

    private function getControllerClass($module)
    {
        return "Piwik\\Plugins\\$module\\Controller";
    }

    private function createPluginController($controllerClass, $action)
    {
        /** @var $controller Controller */
        $controller = $this->abstractFactory->make($controllerClass);

        $action = $action ?: $controller->getDefaultAction();

        if (!is_callable(array($controller, $action))) {
            return null;
        }

        return array($controller, $action);
    }

    private function createWidgetController($module, $action, array &$parameters)
    {
        $parameters['widgetModule'] = $module;
        $parameters['widgetMethod'] = $action;

        return array($this->abstractFactory->make('Piwik\Plugins\CoreHome\Controller'), 'renderWidget');
    }

    private function createReportController($module, $action, array &$parameters)
    {
        $parameters['reportModule'] = $module;
        $parameters['reportAction'] = $action;

        return array($this->abstractFactory->make('Piwik\Plugins\CoreHome\Controller'), 'renderReportWidget');
    }

    private function createReportMenuController($module, $action, array &$parameters)
    {
        $action = lcfirst(substr($action, 4)); // menuGetPageUrls => getPageUrls
        $report = Report::factory($module, $action);

        if ($report) {
            $parameters['reportModule'] = $module;
            $parameters['reportAction'] = $action;

            return array($this->abstractFactory->make('Piwik\Plugins\CoreHome\Controller'), 'renderReportMenu');
        }

        return null;
    }

    private function isReportMenuAction($action)
    {
        $startsWithMenu = (Report::PREFIX_ACTION_IN_MENU === substr($action, 0, strlen(Report::PREFIX_ACTION_IN_MENU)));

        return !empty($action) && $startsWithMenu;
    }
}
