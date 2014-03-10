<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ProxyHttp;

/**
 * Tracker proxy
 */
if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_SERVER['QUERY_STRING'])) {
	include '../piwik.php';
	exit;
}

/**
 * piwik.js proxy
 *
 * @see core/Piwik.php
 */
define('PIWIK_INCLUDE_PATH', '..');
define('PIWIK_DOCUMENT_ROOT', '..');
define('PIWIK_USER_PATH', '..');

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';

$file = '../piwik.js';


$daysExpireFarFuture = 10;

ProxyHttp::serverStaticFile($file, "application/javascript; charset=UTF-8", $daysExpireFarFuture);

exit;
