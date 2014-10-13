<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @package Piwik
 */

if(!defined('PIWIK_DOCUMENT_ROOT')) {
    define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__) == '/' ? '' : dirname(__FILE__));
}
if (file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php')) {
    require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}

error_reporting(E_ALL | E_NOTICE);
@ini_set('display_errors', defined('PIWIK_DISPLAY_ERRORS') ? PIWIK_DISPLAY_ERRORS : @ini_get('display_errors'));
@ini_set('xdebug.show_exception_trace', 0);
@ini_set('magic_quotes_runtime', 0);

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';

// NOTE: the code above this comment must be PHP4 compatible

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';

session_cache_limiter('nocache');
@date_default_timezone_set('UTC');

require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
\Piwik\Loader::init();

if(!defined('PIWIK_PRINT_ERROR_BACKTRACE')) {
    define('PIWIK_PRINT_ERROR_BACKTRACE', false);
}

require_once PIWIK_INCLUDE_PATH . '/core/dispatch.php';