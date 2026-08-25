<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Common;
use Piwik\Db;

/**
 * Undoes a chunk, or a whole run, by deleting the primary-key ranges it owns.
 *
 * This is the pay-off from pre-allocating ids. A crashed worker leaves an unknown number of rows
 * behind somewhere in the middle of a 2-billion-row table; without id ranges the only safe
 * recovery would be to throw the whole corpus away. With them, undoing a chunk is a clustered
 * index range scan over that chunk's rows and nothing else - so a retry cannot duplicate, and
 * cannot orphan.
 *
 * log_conversion and log_conversion_item have no auto-increment key of their own (their primary
 * keys start with idvisit), so they are cleaned by the visit range too.
 */
class Cleaner
{
    /**
     * @return int rows deleted
     */
    public static function deleteChunkRows(array $chunk): int
    {
        $deleted = 0;

        $visitStart = $chunk['idvisit_start'] ?? null;
        $visitCount = (int) ($chunk['visit_count'] ?? 0);

        if (null !== $visitStart && $visitCount > 0) {
            $visitStart = (int) $visitStart;
            $visitEnd = $visitStart + $visitCount - 1;

            foreach (['log_conversion_item', 'log_conversion', 'log_visit'] as $table) {
                $deleted += self::deleteRange($table, 'idvisit', $visitStart, $visitEnd);
            }
        }

        $linkVaStart = $chunk['idlink_va_start'] ?? null;
        $actionCount = (int) ($chunk['action_count'] ?? 0);

        if (null !== $linkVaStart && $actionCount > 0) {
            $linkVaStart = (int) $linkVaStart;
            $deleted += self::deleteRange(
                'log_link_visit_action',
                'idlink_va',
                $linkVaStart,
                $linkVaStart + $actionCount - 1
            );
        }

        return $deleted;
    }

    /**
     * Deletes in bounded batches: a single DELETE covering a whole chunk would hold one long
     * transaction and grow the undo log, and this can run while other workers are writing.
     */
    private static function deleteRange(string $table, string $column, int $from, int $to): int
    {
        $prefixed = Common::prefixTable($table);
        $deleted = 0;

        do {
            $statement = Db::query(
                "DELETE FROM `$prefixed` WHERE `$column` BETWEEN ? AND ? LIMIT 50000",
                [$from, $to]
            );
            $rows = $statement->rowCount();
            $deleted += $rows;
        } while ($rows > 0);

        return $deleted;
    }

    /**
     * Everything a run wrote: the union of all its chunk id ranges, plus the log_action rows it
     * created and its spool directory. Used by --restart.
     */
    public static function deleteRunRows(ChunkQueue $queue, string $spoolDir): int
    {
        $table = ChunkQueue::chunkTable();

        $bounds = Db::fetchRow(
            "SELECT MIN(`idvisit_start`) AS `visit_from`,
                    MAX(`idvisit_start` + `visit_count` - 1) AS `visit_to`,
                    MIN(`idlink_va_start`) AS `link_from`,
                    MAX(`idlink_va_start` + `action_count` - 1) AS `link_to`
               FROM `$table`
              WHERE `idrun` = ? AND `phase` = ? AND `idvisit_start` IS NOT NULL",
            [$queue->getRunId(), ChunkQueue::PHASE_LOAD]
        );

        $deleted = 0;

        if (!empty($bounds['visit_from'])) {
            foreach (['log_conversion_item', 'log_conversion', 'log_visit'] as $logTable) {
                $deleted += self::deleteRange(
                    $logTable,
                    'idvisit',
                    (int) $bounds['visit_from'],
                    (int) $bounds['visit_to']
                );
            }
        }

        if (!empty($bounds['link_from'])) {
            $deleted += self::deleteRange(
                'log_link_visit_action',
                'idlink_va',
                (int) $bounds['link_from'],
                (int) $bounds['link_to']
            );
        }

        self::deleteSpool($spoolDir);

        return $deleted;
    }

    public static function deleteSpool(string $spoolDir): void
    {
        if (!is_dir($spoolDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($spoolDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                @rmdir($path->getPathname());
            } else {
                @unlink($path->getPathname());
            }
        }

        @rmdir($spoolDir);
    }
}
