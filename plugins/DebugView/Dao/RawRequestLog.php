<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\Dao;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * All database access for the raw tracking request storage. Contains no
 * business logic — policies (caps, windows, encoding) live in
 * Model\DebugRequests.
 */
class RawRequestLog
{
    public const TABLE = 'debugview_raw_request';

    public function getTableCreateDefinition(): string
    {
        return "`idrawrequest` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `idsite` INT UNSIGNED NOT NULL,
                `idvisit` BIGINT UNSIGNED NULL,
                `idlink_va` BIGINT UNSIGNED NULL,
                `server_time` DATETIME NOT NULL,
                `parameters` LONGTEXT NOT NULL,
                PRIMARY KEY (`idrawrequest`),
                KEY `index_site_time` (`idsite`, `server_time`)";
    }

    public function install(): void
    {
        DbHelper::createTable(self::TABLE, $this->getTableCreateDefinition());
    }

    public function uninstall(): void
    {
        Db::query('DROP TABLE IF EXISTS `' . Common::prefixTable(self::TABLE) . '`');
    }

    /**
     * Tracker-side insert of one raw request row.
     */
    public function insert(
        int $idSite,
        ?int $idVisit,
        ?int $idLinkVisitAction,
        int $serverTimestamp,
        string $parametersJson
    ): void {
        $date = Date::factory($serverTimestamp)->getDatetime();
        Db::get()->query(
            'INSERT INTO `' . Common::prefixTable(self::TABLE) . '`
             (idsite, idvisit, idlink_va, server_time, parameters)
             VALUES (?, ?, ?, ?, ?)',
            [$idSite, $idVisit, $idLinkVisitAction, $date, $parametersJson]
        );
    }

    /**
     * Reader-side: the newest $limit raw requests of one site newer than the
     * given UTC timestamp, optionally only rows with an id strictly greater
     * than $minId (ids are monotonic, which makes them a reliable incremental
     * cursor). The result is chronological (oldest first).
     *
     * @return array<int, array{idrawrequest: string, idvisit: string|null, idlink_va: string|null, server_time: string, parameters: string}>
     */
    public function getForSite(int $idSite, int $minServerTimestamp, int $minId, int $limit): array
    {
        $table = Common::prefixTable(self::TABLE);

        // 0 means "no lower bound" — Date::factory rejects timestamps that
        // far in the past
        $date = $minServerTimestamp > 0
            ? Date::factory($minServerTimestamp)->getDatetime()
            : '1970-01-01 00:00:00';
        $rows = Db::getReader()->fetchAll(
            "SELECT idrawrequest, idvisit, idlink_va, server_time, parameters
             FROM `$table`
             WHERE idsite = ? AND server_time >= ? AND idrawrequest > ?
             ORDER BY idrawrequest DESC LIMIT " . (int) $limit,
            [$idSite, $date, $minId]
        );

        return array_reverse($rows);
    }

    /**
     * Deletes every row older than the given UTC timestamp.
     *
     * @return int number of deleted rows
     */
    public function deleteOlderThan(int $serverTimestamp): int
    {
        $date = Date::factory($serverTimestamp)->getDatetime();
        $query = Db::query(
            'DELETE FROM `' . Common::prefixTable(self::TABLE) . '` WHERE server_time < ?',
            [$date]
        );

        return $query->rowCount();
    }

    /**
     * Caps every site at its newest $keepPerSite rows: the id of the oldest row
     * to keep is looked up per site, everything older is deleted (auto-increment
     * ids are monotonic).
     *
     * @return int number of deleted rows
     */
    public function trimToNewestPerSite(int $keepPerSite): int
    {
        $table = Common::prefixTable(self::TABLE);
        $deleted = 0;

        $idSites = Db::fetchAll("SELECT DISTINCT idsite FROM `$table`");
        foreach ($idSites as $row) {
            $deleted += $this->trimSiteToNewest((int) $row['idsite'], $keepPerSite);
        }

        return $deleted;
    }

    /**
     * Caps one site at its newest $keep rows: the id of the oldest row to keep
     * is looked up, everything older is deleted (auto-increment ids are
     * monotonic). Two small indexed queries, cheap enough to run per poll.
     *
     * @return int number of deleted rows
     */
    public function trimSiteToNewest(int $idSite, int $keep): int
    {
        $table = Common::prefixTable(self::TABLE);

        $oldestIdToKeep = Db::fetchOne(
            "SELECT idrawrequest FROM `$table` WHERE idsite = ?
             ORDER BY idrawrequest DESC LIMIT 1 OFFSET " . ($keep - 1),
            [$idSite]
        );

        if (empty($oldestIdToKeep)) {
            return 0;
        }

        $query = Db::query(
            "DELETE FROM `$table` WHERE idsite = ? AND idrawrequest < ?",
            [$idSite, (int) $oldestIdToKeep]
        );

        return $query->rowCount();
    }

    /**
     * Deletes the expired per-site arming options (their value is a plain UTC
     * timestamp). Housekeeping for the hourly trim task.
     *
     * @return int number of deleted option rows
     */
    public function deleteExpiredActiveSiteOptions(string $optionPrefix, int $now): int
    {
        $like = str_replace(['%', '_'], ['\\%', '\\_'], $optionPrefix) . '%';

        $query = Db::query(
            'DELETE FROM `' . Common::prefixTable('option') . '`
             WHERE option_name LIKE ? AND CAST(option_value AS UNSIGNED) < ?',
            [$like, $now]
        );

        return $query->rowCount();
    }

    /**
     * Reads a single option value with a direct query, deliberately bypassing
     * the Option class and all its caches: loading all options is too slow
     * for something running on every stream poll, and the tracker-side read
     * must never touch or invalidate the shared tracker cache. Db routes to
     * the tracker connection automatically during tracking requests.
     *
     * @return string|false
     */
    public function getOptionValue(string $optionName)
    {
        return Db::fetchOne(
            'SELECT option_value FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?',
            [$optionName]
        );
    }
}
