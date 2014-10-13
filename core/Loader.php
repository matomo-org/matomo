<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

/**
 * Initializes the Composer Autoloader
 * @package Piwik
 */
class Loader
{
    public static function init()
    {
        return self::getLoader();
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    private static function getLoader()
    {
        if (file_exists(PIWIK_INCLUDE_PATH . '/vendor/autoload.php')) {
            $path = PIWIK_INCLUDE_PATH . '/vendor/autoload.php'; // Piwik is the main project
        } else {
            $path = PIWIK_INCLUDE_PATH . '/../../autoload.php'; // Piwik is installed as a dependency
        }

        $loader = require $path;

        return $loader;
    }

    public static function registerTestNamespace()
    {
        $prefix = 'Piwik\\Tests\\';
        $paths  = PIWIK_INCLUDE_PATH . '/tests/PHPUnit';

        $loader = self::getLoader();
        $loader->addPsr4($prefix, $paths, $prepend = false);
    }
}
