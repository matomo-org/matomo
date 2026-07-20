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
use Piwik\DataTable\Row;
use Piwik\Db;
use Piwik\Plugins\BotTracking\Archiver;
use Piwik\Plugins\BotTracking\Columns\Metrics\DiscrepancyScore;
use Piwik\Plugins\BotTracking\DataTable\FavouredPagesScorer;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

/**
 * Builds the two flat blob records backing the Human-Favoured and AI-Favoured Pages reports, keyed by
 * page URL with `unique_human_pageviews`, `ai_chatbot_requests` and `discrepancy_score`.
 *
 * Both sides are aggregated from the log tables on the same `log_action.name`, so the union is an
 * exact label match. The 0–100 {@see DiscrepancyScore} is materialised per variant and used as the
 * truncation sort, so each record keeps the pages that rank highest for its own report.
 *
 * One record per variant (~2x storage) is needed because the score is variant-specific: a single
 * record truncated by one variant's score would drop the other variant's most-relevant rows.
 */
class AIChatbotFavouredPages extends RecordBuilder
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

        $this->maxRowsInTable    = GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_ai_chatbot_favoured_pages', 50000);
        $this->rankingQueryLimit = $this->getRankingQueryLimit($this->maxRowsInTable);
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $records = [];
        foreach ($this->variantByRecord() as $recordName => $variant) {
            // The discrepancy_score is table-relative (see FavouredPagesScorer), so it can't be summed
            // across child periods: sum the additive traffic, skip the score, then recompute it on the
            // aggregated union via the transform below. Core then truncates by the recomputed score.
            $records[] = Record::make(Record::TYPE_BLOB, $recordName)
                ->setColumnToSortByBeforeTruncation(Metrics::COLUMN_DISCREPANCY_SCORE)
                ->setBlobColumnAggregationOps([
                    Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 'sum',
                    Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 'sum',
                    Metrics::COLUMN_DISCREPANCY_SCORE      => 'skip',
                ])
                ->setAggregatedRecordTransform(function (DataTable $table) use ($variant): void {
                    (new FavouredPagesScorer($variant))->addScores($table);
                });
        }

        return $records;
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        // don't process reports for any segment (these reports declare no segment support)
        return $archiveProcessor->getParams()->getSegment()->isEmpty();
    }

    /**
     * Day archiving: build the human/AI union from the logs once, then materialise the per-variant
     * score on an independent copy for each record. Core truncates each by its score column.
     */
    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        // Only the request count is needed here; the shared query's other metrics are for the Content report.
        $aiTable = $this->queryPageOrDocumentUrls($archiveProcessor, Action::TYPE_PAGE_URL, $this->rankingQueryLimit, [Metrics::COLUMN_REQUESTS]);

        // With no AI chatbot page requests there is nothing to favour against, so skip the (expensive)
        // human-pageviews scan and store empty records.
        if ($aiTable->getRowsCount() === 0) {
            Common::destroy($aiTable);
            return $this->emptyRecords();
        }

        $humanTable = $this->queryHumanPageviews($archiveProcessor);

        $records = [];
        foreach ($this->variantByRecord() as $recordName => $variant) {
            // mergeHumanAndAiTables returns a fresh table each call, so scoring one variant cannot
            // affect the other.
            $table = self::mergeHumanAndAiTables($humanTable, $aiTable);
            (new FavouredPagesScorer($variant))->addScores($table);
            $records[$recordName] = $table;
        }

        Common::destroy($humanTable);
        Common::destroy($aiTable);

        return $records;
    }

    /**
     * @return array<string, DataTable> an empty record per variant
     */
    private function emptyRecords(): array
    {
        $records = [];
        foreach (array_keys($this->variantByRecord()) as $recordName) {
            $records[$recordName] = new DataTable();
        }

        return $records;
    }

    /**
     * Merges the human-pageviews and AI-requests tables (both keyed on `log_action.name`) into the
     * flat per-URL union: every URL on either side appears once, the missing side defaulting to 0.
     *
     * The two per-side truncation tails are combined into one "Others" summary row, left UNSCORED — a
     * discrepancy score over an aggregate of many URLs is meaningless — so the default exclude-low-
     * population filter hides it while it stays visible when that filter is off.
     *
     * Pure (DataTable in / out) so it is unit-testable without a database.
     */
    public static function mergeHumanAndAiTables(DataTable $humanTable, DataTable $aiTable): DataTable
    {
        $byLabel = [];

        // The AI table comes from the shared page-metrics query, which names the request count
        // COLUMN_REQUESTS (the Content report's column); read it here as this report's AI requests.
        foreach ($aiTable->getRows() as $row) {
            if ($row->isSummaryRow()) {
                continue;
            }
            $label = (string) $row->getColumn('label');
            if ($label === '') {
                continue;
            }
            $byLabel[$label] = [
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 0,
                Metrics::COLUMN_AI_CHATBOT_REQUESTS    => (int) $row->getColumn(Metrics::COLUMN_REQUESTS),
            ];
        }

        foreach ($humanTable->getRows() as $row) {
            if ($row->isSummaryRow()) {
                continue;
            }
            $label = (string) $row->getColumn('label');
            if ($label === '') {
                continue;
            }
            $human = (int) $row->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS);
            if (isset($byLabel[$label])) {
                $byLabel[$label][Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS] = $human;
            } else {
                $byLabel[$label] = [
                    Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $human,
                    Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 0,
                ];
            }
        }

        $table = new DataTable();
        foreach ($byLabel as $label => $cols) {
            $table->addRow(new Row([
                Row::COLUMNS => [
                    'label'                                => (string) $label,
                    Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $cols[Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS],
                    Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $cols[Metrics::COLUMN_AI_CHATBOT_REQUESTS],
                ],
            ]));
        }

        self::addCombinedOthersRow($table, $humanTable, $aiTable);

        return $table;
    }

    /**
     * Combines the human and AI tables' "Others" summary rows (the truncated tail of each side) into a
     * single summary row on $table, with no discrepancy_score (see mergeHumanAndAiTables). Does nothing
     * when neither side was truncated.
     */
    private static function addCombinedOthersRow(DataTable $table, DataTable $humanTable, DataTable $aiTable): void
    {
        $humanOthers = $humanTable->getSummaryRow();
        $aiOthers    = $aiTable->getSummaryRow();

        if (!$humanOthers instanceof Row && !$aiOthers instanceof Row) {
            return;
        }

        $label = ($humanOthers instanceof Row ? $humanOthers : $aiOthers)->getColumn('label') ?: DataTable::LABEL_SUMMARY_ROW;

        $summary = new Row([
            Row::COLUMNS => [
                'label'                                => $label,
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $humanOthers instanceof Row ? (int) $humanOthers->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS) : 0,
                Metrics::COLUMN_AI_CHATBOT_REQUESTS    => $aiOthers instanceof Row ? (int) $aiOthers->getColumn(Metrics::COLUMN_REQUESTS) : 0,
            ],
        ]);
        $summary->setIsSummaryRow();
        $table->addSummaryRow($summary);
    }

    /**
     * @return array<string, string> record name => DiscrepancyScore variant
     */
    private function variantByRecord(): array
    {
        return [
            Archiver::AI_CHATBOTS_HUMAN_FAVOURED_PAGES_RECORD => DiscrepancyScore::VARIANT_HUMAN_FAVOURED,
            Archiver::AI_CHATBOTS_AI_FAVOURED_PAGES_RECORD    => DiscrepancyScore::VARIANT_AI_FAVOURED,
        ];
    }

    /**
     * Human pageviews (distinct visits, matching Actions' page `nb_visits`) per page URL, keyed on
     * `log_action.name` to merge directly with the AI-side counts. The `idaction_event_category IS
     * NULL` clause mirrors the Actions Pages report: a URL seen only as an event context is not a
     * pageview, so it must not be counted here.
     */
    private function queryHumanPageviews(ArchiveProcessor $archiveProcessor): DataTable
    {
        $logAggregator = $archiveProcessor->getLogAggregator();
        $where         = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $actionTable   = Common::prefixTable('log_action');
        $visitActions  = Common::prefixTable('log_link_visit_action');

        $innerSql = sprintf(
            "SELECT log_action.name AS url,
                    COUNT(DISTINCT log_link_visit_action.idvisit) AS %s
             FROM `%s` AS log_link_visit_action
             INNER JOIN `%s` AS log_action ON log_action.idaction = log_link_visit_action.idaction_url
             WHERE log_action.name IS NOT NULL
               AND log_action.name <> ''
               AND log_action.type = %d
               AND log_link_visit_action.idaction_event_category IS NULL
               AND %s
             GROUP BY log_action.name
             ORDER BY %s DESC",
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS,
            $visitActions,
            $actionTable,
            Action::TYPE_PAGE_URL,
            $where,
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS
        );

        $rankingQuery = new RankingQuery($this->rankingQueryLimit);
        $rankingQuery->addLabelColumn('url');
        $rankingQuery->addColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, 'sum');

        $wrappedSql = $rankingQuery->generateRankingQuery($innerSql);

        $stmt = Db::query($wrappedSql, $logAggregator->getGeneralQueryBindParams());

        $table = new DataTable();
        while ($row = $stmt->fetch()) {
            /** @var array<string, int|string|null> $row */
            $label = (string) $row['url'];
            $raw   = $row[Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS] ?? 0;

            $table->sumRowWithLabel($label, [
                Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => is_numeric($raw) ? (int) $raw : 0,
            ]);
        }

        return $table;
    }
}
