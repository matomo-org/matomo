<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Level;
use Monolog\LogRecord;

/**
 * Takes the place of the screen writer for web tracking requests, which can't show anything on screen:
 * writes the errors the tracker answers a request with to PHP's error log, but no other records, as these
 * can carry visitor data on every request.
 */
class TrackerErrorLogHandler extends ErrorLogHandler
{
    // context flag of the records the tracker logs for the errors it answers a request with
    private const CONTEXT_TRACKER_ERROR = 'trackerError';

    public function isHandling(LogRecord $record): bool
    {
        if (
            $record->level->isLowerThan(Level::Error)
            || empty($record->context[self::CONTEXT_TRACKER_ERROR])
            || !empty($record->context['ignoreInScreenWriter'])
        ) {
            return false;
        }

        return parent::isHandling($record);
    }
}
