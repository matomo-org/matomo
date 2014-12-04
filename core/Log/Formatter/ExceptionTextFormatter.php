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
    public function format(array $record)
    {
        if (! $this->contextContainsException($record)) {
            return $this->next($record);
        }

        /** @var \Exception $exception */
        $exception = $record['context']['exception'];

        $record['message'] = sprintf("%s(%d): %s\n%s", $exception->getFile(), $exception->getLine(), $exception->getMessage(),
            Log::$debugBacktraceForTests ?: $exception->getTraceAsString());

        // Remove the exception so that it's not formatted again by another formatter
        unset($record['context']['exception']);

        return $this->next($record);
    }

    private function contextContainsException($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \Exception;
    }
}
