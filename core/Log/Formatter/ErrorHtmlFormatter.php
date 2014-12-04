<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Common;
use Piwik\Error;
use Piwik\Log;
use Piwik\Version;

/**
 * Formats a log message containing a Piwik\Error object into an HTML string.
 */
class ErrorHtmlFormatter extends Formatter
{
    public function format(array $record)
    {
        if (! $this->contextContainsError($record)) {
            return $this->next($record);
        }

        /** @var \ErrorException $exception */
        $exception = $record['context']['exception'];

        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $trace = Log::$debugBacktraceForTests ?: $exception->getTraceAsString();

        $html = '';
        $html .= "<div style='word-wrap: break-word; border: 3px solid red; padding:4px; width:70%; background-color:#FFFF96;'>
        <strong>There is an error. Please report the message (Piwik " . (class_exists('Piwik\Version') ? Version::VERSION : '') . ")
        and full backtrace in the <a href='?module=Proxy&action=redirect&url=http://forum.piwik.org' target='_blank'>Piwik forums</a> (please do a Search first as it might have been reported already!).<br /><br/>
        ";
        $html .= Error::getErrNoString($exception->getSeverity());
        $html .= ":</strong> <em>{$exception->getMessage()}</em> in <strong>{$exception->getFile()}</strong>";
        $html .= " on line <strong>{$exception->getLine()}</strong>\n";
        $html .= "<br /><br />Backtrace --&gt;<div style=\"font-family:Courier;font-size:10pt\"><br />\n";
        $html .= str_replace("\n", "<br />\n", $trace);
        $html .= "</div><br />";
        $html .= "\n </pre></div><br />";

        $record['message'] = $html;

        // Remove the exception so that it's not formatted again by another formatter
        unset($record['context']['exception']);

        return $record;
    }

    private function contextContainsError($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \ErrorException;
    }
}
