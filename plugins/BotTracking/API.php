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
use Piwik\Plugins\Referrers\AIAssistant;

class API extends \Piwik\Plugin\API
{
    /**
     * @param string|int|int[] $idSite
     * @param null|string|string[] $columns
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

        $report  = ReportsProvider::factory('BotTracking', 'get');
        $columns = $report->getMetricsRequiredForReport($metrics, $requestedColumns);

        $dataTable = $archive->getDataTableFromNumeric($columns);

        if (!empty($requestedColumns)) {
            $dataTable->queueFilter('ColumnDelete', [$columnsToRemove = [], $requestedColumns]);
        }

        return $dataTable;
    }

    /**
     * Returns a report about AI chatbots crawling your site and how many hits each one generates. Depending on the provided secondary dimension
     * the subtable will either contain all requested page urls or document urls.
     *
     * @param string|int|int[] $idSite
     * @param null|'pages'|'documents' $secondaryDimension can be either `pages` (default) or `documents`
     * @return DataTable|DataTable\Map
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
     * @param string|int|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    public function getPageUrlsForAIChatbot($idSite, string $period, string $date, int $idSubtable): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_PAGES_RECORD, $idSite, $period, $date, '', false, false, $idSubtable);
    }

    /**
     * @param string|int|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    public function getDocumentUrlsForAIChatbot($idSite, string $period, string $date, int $idSubtable): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_CHATBOTS_DOCUMENTS_RECORD, $idSite, $period, $date, '', false, false, $idSubtable);
    }
}
