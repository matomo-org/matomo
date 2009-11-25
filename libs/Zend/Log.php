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
 * @package    Zend_Log
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Log.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * @category   Zend
 * @package    Zend_Log
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Log.php 18951 2009-11-12 16:26:19Z alexander $
 */
class Zend_Log
{
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages

    /**
     * @var array of priorities where the keys are the
     * priority numbers and the values are the priority names
     */
    protected $_priorities = array();

    /**
     * @var array of Zend_Log_Writer_Abstract
     */
    protected $_writers = array();

    /**
     * @var array of Zend_Log_Filter_Interface
     */
    protected $_filters = array();

    /**
     * @var array of extra log event
     */
    protected $_extras = array();

    /**
     * Class constructor.  Create a new logger
     *
     * @param Zend_Log_Writer_Abstract|null  $writer  default writer
     */
    public function __construct(Zend_Log_Writer_Abstract $writer = null)
    {
        $r = new ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());

        if ($writer !== null) {
            $this->addWriter($writer);
        }
    }

    /**
     * Class destructor.  Shutdown log writers
     *
     * @return void
     */
    public function __destruct()
    {
        foreach($this->_writers as $writer) {
            $writer->shutdown();
        }
    }

    /**
     * Undefined method handler allows a shortcut:
     *   $log->priorityName('message')
     *     instead of
     *   $log->log('message', Zend_Log::PRIORITY_NAME)
     *
     * @param  string  $method  priority name
     * @param  string  $params  message to log
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __call($method, $params)
    {
        $priority = strtoupper($method);
        if (($priority = array_search($priority, $this->_priorities)) !== false) {
            $this->log(array_shift($params), $priority);
        } else {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Bad log priority');
        }
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @return void
     * @throws Zend_Log_Exception
     */
    public function log($message, $priority)
    {
        // sanity checks
        if (empty($this->_writers)) {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('No writers were added');
        }

        if (! isset($this->_priorities[$priority])) {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Bad log priority');
        }

        // pack into event required by filters and writers
        $event = array_merge(array('timestamp'    => date('c'),
                                    'message'      => $message,
                                    'priority'     => $priority,
                                    'priorityName' => $this->_priorities[$priority]),
                              $this->_extras);

        // abort if rejected by the global filters
        foreach ($this->_filters as $filter) {
            if (! $filter->accept($event)) {
                return;
            }
        }

        // send to each writer
        foreach ($this->_writers as $writer) {
            $writer->write($event);
        }
    }

    /**
     * Add a custom priority
     *
     * @param  string   $name      Name of priority
     * @param  integer  $priority  Numeric priority
     * @throws Zend_Log_InvalidArgumentException
     */
    public function addPriority($name, $priority)
    {
        // Priority names must be uppercase for predictability.
        $name = strtoupper($name);

        if (isset($this->_priorities[$priority])
            || array_search($name, $this->_priorities)) {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Existing priorities cannot be overwritten');
        }

        $this->_priorities[$priority] = $name;
    }

    /**
     * Add a filter that will be applied before all log writers.
     * Before a message will be received by any of the writers, it
     * must be accepted by all filters added with this method.
     *
     * @param  int|Zend_Log_Filter_Interface $filter
     * @return void
     */
    public function addFilter($filter)
    {
        if (is_integer($filter)) {
            /** @see Zend_Log_Filter_Priority */
            require_once 'Zend/Log/Filter/Priority.php';
            $filter = new Zend_Log_Filter_Priority($filter);
        } elseif(!is_object($filter) || ! $filter instanceof Zend_Log_Filter_Interface) {
            /** @see Zend_Log_Exception */
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Invalid filter provided');
        }

        $this->_filters[] = $filter;
    }

    /**
     * Add a writer.  A writer is responsible for taking a log
     * message and writing it out to storage.
     *
     * @param  Zend_Log_Writer_Abstract $writer
     * @return void
     */
    public function addWriter(Zend_Log_Writer_Abstract $writer)
    {
        $this->_writers[] = $writer;
    }

    /**
     * Set an extra item to pass to the log writers.
     *
     * @param  $name    Name of the field
     * @param  $value   Value of the field
     * @return void
     */
    public function setEventItem($name, $value) {
        $this->_extras = array_merge($this->_extras, array($name => $value));
    }

}
