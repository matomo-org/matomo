<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Monolog\Formatter\LogstashFormatter as MonologLogstashFormatter;

/**
 * Formats a log message into a json string
 */
class LogstashFormatter extends MonologLogstashFormatter
{
    public function __construct($systemName = null, $extraPrefix = null, $contextPrefix = 'ctxt_', $version = MonologLogstashFormatter::V0)
    {
        parent::__construct('Matomo', $systemName, $extraPrefix, $contextPrefix, $version);
    }
}
