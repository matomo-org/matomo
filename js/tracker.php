<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
define('PIWIK_DOCUMENT_ROOT', '..');

if (file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php')) {
    require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';

if (is_dir(PIWIK_INCLUDE_PATH . '/vendor')) {
    define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/vendor'); // Piwik is the main project
} else {
    define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/../..'); // Piwik is installed as a Composer dependency
}

// Composer autoloader
require PIWIK_VENDOR_PATH . '/autoload.php';

$file = '../matomo.js';

$daysExpireFarFuture = 10;

$byteStart = $byteEnd = false;
if (!defined("PIWIK_KEEP_JS_TRACKER_COMMENT")
    || !PIWIK_KEEP_JS_TRACKER_COMMENT
) {
    $byteStart = 378; // length of comment header in bytes
}

class Validator {
    public function validate() {}
}
$validator = new Validator();
$environment = new \Piwik\Application\Environment(null, array(
    'Piwik\Application\Kernel\EnvironmentValidator' => $validator
));
$environment->init();

if (!\Piwik\Tracker\IgnoreCookie::isIgnoreCookieFound()) {
    
    $request = new \Piwik\Tracker\Request(array());
    
    if ($request->shouldUseThirdPartyCookie()) {
        $visitorId = $request->getVisitorIdForThirdPartyCookie();
        if (!$visitorId) {
            $visitorId = \Piwik\Common::hex2bin(\Piwik\Tracker\Visit::generateUniqueVisitorId());
        }
        $request->setThirdPartyCookie($visitorId);
    }
}

ProxyHttp::serverStaticFile($file, "application/javascript; charset=UTF-8", $daysExpireFarFuture, $byteStart, $byteEnd);

exit;
