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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Static methods for loading classes and files.
 *
 * @category   Zend
 * @package    Zend_Loader
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
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
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Directory argument must be a string or an array');
        }
        if (null === $dirs) {
            $dirs = array();
        }
        if (is_string($dirs)) {
            $dirs = (array) $dirs;
        }

        // autodiscover the path from the class name
        $path = str_replace('_', DIRECTORY_SEPARATOR, $class);
        if ($path != $class) {
            // use the autodiscovered path
            $dirPath = dirname($path);
            if (0 == count($dirs)) {
                $dirs = array($dirPath);
            } else {
                foreach ($dirs as $key => $dir) {
                    if ($dir == '.') {
                        $dirs[$key] = $dirPath;
                    } else {
                        $dir = rtrim($dir, '\\/');
                        $dirs[$key] = $dir . DIRECTORY_SEPARATOR . $dirPath;
                    }
                }
            }
            $file = basename($path) . '.php';
        } else {
            $file = $class . '.php';
        }

        self::loadFile($file, $dirs, true);

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("File \"$file\" was loaded but class \"$class\" was not found in the file");
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
        /**
         * Security check
         */
        if (preg_match('/[^a-z0-9\-_.]/i', $filename)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Security check: Illegal character in filename');
        }

        /**
         * Search for the file in each of the dirs named in $dirs.
         */
        if (is_null($dirs)) {
            $dirs = array();
        } elseif (is_string($dirs))  {
            $dirs = explode(PATH_SEPARATOR, $dirs);
        }
        foreach ($dirs as $dir) {
            $filespec = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR . $filename;
            if (self::isReadable($filespec)) {
                return self::_includeFile($filespec, $once);
            }
        }

        /**
         * The file was not found in the $dirs specified.
         * Try finding for the plain filename in the include_path.
         */
        if (self::isReadable($filename)) {
            return self::_includeFile($filename, $once);
        }

        /**
         * The file was not located anywhere.
         */
        require_once 'Zend/Exception.php';
        throw new Zend_Exception("File \"$filename\" was not found");
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
     */
    protected static function _includeFile($filespec, $once = false)
    {
        if ($once) {
            return include_once $filespec;
        } else {
            return include $filespec ;
        }
    }

    /**
     * Returns TRUE if the $filename is readable, or FALSE otherwise.
     * This function uses the PHP include_path, where PHP's is_readable()
     * does not.
     *
     * @param string   $filename
     * @return boolean
     */
    public static function isReadable($filename)
    {
        if (@is_readable($filename)) {
            return true;
        }

        $path = get_include_path();
        $dirs = explode(PATH_SEPARATOR, $path);

        foreach ($dirs as $dir) {
            // No need to check against current dir -- already checked
            if ('.' == $dir) {
                continue;
            }

            if (@is_readable($dir . DIRECTORY_SEPARATOR . $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * spl_autoload() suitable implementation for supporting class autoloading.
     *
     * Attach to spl_autoload() using the following:
     * <code>
     * spl_autoload_register(array('Zend_Loader', 'autoload'));
     * </code>
     * 
     * @param string $class 
     * @return string|false Class name on success; false on failure
     */
    public static function autoload($class)
    {
        try {
            self::loadClass($class);
            return $class;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Register {@link autoload()} with spl_autoload()
     * 
     * @param string OPTIONAL $class
     * @return void
     * @throws Zend_Exception if spl_autoload() is not found
     * or if the specified class does not have an autoload() method.
     */
    public static function registerAutoload($class = 'Zend_Loader')
    {
        if (!function_exists('spl_autoload_register')) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('spl_autoload does not exist in this PHP installation');
        }

        self::loadClass($class);
        $methods = get_class_methods($class);
        if (!in_array('autoload', (array) $methods)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("The class \"$class\" does not have an autoload() method");
        }

        spl_autoload_register(array($class, 'autoload'));
    }
}
