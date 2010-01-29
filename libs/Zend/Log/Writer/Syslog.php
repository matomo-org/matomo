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
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Syslog.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Log_Writer_Abstract */
require_once 'Zend/Log/Writer/Abstract.php';

/**
 * Writes log messages to syslog
 *
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Log_Writer_Syslog extends Zend_Log_Writer_Abstract
{
    /**
     * Maps Zend_Log priorities to PHP's syslog priorities
     * @var array
     */
    protected $_priorities = array(
        Zend_Log::EMERG  => LOG_EMERG,
        Zend_Log::ALERT  => LOG_ALERT,
        Zend_Log::CRIT   => LOG_CRIT,
        Zend_Log::ERR    => LOG_ERR,
        Zend_Log::WARN   => LOG_WARNING,
        Zend_Log::NOTICE => LOG_NOTICE,
        Zend_Log::INFO   => LOG_INFO,
        Zend_Log::DEBUG  => LOG_DEBUG,
    );

    /**
     * The default log priority - for unmapped custom priorities
     * @var string
     */
    protected $_defaultPriority = LOG_NOTICE;

    /**
     * Last application name set by a syslog-writer instance
     * @var string
     */
    protected static $_lastApplication;

    /**
     * Last facility name set by a syslog-writer instance
     * @var string
     */
    protected static $_lastFacility;

    /**
     * Application name used by this syslog-writer instance
     * @var string
     */
    protected $_application = 'Zend_Log';

    /**
     * Facility used by this syslog-writer instance
     * @var string
     */
    protected $_facility = LOG_USER;

    /**
     * Class constructor
     *
     * @param  array $options Array of options; may include "application" and "facility" keys
     * @return void
     */
    public function __construct(array $params = array())
    {
        if (isset($params['application'])) {
            $this->_application = $params['application'];
        }
        if (isset($params['facility'])) {
            $this->_facility = $params['facility'];
        }
        $this->_initializeSyslog();
    }
    
    /**
     * Create a new instance of Zend_Log_Writer_Syslog
     * 
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_Syslog
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        return new self(self::_parseConfig($config));
    }

    /**
     * Initialize syslog / set application name and facility
     *
     * @param  string $application Application name
     * @param  string $facility Syslog facility
     * @return void
     */
    protected function _initializeSyslog()
    {
        self::$_lastApplication = $this->_application;
        self::$_lastFacility    = $this->_facility;
        openlog($this->_application, LOG_PID, $this->_facility);
    }

    /**
     * Set syslog facility
     *
     * @param  string $facility Syslog facility
     * @return void
     */
    public function setFacility($facility)
    {
        if ($this->_facility === $facility) {
            return;
        }
        $this->_facility = $facility;
        $this->_initializeSyslog();
    }

    /**
     * Set application name
     *
     * @param  string $application Application name
     * @return void
     */
    public function setApplicationName($application)
    {
        if ($this->_application === $application) {
            return;
        }
        $this->_application = $application;
        $this->_initializeSyslog();
    }

    /**
     * Close syslog.
     *
     * @return void
     */
    public function shutdown()
    {
        closelog();
    }

    /**
     * Write a message to syslog.
     *
     * @param  array $event  event data
     * @return void
     */
    protected function _write($event)
    {
        if (array_key_exists($event['priority'], $this->_priorities)) {
            $priority = $this->_priorities[$event['priority']];
        } else {
            $priority = $this->_defaultPriority;
        }

        if ($this->_application !== self::$_lastApplication
            || $this->_facility !== self::$_lastFacility)
        {
            $this->_initializeSyslog();
        }

        syslog($priority, $event['message']);
    }
}
