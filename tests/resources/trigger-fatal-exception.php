<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

define('PIWIK_PRINT_ERROR_BACKTRACE', true);
define('PIWIK_ENABLE_DISPATCH', false);

require_once __DIR__ . '/../../tests/PHPUnit/proxy/index.php';

$environment = new \Piwik\Application\Environment(null);
$environment->init();

\Piwik\Access::getInstance()->setSuperUserAccess(true);

$executed = false;
\Piwik\Piwik::addAction('Request.dispatch', function () use (&$executed) {
    if (!$executed) {
        $executed = true;
        throw new \Twig\Error\RuntimeError('test message');
    }
});

\Piwik\FrontController::$enableDispatch = true;

\Piwik\FrontController::getInstance()->init();

echo \Piwik\FrontController::getInstance()->dispatch('CoreHome', 'index');
