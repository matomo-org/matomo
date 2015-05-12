<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\WidgetsList;
use Exception;

/**
 * Defines a new widget. You can create a new widget using the console command `./console generate:widget`.
 * The generated widget will guide you through the creation of a widget.
 *
 * For an example, see {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets/MyExampleWidget.php}
 *
 * @api since Piwik 2.15
 */
class Widget
{
    /**
     * @param WidgetConfig $config
     * @api
     */
    public static function configure(WidgetConfig $config)
    {
    }

    /**
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * Allows you to configure previously added widgets.
     * For instance you can remove any widgets defined by any plugin by calling the
     * {@link \Piwik\WidgetsList::remove()} method.
     *
     * @param WidgetsList $widgetsList
     * @api
     */
    public static function configureWidgetsList(WidgetsList $widgetsList)
    {
    }

    /**
     * @return \Piwik\Plugin\WidgetConfig[]
     */
    public static function getAllWidgetConfigurations()
    {
        $widgetClasses = self::getAllWidgetClassNames();

        $configs = array();
        foreach ($widgetClasses as $widgetClass) {
            $configs[] = self::getWidgetConfigForClassName($widgetClass);
        }

        return $configs;
    }

    private static function getWidgetConfigForClassName($widgetClass)
    {
        /** @var string|Widget $widgetClass */
        $config = new WidgetConfig();
        $config->setModule(self::getModuleFromWidgetClassName($widgetClass));
        $config->setAction(self::getActionFromWidgetClassName($widgetClass));
        $widgetClass::configure($config);

        return $config;
    }

    /**
     * @return string[]
     */
    public static function getAllWidgetClassNames()
    {
        return PluginManager::getInstance()->findMultipleComponents('Widgets', 'Piwik\\Plugin\\Widget');
    }

    private static function getModuleFromWidgetClassName($widgetClass)
    {
        $parts = explode('\\', $widgetClass);

        return $parts[2];
    }

    private static function getActionFromWidgetClassName($widgetClass)
    {
        $parts = explode('\\', $widgetClass);

        if (count($parts) >= 4) {
            return lcfirst(end($parts));
        }

        return '';
    }

    /**
     * @return Widgets|null
     * @throws \Exception
     */
    public static function factory($module, $action)
    {
        if (empty($module) || empty($action)) {
            return;
        }

        $pluginManager = PluginManager::getInstance();

        try {
            if (!$pluginManager->isPluginActivated($module)) {
                return;
            }

            $plugin = $pluginManager->getLoadedPlugin($module);
        } catch (\Exception $e) {
            // we are not allowed to use possible widgets, plugin is not active
            return;
        }

        /** @var Widget[] $widgetContainer */
        $widgets = $plugin->findMultipleComponents('Widgets', 'Piwik\\Plugin\\Widget');

        foreach ($widgets as $widgetClass) {
            $config = self::getWidgetConfigForClassName($widgetClass);
            if ($config->getAction() === $action) {
                $config->checkIsEnabled(); // todo how can we handle this better?!? only isEnabled? or setEnabled on widget?
                return StaticContainer::get($widgetClass);
            }
        }
    }

}
