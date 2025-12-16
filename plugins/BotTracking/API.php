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
use Piwik\DataTable\Filter\ColumnDelete;
use Piwik\Piwik;

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
                return !in_array($metric, [Metrics::METRIC_AI_ASSISTANTS_UNIQUE_DOCUMENT_URLS, Metrics::METRIC_AI_ASSISTANTS_UNIQUE_PAGE_URLS]);
            });
        }

        $dataTable = $archive->getDataTableFromNumeric($metrics);

        $this->filterColumns($dataTable, $columns);

        return $dataTable;
    }

    /**
     * Returns a report about AI assistants crawling your site and how many hits each one generates. Depending on the provided secondary dimension
     * the subtable will either contain all requested page urls or document urls.
     *
     * @param string|int|int[] $idSite
     * @param null|'pages'|'documents' $secondaryDimension can be either `pages` (default) or `documents`
     * @return DataTable|DataTable\Map
     */
    public function getAIAssistantRequests($idSite, string $period, string $date, bool $expanded = false, bool $flat = false, ?string $secondaryDimension = null): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archiveName = Archiver::AI_ASSISTANTS_PAGES_RECORD;

        if ($secondaryDimension === 'documents') {
            $archiveName = Archiver::AI_ASSISTANTS_DOCUMENTS_RECORD;
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

        return $dataTable;
    }

    /**
     * @param string|int|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    public function getPageUrlsForAIAssistant($idSite, string $period, string $date, int $idSubtable): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_ASSISTANTS_PAGES_RECORD, $idSite, $period, $date, '', false, false, $idSubtable);
    }

    /**
     * @param string|int|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    public function getDocumentUrlsForAIAssistant($idSite, string $period, string $date, int $idSubtable): DataTableInterface
    {
        Piwik::checkUserHasViewAccess($idSite);

        return Archive::createDataTableFromArchive(Archiver::AI_ASSISTANTS_DOCUMENTS_RECORD, $idSite, $period, $date, '', false, false, $idSubtable);
    }

    /**
     * @param null|string|string[] $columns
     */
    private function filterColumns(DataTableInterface $table, $columns): void
    {
        if (empty($columns)) {
            return;
        }

        $columnsToKeep = Piwik::getArrayFromApiParameter($columns);
        if (empty($columnsToKeep)) {
            return;
        }

        $table->filter(ColumnDelete::class, [[], $columnsToKeep]);
    }
}
