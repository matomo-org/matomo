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

/**
 * @package PluginsFunctions
 */
class Piwik_WidgetsList
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
     * The event WidgetsList.add is used to create the list
     *
     * @return array
     */
    static public function get()
    {
        self::addWidgets();
        Piwik_PostEvent('WidgetsList.get');

        uksort(self::$widgets, array('Piwik_WidgetsList', '_sortWidgetCategories'));

        $widgets = array();
        foreach (self::$widgets as $key => $v) {
            if (isset($widgets[Piwik_Translate($key)])) {
                $v = array_merge($widgets[Piwik_Translate($key)], $v);
            }
            $widgets[Piwik_Translate($key)] = $v;
        }
        return $widgets;
    }

    private static function addWidgets()
    {
        if (!self::$hookCalled) {
            self::$hookCalled = true;
            Piwik_PostEvent('WidgetsList.add');
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
            'Actions_Actions',
            'Actions_SubmenuSitesearch',
            'Referers_Referers',
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
    static public function add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters)
    {
        $widgetName = Piwik_Translate($widgetName);
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

/**
 * Returns all available widgets
 *
 * @see Piwik_WidgetsList::get
 *
 * @return array
 */
function Piwik_GetWidgetsList()
{
    return Piwik_WidgetsList::get();
}

/**
 * Adds an widget to the list
 *
 * @see Piwik_WidgetsList::add
 *
 * @param string $widgetCategory
 * @param string $widgetName
 * @param string $controllerName
 * @param string $controllerAction
 * @param array $customParameters
 */
function Piwik_AddWidget($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
{
    Piwik_WidgetsList::add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters);
}

/**
 * Checks if the widget with the given parameters exists in der widget list
 *
 * @see Piwik_WidgetsList::isDefined
 *
 * @param string $controllerName
 * @param string $controllerAction
 * @return bool
 */
function Piwik_IsWidgetDefined($controllerName, $controllerAction)
{
    return Piwik_WidgetsList::isDefined($controllerName, $controllerAction);
}
