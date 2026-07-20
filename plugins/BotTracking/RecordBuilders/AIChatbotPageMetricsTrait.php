<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\RecordBuilders;

use InvalidArgumentException;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\RankingQuery;

/**
 * Shared AI-chatbot page/document URL aggregation, used by the Content Requests
 * ({@see AIChatbotContentReports}) and Favoured Pages ({@see AIChatbotFavouredPages}) builders.
 *
 * Keeping the per-URL request query here gives the "what counts as an AI chatbot request" predicate a
 * single source of truth. Each consumer passes only the metric columns it needs, so no caller computes
 * columns it discards.
 */
trait AIChatbotPageMetricsTrait
{
    /**
     * The available per-URL metric columns, mapped to the SQL expression that produces them from the
     * AI-chatbot request rows (the `bot` alias in {@see queryPageOrDocumentUrls}). All are summable.
     *
     * @return array<string, string> column name => SQL select expression
     */
    protected function pageUrlMetricExpressions(): array
    {
        return [
            Metrics::COLUMN_REQUESTS                    => 'COUNT(*)',
            Metrics::COLUMN_SUM_SERVER_TIME             => 'SUM(bot.response_time_ms)',
            Metrics::COLUMN_NB_SERVER_TIME              => 'SUM(CASE WHEN bot.response_time_ms IS NOT NULL THEN 1 ELSE 0 END)',
            Metrics::COLUMN_SUM_RESPONSE_SIZE           => 'SUM(bot.response_size_bytes)',
            Metrics::COLUMN_NB_RESPONSE_SIZE            => 'SUM(CASE WHEN bot.response_size_bytes IS NOT NULL THEN 1 ELSE 0 END)',
            Metrics::COLUMN_PAGE_NOT_FOUND_404_REQUESTS => 'SUM(bot.http_status_code IN (404, 410))',
            Metrics::COLUMN_SERVER_ERROR_5XX_REQUESTS   => 'SUM(bot.http_status_code BETWEEN 500 AND 599)',
        ];
    }

    /**
     * Queries page or document URLs requested by AI chatbots, selecting only the requested metric
     * columns (Content the full set, Favoured Pages just the request count).
     *
     * @param string[] $columnNames metric columns to select, from {@see pageUrlMetricExpressions} keys
     */
    protected function queryPageOrDocumentUrls(ArchiveProcessor $archiveProcessor, int $actionType, int $rankingQueryLimit, array $columnNames): DataTable
    {
        $logAggregator = $archiveProcessor->getLogAggregator();
        $where         = $logAggregator->getWhereStatement('bot', 'server_time');
        $botTable      = BotRequestsDao::getPrefixedTableName();
        $actionTable   = Common::prefixTable('log_action');

        $expressions = $this->pageUrlMetricExpressions();
        $selects     = ['log_action.name AS url'];
        $columns     = [];
        foreach ($columnNames as $columnName) {
            if (!isset($expressions[$columnName])) {
                throw new InvalidArgumentException('Unknown AI chatbot page metric column: ' . $columnName);
            }
            $selects[$columnName] = $expressions[$columnName] . ' AS ' . $columnName;
            $columns[$columnName] = 'sum';
        }

        // Truncate by the request count when present (every consumer selects it); otherwise the first
        // requested column, so the RankingQuery keeps the highest-volume rows.
        $orderColumn = isset($columns[Metrics::COLUMN_REQUESTS]) ? Metrics::COLUMN_REQUESTS : (string) array_key_first($columns);

        $innerSql = sprintf(
            "SELECT %s
             FROM `%s` AS bot
             INNER JOIN `%s` AS log_action ON log_action.idaction = bot.idaction_url
             WHERE log_action.name IS NOT NULL
               AND log_action.name <> ''
               AND log_action.type = %d
               AND bot.bot_type = ?
               AND %s
             GROUP BY log_action.name
             ORDER BY %s DESC",
            implode(",\n                    ", $selects),
            $botTable,
            $actionTable,
            $actionType,
            $where,
            $orderColumn
        );

        return $this->executeUrlQuery($archiveProcessor, $innerSql, $columns, $rankingQueryLimit);
    }

    /**
     * Wraps an already-built inner SQL with a RankingQuery and populates a DataTable; the "Others"
     * summary row is routed by sumRowWithLabel.
     *
     * @param string                $innerSql Already-interpolated SQL (bot_type bind placeholder kept as `?`).
     * @param array<string, string> $columns  column name → RankingQuery aggregation op; must match the
     *                                        SELECT aliases.
     */
    protected function executeUrlQuery(ArchiveProcessor $archiveProcessor, string $innerSql, array $columns, int $rankingQueryLimit): DataTable
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $rankingQuery = new RankingQuery($rankingQueryLimit);
        $rankingQuery->addLabelColumn('url');

        foreach ($columns as $column => $op) {
            $rankingQuery->addColumn($column, $op);
        }

        $wrappedSql = $rankingQuery->generateRankingQuery($innerSql);

        $bind = array_merge([BotDetector::BOT_TYPE_AI_CHATBOT], $logAggregator->getGeneralQueryBindParams());
        $stmt = Db::query($wrappedSql, $bind);

        $table = new DataTable();
        while ($row = $stmt->fetch()) {
            /** @var array<string, int|string|null> $row */
            $label = (string) $row['url'];

            $metrics = [];
            foreach ($columns as $column => $op) {
                $raw = $row[$column] ?? 0;
                $metrics[$column] = is_numeric($raw) ? (int) $raw : 0;
            }

            $table->sumRowWithLabel($label, $metrics);
        }

        return $table;
    }

    protected function getRankingQueryLimit(int $maxRowsInTable): int
    {
        $configLimit = GeneralConfig::getIntegerConfigValue('archiving_ranking_query_row_limit', 0);

        // As we are querying flat data, use `maxRowsInTable` as ranking query limit as it would be pointless to query more
        return max($configLimit, $maxRowsInTable);
    }
}
