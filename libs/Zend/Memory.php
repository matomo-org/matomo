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
 * @package    Zend_Memory
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Memory_Exception */
require_once 'Zend/Memory/Manager.php';

/** Zend_Memory_Exception */
require_once 'Zend/Memory/Exception.php';

/** Zend_Memory_Value */
require_once 'Zend/Memory/Value.php';

/** Zend_Memory_Container */
require_once 'Zend/Memory/Container.php';

/** Zend_Memory_Exception */
require_once 'Zend/Cache.php';


/**
 * @category   Zend
 * @package    Zend_Memory
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Memory
{
    /**
     * Factory
     *
     * @param string $backend backend name
     * @param array $backendOptions associative array of options for the corresponding backend constructor
     * @return Zend_Memory_Manager
     * @throws Zend_Memory_Exception
     */
    public static function factory($backend, $backendOptions = array())
    {
        if (strcasecmp($backend, 'none') == 0) {
            return new Zend_Memory_Manager();
        }

        // because lowercase will fail
        $backend = @ucfirst(strtolower($backend));

        if (!in_array($backend, Zend_Cache::$availableBackends)) {
            throw new Zend_Memory_Exception("Incorrect backend ($backend)");
        }

        $backendClass = 'Zend_Cache_Backend_' . $backend;

        // For perfs reasons, we do not use the Zend_Loader::loadClass() method
        // (security controls are explicit)
        require_once str_replace('_', DIRECTORY_SEPARATOR, $backendClass) . '.php';

        $backendObject = new $backendClass($backendOptions);

        return new Zend_Memory_Manager($backendObject);
    }
}
