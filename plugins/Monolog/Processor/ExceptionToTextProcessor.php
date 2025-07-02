<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Piwik\Common;
use Piwik\ErrorHandler;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\ExceptionHandler;
use Piwik\Log;
use Piwik\Url;

/**
 * Process a log record containing an exception to generate a textual message.
 */
class ExceptionToTextProcessor
{
    private $forcePrintBacktrace;

    public function __construct(bool $forcePrintBacktrace = false)
    {
        $this->forcePrintBacktrace = $forcePrintBacktrace;
    }

    public function __invoke(array $record)
    {
        if (!$this->contextContainsException($record)) {
            return $record;
        }

        /** @var \Exception $exception */
        $exception = $record['context']['exception'];

        if ($exception instanceof InvalidRequestParameterException) {
            return $record;
        }

        $exceptionStr = sprintf(
            "%s(%d): %s",
            ExceptionHandler::replaceSensitiveValues($exception instanceof \Exception ? $exception->getFile() : $exception['file']),
            $exception instanceof \Exception ? $exception->getLine() : $exception['line'],
            $this->getStackTrace($exception)
        );

        if (
            !isset($record['message'])
            || strpos($record['message'], '{exception}') === false
        ) {
            $record['message'] = $exceptionStr;
        } else {
            $record['message'] = str_replace('{exception}', $exceptionStr, ExceptionHandler::replaceSensitiveValues($record['message']));
        }

        $record['message'] .= ' [' . $this->getErrorContext() . ']';

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
            return ErrorHandler::getErrNoString($exception->getSeverity()) . ' - ' . ExceptionHandler::replaceSensitiveValues($exception->getMessage());
        }

        if (is_array($exception) && isset($exception['message'])) {
            return ExceptionHandler::replaceSensitiveValues($exception['message']);
        }

        return ExceptionHandler::replaceSensitiveValues($exception->getMessage());
    }

    private function getStackTrace($exception)
    {
        return Log::$debugBacktraceForTests ?: self::getMessageAndWholeBacktrace($exception, $this->forcePrintBacktrace ? true : null);
    }

    /**
     * @param \Exception|array $exception
     * @param bool|null $shouldPrintBacktrace
     * @return mixed|string
     */
    public static function getMessageAndWholeBacktrace($exception, ?bool $shouldPrintBacktrace = null)
    {
        if ($shouldPrintBacktrace === null) {
            $shouldPrintBacktrace = ExceptionHandler::shouldPrintBackTraceWithMessage();
        }

        if (is_array($exception)) {
            $message = ExceptionHandler::replaceSensitiveValues($exception['message'] ?? '');
            if ($shouldPrintBacktrace && isset($exception['backtrace'])) {
                $trace = $exception['backtrace'];
                $trace = ExceptionHandler::replaceSensitiveValues($trace);
                return $message . "\n" . $trace;
            } else {
                return $message;
            }
        }

        if (!$shouldPrintBacktrace) {
            return ExceptionHandler::replaceSensitiveValues($exception->getMessage());
        }

        $message = "";

        $e = $exception;
        do {
            if ($e !== $exception) {
                $message .= ",\ncaused by: ";
            }

            $message .= ExceptionHandler::replaceSensitiveValues($e->getMessage());
            $message .= "\n" . ExceptionHandler::replaceSensitiveValues($e->getTraceAsString());
        } while ($e = $e->getPrevious());

        return $message;
    }

    private function getErrorContext()
    {
        try {
            $context = 'Query: ' . Url::getCurrentQueryString();
            $context .= ', CLI mode: ' . (int)Common::isPhpCliMode();
            return $context;
        } catch (\Exception $ex) {
            return 'cannot get url or cli mode: ' . ExceptionHandler::replaceSensitiveValues($ex->getMessage());
        }
    }
}
