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

    /**
     * Write the log record to the backend.
     *
     * @param array $record
     * @param Log $logger
     */
    public abstract function __invoke(array $record, Log $logger);

    /**
     * Formats the log message using the configured formatter.
     *
     * @param array $record
     * @param Log $logger
     * @return string
     */
    protected function formatMessage(array $record, Log $logger)
    {
        $record = $this->formatter->format($record, $logger);

        return trim($record['message']);
    }
}
