<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Composer;

/**
 * Scripts executed before/after Composer install and update.
 *
 * We use this PHP class because setting the bash scripts directly in composer.json breaks
 * Composer on Windows systems.
 */
class ScriptHandler
{
    private static function isPhp7orLater()
    {
        return version_compare('7.0.0-dev', PHP_VERSION) < 1;
    }

    public static function cleanXhprof()
    {
        if (! is_dir('vendor/facebook/xhprof/extension')) {
            return;
        }

        if (!self::isPhp7orLater()) {
            // doesn't work with PHP 7 at the moment
            passthru('misc/composer/clean-xhprof.sh');
        }
    }

    public static function buildXhprof()
    {
        if (! is_dir('vendor/facebook/xhprof/extension')) {
            return;
        }


        if (!self::isPhp7orLater()) {
            passthru('misc/composer/clean-xhprof.sh');
        }
    }
}
