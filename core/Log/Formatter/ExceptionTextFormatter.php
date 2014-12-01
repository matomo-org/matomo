<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\ExceptionHandler;
use Piwik\Log;

/**
 * Formats a log message containing an exception object into a textual string.
 */
class ExceptionTextFormatter extends Formatter
{
    public function format($message, $level, $tag, $datetime, Log $logger)
    {
        if ($message instanceof \Exception) {
            $message = sprintf("%s(%d): %s\n%s", $message->getFile(), $message->getLine(), $message->getMessage(),
                ExceptionHandler::$debugBacktraceForTests ?: $message->getTraceAsString());
        }

        return $this->next($message, $level, $tag, $datetime, $logger);
    }
}
