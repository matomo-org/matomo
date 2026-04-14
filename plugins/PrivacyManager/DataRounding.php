<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Plugins\PrivacyManager\Settings\DataRoundingEnabled;
use Piwik\Request;
use Piwik\Site;
use Throwable;

class DataRounding
{
    private const EXCLUDED_COLUMN_NAMES = ['label'];

    private const IDENTIFIER_COLUMN_NAMES = ['idsite', 'idgoal', 'idsubdatatable'];

    private const CHANGE_COLUMN_PATTERN = '/_change$/i';

    private const EXCLUDED_BY_NAME_PATTERN = '/(rate|percent|percentage|evolution|duration|visit_length|bandwidth|byte)/';

    private const EXCLUDED_SPECIFIC_MAX_METRIC_PATTERN =
        '/^(max_actions(?:_(?:returning|new|ai_agent|human))?|max_time_(?:network|server|transfer|dom_processing|dom_completion|on_load|generation)|max_bandwidth)$/';

    private const INCLUDED_COUNT_BY_NAME_PATTERN = '/(^nb_|_nb_|_count$|^count_|^items$|^orders$|^quantity$)/';

    private const IDENTIFIER_BY_NAME_PATTERN = '/(^id_|_id$)/';

    public static function shouldApplyForRequest(array $request): bool
    {
        if (!self::requestHasNonEmptySegment($request)) {
            return false;
        }

        return self::isDataRoundingEnabledForAnyRequestedSite(self::extractRequestedSiteIds($request));
    }

    /**
     * @param int[] $siteIds
     */
    public static function isDataRoundingEnabledForAnySites(array $siteIds): bool
    {
        return self::isDataRoundingEnabledForAnyRequestedSite($siteIds);
    }

