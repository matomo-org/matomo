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
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Option;

/**
 * Whether a website's reports are archived by cron (`core:archive`) or by browser requests only.
 */
final class ArchivingMode
{
    /**
     * A `core:archive` run that last went through the website longer ago than this doesn't count as cron archiving.
     */
    public const CRON_RECENT_SECONDS = 3 * 86400;

    private const OPTION_PREFIX = 'Transitions.lastCronArchiving_';

    /**
     * A more recent record isn't written again, so frequent `core:archive` runs don't write it every time.
     */
    private const REWRITE_AFTER_SECONDS = 3600;

    /**
     * Browser requests archive, and `core:archive` hasn't gone through the website recently. Browser triggering alone
     * says nothing, as installs with cron archiving often keep it on, and cron may archive only some websites. The time
     * cron went through the website is recorded only for websites with selected pages; until then, the last successful
     * `core:archive` run of the install counts.
     */
    public static function isWithoutCron(int $idSite): bool
    {
        if (!Rules::isBrowserTriggerEnabled()) {
            return false;
        }

        $lastArchived = Option::get(self::OPTION_PREFIX . $idSite);
        if (!is_numeric($lastArchived)) {
            $lastArchived = Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS);
        }

        return !is_numeric($lastArchived) || Date::now()->getTimestamp() - (int) $lastArchived > self::CRON_RECENT_SECONDS;
    }

    /**
     * Called when `core:archive` has gone through the website, whether or not one of its archives failed.
     */
    public static function rememberCronArchiving(int $idSite): void
    {
        if (!StaticContainer::get(Dao::class)->hasPagesForSite($idSite)) {
            return;
        }

        $now = Date::now()->getTimestamp();
        $lastArchived = Option::get(self::OPTION_PREFIX . $idSite);
        if (is_numeric($lastArchived) && $now - (int) $lastArchived < self::REWRITE_AFTER_SECONDS) {
            return;
        }

        Option::set(self::OPTION_PREFIX . $idSite, (string) $now);
    }

    public static function forgetSite(int $idSite): void
    {
        Option::delete(self::OPTION_PREFIX . $idSite);
    }
}
