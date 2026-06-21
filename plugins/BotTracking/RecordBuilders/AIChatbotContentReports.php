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
use Piwik\Plugins\BotTracking\Archiver;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tracker\Action;

/**
 * Builds three flat blob records for the "AI Chatbots Content Requests" subcategory:
 *   - Pages record: page URLs requested by AI chatbots, with request counts and avg server-time/response-size.
 *   - Documents record: download URLs requested by AI chatbots, with the same metrics.
 *   - Broken Content record: page + document URLs that returned HTTP 4xx/5xx errors, with per-status counts.
 * All three records are keyed by full URL (single label dimension); no URL-path subtables.
 *
 * The per-URL page/document request query lives in {@see AIChatbotPageMetricsTrait} so the Favoured Pages
 * builder can reuse the exact same "AI chatbot request" counting logic.
 */
class AIChatbotContentReports extends RecordBuilder
{
    use AIChatbotPageMetricsTrait;

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

        $this->maxRowsInTable    = GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_ai_chatbot_content', 50000);
        $this->rankingQueryLimit = $this->getRankingQueryLimit($this->maxRowsInTable);
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
        // The content reports expose the full metric set (requests + avg server-time/response-size + errors).
        $columns = array_keys($this->pageUrlMetricExpressions());

        $tables = [
            Archiver::AI_CHATBOTS_REQUESTED_PAGES_RECORD     => $this->queryPageOrDocumentUrls($archiveProcessor, Action::TYPE_PAGE_URL, $this->rankingQueryLimit, $columns),
            Archiver::AI_CHATBOTS_REQUESTED_DOCUMENTS_RECORD => $this->queryPageOrDocumentUrls($archiveProcessor, Action::TYPE_DOWNLOAD, $this->rankingQueryLimit, $columns),
            Archiver::AI_CHATBOTS_BROKEN_CONTENT_RECORD      => $this->queryBrokenContent($archiveProcessor),
        ];

        return $tables;
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

        return $this->executeUrlQuery($archiveProcessor, $innerSql, $columns, $this->rankingQueryLimit);
    }
}
