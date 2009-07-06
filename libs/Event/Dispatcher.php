<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2005, Bertrand Mansion                                  |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Bertrand Mansion <bmansion@mamasam.com>                       |
// |         Stephan Schmidt <schst@php.net>                               |
// +-----------------------------------------------------------------------+
//
// $Id$

require_once 'Event/Notification.php';

/**
 * Pseudo 'static property' for Notification object
 * @global array $GLOBALS["_Event_Dispatcher"]
 */
$GLOBALS['_Event_Dispatcher'] = array(
                                  'NotificationClass' => 'Event_Notification'
                                     );

/**
 * Registers a global observer
 */
define('EVENT_DISPATCHER_GLOBAL', '');

/**
 * Dispatch notifications using PHP callbacks
 *
 * The Event_Dispatcher acts acts as a notification dispatch table.
 * It is used to notify other objects of interesting things, if
 * they meet certain criteria. This information is encapsulated 
 * in {@link Event_Notification} objects. Client objects register 
 * themselves with the Event_Dispatcher as observers of specific
 * notifications posted by other objects. When an event occurs,
 * an object posts an appropriate notification to the Event_Dispatcher.
 * The Event_Dispatcher dispatches a message to each
 * registered observer, passing the notification as the sole argument.
 *
 * The Event_Dispatcher is actually a combination of three design
 * patterns: the Singleton, {@link http://c2.com/cgi/wiki?MediatorPattern Mediator},
 * and Observer patterns. The idea behind Event_Dispatcher is borrowed from 
 * {@link http://developer.apple.com/documentation/Cocoa/Conceptual/Notifications/index.html Apple's Cocoa framework}.
 *
 * @category   Event
 * @package    Event_Dispatcher
 * @author     Bertrand Mansion <bmansion@mamasam.com>
 * @author     Stephan Schmidt <schst@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Event_Dispatcher
 */
class Event_Dispatcher
{
    /**
     * Registered observer callbacks
     * @var array
     * @access private
     */
    var $_ro = array();
    
    /**
     * Pending notifications
     * @var array
     * @access private
     */
    var $_pending = array();

    /**
     * Nested observers
     * @var array
     * @access private
     */
    var $_nestedDispatchers = array();

    /**
     * Name of the dispatcher
     * @var string
     * @access private
     */
    var $_name = null;

    /**
     * Class used for notifications
     * @var string
     * @access private
     */
    var $_notificationClass = null;

    /**
     * PHP4 constructor
     *
     * Please use {@link getInstance()} instead.
     *
     * @access  private
     * @param   string      Name of the notification dispatcher.
     */
//    function Event_Dispatcher($name)
//    {
//        Event_Dispatcher::__construct($name);
//    }

    /**
     * PHP5 constructor
     *
     * Please use {@link getInstance()} instead.
     *
     * @access  private
     * @param   string      Name of the notification dispatcher.
     */
    function __construct($name)
    {
        $this->_name = $name;
        $this->_notificationClass = $GLOBALS['_Event_Dispatcher']['NotificationClass'];
    }

    /**
     * Returns a notification dispatcher singleton
     *
     * There is usually no need to have more than one notification
     * center for an application so this is the recommended way
     * to get a Event_Dispatcher object.
     *
     * @param string    Name of the notification dispatcher.
     *                  The default notification dispatcher is named __default.
     * 
     * @return object Event_Dispatcher
     */
    static function getInstance($name = '__default')
    {
        static $dispatchers = array();

        if (!isset($dispatchers[$name])) {
            $dispatchers[$name] = new Event_Dispatcher($name);
        }

        return $dispatchers[$name];
    }

