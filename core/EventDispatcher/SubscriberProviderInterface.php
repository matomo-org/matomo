<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\EventDispatcher;

/**
 * Provides a list of event subscribers.
 */
interface SubscriberProviderInterface
{
    /**
     * Returns a list of event subscribers.
     *
     * @param SubscriberInterface[] $eventSubscribers Optional array to filter the results to return.
     * @return SubscriberInterface[]
     */
    public function getEventSubscribers(array $eventSubscribers = array());
}
