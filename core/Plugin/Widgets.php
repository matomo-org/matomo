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
 * add new widgets, to remove or to rename existing items.
 *
 * For an example, see the {@link https://github.com/piwik/piwik/blob/master/plugins/ExampleRssWidget/Widget.php} plugin.
 *
 * @api
 */
class Widgets
{
    protected $category = '';
    protected $widgets  = array();

    private $module = '';

    public function __construct()
    {
        $this->module = $this->getModule();
    }

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
     * @api
     */
    protected function addWidget($name, $method, $parameters = array())
    {
        // to be developer friendly we could check whether such a method exists (in controller or widget) and if
        // not throw an exception so the developer does not have to handle with typos etc. I do not want to do this
        // right now because of performance but if we add a development setting in config we could do such check
        $this->addWidgetWithCustomCategory($this->category, $name, $method, $parameters);
    }

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
     * @api
     */
    protected function init()
    {
    }

    public function getWidgets()
    {
        $this->widgets = array();

        $this->init();

        return $this->widgets;
    }

    /**
     * Configures the widgets. Here you can for instance remove widgets.
     */
    public function configureWidgetsList(WidgetsList $widgetsList)
    {

    }

    /**
     * @return \Piwik\Plugin\Widgets[]
     */
    public static function getAllWidgets()
    {
        return PluginManager::getInstance()->findComponents('Widgets', 'Piwik\\Plugin\\Widgets');
    }

    public static function factory($module, $action)
    {
        if (empty($module) || empty($action)) {
            return;
        }

        try {
            $plugin = PluginManager::getInstance()->getLoadedPlugin($module);
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
