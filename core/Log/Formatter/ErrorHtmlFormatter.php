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
    public function format($message, $level, $tag, $datetime, Log $logger)
    {
        if (! $message instanceof Error) {
            return $this->next($message, $level, $tag, $datetime, $logger);
        }

        $errno = $message->errno & error_reporting();

        // problem when using error_reporting with the @ silent fail operator
        // it gives an errno 0, and in this case the objective is to NOT display anything on the screen!
        // is there any other case where the errno is zero at this point?
        // TODO: (@mnapoli) If `@` is used then the error handler will return, so I guess this step is redundant and useless...
        if ($errno == 0) {
            $message = false;
            return $message;
        }

        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $html = '';
        $html .= "\n<div style='word-wrap: break-word; border: 3px solid red; padding:4px; width:70%; background-color:#FFFF96;'>
        <strong>There is an error. Please report the message (Piwik " . (class_exists('Piwik\Version') ? Version::VERSION : '') . ")
        and full backtrace in the <a href='?module=Proxy&action=redirect&url=http://forum.piwik.org' rel='noreferrer' target='_blank'>Piwik forums</a> (please do a Search first as it might have been reported already!).<br /><br/>
        ";
        $html .= Error::getErrNoString($message->errno);
        $html .= ":</strong> <em>{$message->errstr}</em> in <strong>{$message->errfile}</strong>";
        $html .= " on line <strong>{$message->errline}</strong>\n";
        $html .= "<br /><br />Backtrace --&gt;<div style=\"font-family:Courier;font-size:10pt\"><br />\n";
        $html .= str_replace("\n", "<br />\n", $message->backtrace);
        $html .= "</div><br />";
        $html .= "\n </pre></div><br />";

        return $html;
    }
}
