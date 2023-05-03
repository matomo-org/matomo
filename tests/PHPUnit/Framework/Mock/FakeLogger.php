<?php

namespace Piwik\Tests\Framework\Mock;

use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\AbstractLogger;
use Piwik\Log\LoggerInterface;

class FakeLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var string
     */
    public $output = '';

    /**
     * @var PsrLogMessageProcessor
     */
    private $processor;

    public function __construct()
    {
        $this->processor = new PsrLogMessageProcessor();
    }

    public function log($level, $message, array $context = array())
    {
        if (strpos($message, 'Running command') !== false) {
            return;
        }

        $record = $this->processor->__invoke(array('message' => $message, 'context' => $context));

        $this->output .= $record['message'] . PHP_EOL;
    }
}
