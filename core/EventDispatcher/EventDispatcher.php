<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\EventDispatcher;

/**
 * {@inheritdoc}
 */
class EventDispatcher implements EventDispatcherInterface
{
    // implementation details for postEvent
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
     * {@inheritdoc}
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
                list($function, $callbackGroup) = $this->getCallbackFunctionAndGroupNumber($hooks[$eventName]);

                $callbacks[$callbackGroup][] = is_string($function) ? array($eventSubscriber, $function) : $function;
            }
        }

        // collect callbacks from event observers
        if (isset($this->observers[$eventName])) {
            foreach ($this->observers[$eventName] as $callbackInfo) {
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
     * {@inheritdoc}
     */
    public function addObserver($eventName, $callback)
    {
        $this->observers[$eventName][] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubscriberProvider(SubscriberProviderInterface $subscriberProvider)
    {
        $this->subscriberProvider = $subscriberProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function clearObservers($eventName)
    {
        $this->observers[$eventName] = array();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function postPendingEventsTo(SubscriberInterface $eventSubscriber)
    {
        foreach ($this->pendingEvents as $eventInfo) {
            list($eventName, $eventParams) = $eventInfo;
            $this->postEvent($eventName, $eventParams, $pending = false, array($eventSubscriber));
        }
    }

    private function getCallbackFunctionAndGroupNumber($hookInfo)
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
}