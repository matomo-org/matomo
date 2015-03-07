<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\ConsoleOutput;

define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__) == '/' ? '' : dirname(__FILE__) . '/../..');
if (file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php')) {
    require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

ignore_user_abort(true);
set_time_limit(0);

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false;
define('PIWIK_ENABLE_DISPATCH', false);

if (Piwik\Common::isPhpCliMode()) {
    StaticContainer::setEnvironment('cli');
    /** @var ConsoleHandler $consoleLogHandler */
    $consoleLogHandler = StaticContainer::get('Symfony\Bridge\Monolog\Handler\ConsoleHandler');
    $consoleLogHandler->setOutput(new ConsoleOutput());
}

FrontController::getInstance()->init();
