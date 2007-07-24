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
 * @package    Zend_Server
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/**
 * Zend_Server_Interface 
 * 
 * @category Zend
 * @package  Zend_Server
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Interface.php 3452 2007-02-15 18:14:15Z matthew $
 */
interface Zend_Server_Interface
{
    /**
     * Attach a function as a server method
     *
     * Namespacing is primarily for xmlrpc, but may be used with other 
     * implementations to prevent naming collisions.
     * 
     * @param string $function 
     * @param string $namespace 
     * @param null|array Optional array of arguments to pass to callbacks at 
     * dispatch.
     * @return void
     */
    public function addFunction($function, $namespace = '');

    /**
     * Attach a class to a server
     *
     * The individual implementations should probably allow passing a variable 
     * number of arguments in, so that developers may define custom runtime 
     * arguments to pass to server methods.
     *
     * Namespacing is primarily for xmlrpc, but could be used for other 
     * implementations as well.
     * 
     * @param mixed $class Class name or object instance to examine and attach 
     * to the server.
     * @param string $namespace Optional namespace with which to prepend method 
     * names in the dispatch table.
     * methods in the class will be valid callbacks.
     * @param null|array Optional array of arguments to pass to callbacks at 
     * dispatch.
     * @return void
     */
    public function setClass($class, $namespace = '', $argv = null);

    /**
     * Generate a server fault
     * 
     * @param mixed $fault 
     * @param int $code 
     * @return mixed
     */
    public function fault($fault = null, $code = 404);

    /**
     * Handle a request
     *
     * Requests may be passed in, or the server may automagically determine the 
     * request based on defaults. Dispatches server request to appropriate 
     * method and returns a response
     * 
     * @param mixed $request 
     * @return mixed
     */
    public function handle($request = false);

    /**
     * Return a server definition array
     *
     * Returns a server definition array as created using 
     * {@link * Zend_Server_Reflection}. Can be used for server introspection, 
     * documentation, or persistence.
     * 
     * @access public
     * @return array
     */
    public function getFunctions();

    /**
     * Load server definition
     *
     * Used for persistence; loads a construct as returned by {@link getFunctions()}.
     *
     * @param array $array 
     * @return void
     */
    public function loadFunctions($definition);

    /**
     * Set server persistence
     * 
     * @todo Determine how to implement this
     * @param int $mode 
     * @return void
     */
    public function setPersistence($mode);
}
