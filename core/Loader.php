<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik auto loader
 *
 * @package Piwik
 */
class Piwik_Loader
{
    // our class search path; current directory is intentionally excluded
    protected static $dirs = array('/core/', '/plugins/');

    /**
     * Get class file name
     *
     * @param string $class Class name
     * @return string Class file name
     * @throws Exception if class name is invalid
     */
    protected static function getClassFileName($class)
    {
        if (!preg_match("/^[A-Za-z0-9_]+$/D", $class)) {
            throw new Exception("Invalid class name \"$class\".");
        }

        $class = str_replace('_', '/', $class);

        if ($class == 'Piwik') {
            return $class;
        }

        if (!strncmp($class, 'Piwik/', 6)) {
            return substr($class, 6);
        }

        return $class;
    }

    /**
     * Load class by name
     *
     * @param string $class Class name
     * @throws Exception if class not found
     */
    public static function loadClass($class)
    {
        $classPath = self::getClassFileName($class);
        if ($class == 'Piwik' || !strncmp($class, 'Piwik_', 6)) {
            // Piwik classes are in core/ or plugins/
            do {
                // auto-discover class location
                foreach (self::$dirs as $dir) {
                    $path = PIWIK_INCLUDE_PATH . $dir . $classPath . '.php';
                    if (file_exists($path)) {
                        require_once $path; // prefixed by PIWIK_INCLUDE_PATH
                        if (class_exists($class, false) || interface_exists($class, false)) {
                            return;
                        }
                    }
                }

                // truncate to find file with multiple class definitions
                $lastSlash = strrpos($classPath, '/');
                $classPath = ($lastSlash === false) ? '' : substr($classPath, 0, $lastSlash);
            } while (!empty($classPath));
        } else {
            // non-Piwik classes (e.g., Zend Framework) are in libs/
            $path = PIWIK_INCLUDE_PATH . '/libs/' . $classPath . '.php';
            if (file_exists($path)) {
                require_once $path; // prefixed by PIWIK_INCLUDE_PATH
                if (class_exists($class, false) || interface_exists($class, false)) {
                    return;
                }
            }
        }
        throw new Exception("Class \"$class\" not found.");
    }

    /**
     * Autoloader
     *
     * @param string $class Class name
     */
    public static function autoload($class)
    {
        try {
            self::loadClass($class);
        } catch (Exception $e) {
        }
    }
}

// Note: only one __autoload per PHP instance
if (function_exists('spl_autoload_register')) {
    // use the SPL autoload stack
    spl_autoload_register(array('Piwik_Loader', 'autoload'));

    // preserve any existing __autoload
    if (function_exists('__autoload')) {
        spl_autoload_register('__autoload');
    }
} else {
    function __autoload($class)
    {
        Piwik_Loader::autoload($class);
    }
}
