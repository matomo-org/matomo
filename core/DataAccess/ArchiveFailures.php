<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Period\Range;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;

/**
 * Archives that core:archive failed to process. Their invalidations stay queued, but are skipped, along with the
 * periods built from them, until the retry delay has passed, and for good once the maximum attempts are reached.
 *
 * @internal
 */
class ArchiveFailures
{
    public const STATE_WAITING = 'waiting';
    public const STATE_RETRY_PENDING = 'retry pending';
    public const STATE_GIVEN_UP = 'given up';

    private const DEFAULT_MAX_ATTEMPTS = 5;
    private const DEFAULT_RETRY_DELAYS = [3600, 21600, 86400, 86400];
    private const MAX_RETRY_DELAY = 31536000;
    private const MAX_ERROR_LENGTH = 1000;
    private const INVALIDATIONS_CHUNK_SIZE = 50000;
    private const FAILURES_CHUNK_SIZE = 10000;

    private static bool $isAvailable = false;

    public static function getTable(): string
    {
        return Common::prefixTable('archive_failures');
    }

    /**
     * The table does not exist yet while the database upgrade that adds it is pending.
     */
    public static function isAvailable(): bool
    {
        if (!self::$isAvailable) {
            self::$isAvailable = DbHelper::tableExists(self::getTable());
        }

        return self::$isAvailable;
    }

    public static function getMaxAttempts(int $idSite): int
    {
        $maxAttempts = GeneralConfig::getIntegerConfigValue('archive_failure_max_attempts', self::DEFAULT_MAX_ATTEMPTS, $idSite);

        return max(1, $maxAttempts);
    }

    /**
     * Seconds to wait before retrying an archive that failed for the given number of times.
     */
    public static function getRetryDelay(int $idSite, int $attempts): int
    {
        $configuredDelays = GeneralConfig::getConfigValue('archive_failure_retry_delays', $idSite);
        $configuredDelays = is_array($configuredDelays) ? $configuredDelays : explode(',', (string) $configuredDelays);

        $delays = [];
        foreach ($configuredDelays as $delay) {
            $delay = trim((string) $delay);
            if (!ctype_digit($delay) || (int) $delay === 0) {
                // an invalid list is ignored as a whole rather than misread, eg "1h" as 1 second
                StaticContainer::get(LoggerInterface::class)->warning(
                    'Invalid value for [General] archive_failure_retry_delays, it must be a comma separated list of seconds. Using the default instead.'
                );
                $delays = self::DEFAULT_RETRY_DELAYS;
                break;
            }

            $delays[] = min((int) $delay, self::MAX_RETRY_DELAY);
        }

        if (empty($delays)) {
            $delays = self::DEFAULT_RETRY_DELAYS;
        }

        return $delays[min(max($attempts, 1), count($delays)) - 1];
    }

    /**
     * Records a failed attempt to process the archive of the given invalidation.
     *
     * @return array{attempts: int, maxAttempts: int, retryAfter: string, givenUp: bool}|null null if failures can't be recorded yet
     */
    public function recordFailure(array $invalidation, string $error): ?array
    {
        if (!self::isAvailable()) {
            return null;
        }

        $idSite = (int) $invalidation['idsite'];
        $now = Date::now();
        $error = $this->trimError($error);

        // the row must never look retryable before its final retry time is set below
        $retryAfter = $this->getRetryAfter($now, $idSite, 1);

        $table = self::getTable();
        Db::query(
            "INSERT INTO `$table` (idsite, period, date1, date2, archive_hash, name, `report`, attempts, last_error, ts_first_failed, ts_last_failed, ts_retry_after)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_error = ?, ts_last_failed = ?, ts_retry_after = ?",
            array_merge(
                $this->getArchiveBind($invalidation),
                [$invalidation['name'], (string) ($invalidation['report'] ?? '')],
                [$error, $now->getDatetime(), $now->getDatetime(), $retryAfter],
                [$error, $now->getDatetime(), $retryAfter]
            )
        );

        $attempts = (int) Db::fetchOne(
            "SELECT attempts FROM `$table` WHERE " . $this->getArchiveWhere(),
            $this->getArchiveBind($invalidation)
        );

        $retryAfter = $this->getRetryAfter($now, $idSite, $attempts);
        Db::query(
            "UPDATE `$table` SET ts_retry_after = ? WHERE " . $this->getArchiveWhere(),
            array_merge([$retryAfter], $this->getArchiveBind($invalidation))
        );

        $maxAttempts = self::getMaxAttempts($idSite);

        return [
            'attempts' => $attempts,
            'maxAttempts' => $maxAttempts,
            'retryAfter' => $retryAfter,
            'givenUp' => $attempts >= $maxAttempts,
        ];
    }

