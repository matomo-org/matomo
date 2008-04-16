<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE.txt, and
 * is available through the world-wide-web at the following URL:
 * http://framework.zend.com/license/new-bsd. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 * 
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Mail_Storage_Abstract implements Countable, ArrayAccess, SeekableIterator
{
    /**
     * class capabilities with default values
     * @var array
     */
    protected $_has = array('uniqueid'  => true,
                            'delete'    => false,
                            'create'    => false,
                            'top'       => false,
                            'fetchPart' => true,
                            'flags'     => false);

    /**
     * current iteration position
     * @var int
     */
    protected $_iterationPos = 0;

    /**
     * maximum iteration position (= message count)
     * @var null|int
     */
    protected $_iterationMax = null;

    /**
     * used message class, change it in an extened class to extend the returned message class
     * @var string
     */
    protected $_messageClass = 'Zend_Mail_Message';

    /**
     * Getter for has-properties. The standard has properties
     * are: hasFolder, hasUniqueid, hasDelete, hasCreate, hasTop
     *
     * The valid values for the has-properties are:
     *   - true if a feature is supported
     *   - false if a feature is not supported
     *   - null is it's not yet known or it can't be know if a feature is supported
     *
     * @param  string $var  property name
     * @return bool         supported or not
     * @throws Zend_Mail_Storage_Exception
     */
    public function __get($var)
    {
        if (strpos($var, 'has') === 0) {
            $var = strtolower(substr($var, 3));
            return isset($this->_has[$var]) ? $this->_has[$var] : null;
        }
        
        /**
         * @see Zend_Mail_Storage_Exception
         */
        require_once 'Zend/Mail/Storage/Exception.php';
        throw new Zend_Mail_Storage_Exception($var . ' not found');
    }


    /**
     * Get a full list of features supported by the specific mail lib and the server
     *
     * @return array list of features as array(featurename => true|false[|null])
     */
    public function getCapabilities()
    {
        return $this->_has;
    }


    /**
     * Count messages messages in current box/folder
     *
     * @return int number of messages
     * @throws Zend_Mail_Storage_Exception
     */
    abstract public function countMessages();


    /**
     * Get a list of messages with number and size
     *
     * @param  int $id  number of message
     * @return int|array size of given message of list with all messages as array(num => size)
     */
    abstract public function getSize($id = 0);


    /**
     * Get a message with headers and body
     *
     * @param  $id int number of message
     * @return Zend_Mail_Message
     */
    abstract public function getMessage($id);


    /**
     * Get raw header of message or part
     *
     * @param  int               $id       number of message
     * @param  null|array|string $part     path to part or null for messsage header
     * @param  int               $topLines include this many lines with header (after an empty line)
     * @return string raw header
     */
    abstract public function getRawHeader($id, $part = null, $topLines = 0);

    /**
     * Get raw content of message or part
     *
     * @param  int               $id   number of message
     * @param  null|array|string $part path to part or null for messsage content
     * @return string raw content
     */
    abstract public function getRawContent($id, $part = null);

    /**
     * Create instance with parameters
     *
     * @param  array $params mail reader specific parameters
     * @throws Zend_Mail_Storage_Exception
     */
    abstract public function __construct($params);


    /**
     * Destructor calls close() and therefore closes the resource.
     */
    public function __destruct()
    {
        $this->close();
    }


    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     *
     * @return null
     */
    abstract public function close();


    /**
     * Keep the resource alive.
     *
     * @return null
     */
    abstract public function noop();

    /**
     * delete a message from current box/folder
     *
     * @return null
     */
    abstract public function removeMessage($id);

    /**
     * get unique id for one or all messages
     *
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     * @throws Zend_Mail_Storage_Exception
     */
    abstract public function getUniqueId($id = null);

    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @return int message number
     * @throws Zend_Mail_Storage_Exception
     */
    abstract public function getNumberByUniqueId($id);

    // interface implementations follows

    /**
     * Countable::count()
     *
     * @return   int
     */
     public function count()
     {
        return $this->countMessages();
     }


     /**
      * ArrayAccess::offsetExists()
      *
      * @param    int     $id
      * @return   boolean
      */
     public function offsetExists($id)
     {
        try {
            if ($this->getMessage($id)) {
                return true;
            }
        } catch(Zend_Mail_Storage_Exception $e) {}

        return false;
     }


     /**
      * ArrayAccess::offsetGet()
      *
      * @param    int $id
      * @return   Zend_Mail_Message message object
      */
     public function offsetGet($id)
     {
        return $this->getMessage($id);
     }


     /**
      * ArrayAccess::offsetSet()
      *
      * @param    id     $id
      * @param    mixed  $value
      * @throws   Zend_Mail_Storage_Exception
      * @return   void
      */
     public function offsetSet($id, $value)
     {
        /**
         * @see Zend_Mail_Storage_Exception
         */
        require_once 'Zend/Mail/Storage/Exception.php';
        throw new Zend_Mail_Storage_Exception('cannot write mail messages via array access');
     }


     /**
      * ArrayAccess::offsetUnset()
      *
      * @param    int   $id
      * @return   boolean success
      */
     public function offsetUnset($id)
     {
        return $this->removeMessage($id);
     }


     /**
      * Iterator::rewind()
      *
      * Rewind always gets the new count from the storage. Thus if you use
      * the interfaces and your scripts take long you should use reset()
      * from time to time.
      *
      * @return   void
      */
     public function rewind()
     {
        $this->_iterationMax = $this->countMessages();
        $this->_iterationPos = 1;
     }


     /**
      * Iterator::current()
      *
      * @return   Zend_Mail_Message current message
      */
     public function current()
     {
        return $this->getMessage($this->_iterationPos);
     }


     /**
      * Iterator::key()
      *
      * @return   int id of current position
      */
     public function key()
     {
        return $this->_iterationPos;
     }


     /**
      * Iterator::next()
      *
      * @return   void
      */
     public function next()
     {
        ++$this->_iterationPos;
     }


     /**
      * Iterator::valid()
      *
      * @return   boolean
      */
     public function valid()
     {
        if ($this->_iterationMax === null) {
          $this->_iterationMax = $this->countMessages();
        }
        return $this->_iterationPos && $this->_iterationPos <= $this->_iterationMax;
     }


     /**
      * SeekableIterator::seek()
      *
      * @param  int $pos
      * @return void
      * @throws OutOfBoundsException
      */
     public function seek($pos)
     {
        if ($this->_iterationMax === null) {
          $this->_iterationMax = $this->countMessages();
        }

        if ($pos > $this->_iterationMax) {
            throw new OutOfBoundsException('this position does not exist');
        }
        $this->_iterationPos = $pos;
     }

}
