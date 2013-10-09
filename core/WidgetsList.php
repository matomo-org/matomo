<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package PluginsFunctions
 */
namespace Piwik;

/**
 * @package PluginsFunctions
 */
class WidgetsList
{
    /**
     * List of widgets
     *
     * @var array
     */
    static protected $widgets = null;

    /**
     * Indicates whether the hook was posted or not
     *
     * @var bool
     */
    static protected $hookCalled = false;

    /**
     * Returns all available widgets
     * The event WidgetsList.addWidgets is used to create the list
     *
     * @return array
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
             * This event is triggered to collect all available widgets. Subscribe to this event if you want to create
             * one or more custom widgets. Just define the name of your widgets as well as a controller and an action
             * that should be executed once your widget is requested.
             *
             * Example:
             * ```
             * public function addWidgets()
             * {
             *     WidgetsList::add('General_Actions', 'General_Pages', 'Actions', 'getPageUrls');
             * }
             * ```
             */
            Piwik::postEvent('WidgetsList.addWidgets');
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
     * Adds an widget to the list
     *
     * @param string $widgetCategory
     * @param string $widgetName
     * @param string $controllerName
     * @param string $controllerAction
     * @param array $customParameters
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
        self::$widgets[$widgetCategory][] = array(
            'name'       => $widgetName,
            'uniqueId'   => $widgetUniqueId,
            'parameters' => array('module' => $controllerName,
                                  'action' => $controllerAction
                ) + $customParameters
        );
    }


    static public function remove($widgetCategory, $widgetName = false)
    {
        if (empty($widgetName)) {
            unset(self::$widgets[$widgetCategory]);
            return;
        }
        foreach (self::$widgets[$widgetCategory] as $id => $widget) {
            if ($widget['name'] == $widgetName) {
                unset(self::$widgets[$widgetCategory][$id]);
                return;
            }
        }
    }

    /**
     * Checks if the widget with the given parameters exists in der widget list
     *
     * @param string $controllerName
     * @param string $controllerAction
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
     */
    public static function _reset()
    {
        self::$widgets = null;
        self::$hookCalled = false;
    }
}



