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
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Row;
use Piwik\Plugin\Metric;
use Piwik\Plugin\Report;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\PrivacyManager\Settings\DataRoundingEnabled;
use Piwik\Request;
use Piwik\Site;
use Throwable;

class DataRounding
{
    private const EXCLUDED_COLUMN_NAMES = ['label'];

    private const IDENTIFIER_COLUMN_NAMES = ['idsite', 'idgoal', 'idsubdatatable'];

    private const EXCLUDED_SEMANTIC_TYPES = [
        Dimension::TYPE_PERCENT,
        Dimension::TYPE_DURATION_MS,
        Dimension::TYPE_DURATION_S,
        Dimension::TYPE_MONEY,
        Dimension::TYPE_FLOAT,
        Dimension::TYPE_TIME,
        Dimension::TYPE_DATE,
        Dimension::TYPE_DATETIME,
        Dimension::TYPE_TIMESTAMP,
        Dimension::TYPE_URL,
        Dimension::TYPE_TEXT,
        Dimension::TYPE_ENUM,
        Dimension::TYPE_BOOL,
        Dimension::TYPE_BINARY,
        Dimension::TYPE_BYTE,
        Dimension::TYPE_DIMENSION,
    ];

    private const CHANGE_COLUMN_PATTERN = '/_change$/i';

    private const EXCLUDED_BY_NAME_PATTERN = '/(rate|percent|percentage|revenue|price|cost|tax|shipping|discount|avg_|average|time|duration|evolution|min_|max_)/';

    private const INCLUDED_COUNT_BY_NAME_PATTERN = '/(^nb_|_nb_|_count$|^count_|^sum_daily_nb_|^hits$|^visits$|^actions$|^conversions$|^users$|^goals$|^orders$|^items$|^quantity$|^impressions$|^interactions$|^downloads$|^outlinks$|^bounce_count$|^entry_nb_|^exit_nb_)/';

    private const IDENTIFIER_BY_NAME_PATTERN = '/(^id_|_id$)/';

    public static function shouldApplyForRequest(array $request): bool
    {
        if (!self::requestHasNonEmptySegment($request)) {
            return false;
        }

        return self::isDataRoundingEnabledForSite(self::extractSingleSiteId($request));
    }

    public static function roundCountMetrics(DataTableInterface $dataTable, ?Report $report = null): void
    {
        $dataTable->filter(function (DataTable $table) use ($report) {
            self::roundDataTable($table, $report);
        });
    }

    private static function roundDataTable(DataTable $table, ?Report $report = null): void
    {
        $metricTypes = self::getMetricTypes($table, $report);
        $columnsToRound = self::collectColumnsToRound($table, $metricTypes);

        if (!empty($columnsToRound)) {
            foreach ($table->getRows() as $row) {
                self::roundRowColumns($row, $columnsToRound);
                self::roundRowComparisons($row, $report);
            }
        }

        self::roundTotalsMetadataIfPresent($table, $metricTypes);
    }

    /**
     * @param string[] $columnsToRound
     */
    private static function roundRowColumns(Row $row, array $columnsToRound): void
    {
        foreach ($columnsToRound as $columnName) {
            $value = $row->getColumn($columnName);
            if (!self::shouldRoundValue($value)) {
                continue;
            }

            $row->setColumn($columnName, self::roundToNearestTen((float) $value));
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
        if (!is_array($totals)) {
            return;
        }

        $table->setMetadata('totals', self::roundTotals($totals, $metricTypes));
    }

    /**
     * @return array<string, string|null>
     */
    private static function getMetricTypes(DataTable $table, ?Report $report = null): array
    {
        $metricTypes = [];

        if (!empty($report)) {
            $metricTypes = $report->getMetricSemanticTypes();
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
        $firstRow = $table->getFirstRow();
        if (empty($firstRow)) {
            return [];
        }

        $columns = [];
        foreach ($firstRow->getColumns() as $columnName => $value) {
            if (!self::shouldRoundColumn((string) $columnName, $metricTypes[(string) $columnName] ?? null)) {
                continue;
            }

            if (self::shouldRoundValue($value)) {
                $columns[] = (string) $columnName;
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
        return self::roundArrayValuesRecursive($totals, $metricTypes);
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string|null> $metricTypes
     * @return array<string, mixed>
     */
    public static function roundCountArrayValues(array $values, array $metricTypes = []): array
    {
        return self::roundArrayValuesRecursive($values, $metricTypes);
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string|null> $metricTypes
     * @return array<string, mixed>
     */
    private static function roundArrayValuesRecursive(array $values, array $metricTypes): array
    {
        foreach ($values as $columnName => $value) {
            if (is_array($value)) {
                $values[$columnName] = self::roundArrayValuesRecursive($value, $metricTypes);
                continue;
            }

            if (
                self::shouldRoundColumn((string) $columnName, $metricTypes[(string) $columnName] ?? null)
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

    private static function roundToNearestTen(float $value): int
    {
        if ($value === 0.0) {
            return 0;
        }

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

        if (!empty($semanticType)) {
            if ($semanticType === Dimension::TYPE_NUMBER) {
                return true;
            }

            if (in_array($semanticType, self::EXCLUDED_SEMANTIC_TYPES, true)) {
                return false;
            }
        }

        $columnName = strtolower($columnName);

        if (preg_match(self::EXCLUDED_BY_NAME_PATTERN, $columnName)) {
            return false;
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

    private static function extractSingleSiteId(array $request): ?int
    {
        try {
            $requestObject = new Request($request);
            $idSite = $requestObject->getParameter('idSite', null);
            if (is_null($idSite)) {
                $idSite = $requestObject->getParameter('idsite', null);
            }

            if (!is_scalar($idSite) || trim((string) $idSite) === '') {
                return null;
            }

            $idSites = Site::getIdSitesFromIdSitesString((string) $idSite, false, false);
            if (count($idSites) !== 1) {
                return null;
            }

            return (int) reset($idSites);
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function isDataRoundingEnabledForSite(?int $idSite): bool
    {
        try {
            $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
            if (!$featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
                return false;
            }

            return DataRoundingEnabled::getInstance($idSite)->getValue() === true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
