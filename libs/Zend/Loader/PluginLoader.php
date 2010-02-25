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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PluginLoader.php 21170 2010-02-23 19:50:16Z matthew $
 */

/** Zend_Loader_PluginLoader_Interface */
require_once 'Zend/Loader/PluginLoader/Interface.php';

/** Zend_Loader */
require_once 'Zend/Loader.php';

/**
 * Generic plugin class loader
 *
 * @category   Zend
 * @package    Zend_Loader
 * @subpackage PluginLoader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Loader_PluginLoader implements Zend_Loader_PluginLoader_Interface
{
    /**
     * Class map cache file
     * @var string
     */
    protected static $_includeFileCache;

    /**
     * Instance loaded plugin paths
     *
     * @var array
     */
    protected $_loadedPluginPaths = array();

    /**
     * Instance loaded plugins
     *
     * @var array
     */
    protected $_loadedPlugins = array();

    /**
     * Instance registry property
     *
     * @var array
     */
    protected $_prefixToPaths = array();

    /**
     * Statically loaded plugin path mappings
     *
     * @var array
     */
    protected static $_staticLoadedPluginPaths = array();

    /**
     * Statically loaded plugins
     *
     * @var array
     */
    protected static $_staticLoadedPlugins = array();

    /**
     * Static registry property
     *
     * @var array
     */
    protected static $_staticPrefixToPaths = array();

    /**
     * Whether to use a statically named registry for loading plugins
     *
     * @var string|null
     */
    protected $_useStaticRegistry = null;

    /**
     * Constructor
     *
     * @param array $prefixToPaths
     * @param string $staticRegistryName OPTIONAL
     */
    public function __construct(Array $prefixToPaths = array(), $staticRegistryName = null)
    {
        if (is_string($staticRegistryName) && !empty($staticRegistryName)) {
            $this->_useStaticRegistry = $staticRegistryName;
            if(!isset(self::$_staticPrefixToPaths[$staticRegistryName])) {
                self::$_staticPrefixToPaths[$staticRegistryName] = array();
            }
            if(!isset(self::$_staticLoadedPlugins[$staticRegistryName])) {
                self::$_staticLoadedPlugins[$staticRegistryName] = array();
            }
        }

        foreach ($prefixToPaths as $prefix => $path) {
            $this->addPrefixPath($prefix, $path);
        }
    }

    /**
     * Format prefix for internal use
     *
     * @param  string $prefix
     * @return string
     */
    protected function _formatPrefix($prefix)
    {
        if($prefix == "") {
            return $prefix;
        }

        $last = strlen($prefix) - 1;
        if ($prefix{$last} == '\\') {
            return $prefix;
        }

        return rtrim($prefix, '_') . '_';
    }

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return Zend_Loader_PluginLoader
     */
    public function addPrefixPath($prefix, $path)
    {
        if (!is_string($prefix) || !is_string($path)) {
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception('Zend_Loader_PluginLoader::addPrefixPath() method only takes strings for prefix and path.');
        }

        $prefix = $this->_formatPrefix($prefix);
        $path   = rtrim($path, '/\\') . '/';

        if ($this->_useStaticRegistry) {
            self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix][] = $path;
        } else {
            if (!isset($this->_prefixToPaths[$prefix])) {
                $this->_prefixToPaths[$prefix] = array();
            }
            if (!in_array($path, $this->_prefixToPaths[$prefix])) {
                $this->_prefixToPaths[$prefix][] = $path;
            }
        }
        return $this;
    }

    /**
     * Get path stack
     *
     * @param  string $prefix
     * @return false|array False if prefix does not exist, array otherwise
     */
    public function getPaths($prefix = null)
    {
        if ((null !== $prefix) && is_string($prefix)) {
            $prefix = $this->_formatPrefix($prefix);
            if ($this->_useStaticRegistry) {
                if (isset(self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix])) {
                    return self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix];
                }

                return false;
            }

            if (isset($this->_prefixToPaths[$prefix])) {
                return $this->_prefixToPaths[$prefix];
            }

            return false;
        }

        if ($this->_useStaticRegistry) {
            return self::$_staticPrefixToPaths[$this->_useStaticRegistry];
        }

        return $this->_prefixToPaths;
    }

    /**
     * Clear path stack
     *
     * @param  string $prefix
     * @return bool False only if $prefix does not exist
     */
    public function clearPaths($prefix = null)
    {
        if ((null !== $prefix) && is_string($prefix)) {
            $prefix = $this->_formatPrefix($prefix);
            if ($this->_useStaticRegistry) {
                if (isset(self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix])) {
                    unset(self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix]);
                    return true;
                }

                return false;
            }

            if (isset($this->_prefixToPaths[$prefix])) {
                unset($this->_prefixToPaths[$prefix]);
                return true;
            }

            return false;
        }

        if ($this->_useStaticRegistry) {
            self::$_staticPrefixToPaths[$this->_useStaticRegistry] = array();
        } else {
            $this->_prefixToPaths = array();
        }

        return true;
    }

    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return Zend_Loader_PluginLoader
     */
    public function removePrefixPath($prefix, $path = null)
    {
        $prefix = $this->_formatPrefix($prefix);
        if ($this->_useStaticRegistry) {
            $registry =& self::$_staticPrefixToPaths[$this->_useStaticRegistry];
        } else {
            $registry =& $this->_prefixToPaths;
        }

        if (!isset($registry[$prefix])) {
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception('Prefix ' . $prefix . ' was not found in the PluginLoader.');
        }

        if ($path != null) {
            $pos = array_search($path, $registry[$prefix]);
            if ($pos === null) {
                require_once 'Zend/Loader/PluginLoader/Exception.php';
                throw new Zend_Loader_PluginLoader_Exception('Prefix ' . $prefix . ' / Path ' . $path . ' was not found in the PluginLoader.');
            }
            unset($registry[$prefix][$pos]);
        } else {
            unset($registry[$prefix]);
        }

        return $this;
    }

    /**
     * Normalize plugin name
     *
     * @param  string $name
     * @return string
     */
    protected function _formatName($name)
    {
        return ucfirst((string) $name);
    }

    /**
     * Whether or not a Plugin by a specific name is loaded
     *
     * @param string $name
     * @return Zend_Loader_PluginLoader
     */
    public function isLoaded($name)
    {
        $name = $this->_formatName($name);
        if ($this->_useStaticRegistry) {
            return isset(self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name]);
        }

        return isset($this->_loadedPlugins[$name]);
    }

    /**
     * Return full class name for a named plugin
     *
     * @param string $name
     * @return string|false False if class not found, class name otherwise
     */
    public function getClassName($name)
    {
        $name = $this->_formatName($name);
        if ($this->_useStaticRegistry
            && isset(self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name])
        ) {
            return self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name];
        } elseif (isset($this->_loadedPlugins[$name])) {
            return $this->_loadedPlugins[$name];
        }

        return false;
    }

    /**
     * Get path to plugin class
     *
     * @param  mixed $name
     * @return string|false False if not found
     */
    public function getClassPath($name)
    {
        $name = $this->_formatName($name);
        if ($this->_useStaticRegistry
            && !empty(self::$_staticLoadedPluginPaths[$this->_useStaticRegistry][$name])
        ) {
            return self::$_staticLoadedPluginPaths[$this->_useStaticRegistry][$name];
        } elseif (!empty($this->_loadedPluginPaths[$name])) {
            return $this->_loadedPluginPaths[$name];
        }

        if ($this->isLoaded($name)) {
            $class = $this->getClassName($name);
            $r     = new ReflectionClass($class);
            $path  = $r->getFileName();
            if ($this->_useStaticRegistry) {
                self::$_staticLoadedPluginPaths[$this->_useStaticRegistry][$name] = $path;
            } else {
                $this->_loadedPluginPaths[$name] = $path;
            }
            return $path;
        }

        return false;
    }

    /**
     * Load a plugin via the name provided
     *
     * @param  string $name
     * @param  bool $throwExceptions Whether or not to throw exceptions if the
     * class is not resolved
     * @return string|false Class name of loaded class; false if $throwExceptions
     * if false and no class found
     * @throws Zend_Loader_Exception if class not found
     */
    public function load($name, $throwExceptions = true)
    {
        $name = $this->_formatName($name);
        if ($this->isLoaded($name)) {
            return $this->getClassName($name);
        }

        if ($this->_useStaticRegistry) {
            $registry = self::$_staticPrefixToPaths[$this->_useStaticRegistry];
        } else {
            $registry = $this->_prefixToPaths;
        }

        $registry  = array_reverse($registry, true);
        $found     = false;
        $classFile = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
        $incFile   = self::getIncludeFileCache();
        foreach ($registry as $prefix => $paths) {
            $className = $prefix . $name;

            if (class_exists($className, false)) {
                $found = true;
                break;
            }

            $paths     = array_reverse($paths, true);

            foreach ($paths as $path) {
                $loadFile = $path . $classFile;
                if (Zend_Loader::isReadable($loadFile)) {
                    include_once $loadFile;
                    if (class_exists($className, false)) {
                        if (null !== $incFile) {
                            self::_appendIncFile($loadFile);
                        }
                        $found = true;
                        break 2;
                    }
                }
            }
        }

        if (!$found) {
            if (!$throwExceptions) {
                return false;
            }

            $message = "Plugin by name '$name' was not found in the registry; used paths:";
            foreach ($registry as $prefix => $paths) {
                $message .= "\n$prefix: " . implode(PATH_SEPARATOR, $paths);
            }
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception($message);
       }

        if ($this->_useStaticRegistry) {
            self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name]     = $className;
        } else {
            $this->_loadedPlugins[$name]     = $className;
        }
        return $className;
    }

    /**
     * Set path to class file cache
     *
     * Specify a path to a file that will add include_once statements for each
     * plugin class loaded. This is an opt-in feature for performance purposes.
     *
     * @param  string $file
     * @return void
     * @throws Zend_Loader_PluginLoader_Exception if file is not writeable or path does not exist
     */
    public static function setIncludeFileCache($file)
    {
        if (null === $file) {
            self::$_includeFileCache = null;
            return;
        }

        if (!file_exists($file) && !file_exists(dirname($file))) {
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception('Specified file does not exist and/or directory does not exist (' . $file . ')');
        }
        if (file_exists($file) && !is_writable($file)) {
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception('Specified file is not writeable (' . $file . ')');
        }
        if (!file_exists($file) && file_exists(dirname($file)) && !is_writable(dirname($file))) {
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception('Specified file is not writeable (' . $file . ')');
        }

        self::$_includeFileCache = $file;
    }

    /**
     * Retrieve class file cache path
     *
     * @return string|null
     */
    public static function getIncludeFileCache()
    {
        return self::$_includeFileCache;
    }

    /**
     * Append an include_once statement to the class file cache
     *
     * @param  string $incFile
     * @return void
     */
    protected static function _appendIncFile($incFile)
    {
        if (!file_exists(self::$_includeFileCache)) {
            $file = '<?php';
        } else {
            $file = file_get_contents(self::$_includeFileCache);
        }
        if (!strstr($file, $incFile)) {
            $file .= "\ninclude_once '$incFile';";
            file_put_contents(self::$_includeFileCache, $file);
        }
    }
}