    /**
     * Registers an observer callback
     *
     * This method registers a {@link http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback callback}
     * which is called when the notification corresponding to the
     * criteria given at registration time is posted.
     * The criteria are the notification name and eventually the 
     * class of the object posted with the notification.
     *
     * If there are any pending notifications corresponding to the criteria
     * given here, the callback will be called straight away.
     *
     * If the notification name is empty, the observer will receive all the
     * posted notifications. Same goes for the class name.
     *
     * @access  public
     * @param   mixed       A PHP callback
     * @param   string      Expected notification name, serves as a filter
     * @param   string      Expected contained object class, serves as a filter
     * @return void
     */
    function addObserver($callback, $nName = EVENT_DISPATCHER_GLOBAL, $class = null)
    {
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                // Note : PHP4 does not allow correct object comparison so
                // only the class name is used for registration checks.
                $reg = get_class($callback[0]).'::'.$callback[1];
            } else {
                $reg = $callback[0].'::'.$callback[1];
            }
        } else {
            $reg = $callback;
        }

        $this->_ro[$nName][$reg] = array(
                                    'callback' => $callback,
                                    'class'    => $class
                                    );

        // Post eventual pending notifications for this observer
        if (isset($this->_pending[$nName])) {
            foreach (array_keys($this->_pending[$nName]) as $k) {
                $notification =& $this->_pending[$nName][$k];
                if (!$notification->isNotificationCancelled()) {
                    $object = $notification->getNotificationObject();
                    $objClass = is_object($object) ? get_class($object) : null;
                    if (empty($class) || strcasecmp($class, $objClass) == 0) {
                        call_user_func_array($callback, array(&$notification));
                        //-- Piwik Hack --//
                        $notification->increaseNotificationCount($callback);
                        //-- End Piwik Hack --//
                    }
                }
            }
        }
    }

    /**
     * Creates and posts a notification object
     *
     * The purpose of the optional associated object is generally to pass
     * the object posting the notification to the observers, so that the 
     * observers can query the posting object for more information about
     * the event.
     *
     * Notifications are by default added to a pending notification list.
     * This way, if an observer is not registered by the time they are 
     * posted, it will still be notified when it is added as an observer.
     * This behaviour can be turned off in order to make sure that only
     * the registered observers will be notified.
     *
     * The info array serves as a container for any kind of useful 
     * information. It is added to the notification object and posted along.
     *
     * @access  public
     * @param   object      Notification associated object
     * @param   string      Notification name
     * @param   array       Optional user information
     * @param   bool        Whether the notification is pending
     * @param   bool        Whether you want the notification to bubble up
     * @return  object  The notification object
     */
    function &post(&$object, $nName, $info = array(), $pending = true, $bubble = true)
    {
        $notification = new $this->_notificationClass($object, $nName, $info);
        return $this->postNotification($notification, $pending, $bubble);
    }

    /**
     * Posts the {@link Event_Notification} object
     *
     * @access  public
     * @param   object      The Notification object
     * @param   bool        Whether to post the notification immediately
     * @param   bool        Whether you want the notification to bubble up
     * @see Event_Dispatcher::post()
     * @return  object  The notification object
     */
    function &postNotification(&$notification, $pending = true, $bubble = true)
    {
        $nName = $notification->getNotificationName();
        if ($pending === true) {
            $this->_pending[$nName][] =& $notification;
        }

        $object = $notification->getNotificationObject();
        $objClass = is_object($object) ? get_class($object) : null;

        // Find the registered observers
        if (isset($this->_ro[$nName])) {
            foreach (array_keys($this->_ro[$nName]) as $k) {
                $rObserver =& $this->_ro[$nName][$k];
                if ($notification->isNotificationCancelled()) {
                    return $notification;
                }
                if (empty($rObserver['class']) ||
            	    strcasecmp($rObserver['class'], $objClass) == 0) {
                    $callback = $rObserver['callback'];
                    call_user_func_array($callback, array(&$notification));
                    //-- Piwik Hack --//
                    $notification->increaseNotificationCount($callback);
                    //-- End Piwik Hack --//
                }
            }
        }

        // Notify globally registered observers
        if (isset($this->_ro[EVENT_DISPATCHER_GLOBAL])) {
            foreach (array_keys($this->_ro[EVENT_DISPATCHER_GLOBAL]) as $k) {
                $rObserver =& $this->_ro[EVENT_DISPATCHER_GLOBAL][$k];
                if ($notification->isNotificationCancelled()) {
                    return $notification;
                }
                if (empty($rObserver['class']) || 
                	strcasecmp($rObserver['class'], $objClass) == 0) {
                    call_user_func_array($rObserver['callback'], array(&$notification));
                    //-- Piwik Hack --//
                    $notification->increaseNotificationCount(get_class($rObserver['callback'][0]), $rObserver['callback'][1]);
                    //-- End Piwik Hack --//
                }
            }
        }

        if ($bubble === false) {
            return $notification;
        }
        
        // Notify in nested dispatchers
        foreach (array_keys($this->_nestedDispatchers) as $nested) {
            $notification =& $this->_nestedDispatchers[$nested]->postNotification($notification, $pending);
        }

        return $notification;
    }

    /**
     * Removes a registered observer that correspond to the given criteria
     *
     * @access  public
     * @param   mixed       A PHP callback
     * @param   string      Notification name
     * @param   string      Contained object class
     * @return  bool    True if an observer was removed, false otherwise
     */
    function removeObserver($callback, $nName = EVENT_DISPATCHER_GLOBAL, $class = null)
    {
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $reg = get_class($callback[0]).'::'.$callback[1];
            } else {
                $reg = $callback[0].'::'.$callback[1];
            }
        } else {
            $reg = $callback;
        }

        $removed = false;
        if (isset($this->_ro[$nName][$reg])) {
            if (!empty($class)) {
                if (strcasecmp($this->_ro[$nName][$reg]['class'], $class) == 0) {
                    unset($this->_ro[$nName][$reg]);
                    $removed = true;
                }
            } else {
                unset($this->_ro[$nName][$reg]);
                $removed = true;
            }
        }

        if (isset($this->_ro[$nName]) && count($this->_ro[$nName]) == 0) {
            unset($this->_ro[$nName]);
        }
        return $removed;
    }

   /**
    * Check, whether the specified observer has been registered with the
    * dispatcher
    *
     * @access  public
     * @param   mixed       A PHP callback
     * @param   string      Notification name
     * @param   string      Contained object class
     * @return  bool        True if the observer has been registered, false otherwise
    */
    function observerRegistered($callback, $nName = EVENT_DISPATCHER_GLOBAL, $class = null)
    {
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $reg = get_class($callback[0]).'::'.$callback[1];
            } else {
                $reg = $callback[0].'::'.$callback[1];
            }
        } else {
            $reg = $callback;
        }

        if (!isset($this->_ro[$nName][$reg])) {
            return false;
        }
        if (empty($class)) {
            return true;
        }
        if (strcasecmp($this->_ro[$nName][$reg]['class'], $class) == 0) {
            return true;
        }
        return false;
    }

   /**
    * Get all observers, that have been registered for a notification
    *
     * @access  public
     * @param   string      Notification name
     * @param   string      Contained object class
     * @return  array       List of all observers
    */
    function getObservers($nName = EVENT_DISPATCHER_GLOBAL, $class = null)
    {
        $observers = array();        
        if (!isset($this->_ro[$nName])) {
            return $observers;
        }
        foreach ($this->_ro[$nName] as $reg => $observer) {
        	if ($class == null || $observer['class'] == null ||  strcasecmp($observer['class'], $class) == 0) {
        		$observers[] = $reg;
        	}
        }
        return $observers;
    }
    
    /**
     * Get the name of the dispatcher.
     *
     * The name is the unique identifier of a dispatcher.
     *
     * @access   public
     * @return   string     name of the dispatcher
     */
    function getName()
    {
        return $this->_name;
    }

    /**
     * add a new nested dispatcher
     *
     * Notifications will be broadcasted to this dispatcher as well, which
     * allows you to create event bubbling.
     *
     * @access   public
     * @param    Event_Dispatcher    The nested dispatcher
     */
    function addNestedDispatcher(&$dispatcher)
    {
        $name = $dispatcher->getName();
        $this->_nestedDispatchers[$name] =& $dispatcher;
    }

   /**
    * remove a nested dispatcher
    *
    * @access   public
    * @param    Event_Dispatcher    Dispatcher to remove
    * @return   boolean
    */
    function removeNestedDispatcher($dispatcher)
    {
        if (is_object($dispatcher)) {
            $dispatcher = $dispatcher->getName();
        }
        if (!isset($this->_nestedDispatchers[$dispatcher])) {
            return false;
        }
        unset($this->_nestedDispatchers[$dispatcher]);
        return true;
    }

    /**
     * Changes the class used for notifications
     *
     * You may call this method on an object to change it for a single
     * dispatcher or statically, to set the default for all dispatchers
     * that will be created.
     *
     * @access   public
     * @param    string     name of the notification class
     * @return   boolean
     */
    function setNotificationClass($class)
    {
        if (isset($this) && is_a($this, 'Event_Dispatcher')) {
            $this->_notificationClass = $class;
            return true;
        }
        $GLOBALS['_Event_Dispatcher']['NotificationClass'] = $class;
        return true;
    }

}
