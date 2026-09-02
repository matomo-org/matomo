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
use Piwik\Db;

/**
 * Reads back what the ArchivingMetrics plugin recorded for a run.
 *
 * The archiving time that matters is not the wall clock of the core:archive process. That
 * includes bootstrap, the invalidation scan, scheduled tasks and any other archive the run
 * decided to build. ArchivingMetrics writes one row per archive with total_time and
 * total_time_exclusive (the latter with nested sub-archives subtracted), keyed by site,
 * period, dates and the done flag - which is the number to compare between engines.
 *
 * Rows are found by watermark rather than by deleting the plugin's rows first: MAX(metadataid)
 * is taken before the run and anything above it belongs to the run. Non-destructive, and it
 * also catches the archives the run built that the benchmark did not ask for - worth knowing,
 * because those are in the wall clock too.
 *
 * The table lives in MySQL for both legs, so the same reader serves both.
 *
 * Note this only ever sees all-plugin archives. ArchivingMetrics deliberately skips done flags
 * containing a '.', so a plugin-scoped archive (core:archive --force-report, or an archive
 * request with plugin=X) produces no row and the harness falls back to the wall clock. That is
 * why the archive cases default to archiving every plugin.
 */
final class MetricsReader
{
    private string $table;

    public function __construct()
    {
        $this->table = Common::prefixTable('archiving_metrics');
    }

    public function isAvailable(): bool
    {
        try {
            Db::fetchOne('SELECT MAX(metadataid) FROM ' . $this->table);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return int 0 when the table is empty or absent
     */
    public function watermark(): int
    {
        try {
            return (int) Db::fetchOne('SELECT COALESCE(MAX(metadataid), 0) FROM ' . $this->table);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Everything recorded after $watermark for one site, split into the archives the case asked
     * for and everything else the run also built.
     *
     * @return array{matched: array<int, array<string, mixed>>, other: array<int, array<string, mixed>>}
     */
    public function since(int $watermark, int $idSite, string $doneFlag): array
    {
        try {
            $rows = Db::fetchAll(
                'SELECT metadataid, idarchive, idsite, archive_name, date1, date2, period,
                        ts_started, ts_finished, total_time, total_time_exclusive, is_temporary
                   FROM ' . $this->table . '
                  WHERE metadataid > ? AND idsite = ?
                  ORDER BY metadataid ASC',
                [$watermark, $idSite]
            );
        } catch (Exception $e) {
            return ['matched' => [], 'other' => []];
        }

        $matched = [];
        $other = [];
        foreach ($rows as $row) {
            $name = (string) $row['archive_name'];
            if ($name === $doneFlag || strpos($name, $doneFlag . '.') === 0) {
                $matched[] = $row;
            } else {
                $other[] = $row;
            }
        }

        return ['matched' => $matched, 'other' => $other];
    }

    /**
     * total_time and total_time_exclusive are stored in MILLISECONDS: Timer::complete() takes a
     * difference of microtime(true) values, which is seconds, and multiplies by 1000. The local
     * variable there is named $totalTimeMs before the multiplication, which reads like the value
     * is already milliseconds - it is not. ArchivingMetrics' own unit test pins it: a 1.2 second
     * archive is written as 1200.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return float|null null when nothing was recorded, which is a different thing from zero
     */
    public static function sumMs(array $rows, string $column = 'total_time'): ?float
    {
        if (empty($rows)) {
            return null;
        }

        $total = 0.0;
        foreach ($rows as $row) {
            $total += (float) $row[$column];
        }

        return $total;
    }
}
