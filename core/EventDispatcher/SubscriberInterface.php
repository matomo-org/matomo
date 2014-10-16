<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\EventDispatcher;

/**
 * An Subscriber knows himself what events he is interested in.
 *
 * If a subscriber is added to an event dispatcher, the dispatcher invokes
 * {@link getListHooksRegistered} and registers it as a listener for all
 * returned events.
 */
interface SubscriberInterface
{
    /**
     * Returns a list of events this subscriber wants to listen to.
     *
     * The array keys are event names.
     *
     * @return array eg,
     *
     *                   array(
     *                       'API.getReportMetadata' => 'myPluginFunction',
     *                       'Another.event'         => array(
     *                                                      'function' => 'myOtherPluginFunction',
     *                                                      'after'    => true // execute after callbacks w/o ordering
     *                                                  )
     *                       'Yet.Another.event'     => array(
     *                                                      'function' => 'myOtherPluginFunction',
     *                                                      'before'   => true // execute before callbacks w/o ordering
     *                                                  )
     *                   )
     */
    public function getListHooksRegistered();
}
