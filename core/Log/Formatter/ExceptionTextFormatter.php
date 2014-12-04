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

        $record['message'] = sprintf(
            "%s(%d): %s\n%s",
            $exception->getFile(),
            $exception->getLine(),
            $this->getMessage($exception),
            $this->getStackTrace($exception)
        );

        unset($record['context']['exception']);

        return $this->next($record);
    }

    private function contextContainsException($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \Exception;
    }

    private function getMessage(\Exception $exception)
    {
        if ($exception instanceof \ErrorException) {
            return Error::getErrNoString($exception->getSeverity()) . ' - ' . $exception->getMessage();
        }

        return $exception->getMessage();
    }

    private function getStackTrace(\Exception $exception)
    {
        return Log::$debugBacktraceForTests ?: $exception->getTraceAsString();
    }
}
