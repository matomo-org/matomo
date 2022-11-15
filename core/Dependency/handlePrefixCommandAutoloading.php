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

// NOTE: this file should have no dependencies other than PrefixRemovingAutoloader

function handlePrefixCommandAutoloading()
{
    global $argv;

    if (empty($argv)) {
        return;
    }

    $commandName = null;
    foreach ($argv as $arg) {
        if (!preg_match('/^--/', $arg) && strpos(':', $arg) !== -1) {
            $commandName = $arg;
        }
    }

    // dependencies may not be prefixed yet, so we want to make sure they can still be loaded during this command
    if ($commandName == 'composer:prefix-dependency') {
        $isForPlugin = false;
        foreach ($argv as $arg) {
            if (strpos($arg, '--plugin') !== false) {
                $isForPlugin = true;
                break;
            }
        }

        if (!$isForPlugin) { // running as core, core dependencies not prefixed
            new \Piwik\Dependency\PrefixRemovingAutoloader();
        }
    }
}
