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
class ErrorTextFormatter extends Formatter
{
    public function format(array $record)
    {
        if (! $this->contextContainsError($record)) {
            return $this->next($record);
        }

        /** @var \ErrorException $exception */
        $exception = $record['context']['exception'];

        $trace = Log::$debugBacktraceForTests ?: $exception->getTraceAsString();

        $record['message'] = $exception->getFile() . '(' . $exception->getLine() . '): ' . Error::getErrNoString($exception->getSeverity())
            . ' - ' . $exception->getMessage() . "\n" . $trace;

        // Remove the exception so that it's not formatted again by another formatter
        unset($record['context']['exception']);

        return $this->next($record);
    }

    private function contextContainsError($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \ErrorException;
    }
}
