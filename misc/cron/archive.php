<?php
/**
 * Piwik - Open source web analytics
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

if (empty($_SERVER['argv'])) {

    define('PIWIK_ENABLE_DISPATCH', false);
    define('PIWIK_ENABLE_ERROR_HANDLER', false);
    define('PIWIK_ENABLE_SESSION_START', false);

    require_once PIWIK_INCLUDE_PATH . "/index.php";

    $archiving = new Piwik\CronArchive();
    try {
        $archiving->init();
        $archiving->run();
        $archiving->runScheduledTasks();
        $archiving->end();
    } catch (Exception $e) {
        $archiving->logFatalError($e->getMessage());
    }

    return;
}

$callee = array_shift($_SERVER['argv']);

$args   = array($callee);
$args[] = 'core:archive';
foreach ($_SERVER['argv'] as $arg) {
    if (0 === strpos($arg, '--')) {
        $args[] = $arg;
    } elseif (0 === strpos($arg, '-')) {
        $args[] = '-' . $arg;
    } else {
        $args[] = '--' . $arg;
    }
}

$_SERVER['argv'] = $args;

$piwikHome = PIWIK_INCLUDE_PATH;

if (false !== strpos($callee, 'archive.php')) {
echo "
-------------------------------------------------------
Using this 'archive.php' script is no longer recommended.
Please use '/path/to/php $piwikHome/console core:archive " . implode(' ', array_slice($args, 2)) . "' instead.
-------------------------------------------------------
\n\n";
}

include $piwikHome . '/console';