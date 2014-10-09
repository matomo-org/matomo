<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Plugin;

/**
 * This class allows code to post events from anywhere in Piwik and for
 * plugins to associate callbacks to be executed when events are posted.
 *
 * @method static \Piwik\EventDispatcher getInstance()
 */
class EventDispatcher extends Singleton
{
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

    /**
     * Constructor.
     */
    public function __construct($pluginManager = null)
    {
        $this->pluginManager = $pluginManager;
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
        if ($pending) {
            $this->pendingEvents[] = array($eventName, $params);
        }

        if (empty($plugins)) {
            $plugins = $this->getPluginManager()->getPluginsLoadedAndActivated();
        }

        $callbacks = array();

        // collect all callbacks to execute
        foreach ($plugins as $plugin) {
            if (is_string($plugin)) {
                $plugin = $this->getPluginManager()->getLoadedPlugin($plugin);
            }

            if (empty($plugin)) {
                return; // may happen in unit tests
            }


            $hooks = $plugin->getListHooksRegistered();

            if (isset($hooks[$eventName])) {
                list($pluginFunction, $callbackGroup) = $this->getCallbackFunctionAndGroupNumber($hooks[$eventName]);

                $callbacks[$callbackGroup][] = is_string($pluginFunction) ? array($plugin, $pluginFunction) : $pluginFunction;
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
     * Removes all registered extra observers for an event name. Only used for testing.
     *
     * @param string $eventName
     */
    public function clearObservers($eventName)
    {
        $this->extraObservers[$eventName] = array();
    }

    /**
     * Removes all registered extra observers. Only used for testing.
     */
    public function clearAllObservers()
    {
        foreach ($this->extraObservers as $eventName => $eventObservers) {
            if (strpos($eventName, 'Log.format') === 0) {
                continue;
            }

            $this->extraObservers[$eventName] = array();
        }
    }

    /**
     * Re-posts all pending events to the given plugin.
     *
     * @param Plugin $plugin
     */
    public function postPendingEventsTo($plugin)
    {
        foreach ($this->pendingEvents as $eventInfo) {
            list($eventName, $eventParams) = $eventInfo;
            $this->postEvent($eventName, $eventParams, $pending = false, array($plugin));
        }
    }

    private function getCallbackFunctionAndGroupNumber($hookInfo)
    {
        if (is_array($hookInfo)
            && !empty($hookInfo['function'])
        ) {
            $pluginFunction = $hookInfo['function'];
            if (!empty($hookInfo['before'])) {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_FIRST;
            } else if (!empty($hookInfo['after'])) {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_THIRD;
            } else {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_SECOND;            }
        } else {
            $pluginFunction = $hookInfo;
            $callbackGroup = self::EVENT_CALLBACK_GROUP_SECOND;
        }

        return array($pluginFunction, $callbackGroup);
    }

    /**
     * Returns the Plugin\Manager instance used by the event dispatcher.
     *
     * @return Plugin\Manager
     */
    private function getPluginManager()
    {
        if ($this->pluginManager === null) {
            $this->pluginManager = Plugin\Manager::getInstance();
        }
        return $this->pluginManager;
    }
}