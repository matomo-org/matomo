<?php
/**
 * Bootstraps the Piwik application.
 */

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}

error_reporting(E_ALL | E_NOTICE);
@ini_set('display_errors', defined('PIWIK_DISPLAY_ERRORS') ? PIWIK_DISPLAY_ERRORS : @ini_get('display_errors'));
@ini_set('xdebug.show_exception_trace', 0);
@ini_set('magic_quotes_runtime', 0);

// NOTE: the code above must be PHP4 compatible
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';

session_cache_limiter('nocache');
@date_default_timezone_set('UTC');

disableEaccelerator();

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';

/**
 * See https://github.com/piwik/piwik/issues/4439#comment:8 and https://github.com/eaccelerator/eaccelerator/issues/12
 *
 * Eaccelerator does not support closures and is known to be not comptabile with Piwik. Therefore we are disabling
 * it automatically. At this point it looks like Eaccelerator is no longer under development and the bug has not
 * been fixed within a year.
 */
function disableEaccelerator()
{
    $isEacceleratorUsed = ini_get('eaccelerator.enable');
    if (!empty($isEacceleratorUsed)) {
        @ini_set('eaccelerator.enable', 0);
    }
}
