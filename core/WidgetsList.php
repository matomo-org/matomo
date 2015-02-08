<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Cache as PiwikCache;
use Piwik\Plugin\Report;
use Piwik\Plugin\Widgets;

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
    protected static $widgets = array();

    /**
     * Indicates whether the hook was posted or not
     *
     * @var bool
     */
    protected static $hookCalled = false;

    /**
     * In get() we won't use a cached result in case this is true. Instead we will sort the widgets again and cache
     * a new result. To make tests work...
     * @var bool
     */
    private static $listCacheToBeInvalidated = false;

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
    public static function get()
    {
        $cache   = self::getCacheForCompleteList();
        $cacheId = self::getCacheId();

        if (!self::$listCacheToBeInvalidated && $cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        self::addWidgets();

        uksort(self::$widgets, array('Piwik\WidgetsList', '_sortWidgetCategories'));

        $widgets = array();
        foreach (self::$widgets as $key => $v) {
            $category = Piwik::translate($key);

            if (isset($widgets[$category])) {
                $v = array_merge($widgets[$category], $v);
            }

            $widgets[$category] = $v;
        }

        $cache->save($cacheId, $widgets);
        self::$listCacheToBeInvalidated = false;

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

            $widgetsList = self::getInstance();

            foreach (Report::getAllReports() as $report) {
                if ($report->isEnabled()) {
                    $report->configureWidget($widgetsList);
                }
            }

            $widgetContainers = Widgets::getAllWidgets();
            foreach ($widgetContainers as $widgetContainer) {
                $widgets = $widgetContainer->getWidgets();

                foreach ($widgets as $widget) {
                    $widgetsList->add($widget['category'], $widget['name'], $widget['module'], $widget['method'], $widget['params']);
                }
            }

            foreach ($widgetContainers as $widgetContainer) {
                $widgetContainer->configureWidgetsList($widgetsList);
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
            'General_VisitorSettings',
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
            $widgetUniqueId .= $name . $value;
        }

        return $widgetUniqueId;
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
    public static function add($widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
    {
        $widgetName     = Piwik::translate($widgetName);
        $widgetUniqueId = self::getWidgetUniqueId($controllerName, $controllerAction, $customParameters);

        if (!array_key_exists($widgetCategory, self::$widgets)) {
            self::$widgets[$widgetCategory] = array();
        }

        self::$listCacheToBeInvalidated = true;
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
    public static function remove($widgetCategory, $widgetName = false)
    {
        if (!isset(self::$widgets[$widgetCategory])) {
            return;
        }

        if (empty($widgetName)) {
            unset(self::$widgets[$widgetCategory]);
            self::$listCacheToBeInvalidated = true;
            return;
        }
        foreach (self::$widgets[$widgetCategory] as $id => $widget) {
            if ($widget['name'] == $widgetName || $widget['name'] == Piwik::translate($widgetName)) {
                unset(self::$widgets[$widgetCategory][$id]);
                self::$listCacheToBeInvalidated = true;
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
    public static function isDefined($controllerName, $controllerAction)
    {
        $widgetsList = self::get();
        foreach ($widgetsList as $widgets) {
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
        self::getCacheForCompleteList()->delete(self::getCacheId());
    }

    private static function getCacheId()
    {
        return CacheId::pluginAware('WidgetsList');
    }

    private static function getCacheForCompleteList()
    {
        return PiwikCache::getTransientCache();
    }
}
