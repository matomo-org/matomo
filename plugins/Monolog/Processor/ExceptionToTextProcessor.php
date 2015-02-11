<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Piwik\ErrorHandler;
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

        $record['message'] = sprintf(
            "%s(%d): %s\n%s",
            $exception->getFile(),
            $exception->getLine(),
            $this->getMessage($exception),
            $this->getStackTrace($exception)
        );

        return $record;
    }

    private function contextContainsException($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \Exception;
    }

    private function getMessage(\Exception $exception)
    {
        if ($exception instanceof \ErrorException) {
            return ErrorHandler::getErrNoString($exception->getSeverity()) . ' - ' . $exception->getMessage();
        }

        return $exception->getMessage();
    }

    private function getStackTrace(\Exception $exception)
    {
        return Log::$debugBacktraceForTests ?: $exception->getTraceAsString();
    }
}
