<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Log;

/**
 * Formats a log message containing an exception object into an HTML response.
 */
class ExceptionHtmlFormatter extends Formatter
{
    public function format(array $record)
    {
        if (! $this->contextContainsException($record)) {
            return $this->next($record);
        }

        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $outputFormat = strtolower(Common::getRequestVar('format', 'html', 'string'));
        $response = new ResponseBuilder($outputFormat);

        /** @var \Exception $exception */
        $exception = $record['context']['exception'];

        // Why do we create a new exception and not use the real one??
        $exception = new \Exception($exception->getMessage());

        $record['message'] = $response->getResponseException($exception);

        // Remove the exception so that it's not formatted again by another formatter
        unset($record['context']['exception']);

        return $record;
    }

    private function contextContainsException($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \Exception;
    }
}
