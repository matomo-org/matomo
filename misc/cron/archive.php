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

$USAGE = "
Usage: 
	/path/to/cli/php \"" . @$_SERVER['argv'][0] . "\" --url=http://your-website.org/path/to/piwik/ [arguments]

Arguments:
	--url=[piwik-server-url]
			Mandatory argument. Must be set to the Piwik base URL. 
			For example: --url=http://analytics.example.org/ or --url=https://example.org/piwik/
	--force-all-websites
			If specified, the script will trigger archiving on all websites and all past dates.
			You may use --force-all-periods=[seconds] to only trigger archiving on those websites that had visits in the last [seconds] seconds.
	--force-all-periods[=seconds]
			Limits archiving to websites with some traffic in the last [seconds] seconds.
			For example --force-all-periods=86400 will archive websites that had visits in the last 24 hours.
			If [seconds] is not specified, all websites will visits in the last ". CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE
            . " seconds (" . round(CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE/86400) ." days) will be archived.
	--force-timeout-for-periods=[seconds]
			The current week/ current month/ current year will be processed at most every [seconds].
			If not specified, defaults to ". CronArchive::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES.".
	--force-idsites=1,2,n
			Restricts archiving to the specified website IDs, comma separated list.
	--xhprof
			Enables XHProf profiler for this archive.php run. Requires XHPRof (see tests/README.xhprof.md).
	--accept-invalid-ssl-certificate
			It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!
			It can be useful if you specified --url=https://... or if you are using Piwik with force_ssl=1
	--help
			Displays usage

Notes:
	* It is recommended to run the script with the argument --url=[piwik-server-url] only. Other arguments are not required. 
	* This script should be executed every hour via crontab, or as a deamon.
	* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'), 
	  but it is recommended to run it via command line/CLI instead.
	* If you use Piwik to track dozens/hundreds of websites, please let the team know at hello@piwik.org
	  it makes us happy to learn successful user stories :)
	* Enjoy!

";
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

$archiving = new CronArchive;
try {
    $archiving->init();
    $archiving->run();
    $archiving->runScheduledTasks();
    $archiving->end();
} catch (Exception $e) {
    $archiving->logFatalError($e->getMessage());
}
