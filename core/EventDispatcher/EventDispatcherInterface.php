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
interface EventDispatcherInterface
{
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
    public function postEvent($eventName, $params, $pending = false, array $eventSubscribers = array());

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
    public function addObserver($eventName, $callback);

    /**
     * Set the object that provides the event subscribers.
     *
     * @param SubscriberProviderInterface $subscriberProvider
     */
    public function setSubscriberProvider(SubscriberProviderInterface $subscriberProvider);

    /**
     * Removes all observers registered for an event. Only used for testing.
     *
     * @param string $eventName
     */
    public function clearObservers($eventName);

    /**
     * Removes all registered observers. Only used for testing.
     */
    public function clearAllObservers();

    /**
     * Re-posts all pending events to the given event subscriber.
     *
     * @param SubscriberInterface $eventSubscriber
     */
    public function postPendingEventsTo(SubscriberInterface $eventSubscriber);
}
