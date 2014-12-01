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
     * @param array $record
     * @param Log $logger
     *
     * @return array Updated record.
     */
    public abstract function format(array $record, Log $logger);

    /**
     * Chain of responsibility pattern.
     *
     * @param Formatter $formatter
     */
    public function setNext(Formatter $formatter)
    {
        $this->next = $formatter;
    }

    protected function next(array $record, Log $logger)
    {
        if (! $this->next) {
            return $record;
        }

        return $this->next->format($record, $logger);
    }
}
