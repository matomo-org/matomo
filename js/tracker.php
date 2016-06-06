<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ProxyHttp;

/**
 * Tracker proxy
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST'
    || !empty($_SERVER['QUERY_STRING'])
) {
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

if (is_dir(PIWIK_INCLUDE_PATH . '/vendor')) {
    define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/vendor'); // Piwik is the main project
} else {
    define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/../..'); // Piwik is installed as a Composer dependency
}

// Composer autoloader
require PIWIK_VENDOR_PATH . '/autoload.php';

$file = '../piwik.js';

$daysExpireFarFuture = 10;

$byteStart = $byteEnd = false;
if (!defined("PIWIK_KEEP_JS_TRACKER_COMMENT")
    || !PIWIK_KEEP_JS_TRACKER_COMMENT
) {
    $byteStart = 369; // length of comment header in bytes
}

$environment = new \Piwik\Application\Environment(null);
$environment->init();

ProxyHttp::serverStaticFile($file, "application/javascript; charset=UTF-8", $daysExpireFarFuture, $byteStart, $byteEnd);

exit;