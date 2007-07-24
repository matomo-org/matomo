<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/** Zend_Session */
require_once 'Zend/Session.php';

/**
 * Flash Messenger - implement session-based messages
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */
class Zend_Controller_Action_Helper_FlashMessenger extends Zend_Controller_Action_Helper_Abstract implements IteratorAggregate, Countable
{
    /**
     * $_messages - Messages from previous request
     *
     * @var array
     */
    static protected $_messages = array();

    /**
     * $_session - Zend_Session storage object
     *
     * @var Zend_Session
     */
    static protected $_session = null;
    
    /**
     * $_messageAdded - Wether a message has been previously added
     *
     * @var unknown_type
     */
    static protected $_messageAdded = false;

    /**
     * $_namespace - Instance namespace, default is 'default'
     *
     * @var string
     */
    protected $_namespace = 'default';
    
    /**
     * __construct() - Instance constructor, needed to get iterators, etc
     *
     * @param string $namespace
     */
    public function __construct()
    {
        if (!self::$_session instanceof Zend_Session_Namespace) {
            self::$_session = new Zend_Session_Namespace($this->getName());
            foreach (self::$_session as $namespace => $messages) {
                self::$_messages[$namespace] = $messages;
                unset(self::$_session->{$namespace});
            }
        }
    }
    
    /**
     * postDispatch() - runs after action is dispatched, in this 
     * case, it is resetting the namespace in case we have forwarded to a different
     * action, Flashmessage will be 'clean' (default namespace)
     *
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    public function postDispatch()
    {
        $this->resetNamespace();
        return $this;
    }

    /**
     * setNamespace() - change the namespace messages are added to, useful for 
     * per action controller messaging between requests
     *
     * @param string $namespace
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    public function setNamespace($namespace = 'default')
    {
        $this->_namespace = $namespace;
        return $this;
    }
    
    /**
     * resetNamespace() - reset the namespace to the default
     *
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    public function resetNamespace()
    {
        $this->setNamespace();
        return $this;
    }
    
    /**
     * addMessage() - Add a message to flash message
     *
     * @param string $message
     * @param string $namespace OPTIONAL
     */
    public function addMessage($message)
    {
        if (self::$_messageAdded === false) {
            self::$_session->setExpirationHops(1, null, true);
        }
        
        if (!is_array(self::$_session->{$this->_namespace})) {
            self::$_session->{$this->_namespace} = array();
        }
        
        self::$_session->{$this->_namespace}[] = $message;
        
        return;
    }
    
    /**
     * hasMessages() - Wether a specific namespace has messages
     *
     * @param string $namespace
     * @return bool
     */
    public function hasMessages()
    {
        return isset(self::$_messages[$this->_namespace]);
    }
    
    /**
     * getMessages() - Get messages from a specific namespace
     *
     * @param unknown_type $namespace
     * @return array
     */
    public function getMessages()
    {
        if ($this->hasMessages()) {
            return self::$_messages[$this->_namespace];
        }
        
        return array();
    }

    /**
     * Clear all messages from the current namespace
     * 
     * @return bool True if messages were cleared, false if none existed
     */
    public function clearMessages()
    {
        if ($this->hasMessages()) {
            unset(self::$_messages[$this->_namespace]);
            return true;
        }

        return false;
    }
    
    /**
     * hasCurrentMessages() - check to see if messages have been added to current
     * namespace within this request
     *
     * @return bool
     */
    public function hasCurrentMessages()
    {
        return isset(self::$_session->{$this->_namespace});
    }
    
    /**
     * getCurrentMessages() - get messages that have been added to the current 
     * namespace within this request
     *
     * @return array
     */
    public function getCurrentMessages()
    {
        if ($this->hasCurrentMessages()) {
            return self::$_session->{$this->_namespace};
        }
        
        return array();
    }
    
    /**
     * getIterator() - complete the IteratorAggregate interface, for iterating
     *
     * @return ArrayObject
     */
    public function getIterator()
    {
        if ($this->hasMessages()) {
            return new ArrayObject($this->getMessages());
        }
        
        return ArrayObject();
    }
    
    /**
     * count() - Complete the countable interface
     *
     * @return int
     */
    public function count()
    {
        if ($this->hasMessages()) {
            return count($this->getMessages());
        }
        
        return 0;
    }
}
