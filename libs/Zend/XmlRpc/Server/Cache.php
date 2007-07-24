<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/**
 * class hinting
 */
require_once 'Zend/XmlRpc/Server.php';

/**
 * Zend_XmlRpc_Server_Cache: cache Zend_XmlRpc_Server dispatch tables
 * 
 * @category Zend
 * @package  Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_XmlRpc_Server_Cache
{
    /**
     * Cache a file containing the dispatch list.
     *
     * Serializes the XMLRPC server callbacks array and stores the information 
     * in $filename.
     *
     * Returns false on any error (typically, inability to write to file), true
     * on success.
     *
     * @param string $filename
     * @param Zend_XmlRpc_Server $server
     * @return bool
     */
    public static function save($filename, Zend_XmlRpc_Server $server) 
    {
        if (!is_string($filename)
            || (!file_exists($filename) && !is_writable(dirname($filename))))
        {
            return false;
        }

        // Remove system.* methods
        $methods = $server->getFunctions();
        foreach ($methods as $name => $method) {
            if ($method->system) {
                unset($methods[$name]);
            }
        }

        // Store
        if (0 === @file_put_contents($filename, serialize($methods))) {
            return false;
        }

        return true;
    }

    /**
     * Add dispatch table from a file
     *
     * Unserializes a stored dispatch table from $filename. Returns false if it
     * fails in any way, true on success.
     *
     * Useful to prevent needing to build the dispatch list on each XMLRPC 
     * request. Sample usage:
     *
     * <code>
     * if (!Zend_XmlRpc_Server_Cache::get($filename, $server)) {
     *     require_once 'Some/Service/Class.php';
     *     require_once 'Another/Service/Class.php';
     *
     *     // Attach Some_Service_Class with namespace 'some'
     *     $server->attach('Some_Service_Class', 'some');
     *
     *     // Attach Another_Service_Class with namespace 'another'
     *     $server->attach('Another_Service_Class', 'another');
     *
     *     Zend_XmlRpc_Server_Cache::save($filename, $server);
     * }
     *
     * $response = $server->handle();
     * echo $response;
     * </code>
     *
     * @param string $filename
     * @param Zend_XmlRpc_Server $server
     * @return bool
     */
    public static function get($filename, Zend_XmlRpc_Server $server) 
    {
        if (!is_string($filename) 
            || !file_exists($filename) 
            || !is_readable($filename)) 
        {
            return false;
        }

        if (false === ($dispatch = @file_get_contents($filename))) {
            return false;
        }

        $dispatchArray = @unserialize($dispatch);

        $server->loadFunctions($dispatchArray);

        return true;
    }

    /**
     * Remove a cache file
     * 
     * @param string $filename 
     * @return boolean
     */
    public static function delete($filename)
    {
        if (is_string($filename) && file_exists($filename)) {
            unlink($filename);
            return true;
        }

        return false;
    }
}
