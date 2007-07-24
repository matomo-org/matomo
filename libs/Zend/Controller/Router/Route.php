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
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Route.php 4806 2007-05-15 18:06:12Z matthew $
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/** Zend_Controller_Router_Exception */
require_once 'Zend/Controller/Router/Exception.php';

/** Zend_Controller_Router_Route_Interface */
require_once 'Zend/Controller/Router/Route/Interface.php';

/**
 * Route
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 * @see        http://manuals.rubyonrails.com/read/chapter/65
 */
class Zend_Controller_Router_Route implements Zend_Controller_Router_Route_Interface
{

    protected $_urlVariable = ':';
    protected $_urlDelimiter = '/';
    protected $_regexDelimiter = '#';
    protected $_defaultRegex = null;

    protected $_parts;
    protected $_defaults = array();
    protected $_requirements = array();
    protected $_staticCount = 0;
    protected $_vars = array();
    protected $_params = array();
    protected $_values = array();

    /**
     * Instantiates route based on passed Zend_Config structure
     */
    public static function getInstance(Zend_Config $config)
    {
        $reqs = ($config->reqs instanceof Zend_Config) ? $config->reqs->toArray() : array();
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        return new self($config->route, $defs, $reqs);
    }

    /**
     * Prepares the route for mapping by splitting (exploding) it
     * to a corresponding atomic parts. These parts are assigned
     * a position which is later used for matching and preparing values.
     *
     * @param string Map used to match with later submitted URL path
     * @param array Defaults for map variables with keys as variable names
     * @param array Regular expression requirements for variables (keys as variable names)
     */
    public function __construct($route, $defaults = array(), $reqs = array())
    {

        $route = trim($route, $this->_urlDelimiter);
        $this->_defaults = (array) $defaults;
        $this->_requirements = (array) $reqs;

        if ($route != '') {

            foreach (explode($this->_urlDelimiter, $route) as $pos => $part) {

                if (substr($part, 0, 1) == $this->_urlVariable) {
                    $name = substr($part, 1);
                    $regex = (isset($reqs[$name]) ? $reqs[$name] : $this->_defaultRegex);
                    $this->_parts[$pos] = array('name' => $name, 'regex' => $regex);
                    $this->_vars[] = $name;
                } else {
                    $this->_parts[$pos] = array('regex' => $part);
                    if ($part != '*') {
                        $this->_staticCount++;
                    }
                }

            }

        }

    }

    protected function _getWildcardData($parts, $unique)
    {
        $pos = count($parts);
        if ($pos % 2) {
            $parts[] = null;
        }
        foreach(array_chunk($parts, 2) as $part) {
            list($var, $value) = $part;
            $var = urldecode($var);
            if (!array_key_exists($var, $unique)) {
                $this->_params[$var] = urldecode($value);
                $unique[$var] = true;
            }
        }
    }

    /**
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {

        $pathStaticCount = 0;
        $defaults = $this->_defaults;

        if (count($defaults)) {
        	$unique = array_combine(array_keys($defaults), array_fill(0, count($defaults), true));
        } else {
        	$unique = array();
        }

        $path = trim($path, $this->_urlDelimiter);

        if ($path != '') {

            $path = explode($this->_urlDelimiter, $path);

            foreach ($path as $pos => $pathPart) {

                if (!isset($this->_parts[$pos])) {
                    return false;
                }

                if ($this->_parts[$pos]['regex'] == '*') {
                    $parts = array_slice($path, $pos);
                    $this->_getWildcardData($parts, $unique);
                    break;
                }

                $part = $this->_parts[$pos];
                $name = isset($part['name']) ? $part['name'] : null;
                $pathPart = urldecode($pathPart);
                
                if ($name === null) {
                    if ($part['regex'] != $pathPart) {
                        return false;
                    }
                } elseif ($part['regex'] === null) {
                    if (strlen($pathPart) == 0) {
                        return false;
                    } 
                } else {
                    $regex = $this->_regexDelimiter . '^' . $part['regex'] . '$' . $this->_regexDelimiter . 'iu';
                    if (!preg_match($regex, $pathPart)) {
                        return false;
                    }
                }

                if ($name !== null) {
                    // It's a variable. Setting a value
                    $this->_values[$name] = $pathPart;
                    $unique[$name] = true;
                } else {
                    $pathStaticCount++;
                }

            }

        }

        $return = $this->_values + $this->_params + $this->_defaults;

        // Check if all static mappings have been met
        if ($this->_staticCount != $pathStaticCount) {
            return false;
        }

        // Check if all map variables have been initialized
        foreach ($this->_vars as $var) {
            if (!array_key_exists($var, $return)) {
                return false;
            }
        }

        return $return;

    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route
     *
     * @param  array $data An array of variable and value pairs used as parameters
     * @param  boolean $reset Whether or not to set route defaults with those provided in $data
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false)
    {

        $url = array();
        $flag = false;
        
        foreach ($this->_parts as $key => $part) {

            $resetPart = false;
            if (isset($part['name']) && array_key_exists($part['name'], $data) && $data[$part['name']] === null) {
                $resetPart = true;
            }

            if (isset($part['name'])) {

                if (isset($data[$part['name']]) && !$resetPart) {
                    $url[$key] = $data[$part['name']];
                    unset($data[$part['name']]);
                } elseif (!$reset && !$resetPart && isset($this->_values[$part['name']])) {
                    $url[$key] = $this->_values[$part['name']];
                } elseif (!$reset && !$resetPart && isset($this->_params[$part['name']])) {
                    $url[$key] = $this->_params[$part['name']];
                } elseif (isset($this->_defaults[$part['name']])) {
                    $url[$key] = $this->_defaults[$part['name']];
                } else
                    throw new Zend_Controller_Router_Exception($part['name'] . ' is not specified');

            } else {

                if ($part['regex'] != '*') {
                    $url[$key] = $part['regex'];
                } else {
                    if (!$reset) $data += $this->_params;
                    foreach ($data as $var => $value) {
                        if ($value !== null) {
                            $url[$var] = $var . $this->_urlDelimiter . $value;
                            $flag = true;
                        }
                    }
                }

            }

        }
        
        $return = '';
        
        foreach (array_reverse($url, true) as $key => $value) {
            if ($flag || !isset($this->_parts[$key]['name']) || $value !== $this->getDefault($this->_parts[$key]['name'])) {
                $return = '/' . $value . $return;
                $flag = true;
            }
        }

        return trim($return, '/');

    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name) {
        if (isset($this->_defaults[$name])) {
            return $this->_defaults[$name];
        }
        return null;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults() {
        return $this->_defaults;
    }

}
