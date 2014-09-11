<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Development;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\WidgetsList;

/**
 * Base class of all plugin widget providers. Plugins that define their own widgets can extend this class to easily
 * add new widgets or to remove widgets defined by other plugins.
 *
 * For an example, see the {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets.php} plugin.
 *
 * @api
 */
class Widgets
{
    protected $category = '';
    protected $widgets  = array();

    private $module = '';

    /**
     * @ignore
     */
    public function __construct()
    {
        $this->module = $this->getModule();
    }

    /**
     * @ignore
     */
    public function getCategory()
    {
        return $this->category;
    }

    private function getModule()
    {
        $className = get_class($this);
        $className = explode('\\', $className);

        return $className[2];
    }

    /**
     * Adds a widget. You can add a widget by calling this method and passing the name of the widget as well as a method
     * name that will be executed to render the widget. The method can be defined either directly here in this widget
     * class or in the controller in case you want to reuse the same action for instance in the menu etc.
     * @api
     */
    protected function addWidget($name, $method, $parameters = array())
    {
        $this->addWidgetWithCustomCategory($this->category, $name, $method, $parameters);
    }

    /**
     * Adds a widget with a custom category. By default all widgets that you define in your class will be added under
     * the same category which is defined in the {@link $category} property. Sometimes you may have a widget that
     * belongs to a different category where this method comes handy. It does the same as {@link addWidget()} but
     * allows you to define the category name as well.
     * @api
     */
    protected function addWidgetWithCustomCategory($category, $name, $method, $parameters = array())
    {
        $this->checkIsValidWidget($name, $method);

        $this->widgets[] = array('category' => $category,
                                 'name'     => $name,
                                 'params'   => $parameters,
                                 'method'   => $method,
                                 'module'   => $this->module);
    }

    /**
     * Here you can add one or multiple widgets. To do so call the method {@link addWidget()} or
     * {@link addWidgetWithCustomCategory()}.
     * @api
     */
    protected function init()
    {
    }

    /**
     * @ignore
     */
    public function getWidgets()
    {
        $this->widgets = array();

        $this->init();

        return $this->widgets;
    }

    /**
     * Allows you to configure previously added widgets.
     * For instance you can remove any widgets defined by any plugin by calling the
     * {@link \Piwik\WidgetsList::remove()} method.
     *
     * @param WidgetsList $widgetsList
     * @api
     */
    public function configureWidgetsList(WidgetsList $widgetsList)
    {

    }

    /**
     * @return \Piwik\Plugin\Widgets[]
     * @ignore
     */
    public static function getAllWidgets()
    {
        return PluginManager::getInstance()->findComponents('Widgets', 'Piwik\\Plugin\\Widgets');
    }

    /**
     * @ignore
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

        /** @var Widgets $widgetContainer */
        $widgetContainer = $plugin->findComponent('Widgets', 'Piwik\\Plugin\\Widgets');

        if (empty($widgetContainer)) {
            // plugin does not define any widgets, we cannot do anything
            return;
        }

        if (!is_callable(array($widgetContainer, $action))) {
            // widget does not implement such a method, we cannot do anything
            return;
        }

        // the widget class implements such an action, but we have to check whether it is actually exposed and whether
        // it was maybe disabled by another plugin, this is only possible by checking the widgetslist, unfortunately
        if (!WidgetsList::isDefined($module, $action)) {
            return;
        }

        return $widgetContainer;
    }

    private function checkIsValidWidget($name, $method)
    {
        if (!Development::isEnabled()) {
            return;
        }

        if (empty($name)) {
            Development::error('No name is defined for added widget having method "' . $method . '" in ' . get_class($this));
        }

        if (Development::isCallableMethod($this, $method)) {
            return;
        }

        $controllerClass = '\\Piwik\\Plugins\\' . $this->module . '\\Controller';

        if (!Development::methodExists($this, $method) &&
            !Development::methodExists($controllerClass, $method)) {
            Development::error('The added method "' . $method . '" neither exists in "' . get_class($this) . '" nor "' . $controllerClass . '". Make sure to define such a method.');
        }

        $definedInClass = get_class($this);

        if (Development::methodExists($controllerClass, $method)) {
            if (Development::isCallableMethod($controllerClass, $method)) {
                return;
            }

            $definedInClass = $controllerClass;
        }

        Development::error('The method "' . $method . '" is not callable on "' . $definedInClass . '". Make sure the method is public.');
    }
}
