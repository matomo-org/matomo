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
    public function format(array $record, Log $logger)
    {
        if (! $record['message'] instanceof \Exception) {
            return $this->next($record, $logger);
        }

        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $outputFormat = strtolower(Common::getRequestVar('format', 'html', 'string'));
        $response = new ResponseBuilder($outputFormat);

        $record['message'] = $response->getResponseException(new \Exception($record['message']->getMessage()));

        return $record;
    }
}
