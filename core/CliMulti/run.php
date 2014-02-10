<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . "/../.."));
}

require_once PIWIK_INCLUDE_PATH . '/core/Common.php';

if (!Piwik\Common::isPhpCliMode()) {
    return;
}

include PIWIK_INCLUDE_PATH . '/core/Singleton.php';
include PIWIK_INCLUDE_PATH . '/core/FrontController.php';
include PIWIK_INCLUDE_PATH . '/core/Filesystem.php';
include PIWIK_INCLUDE_PATH . '/core/Lock.php';
\Piwik\FrontController::assignCliParametersToRequest();

if (!empty($_GET['pid']) && \Piwik\Filesystem::isValidFilename($_GET['pid'])) {
    $lock = new \Piwik\Lock($_GET['pid']);
    $lock->lock();
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

if (!empty($lock)) {
    $lock->removeLock();
}