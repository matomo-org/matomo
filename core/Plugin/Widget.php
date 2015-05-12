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
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\WidgetsList;
use Exception;

/**
 * Base class of all plugin widget providers. Plugins that define their own widgets can extend this class to easily
 * add new widgets or to remove widgets defined by other plugins.
 *
 * For an example, see the {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets.php} plugin.
 */
class Widget
{
    protected $category = '';
    protected $module = '';
    protected $action = '';
    protected $parameters = array();
    protected $name   = '';
    protected $order  = 99;

    /**
     * @ignore
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @ignore
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @ignore
     */
    public function getModule()
    {
        if (empty($this->module)) {
            $parts = $this->getClassNameParts();

            $this->module = $parts[2];
        }

        return $this->module;
    }

    /**
     * @ignore
     */
    public function getAction()
    {
        if (empty($this->action)) {
            $parts = $this->getClassNameParts();

            if (count($parts) >= 4) {
                $this->action = lcfirst(end($parts));
            }
        }

        return $this->action;
    }

    /**
     * Set the module of the widget
     * @param string $module
     * @ignore
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Set the action of the widget
     * @param string $action
     * @ignore
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set the parameters of the widget
     * @param array $parameters
     * @ignore
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Set the order of the widget
     * @param int $order
     * @ignore
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Here you can optionally define URL parameters that will be used when this widget is requested.
     * @return array  Eg ('urlparam' => 'urlvalue').
     * @api
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the name of the widget
     * @return string
     * @ignore
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the widget
     * @param string $name
     * @ignore
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * This method renders the widget. It's on you how to generate the content of the widget.
     * As long as you return a string everything is fine. You can use for instance a "Piwik\View" to render a
     * twig template. In such a case don't forget to create a twig template (eg. myViewTemplate.twig) in the
     * "templates" directory of your plugin.
     *
     * @return string
     * @api
     */
    public function render()
    {
        return '';
    }

    /**
     * Returns the order of the report
     * @return int
     * @ignore
     */
    public function getOrder()
    {
        return $this->order;
    }

    private function getClassNameParts()
    {
        $classname = get_class($this);
        return explode('\\', $classname);
    }

    /**
     * @return \Piwik\Plugin\Widget[]
     * @ignore
     */
    public static function getAllWidgets()
    {
        $widgetClasses = PluginManager::getInstance()->findMultipleComponents('Widgets', 'Piwik\\Plugin\\Widget');

        $widgets = array();
        foreach ($widgetClasses as $widgetClass) {
            $widgets[] = StaticContainer::get($widgetClass);
        }

        return $widgets;
    }

    /**
     * @ignore
     * @return Widgets|null
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
            $widget = StaticContainer::get($widgetClass);
            if ($widget->getAction() == $action) {
                return $widget;
            }
        }
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
     * Defines whether a widget is enabled or not. For instance some widgets might not be available to every user or
     * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
     * return `true` or `false`. If your report is only available to users having super user access you can do the
     * following: `return Piwik::hasUserSuperUserAccess();`
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * This method checks whether the widget is available, see {@isEnabled()}. If not, it triggers an exception
     * containing a message that will be displayed to the user. You can overwrite this message in case you want to
     * customize the error message. Eg.
     * ```
    if (!$this->isEnabled()) {
    throw new Exception('Setting XYZ is not enabled or the user has not enough permission');
    }
     * ```
     * @throws \Exception
     * @api
     */
    public function checkIsEnabled()
    {
        if (!$this->isEnabled()) {
            throw new Exception(Piwik::translate('General_ExceptionWidgetNotEnabled'));
        }
    }


}
