<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Plugin\Manager as PluginManager;

/**
 * Manages the global list of reports that can be displayed as dashboard widgets.
 * 
 * Reports are added as dashboard widgets through the {@hook WidgetsList.addWidgets}
 * event. Observers for this event should call the {@link add()} method to add reports.
 * 
 * @api
 * @method static \Piwik\WidgetsList getInstance()
 */
class WidgetsList extends Singleton
{
    /**
     * List of widgets
     *
     * @var array
     */
    static protected $widgets = array();

    /**
     * Indicates whether the hook was posted or not
     *
     * @var bool
     */
    static protected $hookCalled = false;

    /**
     * Returns all available widgets.
     *
     * @return array Array Mapping widget categories with an array of widget information, eg,
     *               ```
     *               array(
     *                   'Visitors' => array(
     *                       array(...), // info about first widget in this category
     *                       array(...) // info about second widget in this category, etc.
     *                   ),
     *                   'Visits' => array(
     *                       array(...),
     *                       array(...)
     *                   ),
     *               )
     *               ```
     */
    static public function get()
    {
        self::addWidgets();

        uksort(self::$widgets, array('Piwik\WidgetsList', '_sortWidgetCategories'));

        $widgets = array();
        foreach (self::$widgets as $key => $v) {
            if (isset($widgets[Piwik::translate($key)])) {
                $v = array_merge($widgets[Piwik::translate($key)], $v);
            }
            $widgets[Piwik::translate($key)] = $v;
        }
        return $widgets;
    }

    private static function addWidgets()
    {
        if (!self::$hookCalled) {
            self::$hookCalled = true;

            /**
             * @ignore
             * @deprecated
             */
            Piwik::postEvent('WidgetsList.addWidgets');

            /** @var \Piwik\Plugin\Widgets[] $widgets */
            $widgets     = PluginManager::getInstance()->findComponents('Widgets', 'Piwik\\Plugin\\Widgets');
            $widgetsList = self::getInstance();

            foreach ($widgets as $widget) {
                $widget->configure($widgetsList);
            }
        }
    }

    /**
     * Sorting method for widget categories
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    protected static function _sortWidgetCategories($a, $b)
    {
        $order = array(
            'VisitsSummary_VisitsSummary',
            'Live!',
            'General_Visitors',
            'UserSettings_VisitorSettings',
            'DevicesDetection_DevicesDetection',
            'General_Actions',
            'Events_Events',
            'Actions_SubmenuSitesearch',
            'Referrers_Referrers',
            'Goals_Goals',
            'Goals_Ecommerce',
            '_others_',
            'Example Widgets',
            'ExamplePlugin_exampleWidgets',
        );

        if (($oa = array_search($a, $order)) === false) {
            $oa = array_search('_others_', $order);
        }
        if (($ob = array_search($b, $order)) === false) {
            $ob = array_search('_others_', $order);
        }
        return $oa > $ob;
    }

    /**
     * Adds a report to the list of dashboard widgets.
     *
     * @param string $widgetCategory The widget category. This can be a translation token.
     * @param string $widgetName The name of the widget. This can be a translation token.
     * @param string $controllerName The report's controller name (same as the plugin name).
     * @param string $controllerAction The report's controller action method name.
     * @param array $customParameters Extra query parameters that should be sent while getting
     *                                this report.
     */
    static public function add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
    {
        $widgetName = Piwik::translate($widgetName);
        $widgetUniqueId = 'widget' . $controllerName . $controllerAction;
        foreach ($customParameters as $name => $value) {
            if (is_array($value)) {
                // use 'Array' for backward compatibility;
                // could we switch to using $value[0]?
                $value = 'Array';
            }
            $widgetUniqueId .= $name . $value;
        }

        if (!array_key_exists($widgetCategory, self::$widgets)) {
            self::$widgets[$widgetCategory] = array();
        }

        self::$widgets[$widgetCategory][] = array(
            'name'       => $widgetName,
            'uniqueId'   => $widgetUniqueId,
            'parameters' => array('module' => $controllerName,
                                  'action' => $controllerAction
                ) + $customParameters
        );
    }

    /**
     * Removes one or more widgets from the widget list.
     * 
     * @param string $widgetCategory The widget category. Can be a translation token.
     * @param string|false $widgetName The name of the widget to remove. Cannot be a 
     *                                 translation token. If not supplied, the entire category
     *                                 will be removed.
     */
    static public function remove($widgetCategory, $widgetName = false)
    {
        if (!isset(self::$widgets[$widgetCategory])) {
            return;
        }

        if (empty($widgetName)) {
            unset(self::$widgets[$widgetCategory]);
            return;
        }
        foreach (self::$widgets[$widgetCategory] as $id => $widget) {
            if ($widget['name'] == $widgetName || $widget['name'] == Piwik::translate($widgetName)) {
                unset(self::$widgets[$widgetCategory][$id]);
                return;
            }
        }
    }

    /**
     * Returns `true` if a report exists in the widget list, `false` if otherwise.
     *
     * @param string $controllerName The controller name of the report.
     * @param string $controllerAction The controller action of the report.
     * @return bool
     */
    static public function isDefined($controllerName, $controllerAction)
    {
        $widgetsList = self::get();
        foreach ($widgetsList as $widgetCategory => $widgets) {
            foreach ($widgets as $widget) {
                if ($widget['parameters']['module'] == $controllerName
                    && $widget['parameters']['action'] == $controllerAction
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Method to reset the widget list
     * For testing only
     * @ignore
     */
    public static function _reset()
    {
        self::$widgets    = array();
        self::$hookCalled = false;
    }
}
