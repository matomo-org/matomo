<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Writes log to stderr.
 */
class StdErrHandler extends AbstractProcessingHandler
{
    /**
     * @var bool
     */
    private $isLoggingToStdOut;

    public function __construct(FormatterInterface $formatter, $isLoggingToStdOut)
    {
        $this->isLoggingToStdOut = $isLoggingToStdOut;

        // Log level is hardcoded for this one
        $level = Logger::ERROR;

        parent::__construct($level);

        $this->setFormatter($formatter);
    }

    protected function write(array $record)
    {
        $message = $record['formatted']['message'] . "\n";

        // Do not log on stderr during tests (prevent display of errors in CI output)
        if (! defined('PIWIK_TEST_MODE')) {
            $this->writeToStdErr($message);
        }

        // This is the result of an old hack, I guess to force the error message to be displayed in case of errors
        // TODO we should get rid of it somehow
        if (! $this->isLoggingToStdOut) {
            echo $message;
        }
    }

    private function writeToStdErr($message)
    {
        $fe = fopen('php://stderr', 'w');
        fwrite($fe, $message);
    }
}
