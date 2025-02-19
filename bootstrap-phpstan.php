<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Exception\NotYetInstalledException;

class Manifest
{
    public static $files = [];
}

define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__) == '/' ? '' : dirname(__FILE__));
define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

if (!defined('PIWIK_PRINT_ERROR_BACKTRACE')) {
    define('PIWIK_PRINT_ERROR_BACKTRACE', false);
}

$environment = new \Piwik\Application\Environment(null);
try {
    $environment->init();
} catch (NotYetInstalledException $e) {
}
