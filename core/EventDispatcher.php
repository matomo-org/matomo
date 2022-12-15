<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Container\StaticContainer;

/**
 * This class allows code to post events from anywhere in Piwik and for
 * plugins to associate callbacks to be executed when events are posted.
 */
class EventDispatcher
{
    /**
     * @return EventDispatcher
     */
    public static function getInstance()
    {
        return StaticContainer::get('Piwik\EventDispatcher');
    }

    // implementation details for postEvent
    const EVENT_CALLBACK_GROUP_FIRST = 0;
    const EVENT_CALLBACK_GROUP_SECOND = 1;
    const EVENT_CALLBACK_GROUP_THIRD = 2;

    /**
     * Array of observers (callbacks attached to events) that are not methods
     * of plugin classes.
     *
     * @var array
     */
    private $extraObservers = array();

    /**
     * Array storing information for all pending events. Each item in the array
     * will be an array w/ two elements:
     *
     * array(
     *     'Event.Name',                  // the event name
     *     array('event', 'parameters')   // the parameters to pass to event observers
     * )
     *
     * @var array
     */
    private $pendingEvents = array();

    /**
     * Plugin\Manager instance used to get list of loaded plugins.
     *
     * @var \Piwik\Plugin\Manager
     */
    private $pluginManager;

    private $pluginHooks = array();

    public static $_SKIP_EVENTS_IN_TESTS = false;

    /**
     * Constructor.
     */
    public function __construct(Plugin\Manager $pluginManager, array $observers = array())
    {
        $this->pluginManager = $pluginManager;

        foreach ($observers as $observerInfo) {
            list($eventName, $callback) = $observerInfo;
            $this->extraObservers[$eventName][] = $callback;
        }
    }

    /**
     * Triggers an event, executing all callbacks associated with it.
     *
     * @param string $eventName The name of the event, ie, API.getReportMetadata.
     * @param array $params The parameters to pass to each callback when executing.
     * @param bool $pending Whether this event should be posted again for plugins
     *                      loaded after the event is fired.
     * @param array|null $plugins The plugins to post events to. If null, the event
     *                            is posted to all plugins. The elements of this array
     *                            can be either the Plugin objects themselves
     *                            or their string names.
     */
    public function postEvent($eventName, $params, $pending = false, $plugins = null)
    {
        if (self::$_SKIP_EVENTS_IN_TESTS) {
            return;
        }

        if ($pending) {
            $this->pendingEvents[] = array($eventName, $params);
        }

        $manager = $this->pluginManager;

        if (empty($plugins)) {
            $plugins = $manager->getPluginsLoadedAndActivated();
        }

        $callbacks = array();

        // collect all callbacks to execute
        foreach ($plugins as $pluginName) {
            if (!is_string($pluginName)) {
                $pluginName = $pluginName->getPluginName();
            }

            if (!isset($this->pluginHooks[$pluginName])) {
                $plugin = $manager->getLoadedPlugin($pluginName);
                $this->pluginHooks[$pluginName] = $plugin->registerEvents();
            }

            $hooks = $this->pluginHooks[$pluginName];

            if (isset($hooks[$eventName])) {
                list($pluginFunction, $callbackGroup) = $this->getCallbackFunctionAndGroupNumber($hooks[$eventName]);

                if (is_string($pluginFunction)) {
                    $plugin = $manager->getLoadedPlugin($pluginName);
                    $callbacks[$callbackGroup][] = array($plugin, $pluginFunction) ;
                } else {
                    $callbacks[$callbackGroup][] = $pluginFunction;
                }
            }
        }

        if (isset($this->extraObservers[$eventName])) {
            foreach ($this->extraObservers[$eventName] as $callbackInfo) {
                list($callback, $callbackGroup) = $this->getCallbackFunctionAndGroupNumber($callbackInfo);

                $callbacks[$callbackGroup][] = $callback;
            }
        }

        // sort callbacks by their importance
        ksort($callbacks);

        // execute callbacks in order
        foreach ($callbacks as $callbackGroup) {
            foreach ($callbackGroup as $callback) {
                call_user_func_array($callback, $params);
            }
        }
    }

    /**
     * Associates a callback that is not a plugin class method with an event
     * name.
     *
     * @param string $eventName
     * @param array|callable $callback This can be a normal PHP callback or an array
     *                        that looks like this:
     *                        array(
     *                            'function' => $callback,
     *                            'before' => true
     *                        )
     *                        or this:
     *                        array(
     *                            'function' => $callback,
     *                            'after' => true
     *                        )
     *                        If 'before' is set, the callback will be executed
     *                        before normal & 'after' ones. If 'after' then it
     *                        will be executed after normal ones.
     */
    public function addObserver($eventName, $callback)
    {
        $this->extraObservers[$eventName][] = $callback;
    }

    /**
     * Re-posts all pending events to the given plugin.
     *
     * @param Plugin $plugin
     */
    public function postPendingEventsTo($plugin)
    {
        foreach ($this->pendingEvents as $eventInfo) {
            [$eventName, $eventParams] = $eventInfo;
            $this->postEvent($eventName, $eventParams, $pending = false, array($plugin));
        }
    }

    /**
     * @internal  For testing purpose only
     */
    public function clearCache()
    {
        $this->pluginHooks = [];
    }

    private function getCallbackFunctionAndGroupNumber($hookInfo)
    {
        if (is_array($hookInfo)
            && !empty($hookInfo['function'])
        ) {
            $pluginFunction = $hookInfo['function'];
            if (!empty($hookInfo['before'])) {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_FIRST;
            } elseif (!empty($hookInfo['after'])) {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_THIRD;
            } else {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_SECOND;
            }
        } else {
            $pluginFunction = $hookInfo;
            $callbackGroup = self::EVENT_CALLBACK_GROUP_SECOND;
        }

        return array($pluginFunction, $callbackGroup);
    }
}
