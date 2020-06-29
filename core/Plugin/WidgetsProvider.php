<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetContainerConfig;

/**
 * Get widgets that are defined by plugins.
 */
class WidgetsProvider
{
    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Get all existing widget configs.
     *
     * @return WidgetConfig[]
     */
    public function getWidgetConfigs()
    {
        $widgetClasses = $this->getAllWidgetClassNames();

        $configs = array();

        /**
         * Triggered to add custom widget configs. To filder widgets have a look at the {@hook Widget.filterWidgets}
         * event.
         *
         * **Example**
         *
         *     public function addWidgetConfigs(&$configs)
         *     {
         *         $config = new WidgetConfig();
         *         $config->setModule('PluginName');
         *         $config->setAction('renderDashboard');
         *         $config->setCategoryId('Dashboard_Dashboard');
         *         $config->setSubcategoryId('dashboardId');
         *         $configs[] = $config;
         *     }
         *
         * @param array &$configs An array containing a list of widget config entries.
         */
        Piwik::postEvent('Widget.addWidgetConfigs', array(&$configs));

        foreach ($widgetClasses as $widgetClass) {
            $configs[] = $this->getWidgetConfigForClassName($widgetClass);
        }

        return $configs;
    }

    /**
     * Get all existing widget container configs.
     * @return WidgetContainerConfig[]
     */
    public function getWidgetContainerConfigs()
    {
        $configs = array();

        $widgetContainerConfigs = $this->getAllWidgetContainerConfigClassNames();
        foreach ($widgetContainerConfigs as $widgetClass) {
            $configs[] = StaticContainer::get($widgetClass);
        }

        return $configs;
    }

    /**
     * Get the widget defined by the given module and action.
     *
     * @param string $module Aka plugin name, eg 'CoreHome'
     * @param string $action An action eg 'renderMe'
     * @return Widget|null
     * @throws \Exception Throws an exception if the widget is not enabled.
     */
    public function factory($module, $action)
    {
        if (empty($module) || empty($action)) {
            return;
        }

        try {
            if (!$this->pluginManager->isPluginActivated($module)) {
                return;
            }

            $plugin = $this->pluginManager->getLoadedPlugin($module);
        } catch (\Exception $e) {
            // we are not allowed to use possible widgets, plugin is not active
            return;
        }

        /** @var Widget[] $widgetContainer */
        $widgets = $plugin->findMultipleComponents('Widgets', 'Piwik\\Widget\\Widget');

        foreach ($widgets as $widgetClass) {
            $config = $this->getWidgetConfigForClassName($widgetClass);
            if ($config->getAction() === $action) {
                $config->checkIsEnabled();
                return StaticContainer::get($widgetClass);
            }
        }
    }

    private function getWidgetConfigForClassName($widgetClass)
    {
        /** @var string|Widget $widgetClass */
        $config = new WidgetConfig();
        $config->setModule($this->getModuleFromWidgetClassName($widgetClass));
        $config->setAction($this->getActionFromWidgetClassName($widgetClass));
        $widgetClass::configure($config);

        return $config;
    }

    /**
     * @return string[]
     */
    private function getAllWidgetClassNames()
    {
        return $this->pluginManager->findMultipleComponents('Widgets', 'Piwik\\Widget\\Widget');
    }

    private function getModuleFromWidgetClassName($widgetClass)
    {
        $parts = explode('\\', $widgetClass);

        return $parts[2];
    }

    private function getActionFromWidgetClassName($widgetClass)
    {
        $parts = explode('\\', $widgetClass);

        if (count($parts) >= 4) {
            return lcfirst(end($parts));
        }

        return '';
    }

    /**
     * @return string[]
     */
    private function getAllWidgetContainerConfigClassNames()
    {
        return $this->pluginManager->findMultipleComponents('Widgets', 'Piwik\\Widget\\WidgetContainerConfig');
    }
}
