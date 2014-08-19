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
Please use '/path/to/php $piwikHome/console core:archive " . implode('', array_slice($_SERVER['argv'], 1)) . "' instead.
To get help use '/path/to/php $piwikHome/console core:archive --help'
See also: http://piwik.org/docs/setup-auto-archiving/

If you cannot use the console because it requires CLI
try 'php archive.php -- url=http://your.piwik/path'
-------------------------------------------------------
\n\n";
}

if (isset($_SERVER['argv']) && Piwik\Console::isSupported()) {
    $console = new Piwik\Console();
    $console->init();

    // manipulate command line arguments so CoreArchiver command will be executed
    $script = array_shift($_SERVER['argv']);
    array_unshift($_SERVER['argv'], 'core:archive');
    array_unshift($_SERVER['argv'], $script);

    $console->run();
} else { // if running via web request, use CronArchive directly
    $archiver = new Piwik\CronArchive();
    $archiver->main();
}