<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Processor;

use Piwik\Common;
use Piwik\Db;
use Piwik\ErrorHandler;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Url;

/**
 * Process a log record containing an exception to generate a textual message.
 */
class ExceptionToTextProcessor
{
    private $forcePrintBacktrace = false;

    public function __construct($forcePrintBacktrace = false)
    {
        $this->forcePrintBacktrace = $forcePrintBacktrace;
    }

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
            "%s(%d): %s",
            $exception instanceof \Exception ? $exception->getFile() : $exception['file'],
            $exception instanceof \Exception ? $exception->getLine() : $exception['line'],
            $this->getStackTrace($exception)
        );

        if (!isset($record['message'])
            || strpos($record['message'], '{exception}') === false
        ) {
            $record['message'] = $exceptionStr;
        } else {
            $record['message'] = str_replace('{exception}', $exceptionStr, $record['message']);
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
            return ErrorHandler::getErrNoString($exception->getSeverity()) . ' - ' . $exception->getMessage();
        }

        if (is_array($exception) && isset($exception['message'])) {
            return $exception['message'];
        }

        return $exception->getMessage();
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
    public static function getMessageAndWholeBacktrace($exception, $shouldPrintBacktrace = null)
    {
        if ($shouldPrintBacktrace === null) {
            $shouldPrintBacktrace = \Piwik_ShouldPrintBackTraceWithMessage();
        }

        if (is_array($exception)) {
            $message = $exception['message'] ?? '';
            if ($shouldPrintBacktrace && isset($exception['backtrace'])) {
                $trace = $exception['backtrace'];
                $trace = self::replaceSensitiveValues($trace);
                return $message . "\n" . $trace;
            } else {
                return $message;
            }
        }

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
                $message .= "\n" . self::replaceSensitiveValues($e->getTraceAsString());
            }
        } while ($e = $e->getPrevious());

        return $message;
    }

    private static function replaceSensitiveValues($trace)
    {
        $dbConfig = Db::getDatabaseConfig();

        $valuesToReplace = [
            Piwik::getCurrentUserTokenAuth() => 'tokenauth',
            SettingsPiwik::getSalt() => 'generalSalt',
            $dbConfig['username'] => 'dbuser',
            $dbConfig['password'] => 'dbpass',
        ];

        return str_replace(array_keys($valuesToReplace), array_values($valuesToReplace), $trace);
    }

    private function getErrorContext()
    {
        try {
            $context = 'Query: ' . Url::getCurrentQueryString();
            $context .= ', CLI mode: ' . (int)Common::isPhpCliMode();
            return $context;
        } catch (\Exception $ex) {
            $context = "cannot get url or cli mode: " . $ex->getMessage();
            return $context;
        }
    }
}
