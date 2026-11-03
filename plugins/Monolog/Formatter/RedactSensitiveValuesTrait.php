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
 * folded into it with its sensitive values replaced. Structured formatters additionally write the raw
 * context, so the same is applied to its strings and to the normalized exception.
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

        return $normalized;
    }

    protected function normalizeException(\Throwable $e, int $depth = 0): array
    {
        $data = parent::normalizeException($e, $depth);

        foreach (['message', 'file'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = ExceptionHandler::replaceSensitiveValues($data[$key]);
            }
        }

        return $data;
    }
}
