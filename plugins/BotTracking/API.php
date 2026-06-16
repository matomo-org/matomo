<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Piwik;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\BotTracking\RecordBuilders\AIChatbotReports;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\BotTracking\Reports\Get;
use Piwik\Plugins\Referrers\AIAssistant;

/**
 * Provides API methods for bot and AI chatbot reporting.
 *
 * @method static \Piwik\Plugins\BotTracking\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the main bot tracking report.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|string[]|null $columns Optional metric names to include in the report.
     * @return DataTable|DataTable\Map Bot tracking metrics for the requested site selection and period.
     */
    public function get($idSite, string $period, string $date, $columns = null): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, '');

        $metrics = Metrics::getReportMetricColumns();

        if ($period !== 'day') {
            $metrics = array_filter($metrics, function ($metric) {
                return !in_array($metric, [Metrics::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS, Metrics::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS]);
            });
        }

        $requestedColumns = Piwik::getArrayFromApiParameter($columns);

        /** @var Get $report */
        $report  = ReportsProvider::factory('BotTracking', 'get');
        $columns = $report->getMetricsRequiredForReport($metrics, $requestedColumns);

        $dataTable = $archive->getDataTableFromNumeric($columns);

        if (!empty($requestedColumns)) {
            $dataTable->queueFilter('ColumnDelete', [$columnsToRemove = [], $requestedColumns]);
        }

        return $dataTable;
    }

    /**
     * Returns a report about AI chatbot requests.
     * Depending on the provided secondary dimension the subtables will either contain all requested page urls or document urls.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param bool $expanded Whether subtables should be expanded in the response.
     * @param bool $flat Whether subtable rows should be flattened into a single table.
     * @param 'pages'|'documents'|null $secondaryDimension Optional secondary dimension for subtable rows.
     *                                                     Use `pages` for page URLs or `documents` for document URLs.
     * @return DataTable|DataTable\Map Requests per AI chatbot for the selected secondary dimension.
     */
    public function getAIChatbotRequests($idSite, string $period, string $date, bool $expanded = false, bool $flat = false, ?string $secondaryDimension = null): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archiveName = Archiver::AI_CHATBOTS_PAGES_RECORD;

        if ($secondaryDimension === 'documents') {
            $archiveName = Archiver::AI_CHATBOTS_DOCUMENTS_RECORD;
        }

        $dataTable = Archive::createDataTableFromArchive($archiveName, $idSite, $period, $date, '', $expanded, $flat);

        // When flattening a report, remove all main table rows, where no subtable exists
        if ($flat) {
            $dataTable->filter(function (DataTable $table) {
                foreach ($table->getRows() as $key => $row) {
                    if (!$row->getIdSubDataTable()) {
                        $table->deleteRow($key);
                    }
                }
            });
        }

        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRows() as $key => $row) {
                $label = $row->getColumn('label');
                // @phpstan-ignore-next-line  check in next line causes PHPStan violations as CHATBOT_MAPPING currently does not have an entry with empty value
                if (array_key_exists($label, AIChatbotReports::CHATBOT_MAPPING) && !empty(AIChatbotReports::CHATBOT_MAPPING[$label])) {
                    $row->setColumn('label', AIChatbotReports::CHATBOT_MAPPING[$label]);
                }
            }
        });

        $dataTable->queueFilter('ColumnCallbackAddMetadata', [
            'label',
            'url',
            function ($label) {
                return AIAssistant::getInstance()->getMainUrlFromName($label);
            },
        ]);
        $dataTable->queueFilter('MetadataCallbackAddMetadata', [
            'url',
            'logo',
            function ($url) {
                return AIAssistant::getInstance()->getLogoFromUrl($url ?: '');
            },
        ]);

        return $dataTable;
    }

    /**
     * Returns page URLs requested by a specific AI chatbot.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param int $idSubtable Subtable ID for the AI chatbot row to expand.
     * @return DataTable|DataTable\Map Page URLs requested by the selected AI chatbot.
     */
    public function getPageUrlsForAIChatbot($idSite, string $period, string $date, int $idSubtable): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_PAGES_RECORD, $idSite, $period, $date, '', false, false, $idSubtable);
    }

    /**
     * Returns document URLs requested by a specific AI chatbot.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param int $idSubtable Subtable ID for the AI chatbot row to expand.
     * @return DataTable|DataTable\Map Document URLs requested by the selected AI chatbot.
     */
    public function getDocumentUrlsForAIChatbot($idSite, string $period, string $date, int $idSubtable): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_DOCUMENTS_RECORD, $idSite, $period, $date, '', false, false, $idSubtable);
    }

    /**
     * Returns page URLs accessed by AI chatbots across all chatbots, with server time and response size metrics.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @return DataTable|DataTable\Map Flat table of page URLs with Requests, Avg. Server Time, and Avg. Response Size.
     */
    public function getAIChatbotContentPages($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_REQUESTED_PAGES_RECORD, $idSite, $period, $date, '', false, false);
    }

    /**
     * Returns document URLs accessed by AI chatbots across all chatbots, with server time and response size metrics.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @return DataTable|DataTable\Map Flat table of document URLs with Requests, Avg. Server Time, and Avg. Response Size.
     */
    public function getAIChatbotContentDocuments($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_REQUESTED_DOCUMENTS_RECORD, $idSite, $period, $date, '', false, false);
    }

    /**
     * Returns page and document URLs accessed by AI chatbots that returned HTTP errors (4xx/5xx).
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @return DataTable|DataTable\Map Flat table of broken URLs with 5XX Requests and Page Not Found (404) Requests counts.
     */
    public function getAIChatbotBrokenContent($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_BROKEN_CONTENT_RECORD, $idSite, $period, $date, '', false, false);
    }

    /**
     * Returns page URLs visited far more by humans than requested by AI chatbots.
     *
     * Each row carries Unique Human Pageviews, AI Chatbot Requests and the Human-Favoured
     * Discrepancy Score (a bounded 0–100 index materialised on the table).
     *
     * Note: the "exclude low population" filter that the UI applies by default is a ViewDataTable
     * decoration only — a direct API call returns every row (including pages with no human
     * pageviews). Segmentation is not supported: any `segment` parameter is ignored and the
     * standard, unsegmented data is returned.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process.
     * @param string $date The date or date range to process.
     * @return DataTable|DataTable\Map Flat table of URLs with the two source metrics and the score.
     */
    public function getAIChatbotHumanFavouredPages($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        // The scored data is archived (see AIChatbotFavouredPages); just read it back. The empty
        // segment is intentional — these reports are unsegmented, so a requested segment is ignored.
        $table = Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_HUMAN_FAVOURED_PAGES_RECORD, $idSite, $period, $date, '', false, false);

        return $this->skipScoreInReportTotals($table);
    }

    /**
     * Returns page URLs requested far more by AI chatbots than visited by humans.
     *
     * Each row carries Unique Human Pageviews, AI Chatbot Requests and the AI-Favoured
     * Discrepancy Score (a bounded 0–100 index materialised on the table).
     *
     * Note: the "exclude low population" filter that the UI applies by default is a ViewDataTable
     * decoration only — a direct API call returns every row (including pages with no AI chatbot
     * requests). Segmentation is not supported: any `segment` parameter is ignored and the
     * standard, unsegmented data is returned.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process.
     * @param string $date The date or date range to process.
     * @return DataTable|DataTable\Map Flat table of URLs with the two source metrics and the score.
     */
    public function getAIChatbotAIFavouredPages($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        // See getAIChatbotHumanFavouredPages: the scored data is archived; read it back unsegmented.
        $table = Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_AI_FAVOURED_PAGES_RECORD, $idSite, $period, $date, '', false, false);

        return $this->skipScoreInReportTotals($table);
    }

    /**
     * Marks the discrepancy_score column 'skip' so the report totals row leaves it blank — summing a
     * per-page 0–100 index is meaningless. The scorer sets this op at archive time, but a DataTable's
     * column-aggregation metadata is transient and is not part of the serialised blob, so it is lost on
     * load and must be re-applied here on read. Recurses into DataTable\Map (multi-period / multi-site).
     *
     * @param DataTable|DataTable\Map $table
     * @return DataTable|DataTable\Map
     */
    private function skipScoreInReportTotals(DataTableInterface $table): DataTableInterface
    {
        if ($table instanceof DataTable\Map) {
            foreach ($table->getDataTables() as $childTable) {
                $this->skipScoreInReportTotals($childTable);
            }

            return $table;
        }

        if ($table instanceof DataTable) {
            $ops = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
            if (!is_array($ops)) {
                $ops = [];
            }
            $ops[Metrics::COLUMN_DISCREPANCY_SCORE] = 'skip';
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $ops);
        }

        return $table;
    }
}
