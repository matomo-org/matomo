<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log;

use Piwik\Log;

/**
 * Log backend.
 */
abstract class Backend
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

    public abstract function __invoke($level, $tag, $datetime, $message, Log $logger);

    /**
     * Creates log message combining logging info including a log level, tag name,
     * date time, and caller-provided log message. The log message can be set through
     * the `[log] string_message_format` INI config option. By default it will
     * create log messages like:
     *
     * **LEVEL [tag:datetime] log message**
     *
     * @param int $level
     * @param string $tag
     * @param string $datetime
     * @param string $message
     * @return string
     */
    protected function formatMessage($level, $tag, $datetime, $message)
    {
        return str_replace(
            array("%tag%", "%message%", "%datetime%", "%level%"),
            array($tag, trim($message), $datetime, $this->getStringLevel($level)),
            $this->logMessageFormat
        );
    }

    protected function getStringLevel($level)
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
