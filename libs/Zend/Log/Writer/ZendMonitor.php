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
 * @version    $Id: ZendMonitor.php 22533 2010-07-07 02:38:14Z ramon $
 */

/** Zend_Log_Writer_Abstract */
// require_once 'Zend/Log/Writer/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ZendMonitor.php 22533 2010-07-07 02:38:14Z ramon $
 */
class Zend_Log_Writer_ZendMonitor extends Zend_Log_Writer_Abstract
{
    /**
     * Is Zend Monitor enabled?
     * @var bool
     */
    protected $_isEnabled = true;

    /**
     * @throws Zend_Log_Exception if Zend Monitor extension not present
     */
    public function __construct()
    {
        if (!function_exists('monitor_custom_event')) {
            $this->_isEnabled = false;
        }
    }

    /**
     * Create a new instance of Zend_Log_Writer_ZendMonitor
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_Syslog
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        return new self();
    }

    /**
     * Is logging to this writer enabled?
     *
     * If the Zend Monitor extension is not enabled, this log writer will
     * fail silently. You can query this method to determine if the log
     * writer is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_isEnabled;
    }

    /**
     * Log a message to this writer.
     *
     * @param  array $event  log data event
     * @return void
     */
    public function write($event)
    {
        if (!$this->isEnabled()) {
            return;
        }

        parent::write($event);
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  log data event
     * @return void
     */
    protected function _write($event)
    {
        $priority = $event['priority'];
        $message  = $event['message'];
        unset($event['priority'], $event['message']);

        if (!empty($event)) {
            monitor_custom_event($priority, $message, false, $event);
        } else {
            monitor_custom_event($priority, $message);
        }
    }
}
