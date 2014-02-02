<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
define('PIWIK_INCLUDE_PATH', '../../..');

if(isset($_GET['ua'])) {
    $userAgent = $_GET['ua'];
} else {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
}

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php';

$result = UserAgentParserEnhanced::getInfoFromUserAgent($userAgent);
echo "<pre>";

var_export($result);
echo "</pre>";
