<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @package Piwik
 */

use Piwik\Error;
use Piwik\ExceptionHandler;
use Piwik\FrontController;

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

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';

// NOTE: the code above this comment must be PHP4 compatible

session_cache_limiter('nocache');
@date_default_timezone_set('UTC');
require_once file_exists(PIWIK_INCLUDE_PATH . '/vendor/autoload.php')
    ? PIWIK_INCLUDE_PATH . '/vendor/autoload.php' // Piwik is the main project
    : PIWIK_INCLUDE_PATH . '/../../autoload.php'; // Piwik is installed as a dependency
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';

if(!defined('PIWIK_PRINT_ERROR_BACKTRACE')) {
    define('PIWIK_PRINT_ERROR_BACKTRACE', false);
}

if (!defined('PIWIK_ENABLE_ERROR_HANDLER') || PIWIK_ENABLE_ERROR_HANDLER) {
    require_once PIWIK_INCLUDE_PATH . '/core/Error.php';
    Error::setErrorHandler();
    require_once PIWIK_INCLUDE_PATH . '/core/ExceptionHandler.php';
    ExceptionHandler::setUp();
}

FrontController::setUpSafeMode();

if (!defined('PIWIK_ENABLE_DISPATCH') || PIWIK_ENABLE_DISPATCH) {
    $controller = FrontController::getInstance();
    $controller->init();
    $response = $controller->dispatch();

    if (!is_null($response)) {
        echo $response;
    }
}
