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
 * @package    Zend_Cache
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
 

/** 
 * @package    Zend_Cache
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Cache 
{
    
    /**
     * Available frontends
     * 
     * @var array $availableFrontends array of frontend name (string)
     */
    public static $availableFrontends = array('Core', 'Output', 'Class', 'File', 'Function', 'Page');
    
    /**
     * Available backends
     * 
     * @var array $availableBackends array of backends name (string)
     */
    public static $availableBackends = array('File', 'Sqlite', 'Memcached', 'Apc', 'ZendPlatform');
    
    /**
     * Consts for clean() method
     */
    const CLEANING_MODE_ALL              = 'all';
    const CLEANING_MODE_OLD	             = 'old';
    const CLEANING_MODE_MATCHING_TAG	 = 'matchingTag';
    const CLEANING_MODE_NOT_MATCHING_TAG = 'notMatchingTag';
     
    /**
     * Factory
     * 
     * @param string $frontend frontend name
     * @param string $backend backend name
     * @param array $frontendOptions associative array of options for the corresponding frontend constructor
     * @param array $backendOptions associative array of options for the corresponding backend constructor
     */
    public static function factory($frontend, $backend, $frontendOptions = array(), $backendOptions = array())
    {
        
        // because lowercase will fail
        $frontend = self::_normalizeName($frontend);
        $backend  = self::_normalizeName($backend);
        
        if (!in_array($frontend, self::$availableFrontends)) {
            self::throwException("Incorrect frontend ($frontend)");
        }
        if (!in_array($backend, Zend_Cache::$availableBackends)) {
            self::throwException("Incorrect backend ($backend)");
        }
        
        // For perfs reasons, with frontend == 'Core', we can interact with the Core itself
        $frontendClass = 'Zend_Cache_' . ($frontend != 'Core' ? 'Frontend_' : '') . $frontend;
        
        $backendClass = 'Zend_Cache_Backend_' . $backend;
        
        // For perfs reasons, we do not use the Zend_Loader::loadClass() method
        // (security controls are explicit)
        require_once str_replace('_', DIRECTORY_SEPARATOR, $frontendClass) . '.php';
        require_once str_replace('_', DIRECTORY_SEPARATOR, $backendClass) . '.php';
        
        $frontendObject = new $frontendClass($frontendOptions);
        $backendObject = new $backendClass($backendOptions);
        $frontendObject->setBackend($backendObject);
        return $frontendObject;
        
    }     
    
    /**
     * Throw an exception
     * 
     * Note : for perf reasons, the "load" of Zend/Cache/Exception is dynamic
     */
    public static function throwException($msg)
    {
        // For perfs reasons, we use this dynamic inclusion
        require_once 'Zend/Cache/Exception.php';
        throw new Zend_Cache_Exception($msg);
    }
    
    /**
     * Normalize frontend and backend names to allow multiple words TitleCased
     * 
     * @param  string $name 
     * @return string
     */
    protected static function _normalizeName($name)
    {
        $name = ucfirst(strtolower($name));
        $name = str_replace(array('-', '_', '.'), ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }

}
