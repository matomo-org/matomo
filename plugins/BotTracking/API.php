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
use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugins\Actions\API as ActionsApi;
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
     * Discrepancy Score (computed by the report's processed metric on read).
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process.
     * @param string $date The date or date range to process.
     * @return DataTable|DataTable\Map Flat table of URLs with the two source metrics.
     */
    public function getAIChatbotHumanFavouredPages($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return $this->buildFavouredPagesTable($idSite, $period, $date);
    }

    /**
     * Returns page URLs requested far more by AI chatbots than visited by humans.
     *
     * Each row carries Unique Human Pageviews, AI Chatbot Requests and the AI-Favoured
     * Discrepancy Score (computed by the report's processed metric on read).
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process.
     * @param string $date The date or date range to process.
     * @return DataTable|DataTable\Map Flat table of URLs with the two source metrics.
     */
    public function getAIChatbotAIFavouredPages($idSite, string $period, string $date): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return $this->buildFavouredPagesTable($idSite, $period, $date);
    }

    /**
     * Builds the merged source table that backs both favoured-pages reports.
     *
     * Strategy (per the DEV-19843 technical notes):
     *  1. Start from the AI chatbot content-pages table, strip every column except `requests`
     *     and rename it to `ai_chatbot_requests`.
     *  2. Walk the (flat) Actions.getPageUrls table; for each row, look up the matching bot row
     *     by URL label and patch `unique_human_pageviews`, or append a fresh row when the URL is
     *     human-only.
     *  3. Default `unique_human_pageviews` to 0 on rows that the Actions walk didn't touch so the
     *     processed-metric formula always has both inputs.
     *
     * @param int|string|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    private function buildFavouredPagesTable($idSite, string $period, string $date): DataTableInterface
    {
        $botData     = $this->getAIChatbotContentPages($idSite, $period, $date);
        $actionsData = ActionsApi::getInstance()->getPageUrls($idSite, $period, $date, false, false, false, false, true);

        return $this->mergeFavouredTables($botData, $actionsData);
    }

    /**
     * @param DataTable|DataTable\Map $botData
     * @param DataTable|DataTable\Map $actionsData
     * @return DataTable|DataTable\Map
     */
    private function mergeFavouredTables(DataTableInterface $botData, DataTableInterface $actionsData): DataTableInterface
    {
        if ($botData instanceof DataTable\Map) {
            $actionsChildren = $actionsData instanceof DataTable\Map ? $actionsData->getDataTables() : [];

            foreach ($botData->getDataTables() as $key => $botChild) {
                $actionsChild = $actionsChildren[$key] ?? new DataTable();
                $botData->addTable($this->mergeFavouredTables($botChild, $actionsChild), $key);
            }

            return $botData;
        }

        // After the early return $botData is necessarily a flat DataTable. Guard the Actions side
        // anyway in case the period/site shape diverges between the two APIs.
        $actionsTable = $actionsData instanceof DataTable ? $actionsData : new DataTable();
        return $this->mergeFavouredTable($botData, $actionsTable);
    }

    private function mergeFavouredTable(DataTable $botTable, DataTable $actionsTable): DataTable
    {
        // Step 1: keep only `requests` on the bot table and rename it to ai_chatbot_requests.
        foreach ($botTable->getRows() as $row) {
            $requests = (int) $row->getColumn(Metrics::COLUMN_REQUESTS);
            $row->setColumns([
                'label'                            => $row->getColumn('label'),
                Metrics::COLUMN_AI_CHATBOT_REQUESTS => $requests,
            ]);
            // Drop any metadata that was carried over from the bot blob (segment hints etc.).
            $row->deleteMetadata();
        }
        $botTable->setLabelsHaveChanged();

        // Step 2: walk Actions rows and patch / append into the bot table.
        foreach ($actionsTable->getRows() as $actionsRow) {
            $url = $actionsRow->getMetadata('url');
            if (!is_string($url) || $url === '') {
                continue;
            }
            $label = preg_replace('#^https?://#', '', $url);
            if (!is_string($label) || $label === '') {
                continue;
            }

            $nbVisits = (int) $actionsRow->getColumn('nb_visits');

            $matchingBotRow = $botTable->getRowFromLabel($label);
            if ($matchingBotRow !== false) {
                $matchingBotRow->setColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, $nbVisits);
                continue;
            }

            $botTable->addRow(new Row([
                Row::COLUMNS => [
                    'label'                                => $label,
                    Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS => $nbVisits,
                    Metrics::COLUMN_AI_CHATBOT_REQUESTS    => 0,
                ],
            ]));
        }

        // Step 3: rows that the Actions walk didn't touch still need unique_human_pageviews = 0.
        foreach ($botTable->getRows() as $row) {
            if ($row->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS) === false) {
                $row->setColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS, 0);
            }
        }

        return $botTable;
    }
}
