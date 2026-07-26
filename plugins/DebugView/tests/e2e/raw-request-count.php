<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * E2E helper: prints the number of rows in the debugview_raw_request table
 * for one site. Usage: php raw-request-count.php <idSite>
 */

define('PIWIK_DOCUMENT_ROOT', realpath(__DIR__ . '/../../../..'));
define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

$environment = new \Piwik\Application\Environment(null);
$environment->init();
\Piwik\Plugin\Manager::getInstance()->loadActivatedPlugins();

$idSite = (int) ($argv[1] ?? 0);

\Piwik\Access::doAsSuperUser(function () use ($idSite) {
    echo (int) \Piwik\Db::fetchOne(
        'SELECT COUNT(*) FROM `'
        . \Piwik\Common::prefixTable(\Piwik\Plugins\DebugView\Dao\RawRequestLog::TABLE)
        . '` WHERE idsite = ?',
        [$idSite]
    );
    echo "\n";
});
