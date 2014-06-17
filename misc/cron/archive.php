<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . "/../.."));
}

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

if (!class_exists('Piwik\Console', false)) {
    define('PIWIK_ENABLE_DISPATCH', false);
    define('PIWIK_ENABLE_ERROR_HANDLER', false);
    define('PIWIK_ENABLE_SESSION_START', false);
    require_once PIWIK_INCLUDE_PATH . "/index.php";
}

if (!empty($_SERVER['argv'][0])) {
    $callee = $_SERVER['argv'][0];
} else {
    $callee = '';
}

if (false !== strpos($callee, 'archive.php')) {
    $piwikHome = PIWIK_INCLUDE_PATH;
    echo "
-------------------------------------------------------
Using this 'archive.php' script is no longer recommended.
Please use '/path/to/php $piwikHome/console core:archive " . implode(' ', array_slice($_SERVER['argv'], 1)) . "' instead.
To get help use '/path/to/php $piwikHome/console core:archive --help'
See also: http://piwik.org/docs/setup-auto-archiving/
-------------------------------------------------------
\n\n";
}

$archiving = new Piwik\CronArchive();
try {
    $archiving->main();
} catch (Exception $e) {
    $archiving->logFatalError($e->getMessage());
} 
