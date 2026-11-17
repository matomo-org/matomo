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
use Piwik\Plugins\SitesManager\Model as SitesModel;

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
     * cron went through the website is recorded only for websites with selected pages. Until then, the last
     * `core:archive` run of the install counts, while the website has no page or added its first one recently: a
     * website `core:archive` skips gets no record, and counts as without cron once its pages are older.
     */
    public static function isWithoutCron(int $idSite): bool
    {
        if (!Rules::isBrowserTriggerEnabled()) {
            return false;
        }

        $now = Date::now()->getTimestamp();
        $lastArchived = Option::get(self::OPTION_PREFIX . $idSite);
        if (!is_numeric($lastArchived)) {
            $oldestPageCreated = StaticContainer::get(Dao::class)->getOldestPageCreatedDate($idSite);
            if ($oldestPageCreated !== null && $now - Date::factory($oldestPageCreated)->getTimestamp() > self::CRON_RECENT_SECONDS) {
                return true;
            }

            // a run that ends with errors records no finish, so the start of the latest run counts too
            $lastArchived = max((int) Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS), (int) Option::get(CronArchive::OPTION_ARCHIVING_STARTED_TS)) ?: null;
        }

        return !is_numeric($lastArchived) || $now - (int) $lastArchived > self::CRON_RECENT_SECONDS;
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

    /**
     * Forgets the websites deleted while the plugin was deactivated.
     */
    public static function forgetDeletedSites(): void
    {
        $idSites = array_map('intval', StaticContainer::get(SitesModel::class)->getSitesId());
        foreach (array_keys(Option::getLike(self::OPTION_PREFIX . '%')) as $name) {
            $name = (string) $name;
            if (!in_array((int) substr($name, strlen(self::OPTION_PREFIX)), $idSites, true)) {
                Option::delete($name);
            }
        }
    }
}