    /**
     * Removes the failure record of the archive of the given invalidation, once that archive was processed.
     */
    public function deleteFailure(array $invalidation): int
    {
        if (!self::isAvailable()) {
            return 0;
        }

        return Db::query(
            "DELETE FROM `" . self::getTable() . "` WHERE " . $this->getArchiveWhere(),
            $this->getArchiveBind($invalidation)
        )->rowCount();
    }

    /**
     * @param int[] $idSites restricts the result to these sites, all sites if empty
     * @param bool $withError whether to include the last_error column
     * @return array[] failure rows, oldest first, each with its 'state'
     */
    public function getFailures(array $idSites = [], bool $givenUpOnly = false, bool $withError = true): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        $columns = $withError
            ? 'f.*'
            : 'f.idfailure, f.idsite, f.name, f.period, f.date1, f.date2, f.report, f.attempts, f.ts_first_failed, f.ts_last_failed, f.ts_retry_after';

        // failures of deleted sites are removed by a scheduled task
        $sql = "SELECT $columns FROM `" . self::getTable() . "` f INNER JOIN `" . Common::prefixTable('site') . "` s ON s.idsite = f.idsite";
        if (!empty($idSites)) {
            $sql .= " WHERE f.idsite IN (" . implode(',', array_map('intval', $idSites)) . ")";
        }
        $sql .= " ORDER BY f.ts_first_failed ASC, f.idfailure ASC";

        $now = Date::now()->getDatetime();
        $maxAttempts = [];
        $failures = [];
        foreach (Db::fetchAll($sql) as $failure) {
            $idSite = (int) $failure['idsite'];
            $maxAttempts[$idSite] = $maxAttempts[$idSite] ?? self::getMaxAttempts($idSite);
            $failure['state'] = $this->getState($failure, $now, $maxAttempts[$idSite]);

            if (!$givenUpOnly || $failure['state'] === self::STATE_GIVEN_UP) {
                $failures[] = $failure;
            }
        }

