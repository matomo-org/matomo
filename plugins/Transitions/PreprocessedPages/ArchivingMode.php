<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Option;

/**
 * Whether reports are archived by cron (`core:archive`) or by browser requests only.
 */
final class ArchivingMode
{
    /**
     * A `core:archive` run that finished longer ago than this doesn't count as cron archiving.
     */
    public const CRON_RECENT_SECONDS = 3 * 86400;

    /**
     * Browser requests archive, and `core:archive` hasn't finished a run recently. Browser triggering alone says
     * nothing, as installs with cron archiving often keep it on.
     */
    public static function isWithoutCron(): bool
    {
        if (!Rules::isBrowserTriggerEnabled()) {
            return false;
        }

        $lastFinished = Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS);

        return !is_numeric($lastFinished) || Date::now()->getTimestamp() - (int) $lastFinished > self::CRON_RECENT_SECONDS;
    }
}
