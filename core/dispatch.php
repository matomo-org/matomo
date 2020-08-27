<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\ErrorHandler;
use Piwik\ExceptionHandler;
use Piwik\FrontController;

if (!defined('PIWIK_ENABLE_ERROR_HANDLER') || PIWIK_ENABLE_ERROR_HANDLER) {
    ErrorHandler::registerErrorHandler();
    ExceptionHandler::setUp();
}

FrontController::setUpSafeMode();

if (!defined('PIWIK_ENABLE_DISPATCH')) {
    define('PIWIK_ENABLE_DISPATCH', true);
}

if (PIWIK_ENABLE_DISPATCH) {
    $environment = new \Piwik\Application\Environment(null);
    $environment->init();

    $controller = FrontController::getInstance();

    try {
        $controller->init();
        $response = $controller->dispatch();

        if (!is_null($response)) {
            echo $response;
        }
    } catch (Exception $ex) {
        ExceptionHandler::dieWithHtmlErrorPage($ex);
    }
}
