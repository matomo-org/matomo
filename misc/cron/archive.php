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

namespace Piwik;

use Exception;

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . "/../.."));
}

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);
if(!defined('PIWIK_MODE_ARCHIVE')) {
    define('PIWIK_MODE_ARCHIVE', true);
}

require_once PIWIK_INCLUDE_PATH . "/index.php";

$archiving = new CronArchive();
try {
    $archiving->init();
    $archiving->run();
    $archiving->runScheduledTasks();
    $archiving->end();
} catch (Exception $e) {
    $archiving->logFatalError($e->getMessage());
}
