<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . "/../.."));
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);

require_once PIWIK_INCLUDE_PATH . '/core/Common.php';

if (!Piwik\Common::isPhpCliMode()) {
    return;
}

include PIWIK_INCLUDE_PATH . '/core/Singleton.php';
include PIWIK_INCLUDE_PATH . '/core/FrontController.php';
include PIWIK_INCLUDE_PATH . '/core/Filesystem.php';
include PIWIK_INCLUDE_PATH . '/core/CliMulti/Process.php';
include PIWIK_INCLUDE_PATH . '/core/SettingsServer.php';
include PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
include PIWIK_INCLUDE_PATH . '/core/Url.php';
include PIWIK_INCLUDE_PATH . '/core/Config.php';
\Piwik\FrontController::assignCliParametersToRequest();

if (!empty($_GET['testmode'])) {
    Piwik\Config::getInstance()->setTestEnvironment();
}

if (!empty($_GET['pid']) && \Piwik\Filesystem::isValidFilename($_GET['pid'])) {
    $pid = new \Piwik\CliMulti\Process($_GET['pid']);
    $pid->startProcess();
}

ob_start();

Piwik\Common::$isCliMode = false;

require_once PIWIK_INCLUDE_PATH . "/index.php";

$content = ob_get_contents();
ob_clean();

if (!empty($_GET['output']) && \Piwik\Filesystem::isValidFilename($_GET['output'])) {
    $cliMulti = new \Piwik\CliMulti\Output($_GET['output']);
    $cliMulti->write($content);
} else {
    echo $content;
}

if (!empty($pid)) {
    $pid->finishProcess();
}