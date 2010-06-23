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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Loader.php 22020 2010-04-27 16:35:56Z matthew $
 */

/**
 * Static methods for loading classes and files.
 *
 * @category   Zend
 * @package    Zend_Loader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Loader
{
    /**
     * Loads a class from a PHP file.  The filename must be formatted
     * as "$class.php".
     *
     * If $dirs is a string or an array, it will search the directories
     * in the order supplied, and attempt to load the first matching file.
     *
     * If $dirs is null, it will split the class name at underscores to
     * generate a path hierarchy (e.g., "Zend_Example_Class" will map
     * to "Zend/Example/Class.php").
     *
     * If the file was not found in the $dirs, or if no $dirs were specified,
     * it will attempt to load it from PHP's include_path.
     *
     * @param string $class      - The full class name of a Zend component.
     * @param string|array $dirs - OPTIONAL Either a path or an array of paths
     *                             to search.
     * @return void
     * @throws Zend_Exception
     */
    public static function loadClass($class, $dirs = null)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }

        if ((null !== $dirs) && !is_string($dirs) && !is_array($dirs)) {
            // require_once 'Zend/Exception.php';
            throw new Zend_Exception('Directory argument must be a string or an array');
        }

        // Autodiscover the path from the class name
        // Implementation is PHP namespace-aware, and based on 
        // Framework Interop Group reference implementation:
        // http://groups.google.com/group/php-standards/web/psr-0-final-proposal
        $className = ltrim($class, '\\');
        $file      = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $file      = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $file .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (!empty($dirs)) {
            // use the autodiscovered path
            $dirPath = dirname($file);
            if (is_string($dirs)) {
                $dirs = explode(PATH_SEPARATOR, $dirs);
            }
            foreach ($dirs as $key => $dir) {
                if ($dir == '.') {
                    $dirs[$key] = $dirPath;
                } else {
                    $dir = rtrim($dir, '\\/');
                    $dirs[$key] = $dir . DIRECTORY_SEPARATOR . $dirPath;
                }
            }
            $file = basename($file);
            self::loadFile($file, $dirs, true);
        } else {
            self::loadFile($file, null, true);
        }

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            // require_once 'Zend/Exception.php';
            throw new Zend_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
        }
    }

    /**
     * Loads a PHP file.  This is a wrapper for PHP's include() function.
     *
     * $filename must be the complete filename, including any
     * extension such as ".php".  Note that a security check is performed that
     * does not permit extended characters in the filename.  This method is
     * intended for loading Zend Framework files.
     *
     * If $dirs is a string or an array, it will search the directories
     * in the order supplied, and attempt to load the first matching file.
     *
     * If the file was not found in the $dirs, or if no $dirs were specified,
     * it will attempt to load it from PHP's include_path.
     *
     * If $once is TRUE, it will use include_once() instead of include().
     *
     * @param  string        $filename
     * @param  string|array  $dirs - OPTIONAL either a path or array of paths
     *                       to search.
     * @param  boolean       $once
     * @return boolean
     * @throws Zend_Exception
     */
    public static function loadFile($filename, $dirs = null, $once = false)
    {
        self::_securityCheck($filename);

        /**
         * Search in provided directories, as well as include_path
         */
        $incPath = false;
        if (!empty($dirs) && (is_array($dirs) || is_string($dirs))) {
            if (is_array($dirs)) {
                $dirs = implode(PATH_SEPARATOR, $dirs);
            }
            $incPath = get_include_path();
            set_include_path($dirs . PATH_SEPARATOR . $incPath);
        }

        /**
         * Try finding for the plain filename in the include_path.
         */
        if ($once) {
            include_once $filename;
        } else {
            include $filename;
        }

        /**
         * If searching in directories, reset include_path
         */
        if ($incPath) {
            set_include_path($incPath);
        }

        return true;
    }

    /**
     * Returns TRUE if the $filename is readable, or FALSE otherwise.
     * This function uses the PHP include_path, where PHP's is_readable()
     * does not.
     *
     * Note from ZF-2900:
     * If you use custom error handler, please check whether return value
     *  from error_reporting() is zero or not.
     * At mark of fopen() can not suppress warning if the handler is used.
     *
     * @param string   $filename
     * @return boolean
     */
    public static function isReadable($filename)
    {
        if (is_readable($filename)) {
            // Return early if the filename is readable without needing the 
            // include_path
            return true;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'
            && preg_match('/^[a-z]:/i', $filename)
        ) {
            // If on windows, and path provided is clearly an absolute path, 
            // return false immediately
            return false;
        }

        foreach (self::explodeIncludePath() as $path) {
            if ($path == '.') {
                if (is_readable($filename)) {
                    return true;
                }
                continue;
            }
            $file = $path . '/' . $filename;
            if (is_readable($file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Explode an include path into an array
     *
     * If no path provided, uses current include_path. Works around issues that
     * occur when the path includes stream schemas.
     * 
     * @param  string|null $path 
     * @return array
     */
    public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream 
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }

    /**
     * spl_autoload() suitable implementation for supporting class autoloading.
     *
     * Attach to spl_autoload() using the following:
     * <code>
     * spl_autoload_register(array('Zend_Loader', 'autoload'));
     * </code>
     *
     * @deprecated Since 1.8.0
     * @param  string $class
     * @return string|false Class name on success; false on failure
     */
    public static function autoload($class)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated as of 1.8.0 and will be removed with 2.0.0; use Zend_Loader_Autoloader instead', E_USER_NOTICE);
        try {
            @self::loadClass($class);
            return $class;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Register {@link autoload()} with spl_autoload()
     *
     * @deprecated Since 1.8.0
     * @param string $class (optional)
     * @param boolean $enabled (optional)
     * @return void
     * @throws Zend_Exception if spl_autoload() is not found
     * or if the specified class does not have an autoload() method.
     */
    public static function registerAutoload($class = 'Zend_Loader', $enabled = true)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated as of 1.8.0 and will be removed with 2.0.0; use Zend_Loader_Autoloader instead', E_USER_NOTICE);
        // require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);

        if ('Zend_Loader' != $class) {
            self::loadClass($class);
            $methods = get_class_methods($class);
            if (!in_array('autoload', (array) $methods)) {
                // require_once 'Zend/Exception.php';
                throw new Zend_Exception("The class \"$class\" does not have an autoload() method");
            }

            $callback = array($class, 'autoload');

            if ($enabled) {
                $autoloader->pushAutoloader($callback);
            } else {
                $autoloader->removeAutoloader($callback);
            }
        }
    }

    /**
     * Ensure that filename does not contain exploits
     *
     * @param  string $filename
     * @return void
     * @throws Zend_Exception
     */
    protected static function _securityCheck($filename)
    {
        /**
         * Security check
         */
        if (preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $filename)) {
            // require_once 'Zend/Exception.php';
            throw new Zend_Exception('Security check: Illegal character in filename');
        }
    }

    /**
     * Attempt to include() the file.
     *
     * include() is not prefixed with the @ operator because if
     * the file is loaded and contains a parse error, execution
     * will halt silently and this is difficult to debug.
     *
     * Always set display_errors = Off on production servers!
     *
     * @param  string  $filespec
     * @param  boolean $once
     * @return boolean
     * @deprecated Since 1.5.0; use loadFile() instead
     */
    protected static function _includeFile($filespec, $once = false)
    {
        if ($once) {
            return include_once $filespec;
        } else {
            return include $filespec ;
        }
    }
}
