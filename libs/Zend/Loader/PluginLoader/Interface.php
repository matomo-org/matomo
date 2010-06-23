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
 * @package    Zend_Loader
 * @subpackage PluginLoader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 16971 2009-07-22 18:05:45Z mikaelkael $
 */

/**
 * Plugin class loader interface
 *
 * @category   Zend
 * @package    Zend_Loader
 * @subpackage PluginLoader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Loader_PluginLoader_Interface
{
    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return Zend_Loader_PluginLoader
     */
    public function addPrefixPath($prefix, $path);
    
    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return Zend_Loader_PluginLoader
     */
    public function removePrefixPath($prefix, $path = null);
    
    /**
     * Whether or not a Helper by a specific name
     *
     * @param string $name
     * @return Zend_Loader_PluginLoader
     */
    public function isLoaded($name);

    /**
     * Return full class name for a named helper
     *
     * @param string $name
     * @return string
     */
    public function getClassName($name);
    
    /**
     * Load a helper via the name provided
     *
     * @param string $name
     * @return string
     */
    public function load($name);
}
