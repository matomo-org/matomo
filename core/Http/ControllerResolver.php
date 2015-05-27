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

        $controller = $this->createReportMenuController($module, $action, $parameters);
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

        if (!is_callable(array($controller, $action))) {
            return null;
        }

        return array($controller, $action);
    }

    private function createWidgetController($module, $action, array &$parameters)
    {
        $widget = Widgets::factory($module, $action);

        if (!$widget) {
            return null;
        }

        $parameters['widget'] = $widget;
        $parameters['method'] = $action;

        return array($this->createCoreHomeController(), 'renderWidget');
    }

    private function createReportController($module, $action, array &$parameters)
    {
        $report = Report::factory($module, $action);

        if (!$report) {
            return null;
        }

        $parameters['report'] = $report;

        return array($this->createCoreHomeController(), 'renderReportWidget');
    }

    private function createReportMenuController($module, $action, array &$parameters)
    {
        if (!$this->isReportMenuAction($action)) {
            return null;
        }

        $action = lcfirst(substr($action, 4)); // menuGetPageUrls => getPageUrls
        $report = Report::factory($module, $action);

        if (!$report) {
            return null;
        }

        $parameters['report'] = $report;

        return array($this->createCoreHomeController(), 'renderReportMenu');
    }

    private function isReportMenuAction($action)
    {
        $startsWithMenu = (Report::PREFIX_ACTION_IN_MENU === substr($action, 0, strlen(Report::PREFIX_ACTION_IN_MENU)));

        return !empty($action) && $startsWithMenu;
    }

    private function createCoreHomeController()
    {
        return $this->abstractFactory->make('Piwik\Plugins\CoreHome\Controller');
    }
}
