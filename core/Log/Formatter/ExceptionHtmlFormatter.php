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
 * Formats a log message containing an exception object into an HTML response.
 */
class ExceptionHtmlFormatter extends Formatter
{
    public function format(array $record)
    {
        if (! $this->contextContainsException($record)) {
            return $this->next($record);
        }

        /** @var \Exception $exception */
        $exception = $record['context']['exception'];

        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $trace = Log::$debugBacktraceForTests ?: $exception->getTraceAsString();

        $message = $this->getMessage($exception);

        $html = '';
        $html .= "<div style='word-wrap: break-word; border: 3px solid red; padding:4px; width:70%; background-color:#FFFF96;'>
        <strong>There is an error. Please report the message (Piwik " . (class_exists('Piwik\Version') ? Version::VERSION : '') . ")
        and full backtrace in the <a href='?module=Proxy&action=redirect&url=http://forum.piwik.org' target='_blank'>Piwik forums</a> (please do a Search first as it might have been reported already!).</strong><br /><br/>
        ";
        $html .= "<em>{$message}</em> in <strong>{$exception->getFile()}</strong>";
        $html .= " on line <strong>{$exception->getLine()}</strong>\n";
        $html .= "<br /><br />Backtrace --&gt;<div style=\"font-family:Courier;font-size:10pt\"><br />\n";
        $html .= str_replace("\n", "<br />\n", $trace);
        $html .= "</div>";
        $html .= "</div>\n";

        return $html;
    }

    private function contextContainsException($record)
    {
        return isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \Exception;
    }

    private function getMessage(\Exception $exception)
    {
        if ($exception instanceof \ErrorException) {
            return Error::getErrNoString($exception->getSeverity()) . ' - ' . $exception->getMessage();
        }

        return $exception->getMessage();
    }
}
