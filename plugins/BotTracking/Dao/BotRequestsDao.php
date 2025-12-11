<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Dao;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;

class BotRequestsDao
{
    public static function getTableName(): string
    {
        return 'log_bot_request';
    }

    public static function getPrefixedTableName(): string
    {
        return Common::prefixTable(self::getTableName());
    }

    /**
     * Creates the log table
     */
    public function createTable(): void
    {
        $tableName  = self::getTableName();
        $definition = '
            `idrequest` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `idsite` INT UNSIGNED NOT NULL,
            `server_time` DATETIME NOT NULL,
            `created_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `idaction_url` INT UNSIGNED NULL,
            `bot_name` VARCHAR(100) NOT NULL,
            `bot_type` VARCHAR(50) NOT NULL,
            `http_status_code` SMALLINT UNSIGNED NULL,
            `response_size_bytes` INT UNSIGNED NULL,
            `response_time_ms` INT UNSIGNED NULL,
            `source` VARCHAR(50) NULL,
            PRIMARY KEY (`idrequest`),
            INDEX `index_idsite_server_time` (`idsite`, `server_time`)';

        DbHelper::createTable($tableName, $definition);
    }

    public function dropTable(): void
    {
        Db::query('DROP TABLE IF EXISTS ' . self::getPrefixedTableName());
    }

    /**
     * Insert a bot telemetry record
     *
     * @param array<string, scalar> $data The telemetry data to insert
     * @return int The inserted record ID
     */
    public function insert(array $data): int
    {
        $tableName = self::getPrefixedTableName();

        $fields = [
            'idsite',
            'server_time',
            'idaction_url',
            'bot_name',
            'bot_type',
            'http_status_code',
            'response_size_bytes',
            'response_time_ms',
            'source',
        ];

        $values = [];
        $bind   = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $values[] = '?';
                $bind[]   = $data[$field];
            } else {
                $values[] = 'NULL';
            }
        }

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $tableName,
            implode(', ', $fields),
            implode(', ', $values)
        );

        Db::query($sql, $bind);

        return (int)Db::get()->lastInsertId();
    }

    /**
     * Delete bot telemetry records older than a specified date
     *
     * @param Date $date Delete records older than this date
     * @return int Number of deleted records
     */
    public function deleteOldRecords(Date $date): int
    {
        $tableName = self::getPrefixedTableName();

        $sql = sprintf(
            'DELETE FROM `%s` WHERE server_time < ?',
            $tableName
        );

        $result = Db::query($sql, [$date->getDatetime()]);

        return (int)Db::get()->rowCount($result);
    }

    /**
     * Delete bot telemetry records for specific sites
     *
     * @param array<int|string> $siteIds Delete records older than this date
     * @return int Number of deleted records
     */
    public function deleteRecordsForIdSites(array $siteIds): int
    {
        $tableName = self::getPrefixedTableName();
        $siteIds   = array_map('intval', $siteIds);

        $sql = sprintf(
            'DELETE FROM `%s` WHERE idsite IN (' . implode(', ', $siteIds) . ') LIMIT 25000',
            $tableName
        );

        $result = Db::query($sql);

        return (int)Db::get()->rowCount($result);
    }

    /**
     * @return int[]
     */
    public function getDistinctIdSitesInTable(int $maxIdSite): array
    {
        $tableName       = self::getPrefixedTableName();
        $idSitesLogTable = Db::fetchAll('SELECT DISTINCT idsite FROM ' . $tableName);
        $idSitesLogTable = array_column($idSitesLogTable, 'idsite');
        $idSitesLogTable = array_map('intval', $idSitesLogTable);
        return array_filter($idSitesLogTable, function ($idSite) use ($maxIdSite) {
            return !empty($idSite) && $idSite <= $maxIdSite;
        });
    }
}
