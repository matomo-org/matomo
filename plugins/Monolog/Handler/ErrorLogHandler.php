<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\ErrorLogHandler as OriginalErrorLogHandler;

/**
 * A class to use the ErrorLogHandler instead of including the dependency directly
 */
class ErrorLogHandler extends OriginalErrorLogHandler
{
}
