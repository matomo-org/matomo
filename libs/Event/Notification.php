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

/**
 * Default state of the notification
 */
define('EVENT_NOTIFICATION_STATE_DEFAULT', 0);

/**
 * Notification has been cancelled
 */
define('EVENT_NOTIFICATION_STATE_CANCELLED', 1);

/**
 * A Notification object
 *
 * The Notification object can be easily subclassed and serves as a container
 * for the information about the notification. It holds an object which is 
 * usually a reference to the object that posted the notification,
 * a notification name used to identify the notification and some user
 * information which can be anything you need.
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
class Event_Notification
{
    /**
     * name of the notofication
     * @var string
     * @access private
     */
    var $_notificationName;
    
    /**
     * object of interesed (the sender of the notification, in most cases)
     * @var object
     * @access private
     */
    var $_notificationObject;
    
    /**
     * additional information about the notification
     * @var mixed
     * @access private
     */
    var $_notificationInfo = array();

    /**
     * state of the notification
     *
     * This may be:
     * - EVENT_NOTIFICATION_STATE_DEFAULT
     * - EVENT_NOTIFICATION_STATE_CANCELLED
     *
     * @var integer
     * @access private
     */
    var $_notificationState = EVENT_NOTIFICATION_STATE_DEFAULT;
    
    /**
     * amount of observers that received this notification
     * @var mixed
     * @access private
     */
    var $_notificationCount = 0;

    /**
     * Constructor
     *
     * @access  public
     * @param   object      The object of interest for the notification,
     *                      usually is the posting object
     * @param   string      Notification name
     * @param   array       Free information array
     */
    function Event_Notification(&$object, $name, $info = array())
    {
        $this->_notificationObject =& $object;
        $this->_notificationName   = $name;
        $this->_notificationInfo   = $info;
    }

    /**
     * Returns the notification name
     * @return  string Notification name
     */
    function getNotificationName()
    {
        return $this->_notificationName;
    }

    /**
     * Returns the contained object
     * @return  object Contained object
     */
    function &getNotificationObject()
    {
        return $this->_notificationObject;
    }

    /**
     * Returns the user info array
     * @return  array user info
     */
    function getNotificationInfo()
    {
        return $this->_notificationInfo;    
    }

   /**
    * Increase the internal count
    *
    * @access   public
    */
    function increaseNotificationCount()
    {
        ++$this->_notificationCount;
    }
    
   /**
    * Get the number of posted notifications
    *
    * @access   public
    * @return   int
    */
    function getNotificationCount()
    {
        return $this->_notificationCount;
    }
    
   /**
    * Cancel the notification
    *
    * @access   public
    * @return   void
    */
    function cancelNotification()
    {
        $this->_notificationState = EVENT_NOTIFICATION_STATE_CANCELLED;
    }

   /**
    * Checks whether the notification has been cancelled
    *
    * @access   public
    * @return   boolean
    */
    function isNotificationCancelled()
    {
        return ($this->_notificationState === EVENT_NOTIFICATION_STATE_CANCELLED);
    }
}
