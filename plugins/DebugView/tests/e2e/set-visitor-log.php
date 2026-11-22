<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * E2E helper: enables/disables the Live visits log system-wide.
 * Usage: php set-visitor-log.php <enabled|disabled>
 */

use Piwik\Access;

if (empty($argv[1]) || !in_array($argv[1], ['enabled', 'disabled'], true)) {
    fwrite(STDERR, "usage: php set-visitor-log.php <enabled|disabled>\n");
    exit(1);
}

$disabled = $argv[1] === 'disabled';

define('PIWIK_DOCUMENT_ROOT', realpath(__DIR__ . '/../../../..'));
define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

$environment = new \Piwik\Application\Environment(null);
$environment->init();
\Piwik\Plugin\Manager::getInstance()->loadActivatedPlugins();

Access::doAsSuperUser(function () use ($disabled) {
    $settings = new \Piwik\Plugins\Live\SystemSettings();
    $settings->disableVisitorLog->setValue($disabled);
    $settings->save();
    echo 'visits log is now ' . ($disabled ? 'disabled' : 'enabled') . "\n";
});
