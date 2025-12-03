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
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Plugins\BotTracking\Archiver;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

class AIAssistantReports extends RecordBuilder
{
    /**
     * @var array<string, string>
     */
    private const ASSISTANT_MAPPING = [
        'ChatGPT-User'         => 'ChatGPT',
        'MistralAI-User'       => 'Le Chat',
        'Gemini-Deep-Research' => 'Gemini',
        'Claude-User'          => 'Claude',
        'Perplexity-User'      => 'Perplexity',
        'Google-NotebookLM'    => 'NotebookLM',
        'Devin'                => '',
    ];

    /**
     * @var int
     */
    private $rankingQueryLimit;

    public function __construct()
    {
        parent::__construct();

        $this->columnToSortByBeforeTruncation = Metrics::COLUMN_REQUESTS;
        $this->maxRowsInTable                 = (int)GeneralConfig::getConfigValue('datatable_archiving_maximum_rows_bots');
        $this->maxRowsInSubtable              = (int)GeneralConfig::getConfigValue('datatable_archiving_maximum_rows_subtable_bots');
        $this->rankingQueryLimit              = $this->getRankingQueryLimit();
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::AI_ASSISTANTS_PAGES_RECORD),
            Record::make(Record::TYPE_BLOB, Archiver::AI_ASSISTANTS_DOCUMENTS_RECORD),
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
            Archiver::AI_ASSISTANTS_PAGES_RECORD     => new DataTable(),
            Archiver::AI_ASSISTANTS_DOCUMENTS_RECORD => new DataTable(),
        ];

        $this->populateTables($archiveProcessor, $tables);

        return $tables;
    }

    /**
     * @param array<string, DataTable> $tables
     */
    private function populateTables(ArchiveProcessor $archiveProcessor, array &$tables): void
    {
        $logAggregator = $archiveProcessor->getLogAggregator();
        $params        = $archiveProcessor->getParams();
        $sites         = $params->getIdSites();

        if (empty($sites)) {
            return;
        }

        $visits   = $this->queryAcquiredVisitsByAIAssistant($logAggregator);

        $this->populateTableForActionType($tables, Action::TYPE_PAGE_URL, $logAggregator, $visits);
        $this->populateTableForActionType($tables, Action::TYPE_DOWNLOAD, $logAggregator, $visits);
    }

    /**
     * @return array<string,int>
     */
    private function queryAcquiredVisitsByAIAssistant(LogAggregator $logAggregator): array
    {
        $where    = $logAggregator->getWhereStatement('log_visit', 'visit_last_action_time');
        $bindBase = $logAggregator->getGeneralQueryBindParams();

        $sql = sprintf(
            "SELECT `referer_name`, COUNT(*) AS `visits`
             FROM %s AS `log_visit`
             WHERE `referer_type` = %d
               AND `referer_name` <> ''
               AND %s
             GROUP BY `referer_name`",
            Common::prefixTable('log_visit'),
            Common::REFERRER_TYPE_AI_ASSISTANT,
            $where
        );

        $stmt   = Db::query($sql, $bindBase);
        $result = [];

        while ($row = $stmt->fetch()) {
            /**
             * @var array{visits: string|int, referer_name: string} $row
             */
            if (in_array($row['referer_name'], self::ASSISTANT_MAPPING)) {
                $key          = (string)array_search($row['referer_name'], self::ASSISTANT_MAPPING);
                $result[$key] = (int)$row['visits'];
            }
        }

        return $result;
    }

    /**
     * @param array<string, DataTable> $tables
     * @param array<string, int> $visits
     * @return void
     */
    private function populateTableForActionType(array $tables, int $actionType, LogAggregator $logAggregator, array $visits): void
    {
        $resultSet  = $this->queryBotRequests($logAggregator, $actionType);
        $actionRows = [];

        while ($row = $resultSet->fetch()) {
            /**
             * @var array{requests: int, bot_name: ?string, url: ?string} $row
             */
            $label = $row['bot_name'];
            $url   = $row['url'];

            if (is_null($label)) {
                continue;
            }

            if (!is_null($url)) {
                $actionRows[] = $row;
                continue;
            }

            $metrics = [
                Metrics::COLUMN_REQUESTS          => $row['requests'],
                Metrics::COLUMN_DOCUMENT_REQUESTS => $actionType === Action::TYPE_DOWNLOAD ? $row['requests'] : 0,
                Metrics::COLUMN_PAGE_REQUESTS     => $actionType === Action::TYPE_PAGE_URL ? $row['requests'] : 0,
                Metrics::COLUMN_ACQUIRED_VISITS   => $visits[$label] ?? 0,
            ];

            $tables[Archiver::AI_ASSISTANTS_PAGES_RECORD]->sumRowWithLabel($label, $metrics, [Metrics::COLUMN_ACQUIRED_VISITS => 'max']);
            $tables[Archiver::AI_ASSISTANTS_DOCUMENTS_RECORD]->sumRowWithLabel($label, $metrics, [Metrics::COLUMN_ACQUIRED_VISITS => 'max']);
        }

        $table = $tables[Archiver::AI_ASSISTANTS_PAGES_RECORD];

        if ($actionType === Action::TYPE_DOWNLOAD) {
            $table = $tables[Archiver::AI_ASSISTANTS_DOCUMENTS_RECORD];
        }

        // use while / array_shift combination instead of foreach to save memory
        while (is_array($actionRows) && count($actionRows)) {
            /**
             * @var array{requests: int, bot_name: string, url: string} $row
             */
            $row   = array_shift($actionRows);
            $label = $row['bot_name'];
            $url   = $row['url'];

            if ($label === RankingQuery::LABEL_SUMMARY_ROW) {
                continue;
            }

            $tableRow = $table->getRowFromLabel($label);

            if (empty($tableRow)) {
                continue;
            }

            $normalized = PageUrl::normalizeUrl($url);
            $url        = $normalized['url'];

            $tableRow->sumRowWithLabelToSubtable($url, [
                Metrics::COLUMN_REQUESTS => $row['requests'],
            ]);
        }
    }

    private function queryBotRequests(LogAggregator $logAggregator, int $actionType)
    {
        $where  = $logAggregator->getWhereStatement('bot', 'server_time');

        $sql = sprintf(
            "SELECT * FROM (SELECT bot.bot_name, log_action.name AS url, COUNT(*) AS requests
             FROM %s AS bot
             INNER JOIN %s AS log_action ON log_action.idaction = bot.idaction_url
             WHERE log_action.name IS NOT NULL
               AND log_action.name <> ''
               AND log_action.type = %d
               AND %s
             GROUP BY bot.bot_name, url WITH ROLLUP) AS rollupQuery
             ORDER BY bot_name, requests DESC, url",
            BotRequestsDao::getPrefixedTableName(),
            Common::prefixTable('log_action'),
            $actionType,
            $where
        );

        if ($this->rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($this->rankingQueryLimit);
            $rankingQuery->addLabelColumn(['bot_name', 'url']);
            $rankingQuery->addColumn('requests', 'sum');
            $sql = $rankingQuery->generateRankingQuery($sql, true);
        }

        return Db::query($sql, $logAggregator->getGeneralQueryBindParams());
    }

    private function getRankingQueryLimit(): int
    {
        $maxRowsInTable    = (int)$this->maxRowsInTable;
        $maxRowsInSubtable = (int)$this->maxRowsInSubtable;

        $configLimit = (int)GeneralConfig::getConfigValue('archiving_ranking_query_row_limit');
        $configLimit = max($configLimit, 10 * $maxRowsInTable);

        if ($configLimit === 0) {
            return 0;
        }

        return max($configLimit, $maxRowsInTable, $maxRowsInSubtable);
    }
}
