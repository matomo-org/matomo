<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\LogRecord;
use Piwik\Log\Logger;

/**
 * Handler used to detect whether a certain level of log has been emitted.
 */
class FailureLogMessageDetector extends AbstractHandler
{
    /**
     * @var boolean
     */
    private bool $hasEncounteredImportantLog = false;

    public function __construct($level = Logger::WARNING)
    {
        parent::__construct($level, $bubble = true);
    }

    public function handle(LogRecord $record): bool
    {
        if ($this->isHandling($record)) {
            $this->hasEncounteredImportantLog = true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasEncounteredImportantLog()
    {
        return $this->hasEncounteredImportantLog;
    }

    /**
     * for tests
     */
    public function reset(): void
    {
        $this->hasEncounteredImportantLog = false;
    }
}
