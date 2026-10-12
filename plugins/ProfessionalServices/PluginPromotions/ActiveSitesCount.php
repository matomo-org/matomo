<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Access;
use Piwik\DataTable;

/**
 * How many of the websites the current user can see had real traffic last week.
 *
 * Two promotions ask this - Crash Analytics and Roll-Up Reporting - and they ask it with
 * identical thresholds. Keeping the answer here rather than in the triggers means a
 * dashboard works it out once however many promotions want it, which matters because the
 * question cannot go in the daily cache: that cache is keyed on a website, while this
 * answer depends on which websites the *user* may see, so a cached answer would be handed
 * to the next user whatever their access.
 *
 * Held for the life of the request only. The container hands both triggers the same
 * instance, and a request renders one dashboard for one user.
 */
class ActiveSitesCount
{
    public const MINIMUM_SITES = 5;

    public const MINIMUM_VISITS_PER_SITE = 100;

    // Every website the user can see is weighed, because the copy quotes the answer back to
    // them as "your %1$s websites" and a capped figure would be wrong on exactly the large
    // instances these two promotions are aimed at. It stays one query however many there
    // are: Archive reads them all in one go, and buildArchiveForSites() opts out of
    // launching archiving, so nothing here is per website.

    private ArchivedReportReader $reader;

    /** @var array<string, int> */
    private array $memo = [];

    public function __construct(ArchivedReportReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * The number of qualifying websites, or 0 when the user cannot see enough websites for
     * the question to be worth asking.
     */
    public function countQualifyingSites(): int
    {
        $idSites = array_map('intval', Access::getInstance()->getSitesIdWithAtLeastViewAccess());
        sort($idSites);

        // Hashed rather than listed: the key is only used to tell one set of websites from
        // another within a request, and an instance with thousands of them would otherwise
        // build a string of every id to look one up.
        $key = md5(implode(',', $idSites));

        if (!array_key_exists($key, $this->memo)) {
            $this->memo[$key] = $this->count($idSites);
        }

        return $this->memo[$key];
    }

    /**
     * @param int[] $idSites
     */
    private function count(array $idSites): int
    {
        if (count($idSites) < self::MINIMUM_SITES) {
            return 0;
        }

        $archive = $this->reader->buildArchiveForSites($idSites, ReportPeriod::PERIOD, ReportPeriod::DATE);
        $visits = $archive->getDataTableFromNumeric(['nb_visits']);

        $tables = $visits instanceof DataTable\Map ? $visits->getDataTables() : [$visits];

        $qualifying = 0;

        foreach ($tables as $table) {
            $row = $table instanceof DataTable ? $table->getFirstRow() : false;

            if (!empty($row) && (int) $row->getColumn('nb_visits') >= self::MINIMUM_VISITS_PER_SITE) {
                $qualifying++;
            }
        }

        return $qualifying;
    }
}
