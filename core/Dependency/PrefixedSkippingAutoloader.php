<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Bootstraps the Piwik application.
 *
 * This file cannot be a class because it needs to be compatible with PHP 4.
 */

namespace Piwik\Dependency;

/**
 * Special autoloader that wraps a composer generated autoloader and makes sure that
 * any dependencies that we've prefixed with the development:prefix-dependency
 * command, are not loaded by the composer autoloader.
 */
class PrefixedSkippingAutoloader
{
    private $originalLoader;

    public function __construct($originalLoader)
    {
        $this->originalLoader = $originalLoader;
    }

    public static function register($originalLoader)
    {
        $wrappedLoader = new PrefixedSkippingAutoloader($originalLoader);

        $originalLoader->unregister();

        spl_autoload_register([$wrappedLoader, 'loadClass'], true, $prepend = true);
    }

    public function loadClass($class)
    {
        $filePath = $this->originalLoader->findFile($class);
        if (!$filePath
            || $this->isFileForPrefixedDependency($filePath)
        ) {
            return false;
        }

        prefixedSkippingIncludeFile($filePath);
        return true;
    }

    private function isFileForPrefixedDependency($filePath)
    {
        if (strpos($filePath, PIWIK_VENDOR_PATH) !== 0
            || strpos($filePath, '..') !== false
        ) {
            return false;
        }

        $dependency = ltrim(substr($filePath, strlen(PIWIK_VENDOR_PATH)), '/');

        $parts = explode('/', $dependency, 3);
        if (count($parts) > 2) {
            array_pop($parts);
        }
        $dependency = implode('/', $parts);

        $isPrefixedDependency = is_dir(PIWIK_VENDOR_PATH . '/prefixed/' . $dependency);
        return $isPrefixedDependency;
    }
}

// prevent access to $this in loaded file
function prefixedSkippingIncludeFile($file)
{
    include $file;
}
