<?php

namespace Piwik\Tests\Framework\Mock;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class FakeLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var string
     */
    public $output = '';

    public function log($level, $message, array $context = array())
    {
        $this->output .= $message . PHP_EOL;
    }
}
