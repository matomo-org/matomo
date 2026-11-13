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
     * Browser requests archive, and `core:archive` hasn't gone through the website recently. Browser triggering alone
     * says nothing, as installs with cron archiving often keep it on, and cron may archive only some websites. A
     * website cron hasn't gone through since this was recorded counts as archived without cron.
     */
    public static function isWithoutCron(int $idSite): bool
    {
        if (!Rules::isBrowserTriggerEnabled()) {
            return false;
        }

        $lastArchived = Option::get(self::OPTION_PREFIX . $idSite);

        return !is_numeric($lastArchived) || Date::now()->getTimestamp() - (int) $lastArchived > self::CRON_RECENT_SECONDS;
    }

    /**
     * Called when `core:archive` has gone through the website, whether or not one of its archives failed.
     */
    public static function rememberCronArchiving(int $idSite): void
    {
        Option::set(self::OPTION_PREFIX . $idSite, (string) Date::now()->getTimestamp());
    }

    public static function forgetSite(int $idSite): void
    {
        Option::delete(self::OPTION_PREFIX . $idSite);
    }
}
