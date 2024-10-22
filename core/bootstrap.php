<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Bootstraps the Piwik application.
 *
 * This file cannot be a class because it needs to be compatible with PHP 4.
 */

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}

$errorLevel = E_ALL;

// We cannot enable deprecations for PHP 8.4 until we are able to update php-di/php-di to a version compatible
// with PHP 8.4. Otherwise deprecation notices would be triggered at a point where they break Matomo completely.
if (version_compare(PHP_VERSION, '8.4.0-dev', '>=')) {
    $errorLevel = E_ALL & ~E_DEPRECATED;
}

error_reporting($errorLevel);
@ini_set('display_errors', defined('PIWIK_DISPLAY_ERRORS') ? PIWIK_DISPLAY_ERRORS : @ini_get('display_errors'));
@ini_set('xdebug.show_exception_trace', 0);

if (!defined('PIWIK_VENDOR_PATH')) {
    if (is_dir(PIWIK_INCLUDE_PATH . '/vendor')) {
        define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/vendor'); // Piwik is the main project
    } else {
        define('PIWIK_VENDOR_PATH', PIWIK_INCLUDE_PATH . '/../..'); // Piwik is installed as a Composer dependency
    }
}

// NOTE: the code above must be PHP 4 compatible
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';

if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    session_cache_limiter('nocache');
}

define('PIWIK_DEFAULT_TIMEZONE', @date_default_timezone_get());
@date_default_timezone_set('UTC');

disableEaccelerator();

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';

// Composer autoloader
require_once PIWIK_VENDOR_PATH . '/autoload.php';

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/dev.php';

require_once PIWIK_INCLUDE_PATH . '/DIObject.php';

\Piwik\Plugin\Manager::initPluginDirectories();

/**
 * Eaccelerator does not support closures and is known to be not compatible with Piwik. Therefore we are disabling
 * it automatically. At this point it looks like Eaccelerator is no longer under development and the bug has not
 * been fixed within a year.
 *
 * @link https://github.com/piwik/piwik/issues/4439#comment:8
 * @link https://github.com/eaccelerator/eaccelerator/issues/12
 */
function disableEaccelerator()
{
    $isEacceleratorUsed = ini_get('eaccelerator.enable');
    if (!empty($isEacceleratorUsed)) {
        @ini_set('eaccelerator.enable', 0);
    }
}
