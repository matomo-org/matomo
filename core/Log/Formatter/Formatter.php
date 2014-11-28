<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Log;

/**
 * Formats a log message.
 */
interface Formatter
{
    /**
     * @param mixed $message Log message.
     * @param int $level The log level used with this log entry.
     * @param string $tag The current plugin that started logging (or if no plugin, the current class).
     * @param string $datetime Datetime of the logging call.
     * @param Log $logger
     *
     * @return mixed
     */
    public function format($message, $level, $tag, $datetime, Log $logger);
}
