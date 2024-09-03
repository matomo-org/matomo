<?php

namespace Piwik\Tests\Framework\Mock;

use Piwik\Log\LoggerInterface;
use Psr\Log\Test\TestLogger as PsrTestLogger;

class TestLogger extends PsrTestLogger implements LoggerInterface
{
    // provide PSR TestLogger as Piwik\LoggerInterface type
}
