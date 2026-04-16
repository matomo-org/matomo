<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\System;

use Piwik\Archive;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Metrics;
use Piwik\Plugins\Actions\Archiver;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Actions
 * @group Plugins
 */
class MixedArchivingAggregationTest extends IntegrationTestCase
{
    public function testWeekAggregationCombinesLegacyAndFlatDaysAndMergesOthers()
    {
        $idSite = Fixture::createWebsite('2026-01-01 00:00:00');

        $this->trackUrlHits($idSite, '2026-01-06', [
            '/a' => 9,
            '/b' => 6,
            '/c' => 3,
        ]);
        $this->trackUrlHits($idSite, '2026-01-07', [
            '/a' => 5,
            '/b' => 2,
            '/d' => 4,
        ]);

        $config = Config::getInstance();
        $configKeys = [
            'datatable_archiving_maximum_rows_actions_flat',
            'datatable_archiving_maximum_rows_actions',
            'datatable_archiving_maximum_rows_subtable_actions',
            'archiving_ranking_query_row_limit',
        ];
        $configBackup = $this->backupGeneralConfig($configKeys);

        try {
            // phase 1: archive legacy day (hierarchical only) with subtable truncation
            $config->General['datatable_archiving_maximum_rows_actions_flat'] = 0;
            $config->General['datatable_archiving_maximum_rows_actions'] = 1;
            $config->General['datatable_archiving_maximum_rows_subtable_actions'] = 2;
            $config->General['archiving_ranking_query_row_limit'] = 1;
            $legacyDayArchive = Archive::build($idSite, 'day', '2026-01-06');
            $legacyDayHierarchical = $legacyDayArchive->getDataTable(Archiver::PAGE_URLS_RECORD_NAME);
            $legacyDaySummaryHits = $this->sumSummaryHitsRecursively($legacyDayHierarchical);

            // phase 2: archive flat day with low ranking query limit to force day-level "Others"
            $config->General['datatable_archiving_maximum_rows_actions_flat'] = 50;
            $config->General['archiving_ranking_query_row_limit'] = 1;
            $flatDayArchive = Archive::build($idSite, 'day', '2026-01-07');
            $flatDayArchive->getDataTable(Archiver::PAGE_URLS_RECORD_NAME);
            $flatDayFlat = $flatDayArchive->getDataTable(Archiver::PAGE_URLS_FLAT_RECORD_NAME);
            $flatDaySummaryHits = $this->getSummaryHits($flatDayFlat);

            // phase 3: aggregate week with higher flat limit to avoid additional week truncation
            $config->General['datatable_archiving_maximum_rows_actions_flat'] = 50;
            $config->General['archiving_ranking_query_row_limit'] = 100000;
            $archive = Archive::build($idSite, 'week', '2026-01-07');
            $weekFlat = $archive->getDataTable(Archiver::PAGE_URLS_FLAT_RECORD_NAME);
            $weekHierarchical = $archive->getDataTable(Archiver::PAGE_URLS_RECORD_NAME);
        } finally {
            $this->restoreGeneralConfig($configBackup);
        }

        $this->assertDayArchiveRecordNames(
            $idSite,
            '2026-01-06',
            [Archiver::PAGE_URLS_RECORD_NAME],
            [Archiver::PAGE_URLS_FLAT_RECORD_NAME]
        );
        $this->assertDayArchiveRecordNames(
            $idSite,
            '2026-01-07',
            [Archiver::PAGE_URLS_RECORD_NAME, Archiver::PAGE_URLS_FLAT_RECORD_NAME],
            []
        );

        $this->assertGreaterThan(0, $legacyDaySummaryHits);
        $this->assertGreaterThan(0, $flatDaySummaryHits);

        $weekSummaryRow = $weekFlat->getRowFromId(DataTable::ID_SUMMARY_ROW);
        $this->assertNotFalse($weekSummaryRow);
        $weekSummaryHits = (int) $weekSummaryRow->getColumn(Metrics::INDEX_PAGE_NB_HITS);
        $this->assertSame($legacyDaySummaryHits + $flatDaySummaryHits, $weekSummaryHits);

        $nonSummaryHits = $this->sumHitsWithoutSummary($weekFlat);
        $this->assertSame(29, $nonSummaryHits + $weekSummaryHits);

        $weekHierarchicalSummary = $weekHierarchical->getRowFromId(DataTable::ID_SUMMARY_ROW);
        $this->assertNotFalse($weekHierarchicalSummary);
        $this->assertSame($weekSummaryHits, (int) $weekHierarchicalSummary->getColumn(Metrics::INDEX_PAGE_NB_HITS));
    }

