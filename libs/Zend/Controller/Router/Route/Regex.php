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
 * @version    $Id$
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/** Zend_Controller_Router_Route_Interface */
require_once 'Zend/Controller/Router/Route/Interface.php';

/**
 * Regex Route 
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_Controller_Router_Route_Regex implements Zend_Controller_Router_Route_Interface
{
    protected $_regex = null;
    protected $_defaults = array();
    protected $_reverse = null;
    
    protected $_values = array();
    
    /**
     * Instantiates route based on passed Zend_Config structure
     */
    public static function getInstance(Zend_Config $config) 
    {
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        $map = ($config->map instanceof Zend_Config) ? $config->map->toArray() : array();
        $reverse = (isset($config->reverse)) ? $config->reverse : null;
        return new self($config->route, $defs, $map, $reverse);
    }

    public function __construct($route, $defaults = array(), $map = array(), $reverse = null)
    {
        $this->_regex = '#^' . $route . '$#i';
        $this->_defaults = (array) $defaults;
        $this->_map = (array) $map;
        $this->_reverse = $reverse;
    }

    /**
     * Matches a user submitted path with a previously defined route. 
     * Assigns and returns an array of defaults on a successful match.  
     *
     * @param string Path used to match against this routing map 
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        $path = trim(urldecode($path), '/');
        $res = preg_match($this->_regex, $path, $values);

        if ($res === 0) return false; 

        // array_filter_key()? Why isn't this in a standard PHP function set yet? :)
        foreach ($values as $i => $value) {
            if (!is_int($i) || $i === 0) {
                unset($values[$i]);
            }
        }
        
        $this->_values = $values;
                     
        $values = $this->_getMappedValues($values);
        $defaults = $this->_getMappedValues($this->_defaults, false, true);

        $return = $values + $defaults;
         
        return $return;
    }
    
    /**
     * Maps numerically indexed array values to it's associative mapped counterpart. 
     * Or vice versa. Uses user provided map array which consists of index => name 
     * parameter mapping. If map is not found, it returns original array.
     * 
     * Method strips destination type of keys form source array. Ie. if source array is 
     * indexed numerically then every associative key will be stripped. Vice versa if reversed 
     * is set to true.      
     *
     * @param array Indexed or associative array of values to map 
     * @param boolean False means translation of index to association. True means reverse.  
     * @param boolean Should wrong type of keys be preserved or stripped.  
     * @return array An array of mapped values
     */
    protected function _getMappedValues($values, $reversed = false, $preserve = false)
    {
        if (count($this->_map) == 0) {
            return $values; 
        }

        $return = array();
        
        foreach ($values as $key => $value) {
            if (is_int($key) && !$reversed) {
                if (array_key_exists($key, $this->_map)) {
                    $index = $this->_map[$key];
                } elseif (false === ($index = array_search($key, $this->_map))) {
                    $index = $key;
                }
                $return[$index] = $values[$key];
            } elseif ($reversed) {
                $index = (!is_int($key)) ? array_search($key, $this->_map, true) : $key;
                if (false !== $index) {
                    $return[$index] = $values[$key];
                }
            } elseif ($preserve) {
                $return[$key] = $value;
            }
        }
        
        return $return;
    }
    
    /**
     * Assembles a URL path defined by this route 
     *
     * @param array An array of name (or index) and value pairs used as parameters 
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array())
    {
        if ($this->_reverse === null) {
            require_once 'Zend/Controller/Router/Exception.php';
            throw new Zend_Controller_Router_Exception('Cannot assemble. Reversed route is not specified.');
        }
        
        $data = $this->_getMappedValues($data, true, false);
        $data += $this->_getMappedValues($this->_defaults, true, false);
        $data += $this->_values;
        
        ksort($data);
        
        $return = @vsprintf($this->_reverse, $data);

        if ($return === false) {
            require_once 'Zend/Controller/Router/Exception.php';
            throw new Zend_Controller_Router_Exception('Cannot assemble. Too few arguments?');
        }
        
        return $return;
        
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
