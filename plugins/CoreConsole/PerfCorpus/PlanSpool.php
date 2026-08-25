<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * The plan spool: one fixed-width binary record per planned visit, in per-(day, shard) files.
 *
 * The spool exists so that per-visitor history can be exact. A visitor's visits are spread over
 * days - that is the whole point of bottleneck B1 - but the load phase works one day at a time,
 * so something has to carry "this is that visitor's fourth visit, 11 days after their third"
 * across days. Recomputing it on the fly would mean replaying every visitor's whole life once
 * per day; 24 bytes per visit on local disk is far cheaper (8.8 GB at p200).
 *
 * A file belongs to exactly one shard, so nothing ever appends to the same file twice. It is
 * written as .part and renamed only once the whole shard is finished, so a half-written shard
 * can never be mistaken for a complete one after a crash.
 */
class PlanSpool
{
    public const RECORD_SIZE = 24;

    /**
     * visitorOrdinal, visitIndex, actionCount, secondsSinceFirst, secondsSinceLast,
     * startSecondOfDay, idsite, flags, itemCount
     */
    private const PACK_FORMAT = 'VvvVVVvCC';
    private const UNPACK_FORMAT = 'Vordinal/vvisitIndex/vactionCount/VsecondsSinceFirst'
        . '/VsecondsSinceLast/VstartSecond/vidsite/Cflags/CitemCount';

    public const FLAG_GOAL_CONVERSION = 1;
    public const FLAG_ECOMMERCE_ORDER = 2;
    public const FLAG_MEGA_VISIT = 4;
    public const FLAG_HAS_USER_ID = 8;

    /** Per-day buffer is flushed once it passes this, keeping memory at roughly days x this. */
    private const FLUSH_THRESHOLD_BYTES = 65536;

    /** Hard ceiling across all buffers; the fullest day is flushed when it is reached. */
    private const MAX_BUFFERED_BYTES = 33554432;

    private string $spoolDir;
    private int $shard;

    /** @var string[] day index => the date it maps to */
    private array $dates;

    /** @var string[] day index => pending bytes */
    private array $buffers = [];

    private int $bufferedBytes = 0;

    public function __construct(string $spoolDir, int $shard, array $dates)
    {
        $this->spoolDir = $spoolDir;
        $this->shard = $shard;
        $this->dates = $dates;
    }

    public function append(
        int $dayIndex,
        int $visitorOrdinal,
        int $visitIndex,
        int $actionCount,
        int $secondsSinceFirst,
        int $secondsSinceLast,
        int $startSecond,
        int $idSite,
        int $flags,
        int $itemCount
    ): void {
        $record = pack(
            self::PACK_FORMAT,
            $visitorOrdinal,
            $visitIndex,
            $actionCount,
            $secondsSinceFirst,
            $secondsSinceLast,
            $startSecond,
            $idSite,
            $flags,
            $itemCount
        );

        if (!isset($this->buffers[$dayIndex])) {
            $this->buffers[$dayIndex] = '';
        }

        $this->buffers[$dayIndex] .= $record;
        $this->bufferedBytes += self::RECORD_SIZE;

        if (strlen($this->buffers[$dayIndex]) >= self::FLUSH_THRESHOLD_BYTES) {
            $this->flushDay($dayIndex);

            return;
        }

        if ($this->bufferedBytes >= self::MAX_BUFFERED_BYTES) {
            $this->flushLargest();
        }
    }

    private function flushDay(int $dayIndex): void
    {
        if (empty($this->buffers[$dayIndex])) {
            return;
        }

        $path = $this->partPath($dayIndex);
        $this->ensureDirectory(dirname($path));

        if (false === file_put_contents($path, $this->buffers[$dayIndex], FILE_APPEND | LOCK_EX)) {
            throw new \RuntimeException('Could not write the plan spool: ' . $path);
        }

        $this->bufferedBytes -= strlen($this->buffers[$dayIndex]);
        $this->buffers[$dayIndex] = '';
    }

    private function flushLargest(): void
    {
        $largestDay = null;
        $largestSize = 0;

        foreach ($this->buffers as $dayIndex => $buffer) {
            $size = strlen($buffer);
            if ($size > $largestSize) {
                $largestSize = $size;
                $largestDay = $dayIndex;
            }
        }

        if (null !== $largestDay) {
            $this->flushDay($largestDay);
        }
    }

    /**
     * Flushes everything and publishes the shard by renaming each .part into place. rename() is
     * atomic within a filesystem, so a reader either sees the finished file or no file at all.
     */
    public function finish(): void
    {
        foreach (array_keys($this->buffers) as $dayIndex) {
            $this->flushDay($dayIndex);
        }

        foreach (array_keys($this->dates) as $dayIndex) {
            $part = $this->partPath($dayIndex);

            if (!file_exists($part)) {
                continue;
            }

            if (!rename($part, $this->finalPath($dayIndex))) {
                throw new \RuntimeException('Could not publish the plan spool file: ' . $part);
            }
        }

        $this->buffers = [];
        $this->bufferedBytes = 0;
    }

    /**
     * Throws away a half-written shard, so a retry starts from nothing rather than appending to
     * what the dead process left behind.
     */
    public function discard(): void
    {
        $this->buffers = [];
        $this->bufferedBytes = 0;

        foreach (array_keys($this->dates) as $dayIndex) {
            @unlink($this->partPath($dayIndex));
            @unlink($this->finalPath($dayIndex));
        }
    }

    private function partPath(int $dayIndex): string
    {
        return $this->finalPath($dayIndex) . '.part';
    }

    private function finalPath(int $dayIndex): string
    {
        return self::pathFor($this->spoolDir, $this->dates[$dayIndex], $this->shard);
    }

    public static function pathFor(string $spoolDir, string $date, int $shard): string
    {
        return sprintf('%s/plan/%s/shard-%04d.bin', $spoolDir, $date, $shard);
    }

    private function ensureDirectory(string $dir): void
    {
        // Shards race to create the same day directory; only a genuine failure matters.
        if (!is_dir($dir) && !@mkdir($dir, 0o770, true) && !is_dir($dir)) {
            throw new \RuntimeException('Could not create the plan spool directory: ' . $dir);
        }
    }

    /**
     * Reads a whole day-shard file. A p200 chunk is ~20k visits, so half a megabyte - small
     * enough to hold, and holding it lets the load phase sort by start time so that idvisit
     * ends up ordered by time within the day as well as across days.
     *
     * @return array[] each with ordinal, visitIndex, actionCount, secondsSinceFirst,
     *                 secondsSinceLast, startSecond, idsite, flags, itemCount
     */
    public static function read(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);

        if (false === $raw) {
            throw new \RuntimeException('Could not read the plan spool file: ' . $path);
        }

        $length = strlen($raw);

        if (0 !== $length % self::RECORD_SIZE) {
            throw new \RuntimeException(sprintf(
                'Plan spool file %s is %d bytes, not a whole number of %d-byte records. It was '
                . 'probably left behind by a killed planner; re-run the plan phase for this shard.',
                $path,
                $length,
                self::RECORD_SIZE
            ));
        }

        $records = [];
        for ($offset = 0; $offset < $length; $offset += self::RECORD_SIZE) {
            $records[] = unpack(self::UNPACK_FORMAT, substr($raw, $offset, self::RECORD_SIZE));
        }

        return $records;
    }

    public static function countRecords(string $path): int
    {
        if (!file_exists($path)) {
            return 0;
        }

        return intdiv((int) filesize($path), self::RECORD_SIZE);
    }
}