    public function testWeekAggregationParityBetweenLegacyAndFlatFirst()
    {
        $legacySiteId = Fixture::createWebsite('2026-02-01 00:00:00');
        $flatSiteId = Fixture::createWebsite('2026-02-01 00:00:00');

        $hitsByDay = [
            '2026-02-02' => [
                '/shop/shoes/nike' => 5,
                '/shop/shoes/adidas' => 3,
                '/about' => 2,
            ],
            '2026-02-03' => [
                '/shop/shoes/nike' => 4,
                '/blog/article-1' => 3,
                '/contact' => 1,
            ],
        ];

        foreach ($hitsByDay as $day => $urlHits) {
            $this->trackUrlHits($legacySiteId, $day, $urlHits);
            $this->trackUrlHits($flatSiteId, $day, $urlHits);
        }

        $config = Config::getInstance();
        $configKeys = [
            'datatable_archiving_maximum_rows_actions_flat',
            'datatable_archiving_maximum_rows_actions',
            'datatable_archiving_maximum_rows_subtable_actions',
            'archiving_ranking_query_row_limit',
        ];
        $configBackup = $this->backupGeneralConfig($configKeys);

        try {
            $config->General['datatable_archiving_maximum_rows_actions'] = 50000;
            $config->General['datatable_archiving_maximum_rows_subtable_actions'] = 50000;
            $config->General['archiving_ranking_query_row_limit'] = 100000;

            $config->General['datatable_archiving_maximum_rows_actions_flat'] = 0;
            $legacyWeekArchive = Archive::build($legacySiteId, 'week', '2026-02-03');
            $legacyWeekUrls = $legacyWeekArchive->getDataTable(Archiver::PAGE_URLS_RECORD_NAME);
            $legacyWeekTitles = $legacyWeekArchive->getDataTable(Archiver::PAGE_TITLES_RECORD_NAME);

            $config->General['datatable_archiving_maximum_rows_actions_flat'] = 50000;
            $flatWeekArchive = Archive::build($flatSiteId, 'week', '2026-02-03');
            $flatWeekUrls = $flatWeekArchive->getDataTable(Archiver::PAGE_URLS_RECORD_NAME);
            $flatWeekTitles = $flatWeekArchive->getDataTable(Archiver::PAGE_TITLES_RECORD_NAME);
        } finally {
            $this->restoreGeneralConfig($configBackup);
        }

        $this->assertSame(
            $this->exportHierarchyTableValues($legacyWeekUrls),
            $this->exportHierarchyTableValues($flatWeekUrls)
        );
        $this->assertSame(
            $this->exportHierarchyTableValues($legacyWeekTitles),
            $this->exportHierarchyTableValues($flatWeekTitles)
        );
    }