    public static function roundCountMetricsForRequest(
        DataTableInterface $dataTable,
        array $request,
        ?Report $report = null
    ): void {
        if (!self::requestHasNonEmptySegment($request)) {
            return;
        }

        $requestedSiteIds = self::extractRequestedSiteIds($request);
        if (empty($requestedSiteIds)) {
            return;
        }

        self::roundCountMetricsForRequestedSites(
            $dataTable,
            $requestedSiteIds,
            $report,
            null,
            [self::class, 'isDataRoundingEnabledForSite']
        );
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public static function roundCountArrayValuesForRequest(array $values, array $request): array
    {
        if (!self::shouldApplyForRequest($request)) {
            return $values;
        }

        $requestedSiteIds = self::extractRequestedSiteIds($request);
        if (empty($requestedSiteIds)) {
            return $values;
        }

        return self::roundArrayValuesForRequestedSites($values, $requestedSiteIds);
    }

    public static function roundCountMetrics(DataTableInterface $dataTable, ?Report $report = null): void
    {
        $roundTable = function (DataTable $table) use ($report) {
            self::roundDataTable($table, $report);
        };

        $dataTable->filter($roundTable);
        if ($dataTable instanceof DataTable || $dataTable instanceof DataTable\Map) {
            $dataTable->filterSubtables($roundTable);
        }
    }

    private static function roundDataTable(DataTable $table, ?Report $report = null): void
    {
        $metricTypes = self::getMetricTypes($table, $report);
        $columnsToRound = self::collectColumnsToRound($table, $metricTypes);

        if (!empty($columnsToRound)) {
            foreach ($table->getRows() as $row) {
                self::roundRowColumns($row, $columnsToRound, $metricTypes, $report);
                self::roundRowComparisons($row, $report);
            }
        }

        self::roundTotalsRowIfPresent($table, $columnsToRound, $metricTypes, $report);
        self::roundTotalsMetadataIfPresent($table, $metricTypes);
        self::reconcileTotalsFromRoundedRowsForConstantRowsReport($table, $columnsToRound, $report);
        self::clearStaleRatioMetadata($table, $columnsToRound);
        self::recomputeProcessedPercentMetrics($table, $report);
    }

    /**
     * @param string[] $columnsToRound
     * @param array<string, string|null> $metricTypes
     */
    private static function roundTotalsRowIfPresent(
        DataTable $table,
        array $columnsToRound,
        array $metricTypes,
        ?Report $report
    ): void {
        $totalsRow = $table->getTotalsRow();
        if (empty($totalsRow)) {
            return;
        }

        self::roundRowColumns($totalsRow, $columnsToRound, $metricTypes, $report);
        self::roundRowComparisons($totalsRow, $report);
    }

    /**
     * @param string[] $columnsToRound
     * @param array<string, string|null> $metricTypes
     */
    private static function roundRowColumns(Row $row, array $columnsToRound, array $metricTypes, ?Report $report): void
    {
        foreach ($columnsToRound as $columnName) {
            $rawColumnName = self::getRawMetricColumnName($row, $columnName);
            $value = $row->getColumn($rawColumnName);
            if (!self::shouldRoundValue($value)) {
                continue;
            }

            $row->setColumn($rawColumnName, self::roundToNearestTen((float) $value));
        }

        foreach ($row->getColumns() as $columnName => $value) {
            if (is_array($value)) {
                if (self::isGoalsContainerColumn((string) $columnName)) {
                    $row->setColumn((string) $columnName, self::roundGoalRowsArray($value, $metricTypes));
                } else {
                    $row->setColumn((string) $columnName, self::roundCountArrayValues($value, $metricTypes));
                }
                continue;
            }

            if ($value instanceof DataTableInterface) {
                self::roundCountMetrics($value, $report);
            }
        }
    }

    private static function roundRowComparisons(Row $row, ?Report $report): void
    {
        $comparisons = $row->getComparisons();
        if (empty($comparisons)) {
            return;
        }

        self::roundDataTable($comparisons, $report);
    }

    /**
     * @param array<string, string|null> $metricTypes
     */
    private static function roundTotalsMetadataIfPresent(DataTable $table, array $metricTypes): void
    {
        $totals = $table->getMetadata('totals');
        if (is_array($totals)) {
            $table->setMetadata('totals', self::roundTotals($totals, $metricTypes));
        }

        $totalsUnformatted = $table->getMetadata('totalsUnformatted');
        if (is_array($totalsUnformatted)) {
            $table->setMetadata('totalsUnformatted', self::roundTotals($totalsUnformatted, $metricTypes));
        }
    }

    /**
     * @param string[] $columnsToRound
     */
    private static function clearStaleRatioMetadata(DataTable $table, array $columnsToRound): void
    {
        if (empty($columnsToRound)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            foreach ($columnsToRound as $columnName) {
                self::clearRatioMetadata($row, $columnName);
            }
        }
    }

    private static function clearRatioMetadata(Row $row, string $columnName): void
    {
        foreach (['_row_percentage', '_site_total_percentage'] as $suffix) {
            $metadataName = $columnName . $suffix;
            if ($row->getMetadata($metadataName) !== false) {
                $row->deleteMetadata($metadataName);
            }
        }
    }

    private static function recomputeProcessedPercentMetrics(DataTable $table, ?Report $report): void
    {
        $processedMetrics = Report::getProcessedMetricsForTable($table, $report);
        if (empty($processedMetrics)) {
            return;
        }

        foreach ($processedMetrics as $metricName => $processedMetric) {
            if (!self::shouldRecomputeProcessedPercentMetric($processedMetric)) {
                continue;
            }

            if (!$processedMetric->beforeCompute($report, $table)) {
                continue;
            }

            foreach ($table->getRows() as $row) {
                self::recomputeProcessedMetricForRow($row, $metricName, $processedMetric);
            }

            $totalsRow = $table->getTotalsRow();
            if (!empty($totalsRow)) {
                self::recomputeProcessedMetricForRow($totalsRow, $metricName, $processedMetric);
            }
        }
    }

    private static function shouldRecomputeProcessedPercentMetric(ProcessedMetric $processedMetric): bool
    {
        return $processedMetric->getSemanticType() === Dimension::TYPE_PERCENT;
    }

    private static function recomputeProcessedMetricForRow(
        Row $row,
        string $metricName,
        ProcessedMetric $processedMetric
    ): void {
        if (!$row->hasColumn($metricName)) {
            return;
        }

        $computedValue = $processedMetric->compute($row);
        if ($computedValue !== false) {
            $row->setColumn($metricName, $computedValue);
        }
    }

    /**
     * @param string[] $columnsToRound
     */
    private static function reconcileTotalsFromRoundedRowsForConstantRowsReport(
        DataTable $table,
        array $columnsToRound,
        ?Report $report
    ): void {
        if (empty($report) || !self::isConstantRowsCountReport($report) || empty($columnsToRound)) {
            return;
        }

        $totals = $table->getMetadata('totals');
        if (!is_array($totals)) {
            $totals = [];
        }

        $totalsUnformatted = $table->getMetadata('totalsUnformatted');
        if (!is_array($totalsUnformatted)) {
            $totalsUnformatted = [];
        }

        $totalsRow = $table->getTotalsRow();

        foreach ($columnsToRound as $columnName) {
            $sum = 0;
            foreach ($table->getRows() as $row) {
                $value = $row->getColumn($columnName);
                if (is_numeric($value) && $value >= 0) {
                    $sum += (int) $value;
                }
            }

            $totals[$columnName] = $sum;
            $totalsUnformatted[$columnName] = $sum;
            if (!empty($totalsRow)) {
                $totalsRow->setColumn($columnName, $sum);
            }
        }

        $table->setMetadata('totals', $totals);
        $table->setMetadata('totalsUnformatted', $totalsUnformatted);
    }

    private static function isConstantRowsCountReport(Report $report): bool
    {
        return $report->hasConstantRowsCount();
    }

    /**
     * @return array<string, string|null>
     */
    private static function getMetricTypes(DataTable $table, ?Report $report = null): array
    {
        if (!empty($report)) {
            $metricTypes = $report->getMetricSemanticTypes();
        } else {
            $metricTypes = Metrics::getDefaultMetricSemanticTypes();
        }

        $metrics = Report::getMetricsForTable($table, $report, Metric::class);
        foreach ($metrics as $metric) {
            $name = $metric->getName();
            $metricTypes[$name] = $metric->getSemanticType() ?: ($metricTypes[$name] ?? null);
        }

        return $metricTypes;
    }

    /**
     * @param array<string, string|null> $metricTypes
     * @return string[]
     */
    private static function collectColumnsToRound(DataTable $table, array $metricTypes): array
    {
        $sampleValuesByColumn = [];

        foreach ($table->getRows() as $row) {
            foreach ($row->getColumns() as $columnName => $value) {
                $columnName = (string) $columnName;
                if (
                    !array_key_exists($columnName, $sampleValuesByColumn)
                    || !self::shouldRoundValue($sampleValuesByColumn[$columnName])
                ) {
                    $sampleValuesByColumn[$columnName] = $value;
                }
            }
        }

        $totalsRow = $table->getTotalsRow();
        if (!empty($totalsRow)) {
            foreach ($totalsRow->getColumns() as $columnName => $value) {
                $columnName = (string) $columnName;
                if (
                    !array_key_exists($columnName, $sampleValuesByColumn)
                    || !self::shouldRoundValue($sampleValuesByColumn[$columnName])
                ) {
                    $sampleValuesByColumn[$columnName] = $value;
                }
            }
        }

        if (empty($sampleValuesByColumn)) {
            return [];
        }

        $columns = [];
        foreach ($sampleValuesByColumn as $columnName => $value) {
            $columnName = (string) $columnName;
            $normalizedColumnName = self::normalizeRawMetricColumnName($columnName);

            if (!self::shouldRoundColumn($normalizedColumnName, $metricTypes[$normalizedColumnName] ?? null)) {
                continue;
            }

            if (self::shouldRoundValue($value)) {
                $columns[] = $normalizedColumnName;
            }
        }

        return $columns;
    }

    /**
     * @param array<string, mixed> $totals
     * @param array<string, string|null> $metricTypes
     * @return array<string, mixed>
     */
    private static function roundTotals(array $totals, array $metricTypes): array
    {
        return self::roundArrayValuesRecursive($totals, $metricTypes, true);
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string|null> $metricTypes
     * @return array<string, mixed>
     */
    public static function roundCountArrayValues(array $values, array $metricTypes = []): array
    {
        return self::roundArrayValuesRecursive($values, $metricTypes, true);
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string|null> $metricTypes
     * @return array<string, mixed>
     */
    private static function roundArrayValuesRecursive(
        array $values,
        array $metricTypes,
        bool $allowSemanticTypes,
        ?string $parentColumnName = null
    ): array {
        foreach ($values as $columnName => $value) {
            $columnName = (string) $columnName;

            if (is_array($value)) {
                if (self::isGoalsContainerColumn($columnName)) {
                    $values[$columnName] = self::roundGoalRowsArray($value, $metricTypes);
                } else {
                    $values[$columnName] = self::roundArrayValuesRecursive($value, $metricTypes, false, $columnName);
                }
                continue;
            }

            $semanticType = $allowSemanticTypes ? ($metricTypes[$columnName] ?? null) : null;
            if (self::isGoalsContainerColumn($parentColumnName)) {
                $metricName = self::getGoalMetricNameFromRawColumn($columnName) ?? $columnName;
                $semanticType = null;
            } else {
                $metricName = self::normalizeRawMetricColumnName($columnName);
                if ($metricName !== $columnName) {
                    $semanticType = $allowSemanticTypes ? ($metricTypes[$metricName] ?? null) : null;
                }
            }

            if (
                self::shouldRoundColumn($metricName, $semanticType)
                && self::shouldRoundValue($value)
            ) {
                $values[$columnName] = self::roundToNearestTen((float) $value);
            }
        }

        return $values;
    }

    private static function shouldRoundValue($value): bool
    {
        return is_numeric($value) && $value >= 0;
    }

    /**
     * @param array<string, mixed> $goalRows
     * @param array<string, string|null> $metricTypes
     * @return array<string, mixed>
     */
    private static function roundGoalRowsArray(array $goalRows, array $metricTypes): array
    {
        foreach ($goalRows as $goalId => $goalValues) {
            if (!is_array($goalValues)) {
                continue;
            }

            $goalRows[$goalId] = self::roundArrayValuesRecursive($goalValues, $metricTypes, false, 'goals');
        }

        return $goalRows;
    }

    private static function isGoalsContainerColumn(?string $columnName): bool
    {
        if ($columnName === null) {
            return false;
        }

        return $columnName === 'goals' || $columnName === (string) Metrics::INDEX_GOALS;
    }

    private static function getGoalMetricNameFromRawColumn(string $columnName): ?string
    {
        if (!is_numeric($columnName)) {
            return null;
        }

        $columnId = (int) $columnName;
        return Metrics::$mappingFromIdToNameGoal[$columnId] ?? null;
    }

    private static function normalizeRawMetricColumnName(string $columnName): string
    {
        if (!is_numeric($columnName)) {
            return $columnName;
        }

        $columnId = (int) $columnName;
        return Metrics::$mappingFromIdToName[$columnId] ?? $columnName;
    }

    /**
     * @return string|int
     */
    private static function getRawMetricColumnName(Row $row, string $columnName)
    {
        if ($row->hasColumn($columnName)) {
            return $columnName;
        }

        $mapping = Metrics::getMappingFromNameToId();
        $rawColumnId = $mapping[$columnName] ?? null;
        if ($rawColumnId !== null) {
            if ($row->hasColumn($rawColumnId)) {
                return $rawColumnId;
            }

            $rawColumnName = (string) $rawColumnId;
            if ($row->hasColumn($rawColumnName)) {
                return $rawColumnName;
            }
        }

        return $columnName;
    }

    private static function roundToNearestTen(float $value): int
    {
        if ($value === 0.0) {
            return 0;
        }

        // Keep any non-zero count at a minimum of 10 so segmented responses do not expose near-single-user volumes.
        return max(10, (int) (floor(($value + 5) / 10) * 10));
    }

    private static function shouldRoundColumn(string $columnName, ?string $semanticType): bool
    {
        if (
            $columnName === ''
            || in_array($columnName, self::EXCLUDED_COLUMN_NAMES, true)
            || self::isIdentifierColumn($columnName)
            || preg_match(self::CHANGE_COLUMN_PATTERN, $columnName)
        ) {
            return false;
        }

        $columnName = strtolower($columnName);
        if (preg_match(self::EXCLUDED_BY_NAME_PATTERN, $columnName)) {
            return false;
        }

        if (preg_match(self::EXCLUDED_SPECIFIC_MAX_METRIC_PATTERN, $columnName)) {
            return false;
        }

        if (!empty($semanticType)) {
            return $semanticType === Dimension::TYPE_NUMBER;
        }

        return (bool) preg_match(self::INCLUDED_COUNT_BY_NAME_PATTERN, $columnName);
    }

    private static function isIdentifierColumn(string $columnName): bool
    {
        $columnName = strtolower($columnName);
        if (in_array($columnName, self::IDENTIFIER_COLUMN_NAMES, true)) {
            return true;
        }

        return (bool) preg_match(self::IDENTIFIER_BY_NAME_PATTERN, $columnName);
    }

    private static function requestHasNonEmptySegment(array $request): bool
    {
        try {
            $segment = (new Request($request))->getStringParameter('segment', '');
            return trim($segment) !== '';
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @return int[]
     */
    private static function extractRequestedSiteIds(array $request): array
    {
        try {
            $requestObject = new Request($request);
            $idSite = $requestObject->getParameter('idSite', null);
            if (is_null($idSite)) {
                $idSite = $requestObject->getParameter('idsite', null);
            }

            if (!is_scalar($idSite) || trim((string) $idSite) === '') {
                return [];
            }

            return Site::getIdSitesFromIdSitesString((string) $idSite, false, false);
        } catch (Throwable $e) {
            return [];
        }
    }

    private static function isDataRoundingEnabledForSite(?int $idSite): bool
    {
        try {
            return DataRoundingEnabled::getInstance($idSite)->getValue() === true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param int[] $requestedSiteIds
     */
    private static function isDataRoundingEnabledForAnyRequestedSite(array $requestedSiteIds): bool
    {
        return self::isAnyRequestedSiteEnabled($requestedSiteIds, [self::class, 'isDataRoundingEnabledForSite']);
    }

    /**
     * @param int[] $requestedSiteIds
     */
    private static function roundCountMetricsForRequestedSites(
        DataTableInterface $dataTable,
        array $requestedSiteIds,
        ?Report $report,
        ?int $currentSiteId = null,
        ?callable $isSiteEnabled = null
    ): void {
        $isSiteEnabled = $isSiteEnabled ?: [self::class, 'isDataRoundingEnabledForSite'];

        if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $key => $childTable) {
                self::roundCountMetricsForRequestedSites(
                    $childTable,
                    $requestedSiteIds,
                    $report,
                    self::resolveCurrentSiteIdFromMapKey($dataTable, $key, $currentSiteId),
                    $isSiteEnabled
                );
            }

            return;
        }

        if ($currentSiteId !== null) {
            if (!(bool) call_user_func($isSiteEnabled, $currentSiteId)) {
                return;
            }
        } elseif (!self::isAnyRequestedSiteEnabled($requestedSiteIds, $isSiteEnabled)) {
            return;
        }

        self::roundDataTableForRequestedSites($dataTable, $requestedSiteIds, $report, $currentSiteId, $isSiteEnabled);
    }

    /**
     * @param int[] $requestedSiteIds
     */
    private static function isAnyRequestedSiteEnabled(array $requestedSiteIds, callable $isSiteEnabled): bool
    {
        foreach ($requestedSiteIds as $siteId) {
            if ((bool) call_user_func($isSiteEnabled, (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $values
     * @param int[] $requestedSiteIds
     * @return array<string, mixed>
     */
    private static function roundArrayValuesForRequestedSites(
        array $values,
        array $requestedSiteIds,
        ?int $currentSiteId = null,
        ?callable $isSiteEnabled = null
    ): array {
        $isSiteEnabled = $isSiteEnabled ?: [self::class, 'isDataRoundingEnabledForSite'];

        $rowSiteId = self::extractSiteIdFromArrayRow($values);
        if ($rowSiteId !== null) {
            $currentSiteId = $rowSiteId;
        }

        if (!self::containsNestedArrays($values)) {
            if ($currentSiteId !== null && !(bool) call_user_func($isSiteEnabled, $currentSiteId)) {
                return $values;
            }

            if ($currentSiteId === null && !self::isAnyRequestedSiteEnabled($requestedSiteIds, $isSiteEnabled)) {
                return $values;
            }

            return self::roundCountArrayValues($values);
        }

        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $values[$key] = self::roundArrayValuesForRequestedSites(
                $value,
                $requestedSiteIds,
                $currentSiteId,
                $isSiteEnabled
            );
        }

        if ($currentSiteId !== null) {
            if (!(bool) call_user_func($isSiteEnabled, $currentSiteId)) {
                return $values;
            }
        } elseif (!self::isAnyRequestedSiteEnabled($requestedSiteIds, $isSiteEnabled)) {
            return $values;
        }

        return self::roundDirectScalarArrayValues($values);
    }

    /**
     * @param int[] $requestedSiteIds
     */
    private static function roundDataTableForRequestedSites(
        DataTableInterface $dataTable,
        array $requestedSiteIds,
        ?Report $report,
        ?int $currentSiteId,
        callable $isSiteEnabled
    ): void {
        if ($dataTable instanceof DataTable\Map) {
            self::roundCountMetrics($dataTable, $report);
            return;
        }

        if (!$dataTable instanceof DataTable) {
            return;
        }

        $metricTypes = self::getMetricTypes($dataTable, $report);
        $columnsToRound = self::collectColumnsToRound($dataTable, $metricTypes);
        $shouldRoundFallbackRows = self::isAnyRequestedSiteEnabled($requestedSiteIds, $isSiteEnabled);

        if (!empty($columnsToRound)) {
            foreach ($dataTable->getRows() as $row) {
                $rowSiteId = $currentSiteId ?? self::extractSiteIdFromRow($row);
                $shouldRoundRow = $rowSiteId !== null
                    ? (bool) call_user_func($isSiteEnabled, $rowSiteId)
                    : $shouldRoundFallbackRows;

                if (!$shouldRoundRow) {
                    $subtable = $row->getSubtable();
                    if (!empty($subtable)) {
                        self::roundDataTableForRequestedSites($subtable, $requestedSiteIds, $report, $rowSiteId, $isSiteEnabled);
                    }
                    continue;
                }

                self::roundRowColumns($row, $columnsToRound, $metricTypes, $report);
                self::roundRowComparisons($row, $report);

                $subtable = $row->getSubtable();
                if (!empty($subtable)) {
                    self::roundDataTableForRequestedSites($subtable, $requestedSiteIds, $report, $rowSiteId, $isSiteEnabled);
                }
            }
        }

        if ($shouldRoundFallbackRows) {
            self::roundTotalsRowIfPresent($dataTable, $columnsToRound, $metricTypes, $report);
            self::roundTotalsMetadataIfPresent($dataTable, $metricTypes);
            self::reconcileTotalsFromRoundedRowsForConstantRowsReport($dataTable, $columnsToRound, $report);
            self::clearStaleRatioMetadata($dataTable, $columnsToRound);
            self::recomputeProcessedPercentMetrics($dataTable, $report);
        }
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function containsNestedArrays(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function extractSiteIdFromArrayRow(array $values): ?int
    {
        foreach (['idsite', 'idSite'] as $siteColumn) {
            if (isset($values[$siteColumn]) && is_numeric($values[$siteColumn])) {
                return (int) $values[$siteColumn];
            }
        }

        return null;
    }

    private static function extractSiteIdFromRow(Row $row): ?int
    {
        foreach (['idsite', 'idSite'] as $siteColumn) {
            $siteId = $row->getColumn($siteColumn);
            if (is_numeric($siteId)) {
                return (int) $siteId;
            }

            $siteId = $row->getMetadata($siteColumn);
            if (is_numeric($siteId)) {
                return (int) $siteId;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private static function roundDirectScalarArrayValues(array $values): array
    {
        foreach ($values as $columnName => $value) {
            if (is_array($value)) {
                continue;
            }

            $metricName = self::normalizeRawMetricColumnName((string) $columnName);
            if (
                self::shouldRoundColumn($metricName, null)
                && self::shouldRoundValue($value)
            ) {
                $values[$columnName] = self::roundToNearestTen((float) $value);
            }
        }

        return $values;
    }

    /**
     * @param string|int $key
     */
    private static function resolveCurrentSiteIdFromMapKey(DataTable\Map $dataTableMap, $key, ?int $currentSiteId): ?int
    {
        if ($currentSiteId !== null) {
            return $currentSiteId;
        }

        $keyName = strtolower((string) $dataTableMap->getKeyName());
        if ($keyName !== 'site' && $keyName !== 'idsite') {
            return null;
        }

        if (is_numeric($key) && (string) (int) $key === (string) $key && (int) $key > 0) {
            return (int) $key;
        }

        return null;
    }
}
