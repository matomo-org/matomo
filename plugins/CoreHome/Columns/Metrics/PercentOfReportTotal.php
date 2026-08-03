<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * Percent of the report total for a metric. Calculated as:
 *
 *     metric value / report total for the metric
 *
 * The report total is the value calculated by ReportTotalsCalculator and stored in the
 * 'totalsUnformatted' metadata of the DataTable. It spans the whole first level report,
 * not just the rows returned for the current page.
 */
class PercentOfReportTotal extends ProcessedMetric
{
    public const COLUMN_NAME_SUFFIX = '_percent_of_total';

    /**
     * @var string
     */
    private $metricName;

    /**
     * @var string
     */
    private $metricTranslation;

    /**
     * @var int|float
     */
    private $total;

    /**
     * All metric ids the metric name maps to. Metrics::getMappingFromNameToId() cannot be used
     * here since some names map to multiple ids (eg 'revenue' maps to INDEX_REVENUE and
     * INDEX_ECOMMERCE_ITEM_REVENUE) and the flipped mapping only keeps one of them.
     *
     * @var int[]
     */
    private $metricIds;

    /**
     * @param string $metricName The name of the metric the percentage is calculated for, eg 'nb_visits'.
     * @param string $metricTranslation The translated name of the metric, eg 'Visits'.
     * @param int|float $total The report total for the metric.
     */
    public function __construct(string $metricName, string $metricTranslation, $total)
    {
        $this->metricName = $metricName;
        $this->metricTranslation = $metricTranslation;
        $this->total = $total;
        $this->metricIds = array_keys(Metrics::$mappingFromIdToName, $metricName, true);
    }

    public function getName()
    {
        return $this->metricName . self::COLUMN_NAME_SUFFIX;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnPercentOfReportTotal', $this->metricTranslation);
    }

    public function getDocumentation()
    {
        return Piwik::translate('General_ColumnPercentOfReportTotalDocumentation', $this->metricTranslation);
    }

    public function compute(Row $row)
    {
        $value = $row->getColumn($this->metricName);

        foreach ($this->metricIds as $metricId) {
            if (is_numeric($value)) {
                break;
            }

            $value = $row->getColumn($metricId);
        }

        if (!is_numeric($value)) {
            return false;
        }

        return Piwik::getQuotientSafe($value, $this->total, $precision = 3);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function getDependentMetrics()
    {
        return [$this->metricName];
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }

    /**
     * Registers percent-of-total metrics on the table for every metric that has a report total
     * and a meaningful percentage (the metrics reports process totals for, ie the ones the
     * HTML table visualization shows ratio percentages for).
     *
     * @param DataTable $table Table with 'totalsUnformatted' metadata set by ReportTotalsCalculator.
     */
    public static function addMetricsToTable(DataTable $table, ?Report $report): void
    {
        $totals = $table->getMetadata('totalsUnformatted');
        if (empty($totals) || !is_array($totals)) {
            return;
        }

        $eligibleNames = [];
        foreach (Metrics::getMetricIdsToProcessReportTotal() as $metricId) {
            $eligibleNames[] = Metrics::getReadableColumnName($metricId);
        }

        $translations = Metrics::getDefaultMetricTranslations();
        if ($report) {
            foreach ($report->getMetricNamesToProcessReportTotals() as $metricName) {
                $eligibleNames[] = $metricName;
            }

            $translations = array_merge($translations, $report->getMetrics() ?: []);
        }

        $metrics = [];
        foreach (array_unique($eligibleNames) as $metricName) {
            // totals are keyed by metric id instead of name when queued filters are disabled
            // for the request (eg, the requests DataComparisonFilter uses to fetch compared series)
            $totalKeys = array_merge([$metricName], array_keys(Metrics::$mappingFromIdToName, $metricName, true));
            foreach ($totalKeys as $totalKey) {
                if (isset($totals[$totalKey]) && is_numeric($totals[$totalKey])) {
                    $metrics[] = new self($metricName, $translations[$metricName] ?? $metricName, $totals[$totalKey]);
                    break;
                }
            }
        }

        if (!empty($metrics)) {
            self::addMetricsToTableRecursively($table, $metrics);
        }
    }

    /**
     * Subtable rows get the same metric instances as the first level table: their percentages
     * are relative to the report total, like the ratios the HTML table visualization shows
     * when a subtable is opened.
     *
     * @param self[] $metrics
     */
    private static function addMetricsToTableRecursively(DataTable $table, array $metrics): void
    {
        $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];

        foreach ($extraProcessedMetrics as $metric) {
            if ($metric instanceof self) {
                return; // already added
            }
        }

        $table->setMetadata(
            DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME,
            array_merge($extraProcessedMetrics, $metrics)
        );

        foreach ($table->getRows() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                self::addMetricsToTableRecursively($subtable, $metrics);
            }
        }
    }
}
