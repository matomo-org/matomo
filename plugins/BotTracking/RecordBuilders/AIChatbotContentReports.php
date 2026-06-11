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
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Plugins\BotTracking\Archiver;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

/**
 * Builds three flat blob records for the "AI Chatbots Content Requests" subcategory:
 *   - Pages record: page URLs requested by AI chatbots, with request counts and avg server-time/response-size.
 *   - Documents record: download URLs requested by AI chatbots, with the same metrics.
 *   - Broken Content record: page + document URLs that returned HTTP 4xx/5xx errors, with per-status counts.
 * All three records are keyed by full URL (single label dimension); no URL-path subtables.
 */
class AIChatbotContentReports extends RecordBuilder
{
    /**
     * @var int
     */
    protected $maxRowsInTable;

    /**
     * @var int
     */
    private $rankingQueryLimit;

    public function __construct()
    {
        parent::__construct();

        $this->maxRowsInTable    = GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_ai_chatbot_content', 20000);
        $this->rankingQueryLimit = $this->getRankingQueryLimit();
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::AI_CHATBOTS_REQUESTED_PAGES_RECORD)
                ->setColumnToSortByBeforeTruncation(Metrics::COLUMN_REQUESTS),
            Record::make(Record::TYPE_BLOB, Archiver::AI_CHATBOTS_REQUESTED_DOCUMENTS_RECORD)
                ->setColumnToSortByBeforeTruncation(Metrics::COLUMN_REQUESTS),
            Record::make(Record::TYPE_BLOB, Archiver::AI_CHATBOTS_BROKEN_CONTENT_RECORD)
                ->setColumnToSortByBeforeTruncation(Metrics::COLUMN_TOTAL_BROKEN_REQUESTS),
        ];
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        // don't process reports for any segment
        return $archiveProcessor->getParams()->getSegment()->isEmpty();
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $tables = [
            Archiver::AI_CHATBOTS_REQUESTED_PAGES_RECORD     => $this->queryPageOrDocumentUrls($archiveProcessor, Action::TYPE_PAGE_URL),
            Archiver::AI_CHATBOTS_REQUESTED_DOCUMENTS_RECORD => $this->queryPageOrDocumentUrls($archiveProcessor, Action::TYPE_DOWNLOAD),
            Archiver::AI_CHATBOTS_BROKEN_CONTENT_RECORD      => $this->queryBrokenContent($archiveProcessor),
        ];

        return $tables;
    }

    /**
     * Queries page or document URLs requested by AI chatbots, including server-time and response-size raw
     * columns needed to compute averages at display time. Error-status columns are also stored so they are
     * available in Row Evolution and the "show all columns" toggle (hidden by default in configureView).
     */
    private function queryPageOrDocumentUrls(ArchiveProcessor $archiveProcessor, int $actionType): DataTable
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

        return $this->executeUrlQuery($archiveProcessor, $innerSql, $columns);
    }

    /**
     * Queries page and document URLs that returned HTTP errors (404/410/5xx) to AI chatbots.
     * The HAVING clause in the inner query ensures only URLs with at least one error row appear.
     */
    private function queryBrokenContent(ArchiveProcessor $archiveProcessor): DataTable
    {
        $logAggregator = $archiveProcessor->getLogAggregator();
        $where         = $logAggregator->getWhereStatement('bot', 'server_time');
        $botTable      = BotRequestsDao::getPrefixedTableName();
        $actionTable   = Common::prefixTable('log_action');

        $innerSql = sprintf(
            "SELECT log_action.name AS url,
                    SUM(bot.http_status_code IN (404, 410)) + SUM(bot.http_status_code BETWEEN 500 AND 599) AS %s,
                    SUM(bot.http_status_code IN (404, 410)) AS %s,
                    SUM(bot.http_status_code BETWEEN 500 AND 599) AS %s
             FROM `%s` AS bot
             INNER JOIN `%s` AS log_action ON log_action.idaction = bot.idaction_url
             WHERE log_action.name IS NOT NULL
               AND log_action.name <> ''
               AND log_action.type IN (%d, %d)
               AND bot.bot_type = ?
               AND %s
             GROUP BY log_action.name
             HAVING %s >= 1
             ORDER BY %s DESC",
            Metrics::COLUMN_TOTAL_BROKEN_REQUESTS,
            Metrics::COLUMN_PAGE_NOT_FOUND_404_REQUESTS,
            Metrics::COLUMN_SERVER_ERROR_5XX_REQUESTS,
            $botTable,
            $actionTable,
            Action::TYPE_PAGE_URL,
            Action::TYPE_DOWNLOAD,
            $where,
            Metrics::COLUMN_TOTAL_BROKEN_REQUESTS,
            Metrics::COLUMN_TOTAL_BROKEN_REQUESTS
        );

        $columns = [
            Metrics::COLUMN_TOTAL_BROKEN_REQUESTS       => 'sum',
            Metrics::COLUMN_PAGE_NOT_FOUND_404_REQUESTS => 'sum',
            Metrics::COLUMN_SERVER_ERROR_5XX_REQUESTS   => 'sum',
        ];

        return $this->executeUrlQuery($archiveProcessor, $innerSql, $columns);
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
    private function executeUrlQuery(ArchiveProcessor $archiveProcessor, string $innerSql, array $columns): DataTable
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $rankingQuery = new RankingQuery($this->rankingQueryLimit);
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

    private function getRankingQueryLimit(): int
    {
        $configLimit = GeneralConfig::getIntegerConfigValue('archiving_ranking_query_row_limit', 0);

        // As we are querying flat data, use `maxRowsInTable` as ranking query limit as it would be pointless to query more
        return max($configLimit, $this->maxRowsInTable);
    }
}
