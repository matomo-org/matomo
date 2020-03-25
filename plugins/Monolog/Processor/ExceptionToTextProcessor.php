<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Piwik\ErrorHandler;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Log;

/**
 * Process a log record containing an exception to generate a textual message.
 */
class ExceptionToTextProcessor
{
    public function __invoke(array $record)
    {
        if (! $this->contextContainsException($record)) {
            return $record;
        }

        /** @var \Exception $exception */
        $exception = $record['context']['exception'];

        if ($exception instanceof InvalidRequestParameterException) {
            return $record;
        }

        $exceptionStr = sprintf(
            "%s(%d): %s\n%s",
            $exception instanceof \Exception ? $exception->getFile() : $exception['file'],
            $exception instanceof \Exception ? $exception->getLine() : $exception['line'],
            $this->getMessage($exception),
            $this->getStackTrace($exception)
        );

        if (!isset($record['message'])
            || strpos($record['message'], '{exception}') === false
        ) {
            $record['message'] = $exceptionStr;
        } else {
            $record['message'] = str_replace('{exception}', $exceptionStr, $record['message']);
        }

        return $record;
    }

    private function contextContainsException($record)
    {
        return isset($record['context']['exception'])
            && ($record['context']['exception'] instanceof \Exception
                || $this->isLooksLikeFatalErrorArray($record['context']['exception']));
    }

    private function isLooksLikeFatalErrorArray($exception)
    {
        return is_array($exception) && isset($exception['message']) && isset($exception['file']) && isset($exception['line']);
    }

    private function getMessage($exception)
    {
        if ($exception instanceof \ErrorException) {
            return ErrorHandler::getErrNoString($exception->getSeverity()) . ' - ' . $exception->getMessage();
        }

        if (is_array($exception) && isset($exception['message'])) {
            return $exception['message'];
        }

        return $exception->getMessage();
    }

    private function getStackTrace($exception)
    {
        if (is_array($exception) && isset($exception['backtrace'])) {
            return $exception['backtrace'];
        }

        return Log::$debugBacktraceForTests ?: self::getWholeBacktrace($exception);
    }

    public static function getWholeBacktrace(\Exception $exception, $shouldPrintBacktrace = true)
    {
        if (!$shouldPrintBacktrace) {
            return $exception->getMessage();
        }

        $message = "";

        $e = $exception;
        do {
            if ($e !== $exception) {
                $message .= ",\ncaused by: ";
            }

            $message .= $e->getMessage();
            if ($shouldPrintBacktrace) {
                $message .= "\n" . $e->getTraceAsString();
            }
        } while ($e = $e->getPrevious());

        return $message;
    }
}
