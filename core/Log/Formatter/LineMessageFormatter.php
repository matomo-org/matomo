<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Log;

/**
 * Formats a log message into a single line of text.
 */
class LineMessageFormatter extends Formatter
{
    /**
     * The log message format string that turns a tag name, date-time and message into
     * one string to log.
     *
     * @var string
     */
    private $logMessageFormat;

    public function __construct($logMessageFormat)
    {
        $this->logMessageFormat = $logMessageFormat;
    }

    public function format($message, $level, $tag, $datetime, Log $logger)
    {
        if (! is_string($message)) {
            throw new \InvalidArgumentException('Trying to log a message that is not a string');
        }

        $message = str_replace(
            array("%tag%", "%message%", "%datetime%", "%level%"),
            array($tag, trim($message), $datetime, $this->getStringLevel($level)),
            $this->logMessageFormat
        );

        return $this->next($message, $level, $tag, $datetime, $logger);
    }

    private function getStringLevel($level)
    {
        static $levelToName = array(
            Log::NONE    => 'NONE',
            Log::ERROR   => 'ERROR',
            Log::WARN    => 'WARN',
            Log::INFO    => 'INFO',
            Log::DEBUG   => 'DEBUG',
            Log::VERBOSE => 'VERBOSE'
        );
        return $levelToName[$level];
    }
}
