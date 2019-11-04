<?php
/**
 * This php file is used to unit test Piwik::serverStaticFile()
 * To make a comprehensive test suit for Piwik::serverStaticFile() (ie. being able to test combinations of request
 * headers, being able to test response headers and so on) we need to simulate static file requests in a controlled
 * environment
 * The php code which simulates requests using Piwik::serverStaticFile() is provided in the same file (ie. this one)
 * as the unit testing code for Piwik::serverStaticFile()
 * This decision has a structural impact on the usual unit test file structure
 * serverStaticFile.test.php has been created to avoid making too many modifications to /tests/core/Piwik.test.php
 */
use Piwik\FrontController;
use Piwik\Nonce;

session_start(); // matomo should not fail if session was started by someone else
define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__).'/../../');
if(file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php')) {
    require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

$environment = new \Piwik\Application\Environment(null);
try {
    $environment->init();
} catch(\Piwik\Exception\NotYetInstalledException $e) {
    die($e->getMessage());
}
FrontController::getInstance()->init();

Nonce::getNonce('test');

echo 'ok';