<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

/**
 * Formats a log record as one JSON object per line.
 */
class JsonFormatter extends MonologJsonFormatter
{
    use RedactSensitiveValuesTrait;
}
