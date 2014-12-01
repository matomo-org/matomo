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
 * Formats a log message.
 *
 * Follows the Chain of responsibility design pattern, so don't forget to call `$this->next(...)`
 * at the end of the `format()` method.
 */
abstract class Formatter
{
    /**
     * @var Formatter|null
     */
    protected $next;

    /**
     * @param mixed $message Log message.
     * @param int $level The log level used with this log entry.
     * @param string $tag The current plugin that started logging (or if no plugin, the current class).
     * @param string $datetime Datetime of the logging call.
     * @param Log $logger
     *
     * @return mixed
     */
    public abstract function format($message, $level, $tag, $datetime, Log $logger);

    /**
     * Chain of responsibility pattern.
     *
     * @param Formatter $formatter
     */
    public function setNext(Formatter $formatter)
    {
        $this->next = $formatter;
    }

    protected function next($message, $level, $tag, $datetime, Log $logger)
    {
        if (! $this->next) {
            return $message;
        }

        return $this->next->format($message, $level, $tag, $datetime, $logger);
    }
}
