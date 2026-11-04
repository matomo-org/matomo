<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Period\Factory as PeriodFactory;
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
    public function deleteFailure(array $invalidation): void
    {
        if (!self::isAvailable()) {
            return;
        }

        Db::query(
            "DELETE FROM `" . self::getTable() . "` WHERE " . $this->getArchiveWhere(),
            $this->getArchiveBind($invalidation)
        );
    }

    /**
     * @param int[] $idSites restricts the result to these sites, all sites if empty
     * @return array[] failure rows, oldest first, each with its 'state'
     */
    public function getFailures(array $idSites = [], bool $givenUpOnly = false): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        // failures of deleted sites are removed by a scheduled task
        $sql = "SELECT f.* FROM `" . self::getTable() . "` f INNER JOIN `" . Common::prefixTable('site') . "` s ON s.idsite = f.idsite";
        if (!empty($idSites)) {
            $sql .= " WHERE f.idsite IN (" . implode(',', array_map('intval', $idSites)) . ")";
        }
        $sql .= " ORDER BY f.ts_first_failed ASC, f.idfailure ASC";

        $now = Date::now()->getDatetime();
        $failures = [];
        foreach (Db::fetchAll($sql) as $failure) {
            $failure['state'] = $this->getState($failure, $now);

            if (!$givenUpOnly || $failure['state'] === self::STATE_GIVEN_UP) {
                $failures[] = $failure;
            }
        }

        return $failures;
    }

    /**
     * Returns the queued invalidations core:archive must skip for a site: those of archives that failed recently or
     * were given up, and those of the periods built from them, which would re-archive the failed archive.
     *
     * @return array{ids: int[], validUntil: ?string} validUntil is when the next failure becomes retryable
     */
    public function getBlockedInvalidationIds(int $idSite): array
    {
        if (!self::isAvailable()) {
            return ['ids' => [], 'validUntil' => null];
        }

        $now = Date::now()->getDatetime();
        $blockedPeriods = [];
        $blockedRangeDates = [];
        $validUntil = null;

        foreach ($this->getFailures([$idSite]) as $failure) {
            if ($failure['state'] === self::STATE_RETRY_PENDING) {
                continue;
            }

            if ($failure['state'] === self::STATE_WAITING && (null === $validUntil || $failure['ts_retry_after'] < $validUntil)) {
                $validUntil = $failure['ts_retry_after'];
            }

            $archiveKey = $failure['name'] . '|' . $failure['report'];
            foreach ($this->getPeriodsBuiltFrom($failure) as [$period, $date1, $date2]) {
                $blockedPeriods[$archiveKey][$period . '|' . $date1 . '|' . $date2] = true;
            }

            if ((int) $failure['period'] !== Range::PERIOD_ID) {
                $blockedRangeDates[$archiveKey][] = [$failure['date1'], $failure['date2']];
            }
        }

        if (empty($blockedPeriods)) {
            return ['ids' => [], 'validUntil' => $validUntil];
        }

        $names = array_unique(array_map(function ($archiveKey) {
            return explode('|', $archiveKey, 2)[0];
        }, array_keys($blockedPeriods)));

        $invalidations = Db::fetchAll(
            "SELECT idinvalidation, name, `report`, period, date1, date2 FROM `" . Common::prefixTable('archive_invalidations') . "`
              WHERE idsite = ? AND name IN (" . Common::getSqlStringFieldsArray($names) . ")",
            array_merge([$idSite], $names)
        );

        $ids = [];
        foreach ($invalidations as $invalidation) {
            $archiveKey = $invalidation['name'] . '|' . ($invalidation['report'] ?? '');
            $periodKey = $invalidation['period'] . '|' . $invalidation['date1'] . '|' . $invalidation['date2'];

            if (isset($blockedPeriods[$archiveKey][$periodKey])) {
                $ids[] = (int) $invalidation['idinvalidation'];
            } elseif ((int) $invalidation['period'] === Range::PERIOD_ID) {
                foreach ($blockedRangeDates[$archiveKey] ?? [] as [$date1, $date2]) {
                    if ($invalidation['date1'] <= $date1 && $invalidation['date2'] >= $date2) {
                        $ids[] = (int) $invalidation['idinvalidation'];
                        break;
                    }
                }
            }
        }

        return ['ids' => $ids, 'validUntil' => $validUntil];
    }

    /**
     * Changes whenever a failure of the site is recorded, updated or removed, or, while the site has failures, an
     * invalidation is added. Empty when the site has no failures.
     */
    public function getFingerprint(int $idSite): string
    {
        if (!self::isAvailable()) {
            return '';
        }

        $row = Db::fetchRow(
            "SELECT COUNT(*) AS num, SUM(attempts) AS attempts, MAX(ts_last_failed) AS last_failed, MAX(idfailure) AS max_id
               FROM `" . self::getTable() . "` WHERE idsite = ?",
            [$idSite]
        );

        if (empty($row['num'])) {
            return '';
        }

        $row['max_idinvalidation'] = Db::fetchOne(
            "SELECT MAX(idinvalidation) FROM `" . Common::prefixTable('archive_invalidations') . "` WHERE idsite = ?",
            [$idSite]
        );

        return implode('|', $row);
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

    public function getState(array $failure, string $now): string
    {
        if ((int) $failure['attempts'] >= self::getMaxAttempts((int) $failure['idsite'])) {
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

        foreach ($parentLabels as $label) {
            $parent = PeriodFactory::build($label, $failure['date1']);
            $periods[] = [(int) $parent->getId(), $parent->getDateStart()->toString(), $parent->getDateEnd()->toString()];
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
            md5($invalidation['name'] . '|' . ($invalidation['report'] ?? '')),
        ];
    }
}
