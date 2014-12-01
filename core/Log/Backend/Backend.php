<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Backend;

use Piwik\Log;
use Piwik\Log\Formatter\Formatter;

/**
 * Log backend.
 */
abstract class Backend
{
    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public abstract function __invoke($level, $tag, $datetime, $message, Log $logger);

    /**
     * Formats the log message using the configured formatter.
     *
     * @param int $level
     * @param string $tag
     * @param string $datetime
     * @param string $message
     * @param Log $logger
     * @return string
     */
    protected function formatMessage($level, $tag, $datetime, $message, Log $logger)
    {
        return trim($this->formatter->format($message, $level, $tag, $datetime, $logger));
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
