<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

/**
 * Handler used to detect whether a certain level of log has been emitted.
 */
class FailureLogMessageDetector extends AbstractHandler
{
    /**
     * @var boolean
     */
    private $hasEncounteredImportantLog = false;

    public function __construct($level = Logger::WARNING)
    {
        parent::__construct($level, $bubble = true);
    }

    public function handle(array $record)
    {
        if ($this->isHandling($record)) {
            $this->hasEncounteredImportantLog = true;
        }
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
    public function reset()
    {
        $this->hasEncounteredImportantLog = false;
    }
}