        return $failures;
    }

    /**
     * Like getFailures(), but loads them in chunks, in the order they were first recorded, to keep the memory use
     * bounded for any number of failures.
     *
     * @param int[] $idSites restricts the result to these sites, all sites if empty
     * @return \Generator<array> failure rows, each with its 'state'
     */
    public function iterateFailures(array $idSites = [], bool $givenUpOnly = false, bool $withError = true, int $chunkSize = 1000): \Generator
    {
        if (!self::isAvailable()) {
            return;
        }

        $columns = $withError
            ? 'f.*'
            : 'f.idfailure, f.idsite, f.name, f.period, f.date1, f.date2, f.report, f.attempts, f.ts_first_failed, f.ts_last_failed, f.ts_retry_after';
        $siteCondition = empty($idSites) ? '' : ' AND f.idsite IN (' . implode(',', array_map('intval', $idSites)) . ')';

        $now = Date::now()->getDatetime();
        $maxAttempts = [];
        $lastIdFailure = 0;

        do {
            $rows = Db::fetchAll(
                "SELECT $columns FROM `" . self::getTable() . "` f INNER JOIN `" . Common::prefixTable('site') . "` s ON s.idsite = f.idsite
                  WHERE f.idfailure > ?$siteCondition ORDER BY f.idfailure ASC LIMIT " . (int) $chunkSize,
                [$lastIdFailure]
            );

            foreach ($rows as $failure) {
                $lastIdFailure = (int) $failure['idfailure'];
                $idSite = (int) $failure['idsite'];
                $maxAttempts[$idSite] = $maxAttempts[$idSite] ?? self::getMaxAttempts($idSite);
                $failure['state'] = $this->getState($failure, $now, $maxAttempts[$idSite]);

                if (!$givenUpOnly || $failure['state'] === self::STATE_GIVEN_UP) {
                    yield $failure;
                }
            }
        } while (count($rows) === $chunkSize);
    }

    /**
     * Releases invalidations put on hold because of a failure (status ON_HOLD back to QUEUED) once no failure blocks
     * them anymore: the retry delay passed, or the failure was removed (archive processed, reset, cleaned up).
     *
     * @param array|null $archive only release what this archive held, after it was processed
     * @return int number of released invalidations
     */
    public function releaseUnblockedInvalidations(int $idSite, ?array $archive = null): int
    {
        if (!self::isAvailable()) {
            return 0;
        }

        $invalidationsTable = Common::prefixTable('archive_invalidations');
        $failureCondition = '';
        $failureBind = [];
        $invalidationCondition = '';
        $invalidationBind = [];
        if (null !== $archive) {
            $failureCondition = ' AND f.archive_hash = ?';
            $failureBind = [$this->getArchiveHash($archive)];
            $invalidationCondition = " AND name = ? AND IFNULL(`report`, '') = ?";
            $invalidationBind = [$archive['name'], (string) ($archive['report'] ?? '')];
        }

        $hasFailures = (bool) Db::fetchOne(
            "SELECT 1 FROM `" . self::getTable() . "` f WHERE f.idsite = ?$failureCondition LIMIT 1",
            array_merge([$idSite], $failureBind)
        );
        if (!$hasFailures) {
            // nothing can block them anymore
            return Db::query(
                "UPDATE `$invalidationsTable` SET status = ? WHERE idsite = ? AND status = ?$invalidationCondition",
                array_merge([ArchiveInvalidator::INVALIDATION_STATUS_QUEUED, $idSite, ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD], $invalidationBind)
            )->rowCount();
        }

        [$blockedPeriods, $blockedRangeDates] = $this->getBlockedPeriods($idSite, null === $archive ? null : $failureBind[0]);

        $released = 0;
        $lastIdInvalidation = 0;
        do {
            $invalidations = Db::fetchAll(
                "SELECT idinvalidation, name, `report`, period, date1, date2 FROM `$invalidationsTable`
                  WHERE idsite = ? AND status = ?$invalidationCondition AND idinvalidation > ?
                  ORDER BY idinvalidation ASC LIMIT " . self::INVALIDATIONS_CHUNK_SIZE,
                array_merge([$idSite, ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD], $invalidationBind, [$lastIdInvalidation])
            );

            $idsToRelease = [];
            foreach ($invalidations as $invalidation) {
                $lastIdInvalidation = (int) $invalidation['idinvalidation'];
                if (!$this->isBlocked($invalidation, $blockedPeriods, $blockedRangeDates)) {
                    $idsToRelease[] = $lastIdInvalidation;
                }
            }

            if (!empty($idsToRelease)) {
                $released += Db::query(
                    "UPDATE `$invalidationsTable` SET status = ? WHERE status = ? AND idinvalidation IN (" . implode(',', $idsToRelease) . ")",
                    [ArchiveInvalidator::INVALIDATION_STATUS_QUEUED, ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD]
                )->rowCount();
            }
        } while (count($invalidations) === self::INVALIDATIONS_CHUNK_SIZE);

        return $released;
    }

    /**
     * Puts a queued invalidation on hold if a failure blocks it. If the same archive is already on hold, the
     * invalidation is deleted instead, so at most one invalidation per archive waits.
     *
     * @return string|null 'held', 'deleted', or null if not blocked
     */
    public function holdIfBlocked(array $invalidation): ?string
    {
        if (!self::isAvailable()) {
            return null;
        }

        $table = Common::prefixTable('archive_invalidations');
        [$blockedSql, $blockedBind] = $this->getBlockingFailureExistsCondition($invalidation);

        $idHeldTwin = Db::fetchOne(
            "SELECT idinvalidation FROM `$table` WHERE idsite = ? AND date1 = ? AND period = ? AND date2 = ? AND name = ?
                AND IFNULL(`report`, '') = ? AND status = ? AND idinvalidation != ? LIMIT 1",
            [
                $invalidation['idsite'], $invalidation['date1'], $invalidation['period'], $invalidation['date2'], $invalidation['name'],
                (string) ($invalidation['report'] ?? ''), ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD, $invalidation['idinvalidation'],
            ]
        );

        if ($idHeldTwin) {
            $deleted = Db::query(
                "DELETE FROM `$table` WHERE idinvalidation = ? AND status = ? AND $blockedSql",
                array_merge([$invalidation['idinvalidation'], ArchiveInvalidator::INVALIDATION_STATUS_QUEUED], $blockedBind)
            )->rowCount();

            return $deleted > 0 ? 'deleted' : null;
        }

        $held = Db::query(
            "UPDATE `$table` SET status = ? WHERE idinvalidation = ? AND status = ? AND $blockedSql",
            array_merge([ArchiveInvalidator::INVALIDATION_STATUS_ON_HOLD, $invalidation['idinvalidation'], ArchiveInvalidator::INVALIDATION_STATUS_QUEUED], $blockedBind)
        )->rowCount();

        return $held > 0 ? 'held' : null;
    }

    /**
     * SQL condition that is true if a failure blocks the given invalidation: its own archive or an archive it is built
     * from failed recently or was given up. Same rule as getBlockedPeriods() and isBlocked(), for a single row.
     *
     * @return array{0: string, 1: array} SQL and bind
     */
    public function getBlockingFailureExistsCondition(array $invalidation): array
    {
        $idSite = (int) $invalidation['idsite'];
        $period = (int) $invalidation['period'];

        $sql = "EXISTS (SELECT 1 FROM `" . self::getTable() . "` f WHERE f.idsite = ? AND f.archive_hash = ?
                   AND (f.ts_retry_after > ? OR f.attempts >= ?)
                   AND ((f.period = ? AND f.date1 = ? AND f.date2 = ?)";
        $bind = [
            $idSite,
            $this->getArchiveHash($invalidation),
            Date::now()->getDatetime(),
            self::getMaxAttempts($idSite),
            $period,
            $invalidation['date1'],
            $invalidation['date2'],
        ];

        if ($period !== Piwik::$idPeriods['day']) {
            $sql .= " OR (f.period < ? AND f.date1 >= ? AND f.date2 <= ?";
            array_push($bind, $period, $invalidation['date1'], $invalidation['date2']);

            if ($period === Piwik::$idPeriods['year']) {
                // years are built from months, which only contain weeks that lie within them
                $sql .= " AND (f.period != ? OR DATE_FORMAT(f.date1, '%m') = DATE_FORMAT(f.date2, '%m'))";
                $bind[] = Piwik::$idPeriods['week'];
            }

            $sql .= ")";
        }

        return [$sql . "))", $bind];
    }

    /**
     * Periods blocked by the site's failures that are waiting or given up: their own period, and the periods built
     * from it. Ranges are matched by date.
     *
     * @return array{0: array<string, array<string, true>>, 1: array<string, array>} blocked periods and range dates, by archive
     */
    private function getBlockedPeriods(int $idSite, ?string $archiveHash): array
    {
        $blockedPeriods = [];
        $blockedRangeDates = [];

        if (null === $archiveHash) {
            $failures = $this->iterateFailures([$idSite], false, false, self::FAILURES_CHUNK_SIZE);
        } else {
            // only the failures of one archive, served by the unique key
            $failures = Db::fetchAll(
                "SELECT idsite, name, period, date1, date2, `report`, attempts, ts_retry_after FROM `" . self::getTable() . "`
                  WHERE idsite = ? AND archive_hash = ?",
                [$idSite, $archiveHash]
            );
            $now = Date::now()->getDatetime();
            $maxAttempts = self::getMaxAttempts($idSite);
            foreach ($failures as &$failure) {
                $failure['state'] = $this->getState($failure, $now, $maxAttempts);
            }
            unset($failure);
        }

        foreach ($failures as $failure) {
            if ($failure['state'] === self::STATE_RETRY_PENDING) {
                continue;
            }

            $archiveKey = $failure['name'] . '|' . $failure['report'];

            foreach ($this->getPeriodsBuiltFrom($failure) as [$period, $date1, $date2]) {
                $blockedPeriods[$archiveKey][$period . '|' . $date1 . '|' . $date2] = true;
            }

            if ((int) $failure['period'] !== Range::PERIOD_ID) {
                $blockedRangeDates[$archiveKey][] = [$failure['date1'], $failure['date2']];
            }
        }

        return [$blockedPeriods, $blockedRangeDates];
    }

    private function isBlocked(array $invalidation, array $blockedPeriods, array $blockedRangeDates): bool
    {
        $archiveKey = $invalidation['name'] . '|' . ($invalidation['report'] ?? '');
        if (isset($blockedPeriods[$archiveKey][$invalidation['period'] . '|' . $invalidation['date1'] . '|' . $invalidation['date2']])) {
            return true;
        }

        if ((int) $invalidation['period'] === Range::PERIOD_ID) {
            foreach ($blockedRangeDates[$archiveKey] ?? [] as [$date1, $date2]) {
                if ($invalidation['date1'] <= $date1 && $invalidation['date2'] >= $date2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array{waiting: int, givenUp: int, oldestFailure: ?string}
     */
    public function getFailureCounts(): array
    {
        $counts = ['waiting' => 0, 'givenUp' => 0, 'oldestFailure' => null];
        if (!self::isAvailable()) {
            return $counts;
        }

        $rows = Db::fetchAll(
            "SELECT f.idsite, f.attempts, COUNT(*) AS num, MIN(f.ts_first_failed) AS oldest_failure
               FROM `" . self::getTable() . "` f INNER JOIN `" . Common::prefixTable('site') . "` s ON s.idsite = f.idsite
           GROUP BY f.idsite, f.attempts"
        );

        foreach ($rows as $row) {
            if ((int) $row['attempts'] >= self::getMaxAttempts((int) $row['idsite'])) {
                $counts['givenUp'] += (int) $row['num'];
            } else {
                $counts['waiting'] += (int) $row['num'];
            }

            if (null === $counts['oldestFailure'] || $row['oldest_failure'] < $counts['oldestFailure']) {
                $counts['oldestFailure'] = $row['oldest_failure'];
            }
        }

        return $counts;
    }

    /**
     * @param int[] $idFailures
     */
    public function deleteFailuresById(array $idFailures): int
    {
        if (empty($idFailures) || !self::isAvailable()) {
            return 0;
        }

        $sql = "DELETE FROM `" . self::getTable() . "` WHERE idfailure IN (" . implode(',', array_map('intval', $idFailures)) . ")";

        return Db::query($sql)->rowCount();
    }

    /**
     * Removes the failures of a site that have no invalidation left to retry.
     */
    public function deleteFailuresWithoutInvalidations(int $idSite): int
    {
        if (!self::isAvailable()) {
            return 0;
        }

        $table = self::getTable();
        $invalidationsTable = Common::prefixTable('archive_invalidations');

        $idFailures = Db::fetchAll(
            "SELECT f.idfailure FROM `$table` f
               LEFT JOIN `$invalidationsTable` i ON i.idsite = f.idsite AND i.name = f.name AND i.period = f.period
                     AND i.date1 = f.date1 AND i.date2 = f.date2 AND IFNULL(i.report, '') = f.report
              WHERE f.idsite = ? AND i.idinvalidation IS NULL",
            [$idSite]
        );

        return $this->deleteFailuresById(array_column($idFailures, 'idfailure'));
    }

    public function deleteFailuresForDeletedSites(): void
    {
        if (!self::isAvailable()) {
            return;
        }

        $table = self::getTable();
        $siteTable = Common::prefixTable('site');

        Db::query("DELETE f FROM `$table` f LEFT JOIN `$siteTable` s ON f.idsite = s.idsite WHERE s.idsite IS NULL");
    }

    public function getState(array $failure, string $now, ?int $maxAttempts = null): string
    {
        if ((int) $failure['attempts'] >= ($maxAttempts ?? self::getMaxAttempts((int) $failure['idsite']))) {
            return self::STATE_GIVEN_UP;
        }

        return $failure['ts_retry_after'] > $now ? self::STATE_WAITING : self::STATE_RETRY_PENDING;
    }

    /**
     * The failed period itself, for queued duplicates, and the periods archived from it as a subperiod: a week from
     * its days, a month from its days and the weeks within it, a year from its months. Ranges are matched by date.
     *
     * @return array[] list of [period id, date1, date2]
     */
    private function getPeriodsBuiltFrom(array $failure): array
    {
        $period = (int) $failure['period'];
        $periods = [[$period, $failure['date1'], $failure['date2']]];

        $parentLabels = [];
        if ($period === Piwik::$idPeriods['day']) {
            $parentLabels = ['week', 'month', 'year'];
        } elseif ($period === Piwik::$idPeriods['week'] && substr($failure['date1'], 0, 7) === substr($failure['date2'], 0, 7)) {
            $parentLabels = ['month', 'year'];
        } elseif ($period === Piwik::$idPeriods['month']) {
            $parentLabels = ['year'];
        }

        // plain date arithmetic, building period objects is too slow for many failures
        $timestamp = strtotime($failure['date1'] . ' UTC');
        foreach ($parentLabels as $label) {
            if ($label === 'week') {
                $weekStart = $timestamp - ((int) gmdate('N', $timestamp) - 1) * 86400;
                $periods[] = [Piwik::$idPeriods['week'], gmdate('Y-m-d', $weekStart), gmdate('Y-m-d', $weekStart + 6 * 86400)];
            } elseif ($label === 'month') {
                $periods[] = [Piwik::$idPeriods['month'], gmdate('Y-m-01', $timestamp), gmdate('Y-m-t', $timestamp)];
            } else {
                $periods[] = [Piwik::$idPeriods['year'], gmdate('Y-01-01', $timestamp), gmdate('Y-12-31', $timestamp)];
            }
        }

        return $periods;
    }

    private function getRetryAfter(Date $now, int $idSite, int $attempts): string
    {
        return Date::factory($now->getTimestamp() + self::getRetryDelay($idSite, $attempts))->getDatetime();
    }

    private function trimError(string $error): string
    {
        // error messages may contain a stack trace, on one line in API responses
        $error = preg_split('/\s#0\s/', $error, 2)[0];

        return mb_substr(trim($error), 0, self::MAX_ERROR_LENGTH);
    }

    private function getArchiveWhere(): string
    {
        return "idsite = ? AND period = ? AND date1 = ? AND date2 = ? AND archive_hash = ?";
    }

    private function getArchiveBind(array $invalidation): array
    {
        return [
            (int) $invalidation['idsite'],
            (int) $invalidation['period'],
            $invalidation['date1'],
            $invalidation['date2'],
            $this->getArchiveHash($invalidation),
        ];
    }

    private function getArchiveHash(array $invalidation): string
    {
        return md5($invalidation['name'] . '|' . ($invalidation['report'] ?? ''));
    }
}
