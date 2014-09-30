<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @package Piwik
 */

use Piwik\Error;
use Piwik\ExceptionHandler;
use Piwik\FrontController;
use Piwik\Plugin\ControllerAdmin as PluginControllerAdmin;

PluginControllerAdmin::disableEacceleratorIfEnabled();

if (!defined('PIWIK_ENABLE_ERROR_HANDLER') || PIWIK_ENABLE_ERROR_HANDLER) {
    require_once PIWIK_INCLUDE_PATH . '/core/Error.php';
    Error::setErrorHandler();
    require_once PIWIK_INCLUDE_PATH . '/core/ExceptionHandler.php';
    ExceptionHandler::setUp();
}

FrontController::setUpSafeMode();

if (!defined('PIWIK_ENABLE_DISPATCH')) {
    define('PIWIK_ENABLE_DISPATCH', true);
}

if (PIWIK_ENABLE_DISPATCH) {
    $controller = FrontController::getInstance();
    $controller->init();
    $response = $controller->dispatch();

    if (is_array($response)) {
        var_export($response);
    } elseif (!is_null($response)) {
        echo $response;
    }
}
