<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Monolog\LogRecord;
use Piwik\ExceptionHandler;
use Piwik\Plugins\Monolog\Processor\TokenProcessor;

/**
 * Applies the redactions of the text log format to the context a structured formatter writes as well.
 *
 * The processors only redact the message: token_auth values are removed from it and an exception is
 * folded into it with its sensitive values and the document root replaced. Structured formatters
 * additionally write the raw context, so the same is applied to its strings, to the normalized
 * exception (a Throwable or the fatal error array) and to the backtrace of a PHP error.
 */
trait RedactSensitiveValuesTrait
{
    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        array_walk_recursive($normalized, function (&$value) {
            if (is_string($value)) {
                $value = TokenProcessor::removeTokenAuth($value);
            }
        });

        // an empty context is a stdClass in the json formatter
        if (!is_array($normalized['context'] ?? null)) {
            return $normalized;
        }

        if (is_array($normalized['context']['exception'] ?? null)) {
            $normalized['context']['exception'] = self::redactException($normalized['context']['exception']);
        }

        if (is_array($normalized['context']['trace'] ?? null)) {
            foreach ($normalized['context']['trace'] as &$frame) {
                if (is_string($frame['file'] ?? null)) {
                    $frame['file'] = str_replace(PIWIK_DOCUMENT_ROOT, '', $frame['file']);
                }
            }
        }

        return $normalized;
    }

    private static function redactException(array $exception): array
    {
        foreach (['message', 'file', 'backtrace'] as $key) {
            if (isset($exception[$key]) && is_string($exception[$key])) {
                $exception[$key] = ExceptionHandler::replaceSensitiveValues($exception[$key]);
            }
        }

        if (isset($exception['trace']) && is_array($exception['trace'])) {
            foreach ($exception['trace'] as &$frame) {
                if (is_string($frame)) {
                    $frame = ExceptionHandler::replaceSensitiveValues($frame);
                }
            }
        }

        if (isset($exception['previous']) && is_array($exception['previous'])) {
            $exception['previous'] = self::redactException($exception['previous']);
        }

        return $exception;
    }
}