    public function testDayFlatAndHierarchyStayInParityWhenHostsDifferButPathsMatch()
    {
        $idSite = Fixture::createWebsite('2026-02-10 00:00:00');

        $tracker = Fixture::getTracker($idSite, '2026-02-10 00:00:01');
        $tracker->setUrl('http://EXAMPLE.org/shared/path#');
        Fixture::checkResponse($tracker->doTrackPageView('first'));

        $tracker->setForceVisitDateTime('2026-02-10 00:00:02');
        $tracker->setUrl('https://other.example/shared/path');
        Fixture::checkResponse($tracker->doTrackPageView('second'));

        $config = Config::getInstance();
        $configKeys = [
            'datatable_archiving_maximum_rows_actions_flat',
            'datatable_archiving_maximum_rows_actions',
            'datatable_archiving_maximum_rows_subtable_actions',
            'archiving_ranking_query_row_limit',
        ];
        $configBackup = $this->backupGeneralConfig($configKeys);

        try {
            $config->General['datatable_archiving_maximum_rows_actions_flat'] = 50;
            $config->General['datatable_archiving_maximum_rows_actions'] = 50000;
            $config->General['datatable_archiving_maximum_rows_subtable_actions'] = 50000;
            $config->General['archiving_ranking_query_row_limit'] = 100000;

            $archive = Archive::build($idSite, 'day', '2026-02-10');
            $flatUrls = $archive->getDataTable(Archiver::PAGE_URLS_FLAT_RECORD_NAME);
            $hierarchicalUrls = $archive->getDataTable(Archiver::PAGE_URLS_RECORD_NAME);
        } finally {
            $this->restoreGeneralConfig($configBackup);
        }

        $flatExport = $this->exportFlatTableValues($flatUrls);
        $hierarchicalExport = $this->exportHierarchyTableValues($hierarchicalUrls);

        $this->assertCount(1, $flatExport['rows']);
        $this->assertArrayHasKey('/shared/path', $flatExport['rows']);
        $flatUrlRow = $flatUrls->getRowFromLabel('/shared/path');
        $this->assertNotFalse($flatUrlRow);
        $this->assertNotFalse($flatUrlRow->getMetadata('url'));
        $this->assertCount(1, $hierarchicalExport['rows']);
        $hierarchicalRows = array_values($hierarchicalExport['rows']);
        $this->assertCount(1, $hierarchicalRows);
        $this->assertSame(2, $hierarchicalRows[0][Metrics::INDEX_PAGE_NB_HITS]);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function trackUrlHits(int $idSite, string $day, array $urlHits): void
    {
        $tracker = Fixture::getTracker($idSite, $day . ' 00:00:01');
        $second = 1;

        foreach ($urlHits as $path => $hits) {
            for ($i = 0; $i < $hits; ++$i) {
                $tracker->setForceVisitDateTime($day . sprintf(' 00:00:%02d', $second));
                $tracker->setUrl('http://example.org' . $path);
                Fixture::checkResponse($tracker->doTrackPageView('title ' . $path));
                ++$second;
            }
        }
    }

    private function backupGeneralConfig(array $configKeys): array
    {
        $config = Config::getInstance();
        $backup = [];
        foreach ($configKeys as $key) {
            $exists = array_key_exists($key, $config->General);
            $backup[$key] = [
                'exists' => $exists,
                'value' => $exists ? $config->General[$key] : null,
            ];
        }

        return $backup;
    }

    private function restoreGeneralConfig(array $configBackup): void
    {
        $config = Config::getInstance();
        foreach ($configBackup as $key => $state) {
            if (!empty($state['exists'])) {
                $config->General[$key] = $state['value'];
            } else {
                unset($config->General[$key]);
            }
        }
    }

    private function assertDayArchiveRecordNames(int $idSite, string $day, array $expectedPresent, array $expectedAbsent): void
    {
        $table = Common::prefixTable('archive_blob_2026_01');
        $names = Db::fetchAll(
            "SELECT DISTINCT name
             FROM $table
             WHERE idsite = ?
               AND period = 1
               AND date1 = ?
               AND date2 = ?
               AND name IN (?, ?)",
            [$idSite, $day, $day, Archiver::PAGE_URLS_RECORD_NAME, Archiver::PAGE_URLS_FLAT_RECORD_NAME]
        );
        $names = array_column($names, 'name');

        foreach ($expectedPresent as $name) {
            $this->assertContains($name, $names);
        }
        foreach ($expectedAbsent as $name) {
            $this->assertNotContains($name, $names);
        }
    }

    private function getSummaryHits(DataTable $table): int
    {
        $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
        if ($summaryRow === false) {
            return 0;
        }

        return (int) $summaryRow->getColumn(Metrics::INDEX_PAGE_NB_HITS);
    }

    private function sumSummaryHitsRecursively(DataTable $table): int
    {
        $sum = $this->getSummaryHits($table);
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            /** @var Row $row */
            $subtable = $row->getSubtable();
            if ($subtable instanceof DataTable) {
                $sum += $this->sumSummaryHitsRecursively($subtable);
            }
        }

        return $sum;
    }

    private function sumHitsWithoutSummary(DataTable $table): int
    {
        $sum = 0;
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            /** @var Row $row */
            $sum += (int) $row->getColumn(Metrics::INDEX_PAGE_NB_HITS);
        }

        return $sum;
    }

    private function exportHierarchyTableValues(DataTable $hierarchicalTable): array
    {
        $flattened = new DataTable();
        ArchivingHelper::mergeHierarchicalActionsTableIntoFlatTable($hierarchicalTable, $flattened);

        return $this->exportFlatTableValues($flattened);
    }

    private function exportFlatTableValues(DataTable $flatTable): array
    {
        $rows = [];
        foreach ($flatTable->getRowsWithoutSummaryRow() as $row) {
            $label = $row->getColumn('label');
            if (!is_string($label)) {
                continue;
            }

            $columns = $row->getColumns();
            unset($columns['label']);
            ksort($columns);
            $rows[$label] = $columns;
        }
        ksort($rows);

        $summaryColumns = [];
        $summaryRow = $flatTable->getRowFromId(DataTable::ID_SUMMARY_ROW);
        if ($summaryRow !== false) {
            $summaryColumns = $summaryRow->getColumns();
            unset($summaryColumns['label']);
            ksort($summaryColumns);
        }

        return [
            'rows' => $rows,
            'summary' => $summaryColumns,
        ];
    }
}
