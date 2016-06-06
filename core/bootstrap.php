<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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

if (!defined('PIWIK_VENDOR_PATH')) {
	if (is_dir(PIWIK_INCLUDE_PATH . '/vendor')) {
		define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/vendor'); // Piwik is the main project
	} else {
		define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/../..'); // Piwik is installed as a Composer dependency
	}
}

// NOTE: the code above must be PHP 4 compatible
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';

session_cache_limiter('nocache');
@date_default_timezone_set('UTC');

disableEaccelerator();

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';

// Composer autoloader
require_once PIWIK_VENDOR_PATH . '/autoload.php';

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
