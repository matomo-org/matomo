<?php

namespace Piwik\Scheduler;

use Piwik\Exception\Exception;

/**
 * Exception a scheduled task can throw to indicate that the task is safe to retry.
 * The scheduler then reschedules the task to run again in one hour, at most three times in a row.
 *
 * @api
 */
class RetryableException extends Exception
{
}
