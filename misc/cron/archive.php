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

/*
Ideas for improvements:
	- Known limitation: when adding new segments to preprocess, script will assume that data was processed for this segment in the past
      Workaround: run --force-all-websites --force-all-periods=10000000 to archive everything.
	- Possible performance improvement
      - Run first websites which are faster to process (weighted by visits and/or time to generate the last daily report)
	    This would make sure that huge websites do not 'block' processing of smaller websites' reports.
*/

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
