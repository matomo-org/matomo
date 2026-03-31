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
}
