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
 * Builds the two flat blob records backing the Human-Favoured and AI-Favoured Pages reports, keyed
 * by full page URL with `unique_human_pageviews`, `ai_chatbot_requests` and `discrepancy_score`.
 *
 * Each row pairs the human pageviews a URL received (distinct visits, matching the Actions Pages
 * report's `nb_visits`) with the AI chatbot requests it received. Both sides are aggregated straight
 * from the log tables and keyed on the same `log_action.name`, so the union is an exact label match
 * (no URL reconstruction/normalisation needed). The bounded 0–100 {@see DiscrepancyScore} is then
 * materialised per variant and used as the truncation sort column, so each record keeps the pages
 * that rank highest for its own report.
 *
 * One record per variant is required because the score is variant-specific (Human/AI swap the strong
 * side) and truncation must keep each report's own top pages. This costs ~2x the storage of a single
 * shared record, accepted deliberately: a single record truncated by one variant's score would drop
 * the other variant's most-relevant rows.
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
            $records[] = Record::make(Record::TYPE_BLOB, $recordName)
                ->setColumnToSortByBeforeTruncation(Metrics::COLUMN_DISCREPANCY_SCORE);
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
        $humanTable = $this->queryHumanPageviews($archiveProcessor);
        // Only the request count is needed here; the shared query's other metrics are for the Content report.
        $aiTable    = $this->queryPageOrDocumentUrls($archiveProcessor, Action::TYPE_PAGE_URL, $this->rankingQueryLimit, [Metrics::COLUMN_REQUESTS]);

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
     * Non-day archiving: the score is table-relative (anchored to the period's busiest page) and so
     * cannot be summed across child periods. We therefore override the standard "sum the day blobs"
     * path: sum the additive traffic columns from the child blobs, then re-run the scorer on the
     * period's full union so the stored score is exact, and truncate by that real score.
     *
     * This is the only RecordBuilder in the codebase that overrides this method; it does so because
     * a per-row, table-normalised metric has no valid blob-aggregation operation. If core changes
     * the non-day aggregation contract, this override must be revisited.
     */
    public function buildForNonDayPeriod(ArchiveProcessor $archiveProcessor): void
    {
        if (!$this->isEnabled($archiveProcessor)) {
            return;
        }

        $requestedReports     = $archiveProcessor->getParams()->getArchiveOnlyReportAsArray();
        $foundRequestedReports = $archiveProcessor->getParams()->getFoundRequestedReports();

        $columnAggregationOps = [
            Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => 'sum',
            Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 'sum',
            // skipped: recomputed from the summed traffic columns below, never summed
            Metrics::COLUMN_DISCREPANCY_SCORE      => 'skip',
        ];

        foreach ($this->variantByRecord() as $recordName => $variant) {
            // honour partial archiving (eg core:archive --force-report / single-report invalidation):
            // only (re)build the record(s) actually requested, and skip ones already found.
            if (
                !empty($requestedReports)
                && (!in_array($recordName, $requestedReports) || in_array($recordName, $foundRequestedReports))
            ) {
                continue;
            }

            [$table] = $this->aggregateRootDataTableFromBlobs($archiveProcessor, $recordName, $columnAggregationOps, null);

            (new FavouredPagesScorer($variant))->addScores($table);

            $this->insertBlobRecord(
                $archiveProcessor,
                $recordName,
                $table,
                $this->maxRowsInTable,
                null,
                Metrics::COLUMN_DISCREPANCY_SCORE
            );

            Common::destroy($table);
        }
    }

    /**
     * Merges the human-pageviews table and the AI-requests table into the flat per-URL union that
     * backs the favoured-pages records. Both inputs are keyed by the same `log_action.name` label;
     * the human table carries {@see Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS} and the AI table carries
     * {@see Metrics::COLUMN_REQUESTS}. Every URL on either side appears once, with the missing side
     * defaulting to 0.
     *
     * The two per-side truncation tails are combined into a single "Others" summary row carrying the
     * summed human pageviews and AI requests of the pages beyond the cap. That row is left UNSCORED on
     * purpose — a discrepancy score over an aggregate of many URLs is meaningless — so the default
     * exclude-low-population filter (score < 1) hides it, while it stays visible (pinned at the foot of
     * the table) when that filter is turned off.
     *
     * Pure (DataTable in / DataTable out) so it is unit-testable without a database.
     */
    public static function mergeHumanAndAiTables(DataTable $humanTable, DataTable $aiTable): DataTable
    {
        $byLabel = [];

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

        $label = ($humanOthers instanceof Row ? $humanOthers : $aiOthers)->getColumn('label');

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
     * Queries human pageviews (distinct visits, matching Actions' page `nb_visits`) per page URL,
     * keyed by `log_action.name` so it merges directly with the AI-side request counts.
     *
     * The `idaction_event_category IS NULL` filter mirrors the Actions Pages report
     * (see ActionReports::getWhereClauseActionIsNotEvent): a page URL that only appears as the
     * context of an event is not a pageview, so it must not be counted here — otherwise the human
     * pageviews would diverge from the Pages report.
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
