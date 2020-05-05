<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Widget;

use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Piwik;
use Piwik\Report\ReportWidgetFactory;

/**
 * Manages the global list of reports that can be displayed as dashboard widgets.
 *
 * Widgets are added through the {@hook WidgetsList.addWidgets} and filtered through the {@hook Widgets.filterWidgets}
 * event. Observers for this event should call the {@link addWidget()} method to add widgets or use any of the other
 * methods to remove widgets.
 *
 * @api since Piwik 3.0.0
 */
class WidgetsList
{
    /**
     * List of widgets
     *
     * @var WidgetConfig[]
     */
    private $widgets = array();

    /**
     * @var WidgetContainerConfig[]
     */
    private $container;

    /**
     * @var array
     */
    private $containerWidgets;

    /**
     * Adds a new widget to the widget config. Please make sure the widget is enabled before adding a widget as
     * no such checks will be performed.
     *
     * @param WidgetConfig $widget
     */
    public function addWidgetConfig(WidgetConfig $widget)
    {
        if ($widget instanceof WidgetContainerConfig) {
            $this->addContainer($widget);
        } elseif (Development::isEnabled()) {
            $this->checkIsValidWidget($widget);
        }

        $this->widgets[] = $widget;
    }

    /**
     * Add multiple widget configs at once. See {@link addWidgetConfig()}.
     *
     * @param WidgetConfig[] $widgets
     */
    public function addWidgetConfigs($widgets)
    {
        foreach ($widgets as $widget) {
            $this->addWidgetConfig($widget);
        }
    }

    private function addContainer(WidgetContainerConfig $containerWidget)
    {
        $widgetId = $containerWidget->getId();

        $this->container[$widgetId] = $containerWidget;

        // widgets were added to this container, but the container did not exist yet.
        if (isset($this->containerWidgets[$widgetId])) {
            foreach ($this->containerWidgets[$widgetId] as $widget) {
                $containerWidget->addWidgetConfig($widget);
            }
            unset($this->containerWidgets[$widgetId]);
        }
    }

    /**
     * Get all added widget configs.
     *
     * @return WidgetConfig[]
     */
    public function getWidgetConfigs()
    {
        return $this->widgets;
    }

    private function checkIsValidWidget(WidgetConfig $widget)
    {
        if (!$widget->getModule()) {
            Development::error('No module is defined for added widget having name "' . $widget->getName());
        }

        if (!$widget->getAction()) {
            Development::error('No action is defined for added widget having name "' . $widget->getName());
        }
    }

    /**
     * Add a widget to a widget container. It doesn't matter whether the container was added to this list already
     * or whether the container is added later. As long as a container having the same containerId is added at
     * some point the widget will be added to that container. If no container having this id is added the widget
     * will not be recognized.
     *
     * @param string $containerId  eg 'Products' or 'Contents'. See {@link WidgetContainerConfig::setId}
     * @param WidgetConfig $widget
     */
    public function addToContainerWidget($containerId, WidgetConfig $widget)
    {
        if (isset($this->container[$containerId])) {
            $this->container[$containerId]->addWidgetConfig($widget);
        } else {
            if (!isset($this->containerWidgets[$containerId])) {
                $this->containerWidgets[$containerId] = array();
            }

            $this->containerWidgets[$containerId][] = $widget;
        }
    }

    /**
     * Removes one or more widgets from the widget list.
     *
     * @param string $widgetCategoryId The widget category id. Can be a translation token eg 'General_Visits'
     *                                 see {@link WidgetConfig::setCategoryId()}.
     * @param string|false $widgetName The name of the widget to remove eg 'VisitTime_ByServerTimeWidgetName'.
     *                                 If not supplied, all widgets within that category will be removed.
     */
    public function remove($widgetCategoryId, $widgetName = false)
    {
        foreach ($this->widgets as $index => $widget) {
            if ($widget->getCategoryId() === $widgetCategoryId) {
                if (!$widgetName || $widget->getName() === $widgetName) {
                    unset($this->widgets[$index]);
                }
            }
        }
    }

    /**
     * Returns `true` if a widget exists in the widget list, `false` if otherwise.
     *
     * @param string $module The controller name of the widget.
     * @param string $action The controller action of the widget.
     * @return bool
     */
    public function isDefined($module, $action)
    {
        foreach ($this->widgets as $widget) {
            if ($widget->getModule() === $module && $widget->getAction() === $action) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all widgets defined in the Piwik platform.
     * @ignore
     * @return static
     */
    public static function get()
    {
        $list = new static;

        $widgets = StaticContainer::get('Piwik\Plugin\WidgetsProvider');

        $widgetContainerConfigs = $widgets->getWidgetContainerConfigs();
        foreach ($widgetContainerConfigs as $config) {
            if ($config->isEnabled()) {
                $list->addWidgetConfig($config);
            }
        }

        $widgetConfigs = $widgets->getWidgetConfigs();
        foreach ($widgetConfigs as $widget) {
            if ($widget->isEnabled()) {
                $list->addWidgetConfig($widget);
            }
        }

        $reports = StaticContainer::get('Piwik\Plugin\ReportsProvider');
        $reports = $reports->getAllReports();
        foreach ($reports as $report) {
            if ($report->isEnabled()) {
                $factory = new ReportWidgetFactory($report);
                $report->configureWidgets($list, $factory);
            }
        }

        /**
         * Triggered to filter widgets.
         *
         * **Example**
         *
         *     public function removeWidgetConfigs(Piwik\Widget\WidgetsList $list)
         *     {
         *         $list->remove($category='General_Visits'); // remove all widgets having this category
         *     }
         *
         * @param WidgetsList $list An instance of the WidgetsList. You can change the list of widgets this way.
         */
        Piwik::postEvent('Widget.filterWidgets', array($list));

        return $list;
    }

    /**
     * CAUTION! If you ever change this method, existing updates will fail as they currently use that method!
     * If you change the output the uniqueId for existing widgets would not be found anymore
     *
     * Returns the unique id of an widget with the given parameters
     *
     * @param $controllerName
     * @param $controllerAction
     * @param array $customParameters
     * @return string
     */
    public static function getWidgetUniqueId($controllerName, $controllerAction, $customParameters = array())
    {
        $widgetUniqueId = 'widget' . $controllerName . $controllerAction;

        foreach ($customParameters as $name => $value) {
            if (is_array($value)) {
                // use 'Array' for backward compatibility;
                // could we switch to using $value[0]?
                $value = 'Array';
            }
            $value = urlencode($value);
            $value = str_replace('%', '', $value);
            $widgetUniqueId .= $name . $value;
        }

        return $widgetUniqueId;
    }

}
