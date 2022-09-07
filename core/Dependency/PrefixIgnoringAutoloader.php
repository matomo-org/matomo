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

class PrefixIgnoringAutoloader
{
    const PREFIX = 'Matomo\\Dependencies\\';

    public static function register()
    {
        $wrappedLoader = new PrefixIgnoringAutoloader();
        spl_autoload_register([$wrappedLoader, 'loadClass'], true, $prepend = true);
    }

    public function loadClass($name)
    {
        $name = ltrim($name, '\\');
        if (substr($name, 0, strlen(self::PREFIX)) === self::PREFIX) {
            class_alias(substr($name, strlen(self::PREFIX)), $name);
            return true;
        }
    }
}