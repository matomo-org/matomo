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
 * Formats a log record as one JSON object per line in the format expected by Logstash.
 */
class LogstashFormatter extends MonologLogstashFormatter
{
    use RedactSensitiveValuesTrait;

    public function __construct(?string $systemName = null, string $extraKey = 'extra', string $contextKey = 'context')
    {
        parent::__construct('Matomo', $systemName, $extraKey, $contextKey);
    }
}
