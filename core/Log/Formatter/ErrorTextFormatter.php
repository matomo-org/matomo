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
 * Formats a log message containing a Piwik\Error object into a textual string.
 */
class ErrorTextFormatter extends Formatter
{
    public function format(array $record, Log $logger)
    {
        $message = $record['message'];

        if ($message instanceof Error) {
            $record['message'] = $message->errfile . '(' . $message->errline . '): ' . Error::getErrNoString($message->errno)
                . ' - ' . $message->errstr . "\n" . $message->backtrace;
        }

        return $this->next($record, $logger);
    }
}
