<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use Exception;
use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period;

/**
 * Removes every trace of a previous run of one case, so the next iteration starts cold.
 *
 * Invalidation alone is not enough, for two reasons that both make a leg look faster than it is:
 *
 * 1. **The archive rows survive.** Every iteration writes a new idarchive and the old ones stay,
 *    so later iterations carry `deleteOlderArchives` work the first one did not, and the tables
 *    grow for the rest of the benchmark.
 * 2. **The invalidations survive, and archiving reads them.**
 *    {@see \Piwik\ArchiveProcessor\Loader} decides whether it may skip an archive by asking
 *    `hasInvalidationForPeriodAndName()`, and CronArchive's queue consumer dedupes against
 *    `getInvalidationsInProgress()`. A row left behind by the previous iteration can therefore
 *    change what the next one does - and the failure is silent, because "skipped" and "archived
 *    very quickly" look identical in a timing.
 *
 * Scope is exactly one site, one period, one date range and one segment's done-flag family.
 * Deliberately not "all archive tables": on a shared instance that would destroy every other
 * site's reports, and it buys nothing - an archive for a different site or day cannot be reused
 * for this one.
 */
final class ArchiveScrubber
{
    /**
     * @return array{archives: int, rows: int, invalidations: int, tables: string[]}
     */
    public function scrub(int $idSite, Period $period, string $doneFlag): array
    {
        $date1 = $period->getDateStart()->toString('Y-m-d');
        $date2 = $period->getDateEnd()->toString('Y-m-d');
        $periodId = $period->getId();

        $archives = 0;
        $rows = 0;
        $tables = [];

        foreach ($this->archiveTables($period) as $pair) {
            $idArchives = $this->findArchiveIds($pair['numeric'], $idSite, $date1, $date2, $periodId, $doneFlag);
            if (empty($idArchives)) {
                continue;
            }

            $archives += count($idArchives);
            $rows += $this->deleteFrom($pair['numeric'], $idArchives);
            if (null !== $pair['blob']) {
                $rows += $this->deleteFrom($pair['blob'], $idArchives);
            }
            $tables[] = $pair['numeric'];
        }

        return [
            'archives' => $archives,
            'rows' => $rows,
            'invalidations' => $this->deleteInvalidations($idSite, $date1, $date2, $periodId, $doneFlag),
            'tables' => $tables,
        ];
    }

    /**
     * @return int[]
     */
    private function findArchiveIds(
        string $table,
        int $idSite,
        string $date1,
        string $date2,
        int $periodId,
        string $doneFlag
    ): array {
        try {
            $result = Db::fetchAll(
                "SELECT DISTINCT idarchive FROM `$table`
                  WHERE idsite = ? AND date1 = ? AND date2 = ? AND period = ?
                    AND (name = ? OR name LIKE ?)",
                [$idSite, $date1, $date2, $periodId, $doneFlag, $doneFlag . '.%']
            );
        } catch (Exception $e) {
            return [];
        }

        return array_map(static fn(array $row): int => (int) $row['idarchive'], $result);
    }

    /**
     * @param int[] $idArchives
     */
    private function deleteFrom(string $table, array $idArchives): int
    {
        $ids = implode(',', array_map('intval', $idArchives));

        try {
            return Db::query("DELETE FROM `$table` WHERE idarchive IN ($ids)")->rowCount();
        } catch (Exception $e) {
            // Blob tables are created on demand and may legitimately be absent.
            return 0;
        }
    }

    private function deleteInvalidations(
        int $idSite,
        string $date1,
        string $date2,
        int $periodId,
        string $doneFlag
    ): int {
        $table = Common::prefixTable('archive_invalidations');

        try {
            return Db::query(
                "DELETE FROM `$table`
                  WHERE idsite = ? AND date1 = ? AND date2 = ? AND period = ?
                    AND (name = ? OR name LIKE ?)",
                [$idSite, $date1, $date2, $periodId, $doneFlag, $doneFlag . '.%']
            )->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Archives live in per-month tables and a period can span more than one, so the months are
     * walked rather than derived from the start date. Missing tables are skipped:
     * getNumericTable(..., false) returns null instead of creating one, because a benchmark has
     * no business creating archive tables as a side effect of measuring.
     *
     * @return array<int, array{numeric: string, blob: ?string}>
     */
    private function archiveTables(Period $period): array
    {
        $tables = [];
        $cursor = $period->getDateStart()->setDay(1);
        $last = $period->getDateEnd()->setDay(1);

        while ($cursor->isEarlier($last) || $cursor->toString('Y-m') === $last->toString('Y-m')) {
            $numeric = ArchiveTableCreator::getNumericTable($cursor, false);
            if (!empty($numeric)) {
                $tables[] = [
                    'numeric' => $numeric,
                    'blob' => ArchiveTableCreator::getBlobTable($cursor, false),
                ];
            }

            $cursor = $cursor->addMonth(1)->setDay(1);
        }

        return $tables;
    }

    /**
     * @return Date the first day of the month whose archive table holds $date
     */
    public static function tableMonth(Date $date): Date
    {
        return $date->setDay(1);
    }
}
