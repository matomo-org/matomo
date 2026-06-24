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
use Piwik\Config\GeneralConfig;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tracker\Action;

class BotRequestsDao
{
    private const DEFAULT_REAL_TIME_CHATBOT_LIMIT = 250;
    private const DEFAULT_REAL_TIME_PAGE_URL_LIMIT = 50000;

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

    public function getLastServerTimeForSiteAndBotType(int $idSite, string $botType): ?string
    {
        $tableName = self::getPrefixedTableName();

        return Db::fetchOne(
            sprintf(
                'SELECT MAX(server_time) FROM `%s` WHERE idsite = ? AND bot_type = ?',
                $tableName
            ),
            [$idSite, $botType]
        );
    }

    /**
     * @param int[] $idSites
     */
    public function getAIChatbotsRealTime(array $idSites, string $startDate, string $endDate): DataTable
    {
        $idSites = $this->normalizeIdSites($idSites);

        if (empty($idSites)) {
            return new DataTable();
        }

        $limit = GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_bots', self::DEFAULT_REAL_TIME_CHATBOT_LIMIT);
        if ($limit <= 0) {
            $limit = self::DEFAULT_REAL_TIME_CHATBOT_LIMIT;
        }

        $idSitePlaceholders = Common::getSqlStringFieldsArray($idSites);
        $tableName          = self::getPrefixedTableName();
        $actionTable        = Common::prefixTable('log_action');

        $sql = sprintf(
            "SELECT bot.bot_name AS label,
                    COUNT(*) AS %s,
                    COUNT(DISTINCT CASE WHEN log_action.type = %d THEN log_action.name END) AS %s,
                    SUM(CASE WHEN bot.http_status_code IN (404, 410) THEN 1 ELSE 0 END) AS %s,
                    SUM(CASE WHEN bot.http_status_code BETWEEN 500 AND 599 THEN 1 ELSE 0 END) AS %s
             FROM `%s` AS bot
             LEFT JOIN `%s` AS log_action ON log_action.idaction = bot.idaction_url
             WHERE bot.idsite IN (%s)
               AND bot.bot_type = ?
               AND bot.server_time >= ?
               AND bot.server_time <= ?
             GROUP BY bot.bot_name
             ORDER BY %s DESC, bot.bot_name
             LIMIT %d",
            Metrics::COLUMN_CHATBOT_REQUESTS,
            Action::TYPE_PAGE_URL,
            Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS,
            Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS,
            Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS,
            $tableName,
            $actionTable,
            $idSitePlaceholders,
            Metrics::COLUMN_CHATBOT_REQUESTS,
            $limit
        );

        $bind = array_merge($idSites, [BotDetector::BOT_TYPE_AI_CHATBOT, $startDate, $endDate]);

        $stmt  = Db::query($sql, $bind);
        $table = new DataTable();
        $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, [
            Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS => 'skip',
        ]);

        while ($row = $stmt->fetch()) {
            /** @var array<string, int|string|null> $row */
            $table->addRow(new Row([
                Row::COLUMNS => [
                    'label'                                      => (string) $row['label'],
                    Metrics::COLUMN_CHATBOT_REQUESTS                   => (int) $row[Metrics::COLUMN_CHATBOT_REQUESTS],
                    Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS       => (int) $row[Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS],
                    Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS     => (int) $row[Metrics::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS],
                    Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS => (int) $row[Metrics::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS],
                ],
            ]));
        }

        return $table;
    }

    /**
     * @param int[] $idSites
     */
    public function getTopPageUrlsRealTime(array $idSites, string $startDate, string $endDate): DataTable
    {
        $idSites = $this->normalizeIdSites($idSites);

        if (empty($idSites)) {
            return new DataTable();
        }

        $limit = GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_ai_chatbot_content', self::DEFAULT_REAL_TIME_PAGE_URL_LIMIT);
        if ($limit <= 0) {
            $limit = self::DEFAULT_REAL_TIME_PAGE_URL_LIMIT;
        }

        $idSitePlaceholders = Common::getSqlStringFieldsArray($idSites);
        $tableName          = self::getPrefixedTableName();
        $actionTable        = Common::prefixTable('log_action');

        $sql = sprintf(
            "SELECT log_action.name AS label,
                    COUNT(*) AS %s
             FROM `%s` AS bot
             INNER JOIN `%s` AS log_action ON log_action.idaction = bot.idaction_url
             WHERE bot.idsite IN (%s)
               AND bot.bot_type = ?
               AND bot.server_time >= ?
               AND bot.server_time <= ?
               AND log_action.name IS NOT NULL
               AND log_action.name <> ''
               AND log_action.type = %d
             GROUP BY log_action.name
             ORDER BY %s DESC, log_action.name
             LIMIT %d",
            Metrics::COLUMN_CHATBOT_REQUESTS,
            $tableName,
            $actionTable,
            $idSitePlaceholders,
            Action::TYPE_PAGE_URL,
            Metrics::COLUMN_CHATBOT_REQUESTS,
            $limit
        );

        $bind = array_merge($idSites, [BotDetector::BOT_TYPE_AI_CHATBOT, $startDate, $endDate]);

        $stmt  = Db::query($sql, $bind);
        $table = new DataTable();

        while ($row = $stmt->fetch()) {
            /** @var array{label: string, chatbot_requests: int|string} $row */
            $table->addRow(new Row([
                Row::COLUMNS => [
                    'label'                          => (string) $row['label'],
                    Metrics::COLUMN_CHATBOT_REQUESTS => (int) $row[Metrics::COLUMN_CHATBOT_REQUESTS],
                ],
            ]));
        }

        return $table;
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

    /**
     * @param int[] $idSites
     * @return int[]
     */
    private function normalizeIdSites(array $idSites): array
    {
        $idSites = array_map('intval', $idSites);
        $idSites = array_filter($idSites, function (int $idSite): bool {
            return $idSite > 0;
        });

        return array_values(array_unique($idSites));
    }
}
