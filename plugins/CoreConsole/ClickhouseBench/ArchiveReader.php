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
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Period;

/**
 * Reads back the reports an archive case actually produced.
 *
 * An archive case is timed on how long it took to aggregate the log tables, but a timing is
 * only worth having if both engines aggregated them to the same answer. The archiving request
 * returns nothing that would show that - its response carries nb_visits and the new archive
 * ids, so a fingerprint taken from the response can only ever compare a visit count. Two
 * engines can agree on how many visits a day had and still disagree about every report built
 * from them.
 *
 * What archiving produced is in the archive tables: archive_numeric_YYYY_MM holds one row per
 * metric and archive_blob_YYYY_MM one row per report. Those tables live in MySQL for both legs
 * - only the log tables move to the analytics database - so the same reader serves both, and a
 * difference between the two reads is a difference in what the engines computed rather than a
 * difference in how two stores render it.
 *
 * Archives are found by done flag rather than by archive id, because the id is new on every
 * run. The flag is the case's identity: one all-plugins flag, plus any plugin-scoped flags
 * ("<flag>.VisitsSummary") the run happened to build.
 */
final class ArchiveReader
{
    /**
     * Whether this period's archive tables exist. They are created on demand, so a month that
     * has never been archived on this instance has no table to read.
     */
    public function isAvailable(Period $period): bool
    {
        try {
            return ArchiveTableCreator::getNumericTable($period->getDateStart(), false) !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Every metric and every report belonging to one case's archives.
     *
     * Only the newest archive is taken per done flag. Without --purge-archives an earlier,
     * superseded archive for the same flag can still be present, and including it would digest
     * a report the case did not just build.
     *
     * @return array{
     *     numeric: array<int, array{name: string, value: string}>,
     *     blob: array<int, array{name: string, value: string}>
     * }
     *         empty arrays when nothing matches, which the caller must treat as "no evidence"
     *         rather than as agreement
     */
    public function read(int $idSite, Period $period, string $doneFlag): array
    {
        $empty = ['numeric' => [], 'blob' => []];

        $numericTable = ArchiveTableCreator::getNumericTable($period->getDateStart(), false);
        if ($numericTable === null) {
            return $empty;
        }

        $idArchives = $this->idArchivesFor($numericTable, $idSite, $period, $doneFlag);
        if (empty($idArchives)) {
            return $empty;
        }

        // The ids are read straight back out of the same table and cast to int, so they are
        // safe to inline - and inlining keeps this to one statement whatever the archive count.
        $ids = implode(',', $idArchives);

        try {
            $numeric = Db::fetchAll(
                'SELECT name, value FROM ' . $numericTable . ' WHERE idarchive IN (' . $ids . ')'
            );
        } catch (Exception $e) {
            return $empty;
        }

        $blob = [];
        $blobTable = ArchiveTableCreator::getBlobTable($period->getDateStart(), false);
        if ($blobTable !== null) {
            try {
                $blob = Db::fetchAll(
                    'SELECT name, value FROM ' . $blobTable . ' WHERE idarchive IN (' . $ids . ')'
                );
            } catch (Exception $e) {
                $blob = [];
            }
        }

        return ['numeric' => $numeric, 'blob' => $blob];
    }

    /**
     * The newest archive id for the case's done flag and for every plugin-scoped flag under it.
     *
     * @return int[]
     */
    private function idArchivesFor(string $numericTable, int $idSite, Period $period, string $doneFlag): array
    {
        try {
            $rows = Db::fetchAll(
                'SELECT name, MAX(idarchive) AS idarchive
                   FROM ' . $numericTable . '
                  WHERE idsite = ? AND period = ? AND date1 = ? AND date2 = ?
                    AND (name = ? OR name LIKE ?)
                  GROUP BY name',
                [
                    $idSite,
                    $period->getId(),
                    $period->getDateStart()->toString('Y-m-d'),
                    $period->getDateEnd()->toString('Y-m-d'),
                    $doneFlag,
                    // Escaped so a done flag containing _ or % could not widen the match. It is
                    // a hash so it will not, but the query should not depend on that.
                    addcslashes($doneFlag, '_%\\') . '.%',
                ]
            );
        } catch (Exception $e) {
            return [];
        }

        return array_map('intval', array_column($rows, 'idarchive'));
    }
}
