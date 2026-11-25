<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Monolog\Formatter\LogglyFormatter as MonologLogglyFormatter;

/**
 * Formats a log record as one JSON object per line in the format expected by Loggly.
 */
class LogglyFormatter extends MonologLogglyFormatter
{
    use RedactSensitiveValuesTrait;

    // Monolog disables the newline as the format is meant for its Loggly handler, but our writers are line based
    public function __construct(int $batchMode = self::BATCH_MODE_NEWLINES, bool $appendNewline = true)
    {
        parent::__construct($batchMode, $appendNewline);
    }
}
