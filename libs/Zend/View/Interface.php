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
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Interface class for Zend_View compatible template engine implementations
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_View_Interface
{
    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate, 
     * phplib, etc, return the template engine object. Useful for calling 
     * methods on these objects, such as for setting filters, modifiers, etc.
     * 
     * @return mixed
     */
    public function getEngine();

    /**
     * Set the path to find the view script used by render()
     * 
     * @param string|array The directory (-ies) to set as the path. Note that
     * the concrete view implentation may not necessarily support multiple 
     * directories.
     * @return void
     */
    public function setScriptPath($path);

    /**
     * Retrieve all view script paths
     * 
     * @return array
     */
    public function getScriptPaths();

    /**
     * Set a base path to all view resources
     * 
     * @param  string $path 
     * @param  string $classPrefix 
     * @return void
     */
    public function setBasePath($path, $classPrefix = 'Zend_View');

    /**
     * Add an additional path to view resources
     * 
     * @param  string $path 
     * @param  string $classPrefix 
     * @return void
     */
    public function addBasePath($path, $classPrefix = 'Zend_View');

    /**
     * Assign a variable to the view
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val);

    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key);

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key);

    /**
     * Assign variables to the view script via differing strategies.
     *
     * Suggested implementation is to allow setting a specific key to the 
     * specified value, OR passing an array of key => value pairs to set en 
     * masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or array of key 
     * => value pairs)
     * @param mixed $value (Optional) If assigning a named variable, use this
     * as the value.
     * @return void
     */
    public function assign($spec, $value = null);

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or 
     * property overloading ({@link __get()}/{@link __set()}).
     * 
     * @return void
     */
    public function clearVars();

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script script name to process.
     * @return string The script output.
     */
    public function render($name);
}
