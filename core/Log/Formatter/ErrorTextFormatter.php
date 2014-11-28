<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Error;
use Piwik\Log;

/**
 * Formats a log message containing a Piwik\Error object into a textual string.
 */
class ErrorTextFormatter implements Formatter
{
    public function format($message, $level, $tag, $datetime, Log $logger)
    {
        if (! $message instanceof Error) {
            return $message;
        }

        $message = $message->errfile . '(' . $message->errline . '): ' . Error::getErrNoString($message->errno)
            . ' - ' . $message->errstr . "\n" . $message->backtrace;

        return $logger->formatMessage($level, $tag, $datetime, $message);
    }
}
