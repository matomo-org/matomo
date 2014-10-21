<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\EventDispatcher;

/**
 * This class allows to dispatch events to observers and subscribers.
 *
 * Listeners are callbacks registered manually for a specific event.
 * Subscribers are classes that declare which events they want to register to.
 */
class EventDispatcher
{
    // priorities
    const EVENT_CALLBACK_GROUP_FIRST = 0;
    const EVENT_CALLBACK_GROUP_SECOND = 1;
    const EVENT_CALLBACK_GROUP_THIRD = 2;

    /**
     * @var SubscriberProviderInterface
     */
    private $subscriberProvider;

    /**
     * Array of observers (callbacks attached to events).
     *
     * @var array
     */
    private $observers = array();

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

    public function __construct(SubscriberProviderInterface $subscriberProvider)
    {
        $this->subscriberProvider = $subscriberProvider;
    }

    /**
     * Triggers an event, executing all callbacks associated with it.
     *
     * @param string $eventName The name of the event, ie, API.getReportMetadata.
     * @param array $params The parameters to pass to each callback when executing.
     * @param bool $pending Whether this event should be posted again for subscribers
     *                      loaded after the event is fired.
     * @param array $eventSubscribers The event subscribers to post events to. If empty, the event
     *                                is posted to all subscribers. The elements of this array
     *                                can be either Subscriber objects or their string names.
     */
    public function postEvent($eventName, $params, $pending = false, array $eventSubscribers = array())
    {
        if ($pending) {
            $this->pendingEvents[] = array($eventName, $params);
        }

        $eventSubscribers = $this->subscriberProvider->getEventSubscribers($eventSubscribers);

        $callbacks = array();

        // collect callbacks from event subscribers
        foreach ($eventSubscribers as $eventSubscriber) {
            $hooks = $eventSubscriber->getListHooksRegistered();

            if (isset($hooks[$eventName])) {
                list($function, $callbackGroup) = $this->getCallbackAndGroup($hooks[$eventName]);

                $callbacks[$callbackGroup][] = is_string($function) ? array($eventSubscriber, $function) : $function;
            }
        }

        // collect callbacks from event observers
        if (isset($this->observers[$eventName])) {
            foreach ($this->observers[$eventName] as $callbackInfo) {
                list($callback, $callbackGroup) = $this->getCallbackAndGroup($callbackInfo);

                $callbacks[$callbackGroup][] = $callback;
            }
        }

        $this->dispatchToListeners($callbacks, $params);
    }

    /**
     * Associates a callback to an event name.
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
        $this->observers[$eventName][] = $callback;
    }

    /**
     * Set the object that provides the event subscribers.
     *
     * @param SubscriberProviderInterface $subscriberProvider
     */
    public function setSubscriberProvider(SubscriberProviderInterface $subscriberProvider)
    {
        $this->subscriberProvider = $subscriberProvider;
    }

    /**
     * Removes all observers registered for an event. Only used for testing.
     *
     * @param string $eventName
     */
    public function clearObservers($eventName)
    {
        $this->observers[$eventName] = array();
    }

    /**
     * Removes all registered observers. Only used for testing.
     */
    public function clearAllObservers()
    {
        foreach ($this->observers as $eventName => $eventObservers) {
            if (strpos($eventName, 'Log.format') === 0) {
                continue;
            }

            $this->observers[$eventName] = array();
        }
    }

    /**
     * Re-posts all pending events to the given event subscriber.
     *
     * @param SubscriberInterface $eventSubscriber
     */
    public function postPendingEventsTo(SubscriberInterface $eventSubscriber)
    {
        foreach ($this->pendingEvents as $eventInfo) {
            list($eventName, $eventParams) = $eventInfo;
            $this->postEvent($eventName, $eventParams, $pending = false, array($eventSubscriber));
        }
    }

    private function getCallbackAndGroup($hookInfo)
    {
        if (is_array($hookInfo)
            && !empty($hookInfo['function'])
        ) {
            $function = $hookInfo['function'];
            if (!empty($hookInfo['before'])) {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_FIRST;
            } elseif (!empty($hookInfo['after'])) {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_THIRD;
            } else {
                $callbackGroup = self::EVENT_CALLBACK_GROUP_SECOND;            }
        } else {
            $function = $hookInfo;
            $callbackGroup = self::EVENT_CALLBACK_GROUP_SECOND;
        }

        return array($function, $callbackGroup);
    }

    /**
     * @param array $listeners
     * @param array $params
     */
    private function dispatchToListeners(array $listeners, array $params)
    {
        // sort listeners by their group/priority
        ksort($listeners);

        // invoke listeners in order
        foreach ($listeners as $group => $listenerGroup) {
            foreach ($listenerGroup as $listener) {
                call_user_func_array($listener, $params);
            }
        }
    }
}
