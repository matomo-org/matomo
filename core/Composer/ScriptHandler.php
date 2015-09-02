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
    public static function cleanXhprof()
    {
        if (! is_dir('vendor/facebook/xhprof/extension')) {
            return;
        }

        passthru('misc/composer/clean-xhprof.sh');
    }

    public static function buildXhprof()
    {
        if (! is_dir('vendor/facebook/xhprof/extension')) {
            return;
        }

        passthru('misc/composer/build-xhprof.sh');
    }
}
