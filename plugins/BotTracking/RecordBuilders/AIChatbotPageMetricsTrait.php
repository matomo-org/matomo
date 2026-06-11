<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\RecordBuilders;

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
 * Shared AI-chatbot page/document URL aggregation, used by both the Content Requests record builder
 * ({@see AIChatbotContentReports}) and the Favoured Pages record builder ({@see AIChatbotFavouredPages}).
 *
 * Keeping the per-URL request query in one place means the "how AI chatbot requests are counted"
 * predicate (join on idaction_url, bot_type = AI chatbot, action type, request-time window) has a
 * single source of truth: the Favoured Pages builder reuses the same `requests` count the Content
 * report exposes, so the two reports can never drift apart on what an "AI chatbot request" is.
 */
trait AIChatbotPageMetricsTrait
{
    /**
     * Queries page or document URLs requested by AI chatbots, including server-time and response-size raw
     * columns needed to compute averages at display time. Error-status columns are also stored so they are
     * available in Row Evolution and the "show all columns" toggle (hidden by default in configureView).
     */
    protected function queryPageOrDocumentUrls(ArchiveProcessor $archiveProcessor, int $actionType, int $rankingQueryLimit): DataTable
    {
        $logAggregator = $archiveProcessor->getLogAggregator();
        $where         = $logAggregator->getWhereStatement('bot', 'server_time');
        $botTable      = BotRequestsDao::getPrefixedTableName();
        $actionTable   = Common::prefixTable('log_action');

        $innerSql = sprintf(
            "SELECT log_action.name AS url,
                    COUNT(*) AS %s,
                    SUM(bot.response_time_ms) AS %s,
                    SUM(CASE WHEN bot.response_time_ms IS NOT NULL THEN 1 ELSE 0 END) AS %s,
                    SUM(bot.response_size_bytes) AS %s,
                    SUM(CASE WHEN bot.response_size_bytes IS NOT NULL THEN 1 ELSE 0 END) AS %s,
                    SUM(bot.http_status_code IN (404, 410)) AS %s,
                    SUM(bot.http_status_code BETWEEN 500 AND 599) AS %s
             FROM `%s` AS bot
             INNER JOIN `%s` AS log_action ON log_action.idaction = bot.idaction_url
             WHERE log_action.name IS NOT NULL
               AND log_action.name <> ''
               AND log_action.type = %d
               AND bot.bot_type = ?
               AND %s
             GROUP BY log_action.name
             ORDER BY %s DESC",
            Metrics::COLUMN_REQUESTS,
            Metrics::COLUMN_SUM_SERVER_TIME,
            Metrics::COLUMN_NB_SERVER_TIME,
            Metrics::COLUMN_SUM_RESPONSE_SIZE,
            Metrics::COLUMN_NB_RESPONSE_SIZE,
            Metrics::COLUMN_PAGE_NOT_FOUND_404_REQUESTS,
            Metrics::COLUMN_SERVER_ERROR_5XX_REQUESTS,
            $botTable,
            $actionTable,
            $actionType,
            $where,
            Metrics::COLUMN_REQUESTS
        );

        $columns = [
            Metrics::COLUMN_REQUESTS                    => 'sum',
            Metrics::COLUMN_SUM_SERVER_TIME             => 'sum',
            Metrics::COLUMN_NB_SERVER_TIME              => 'sum',
            Metrics::COLUMN_SUM_RESPONSE_SIZE           => 'sum',
            Metrics::COLUMN_NB_RESPONSE_SIZE            => 'sum',
            Metrics::COLUMN_PAGE_NOT_FOUND_404_REQUESTS => 'sum',
            Metrics::COLUMN_SERVER_ERROR_5XX_REQUESTS   => 'sum',
        ];

        return $this->executeUrlQuery($archiveProcessor, $innerSql, $columns, $rankingQueryLimit);
    }

    /**
     * Shared query helper: wraps an already-built inner SQL with a RankingQuery, executes it,
     * and populates a DataTable via sumRowWithLabel. The RankingQuery "Others" summary row label
     * is passed through as-is (matching the AIChatbotReports / AIReferrers patterns); the
     * framework's sumRowWithLabel handles the summary row routing via the string sentinel.
     *
     * @param string                $innerSql Already-interpolated SQL (bot_type bind placeholder kept as `?`).
     * @param array<string, string> $columns  Map of column name → RankingQuery aggregation op ('sum').
     *                                        These must match the SELECT aliases in $innerSql exactly.
     *                                        NOTE: (int) casts are safe for NULL-able sum/nb columns:
     *                                        NULL→0 is harmless because nb_* == 0 gates the avg computation
     *                                        in AvgServerTime::compute() / AvgResponseSize::compute().
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
