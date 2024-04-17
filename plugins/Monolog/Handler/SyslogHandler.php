<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\SyslogHandler as OriginalSyslogHandler;

/**
 * A class to use the SyslogHandler instead of including the dependency directly
 */

class SyslogHandler extends OriginalSyslogHandler
{
}
