<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log;

use Piwik\ExceptionHandler;

/**
 * Proxy class for \Monolog\Logger
 * @see \Monolog\Logger
 */
class Logger extends \Monolog\Logger implements LoggerInterface
{
    public function addRecord($level, $message, array $context = array())
    {
        // if we're logging an exception, make sure we only log the message and not the stack trace if not permitted
        if (array_key_exists('exception', $context) && is_a($context['exception'], \Exception::class)) {
            if (!ExceptionHandler::shouldPrintBackTraceWithMessage()) {
                $context['exception'] = $context['exception']->getMessage();
            }
        }

        return parent::addRecord($level, $message, $context);
    }
}
