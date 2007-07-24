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
 * Zend
 */
require_once 'Zend/Loader.php';

/**
 * Zend_View_Interface
 */
require_once 'Zend/View/Interface.php';

/**
 * Abstract class for Zend_View to help enforce private constructs.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_View_Abstract implements Zend_View_Interface
{
    /**
     * Path stack for script, helper, and filter directories.
     *
     * @var array
     */
    private $_path = array(
        'script' => array(),
        'helper' => array(),
        'filter' => array(),
    );

    /**
     * Script file name to execute
     *
     * @var string
     */
    private $_file = null;

    /**
     * Instances of helper objects.
     *
     * @var array
     */
    private $_helper = array();

    /**
     * Map of helper => class pairs to help in determining helper class from 
     * name
     * @var array 
     */
    private $_helperLoaded = array();

    /**
     * Map of helper => classfile pairs to aid in determining helper classfile
     * @var array
     */
    private $_helperLoadedDir = array();

    /**
     * Stack of Zend_View_Filter names to apply as filters.
     * @var array
     */
    private $_filter = array();

    /**
     * Stack of Zend_View_Filter objects that have been loaded
     * @var array
     */
    private $_filterClass = array();

    /**
     * Map of filter => class pairs to help in determining filter class from 
     * name
     * @var array 
     */
    private $_filterLoaded = array();

    /**
     * Map of filter => classfile pairs to aid in determining filter classfile
     * @var array
     */
    private $_filterLoadedDir = array();

    /**
     * Callback for escaping.
     *
     * @var string
     */
    private $_escape = 'htmlspecialchars';

    /**
     * Encoding to use in escaping mechanisms; defaults to latin1 (ISO-8859-1)
     * @var string 
     */
    private $_encoding = 'ISO-8859-1';

    /**
     * Strict variables flag; when on, undefined variables accessed in the view 
     * scripts will trigger notices 
     * @var boolean
     */
    private $_strictVars = false;

    /**
     * Constructor.
     *
     * @param array $config Configuration key-value pairs.
     */
    public function __construct($config = array())
    {
        // set inital paths and properties 
        $this->setScriptPath(null);
        $this->setHelperPath(null);
        $this->setFilterPath(null);

        // user-defined escaping callback
        if (array_key_exists('escape', $config)) {
            $this->setEscape($config['escape']);
        }

        // encoding
        if (array_key_exists('encoding', $config)) {
            $this->setEncoding($config['encoding']);
        }

        // base path
        if (array_key_exists('basePath', $config)) {
            $prefix = 'Zend_View';
            if (array_key_exists('basePathPrefix', $config)) {
                $prefix = $config['basePathPrefix'];
            }
            $this->setBasePath($config['basePath'], $prefix);
        }

        // user-defined view script path
        if (array_key_exists('scriptPath', $config)) {
            $this->addScriptPath($config['scriptPath']);
        }

        // user-defined helper path
        if (array_key_exists('helperPath', $config)) {
            $prefix = 'Zend_View_Helper';
            if (array_key_exists('helperPathPrefix', $config)) {
                $prefix = $config['helperPathPrefix'];
            }
            $this->addHelperPath($config['helperPath'], $prefix);
        }

        // user-defined filter path
        if (array_key_exists('filterPath', $config)) {
            $prefix = 'Zend_View_Filter';
            if (array_key_exists('filterPathPrefix', $config)) {
                $prefix = $config['filterPathPrefix'];
            }
            $this->addFilterPath($config['filterPath'], $prefix);
        }

        // user-defined filters
        if (array_key_exists('filter', $config)) {
            $this->addFilter($config['filter']);
        }

        // strict vars
        if (array_key_exists('strictVars', $config)) {
            $this->strictVars($config['strictVars']);
        }

        $this->init();
    }

    /**
     * Return the template engine object
     *
     * Returns the object instance, as it is its own template engine
     * 
     * @return Zend_View_Abstract
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Allow custom object initialization when extending Zend_View_Abstract or 
     * Zend_View
     *
     * Triggered by {@link __construct() the constructor} as its final action.
     * 
     * @return void
     */
    public function init()
    {
    }

    /**
     * Prevent E_NOTICE for nonexistent values
     *
     * If {@link strictVars()} is on, raises a notice.
     * 
     * @param  string $key 
     * @return null
     */
    public function __get($key)
    {
        if ($this->_strictVars) {
            trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
        }

        return null;
    }

    /**
     * Allows testing with empty() and isset() to work inside
     * templates.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        if ('_' != substr($key, 0, 1)) {
            return isset($this->$key);
        }

        return false;
    }

    /**
     * Directly assigns a variable to the view script.
     *
     * Checks first to ensure that the caller is not attempting to set a 
     * protected or private member (by checking for a prefixed underscore); if 
     * not, the public member is set; otherwise, an exception is raised.
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     * @throws Zend_View_Exception if an attempt to set a private or protected 
     * member is detected
     */
    public function __set($key, $val)
    {
        if ('_' != substr($key, 0, 1)) {
            $this->$key = $val;
            return;
        }

        require_once 'Zend/View/Exception.php';
        throw new Zend_View_Exception('Setting private or protected class members is not allowed', $this);
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if ('_' != substr($key, 0, 1) && isset($this->$key)) {
            unset($this->$key);
        }
    }

    /**
     * Accesses a helper object from within a script.
     *
     * If the helper class has a 'view' property, sets it with the current view 
     * object.
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        // call the helper method
        return call_user_func_array(
            array($helper, $name),
            $args
        );
    }

    /**
     * Given a base path, sets the script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/
     *     scripts/
     *     helpers/
     *     filters/
     * </code>
     * 
     * @param  string $path 
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Zend_View_Abstract
     */
    public function setBasePath($path, $classPrefix = 'Zend_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->setScriptPath($path . 'scripts');
        $this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->setFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * Given a base path, add script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/
     *     scripts/
     *     helpers/
     *     filters/
     * </code>
     * 
     * @param  string $path 
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Zend_View_Abstract
     */
    public function addBasePath($path, $classPrefix = 'Zend_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->addScriptPath($path . 'scripts');
        $this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->addFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * Adds to the stack of view script paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @return Zend_View_Abstract
     */
    public function addScriptPath($path)
    {
        $this->_addPath('script', $path);
        return $this;
    }

    /**
     * Resets the stack of view script paths.
     *
     * To clear all paths, use Zend_View::setScriptPath(null).
     *
     * @param string|array The directory (-ies) to set as the path.
     * @return Zend_View_Abstract
     */
    public function setScriptPath($path)
    {
        $this->_path['script'] = array();
        $this->_addPath('script', $path);
        return $this;
    }

    /**
     * Return full path to a view script specified by $name
     * 
     * @param  string $name 
     * @return false|string False if script not found
     * @throws Zend_View_Exception if no script directory set
     */
    public function getScriptPath($name)
    {
        try {
            $path = $this->_script($name);
            return $path;
        } catch (Zend_View_Exception $e) {
            if (strstr($e->getMessage(), 'no view script directory set')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Returns an array of all currently set script paths
     * 
     * @return array
     */
    public function getScriptPaths()
    {
        return $this->_getPaths('script');
    }

    /**
     * Adds to the stack of helper paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @param string $classPrefix Class prefix to use with classes in this 
     * directory; defaults to Zend_View_Helper
     * @return Zend_View_Abstract
     */
    public function addHelperPath($path, $classPrefix = 'Zend_View_Helper_')
    {
        if (!empty($classPrefix) && ('_' != substr($classPrefix, -1))) {
            $classPrefix .= '_';
        }

        $this->_addPath('helper', $path, $classPrefix);
        return $this;
    }

    /**
     * Resets the stack of helper paths.
     *
     * To clear all paths, use Zend_View::setHelperPath(null).
     *
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix The class prefix to apply to all elements in 
     * $path; defaults to Zend_View_Helper
     * @return Zend_View_Abstract
     */
    public function setHelperPath($path, $classPrefix = 'Zend_View_Helper_')
    {
        if (!empty($classPrefix) && ('_' != substr($classPrefix, -1))) {
            $classPrefix .= '_';
        }

        $this->_setPath('helper', $path, $classPrefix);
        return $this;
    }

    /**
     * Get full path to a helper class file specified by $name
     * 
     * @param  string $name 
     * @return string|false False on failure, path on success
     */
    public function getHelperPath($name)
    {
        if (isset($this->_helperLoadedDir[$name])) {
            return $this->_helperLoadedDir[$name];
        }

        try {
            $this->_loadClass('helper', $name);
            return $this->_helperLoadedDir[$name];
        } catch (Zend_View_Exception $e) {
            return false;
        }
    }

    /**
     * Returns an array of all currently set helper paths
     * 
     * @return array
     */
    public function getHelperPaths()
    {
        return $this->_getPaths('helper');
    }

    /**
     * Get a helper by name
     * 
     * @param  string $name 
     * @return object
     */
    public function getHelper($name)
    {
        if (!isset($this->_helper[$name])) {
            $class = $this->_loadClass('helper', $name);
            $this->_helper[$name] = new $class();
            if (method_exists($this->_helper[$name], 'setView')) {
                $this->_helper[$name]->setView($this);
            }
        }

        return $this->_helper[$name];
    }

    /**
     * Get array of all active helpers
     *
     * Only returns those that have already been instantiated.
     * 
     * @return array
     */
    public function getHelpers()
    {
        return $this->_helper;
    }

    /**
     * Adds to the stack of filter paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @param string $classPrefix Class prefix to use with classes in this 
     * directory; defaults to Zend_View_Filter
     * @return Zend_View_Abstract
     */
    public function addFilterPath($path, $classPrefix = 'Zend_View_Filter_')
    {
        if (!empty($classPrefix) && ('_' != substr($classPrefix, -1))) {
            $classPrefix .= '_';
        }

        $this->_addPath('filter', $path, $classPrefix);
        return $this;
    }

    /**
     * Resets the stack of filter paths.
     *
     * To clear all paths, use Zend_View::setFilterPath(null).
     *
     * @param string|array The directory (-ies) to set as the path.
     * @param string $classPrefix The class prefix to apply to all elements in 
     * $path; defaults to Zend_View_Filter
     * @return Zend_View_Abstract
     */
    public function setFilterPath($path, $classPrefix = 'Zend_View_Filter_')
    {
        if (!empty($classPrefix) && ('_' != substr($classPrefix, -1))) {
            $classPrefix .= '_';
        }

        $this->_setPath('filter', $path, $classPrefix);
        return $this;
    }

    /**
     * Get full path to a filter class file specified by $name
     * 
     * @param  string $name 
     * @return string|false False on failure, path on success
     */
    public function getFilterPath($name)
    {
        if (isset($this->_filterLoadedDir[$name])) {
            return $this->_filterLoadedDir[$name];
        }

        try {
            $this->_loadClass('filter', $name);
            return $this->_filterLoadedDir[$name];
        } catch (Zend_View_Exception $e) {
            return false;
        }
    }

    /**
     * Get a filter object by name
     * 
     * @param  string $name 
     * @return object
     */
    public function getFilter($name)
    {
        if (!isset($this->_filterClass[$name])) {
            $class = $this->_loadClass('filter', $name);
            $this->_filterClass[$name] = new $class();
            if (method_exists($this->_filterClass[$name], 'setView')) {
                $this->_filterClass[$name]->setView($this);
            }
        }

        return $this->_filterClass[$name];
    }

    /**
     * Return array of all currently active filters
     *
     * Only returns those that have already been instantiated.
     * 
     * @return array
     */
    public function getFilters()
    {
        return $this->_filter;
    }

    /**
     * Returns an array of all currently set filter paths
     * 
     * @return array
     */
    public function getFilterPaths()
    {
        return $this->_getPaths('filter');
    }

    /**
     * Return associative array of path types => paths
     * 
     * @return array
     */
    public function getAllPaths()
    {
        return $this->_path;
    }

    /**
     * Add one or more filters to the stack in FIFO order.
     *
     * @param string|array One or more filters to add.
     * @return Zend_View_Abstract
     */
    public function addFilter($name)
    {
        foreach ((array) $name as $val) {
            $this->_filter[] = $val;
        }
        return $this;
    }

    /**
     * Resets the filter stack.
     *
     * To clear all filters, use Zend_View::setFilter(null).
     *
     * @param string|array One or more filters to set.
     * @return Zend_View_Abstract
     */
    public function setFilter($name)
    {
        $this->_filter = array();
        $this->addFilter($name);
        return $this;
    }

    /**
     * Sets the _escape() callback.
     *
     * @param mixed $spec The callback for _escape() to use.
     * @return Zend_View_Abstract
     */
    public function setEscape($spec)
    {
        $this->_escape = $spec;
        return $this;
    }

    /**
     * Assigns variables to the view script via differing strategies.
     *
     * Zend_View::assign('name', $value) assigns a variable called 'name'
     * with the corresponding $value.
     *
     * Zend_View::assign($array) assigns the array keys as variable
     * names (with the corresponding array values).
     *
     * @see    __set()
     * @param  string|array The assignment strategy to use.
     * @param  mixed (Optional) If assigning a named variable, use this
     * as the value.
     * @return Zend_View_Abstract Fluent interface
     * @throws Zend_View_Exception if $spec is neither a string nor an array, 
     * or if an attempt to set a private or protected member is detected
     */
    public function assign($spec, $value = null)
    {
        // which strategy to use?
        if (is_string($spec)) {
            // assign by name and value
            if ('_' == substr($spec, 0, 1)) {
                require_once 'Zend/View/Exception.php';
                throw new Zend_View_Exception('Setting private or protected class members is not allowed', $this);
            }
            $this->$spec = $value;
        } elseif (is_array($spec)) {
            // assign from associative array
            $error = false;
            foreach ($spec as $key => $val) {
                if ('_' == substr($key, 0, 1)) {
                    $error = true;
                    break;
                }
                $this->$key = $val;
            }
            if ($error) {
                require_once 'Zend/View/Exception.php';
                throw new Zend_View_Exception('Setting private or protected class members is not allowed', $this);
            }
        } else {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('assign() expects a string or array, received ' . gettype($spec), $this);
        }

        return $this;
    }

    /**
     * Return list of all assigned variables
     *
     * Returns all public properties of the object. Reflection is not used 
     * here as testing reflection properties for visibility is buggy.
     * 
     * @return array
     */
    public function getVars()
    {
        $vars   = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ('_' == substr($key, 0, 1)) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or 
     * property overloading ({@link __set()}).
     * 
     * @return void
     */
    public function clearVars()
    {
        $vars   = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ('_' != substr($key, 0, 1)) {
                unset($this->$key);
            }
        }
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        // find the script file name using the parent private method
        $this->_file = $this->_script($name);
        unset($name); // remove $name from local scope

        ob_start();
        $this->_run($this->_file); 

        return $this->_filter(ob_get_clean()); // filter output
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses 
     * {@link $_encoding} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
            return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_encoding);
        }

        return call_user_func($this->_escape, $var);
    }

    /**
     * Set encoding to use with htmlentities() and htmlspecialchars()
     * 
     * @param string $encoding 
     * @return Zend_View_Abstract
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Return current escape encoding
     * 
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Enable or disable strict vars
     *
     * If strict variables are enabled, {@link __get()} will raise a notice 
     * when a variable is not defined.
     *
     * Use in conjunction with {@link Zend_View_Helper_DeclareVars the declareVars() helper}
     * to enforce strict variable handling in your view scripts.
     * 
     * @param  boolean $flag 
     * @return Zend_View_Abstract
     */
    public function strictVars($flag = true)
    {
        $this->_strictVars = ($flag) ? true : false;

        return $this;
    }

    /**
     * Finds a view script from the available directories.
     *
     * @param $name string The base name of the script.
     * @return void
     */
    protected function _script($name)
    {
        if (0 == count($this->_path['script'])) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('no view script directory set; unable to determine location for view script',
                $this);
        }

        foreach ($this->_path['script'] as $dir) {
            if (is_readable($dir . $name)) {
                return $dir . $name;
            }
        }

        require_once 'Zend/View/Exception.php';
        $message = "script '$name' not found in path (" 
                 . implode(PATH_SEPARATOR, $this->_path['script'])
                 . ")";
        throw new Zend_View_Exception($message, $this);
    }

    /**
     * Applies the filter callback to a buffer.
     *
     * @param string $buffer The buffer contents.
     * @return string The filtered buffer.
     */
    private function _filter($buffer)
    {
        // loop through each filter class
        foreach ($this->_filter as $name) {
            // load and apply the filter class
            $filter = $this->getFilter($name);
            $buffer = call_user_func(array($filter, 'filter'), $buffer);
        }

        // done!
        return $buffer;
    }

    /**
     * Adds paths to the path stack in LIFO order.
     *
     * Zend_View::_addPath($type, 'dirname') adds one directory
     * to the path stack.
     *
     * Zend_View::_addPath($type, $array) adds one directory for
     * each array element value.
     *
     * In the case of filter and helper paths, $prefix should be used to 
     * specify what class prefix to use with the given path.
     *
     * @param string $type The path type ('script', 'helper', or 'filter').
     * @param string|array $path The path specification.
     * @param string $prefix Class prefix to use with path (helpers and filters 
     * only)
     * @return void
     */
    private function _addPath($type, $path, $prefix = null)
    {
        foreach ((array) $path as $dir) {
            // attempt to strip any possible separator and
            // append the system directory separator
            $dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $dir);
            $dir = rtrim($dir, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR) 
                 . DIRECTORY_SEPARATOR;
            
            switch ($type) {
                case 'script':
                    // add to the top of the stack.
                    array_unshift($this->_path[$type], $dir);
                    break;
                case 'filter':
                case 'helper':
                default:
                    // add as array with prefix and dir keys
                    array_unshift($this->_path[$type], array('prefix' => $prefix, 'dir' => $dir));
                    break;
            }
        }
    }

    /**
     * Resets the path stack for helpers and filters.
     *
     * @param string $type The path type ('helper' or 'filter').
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix Class prefix to apply to elements of $path
     */
    private function _setPath($type, $path, $classPrefix = null)
    {
        $dir = DIRECTORY_SEPARATOR . ucfirst($type) . DIRECTORY_SEPARATOR;

        switch ($type) {
            case 'script':
                $this->_path[$type] = array(dirname(__FILE__) . $dir);
                $this->_addPath($type, $path);
                break;
            case 'filter':
            case 'helper':
            default:
                $this->_path[$type] = array(array(
                    'prefix' => 'Zend_View_' . ucfirst($type) . '_',
                    'dir'    => dirname(__FILE__) . $dir
                ));
                $this->_addPath($type, $path, $classPrefix);
                break;
        }
    }

    /**
     * Return all paths for a given path type
     * 
     * @param string $type The path type  ('helper', 'filter', 'script')
     * @return array
     */
    private function _getPaths($type)
    {
        return $this->_path[$type];
    }

    /**
     * Loads a helper or filter class.
     *
     * @param  string $type The class type ('helper' or 'filter').
     * @param  string $name The base name.
     * @param  string The full class name.
     * @return string class name loaded
     * @throws Zend_View_Exception if unable to load class
     */
    private function _loadClass($type, $name)
    {
        // check to see if name => class mapping exists for helper/filter
        $classLoaded = '_' . $type . 'Loaded';
        $classAccess = '_set' . ucfirst($type) . 'Class';
        if (isset($this->$classLoaded[$name])) {
            return $this->$classLoaded[$name];
        }

        // only look for "$Name.php"
        $file = ucfirst($name) . '.php';

        // do LIFO search for helper
        foreach ($this->_path[$type] as $info) {
            $dir    = $info['dir'];
            $prefix = $info['prefix'];

            $class = $prefix . ucfirst($name);
            
            if (class_exists($class, false)) {
                $reflection = new ReflectionClass($class);
                $file = $reflection->getFileName();
                $this->$classAccess($name, $class, $file);
                return $class;
            } elseif (Zend_Loader::isReadable($dir . $file)) {
                include_once $dir . $file;

                if (class_exists($class, false)) {
                    $this->$classAccess($name, $class, $dir . $file);
                    return $class;
                }
            }
        }

        require_once 'Zend/View/Exception.php';
        throw new Zend_View_Exception("$type '$name' not found in path", $this);
    }

    /**
     * Register helper class as loaded
     * 
     * @param  string $name 
     * @param  string $class 
     * @param  string $file path to class file
     * @return void
     */
    private function _setHelperClass($name, $class, $file)
    {
        $this->_helperLoadedDir[$name] = $file;
        $this->_helperLoaded[$name]    = $class;
    }

    /**
     * Register filter class as loaded
     * 
     * @param  string $name 
     * @param  string $class 
     * @param  string $file path to class file
     * @return void
     */
    private function _setFilterClass($name, $class, $file)
    {
        $this->_filterLoadedDir[$name] = $file;
        $this->_filterLoaded[$name]    = $class;
    }

    /**
     * Use to include the view script in a scope that only allows public 
     * members.
     * 
     * @return mixed
     */
    abstract protected function _run();
}
