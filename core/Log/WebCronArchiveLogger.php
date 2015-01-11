<?php

namespace Piwik\Log;

use Monolog\Logger;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Basic logger that streams messages to stdout.
 *
 * This logger is used in the archiving when archive.php is called from a HTTP request. In that specific case,
 * we want to log to stdout.
 *
 * @see misc/cron/archive.php
 */
class WebCronArchiveLogger extends AbstractLogger implements LoggerInterface
{
    public function log($level, $message, array $context = array())
    {
        if ($level <= Logger::DEBUG) {
            return;
        }
        echo $message . "\n";
    }
}
