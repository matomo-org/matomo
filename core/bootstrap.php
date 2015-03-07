<?php
/**
 * Bootstraps the Piwik application.
 *
 * This file cannot be a class because it needs to be compatible with PHP 4.
 */

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}

error_reporting(E_ALL | E_NOTICE);
@ini_set('display_errors', defined('PIWIK_DISPLAY_ERRORS') ? PIWIK_DISPLAY_ERRORS : @ini_get('display_errors'));
@ini_set('xdebug.show_exception_trace', 0);
@ini_set('magic_quotes_runtime', 0);

// NOTE: the code above must be PHP 4 compatible
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';

session_cache_limiter('nocache');
@date_default_timezone_set('UTC');

disableEaccelerator();

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';

// Composer autoloader
if (file_exists(PIWIK_INCLUDE_PATH . '/vendor/autoload.php')) {
    $path = PIWIK_INCLUDE_PATH . '/vendor/autoload.php'; // Piwik is the main project
} else {
    $path = PIWIK_INCLUDE_PATH . '/../../autoload.php'; // Piwik is installed as a dependency
}
require_once $path;

/**
 * Eaccelerator does not support closures and is known to be not comptabile with Piwik. Therefore we are disabling
 * it automatically. At this point it looks like Eaccelerator is no longer under development and the bug has not
 * been fixed within a year.
 *
 * @link https://github.com/piwik/piwik/issues/4439#comment:8
 * @link https://github.com/eaccelerator/eaccelerator/issues/12
 */
function disableEaccelerator()
{
    $isEacceleratorUsed = ini_get('eaccelerator.enable');
    if (!empty($isEacceleratorUsed)) {
        @ini_set('eaccelerator.enable', 0);
    }
}
