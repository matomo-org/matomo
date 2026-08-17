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
     * Suffix of the columns reports use for a percentage they compute themselves, see
     * VisitsPercent.
     */
    private const OWN_PERCENTAGE_COLUMN_SUFFIX = '_percentage';

    /**
     * Unique visitor / user counts cannot be summed over rows, so the report total computed
     * for them by ReportTotalsCalculator (a plain sum) is not a meaningful denominator: the
     * percentages could exceed 100% and would differ depending on which archived record a
     * report is served from (eg the flat vs the hierarchical Actions record).
     */
    private const NON_ADDITIVE_METRIC_NAMES = [
        'nb_uniq_visitors',
        'nb_users',
        'entry_nb_uniq_visitors',
        'exit_nb_uniq_visitors',
        'sum_daily_nb_uniq_visitors',
        'sum_daily_nb_users',
        'sum_daily_entry_nb_uniq_visitors',
        'sum_daily_exit_nb_uniq_visitors',
    ];

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

    /**
     * The report total may change after the metric is registered: DataRounding rounds the
     * 'totalsUnformatted' metadata together with the row values before recomputing percent
     * metrics, so refresh the total to keep the quotient consistent with the row values.
     * Subtables do not carry the totals metadata and keep the first level report total.
     */
    public function beforeCompute($report, DataTable $table)
    {
        $totals = $table->getMetadata('totalsUnformatted');
        if (is_array($totals)) {
            foreach (array_merge([$this->metricName], $this->metricIds) as $totalKey) {
                if (isset($totals[$totalKey]) && is_numeric($totals[$totalKey])) {
                    $this->total = $totals[$totalKey];
                    break;
                }
            }
        }

        return parent::beforeCompute($report, $table);
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

        $eligibleNames = array_diff(
            $eligibleNames,
            self::NON_ADDITIVE_METRIC_NAMES,
            self::getMetricNamesWithTheirOwnPercentage($table, $report)
        );

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
     * Names of the metrics the report already expresses as a percentage itself, through a
     * '{metric}_percentage' column (the naming convention of VisitsPercent). Those reports
     * compute the percentage against a denominator of their own, eg DevicePlugins.getPlugin
     * leaves out the visits of browsers whose plugins cannot be detected, so a second
     * percentage of the report total would contradict the one already shown.
     *
     * @return string[]
     */
    private static function getMetricNamesWithTheirOwnPercentage(DataTable $table, ?Report $report): array
    {
        $percentageMetricNames = [];

        // metrics the API method registered on the table itself, eg DevicePlugins.getPlugin
        $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];
        foreach ($extraProcessedMetrics as $metric) {
            $percentageMetricNames[] = $metric->getName();
        }

        if ($report) {
            // metrics the report declares, either as a Metric instance or as a plain name
            $percentageMetricNames = array_merge(
                $percentageMetricNames,
                array_keys($report->getProcessedMetricsById()),
                array_keys($report->getProcessedMetrics() ?: [])
            );
        }

        $suffixLength = strlen(self::OWN_PERCENTAGE_COLUMN_SUFFIX);
        $metricNames  = [];

        foreach ($percentageMetricNames as $percentageMetricName) {
            if (str_ends_with($percentageMetricName, self::OWN_PERCENTAGE_COLUMN_SUFFIX)) {
                $metricNames[] = substr($percentageMetricName, 0, -$suffixLength);
            }
        }

        return $metricNames;
    }

    /**
     * Recomputes the percent-of-total columns on truncation summary rows. When a generic filter
     * needs processed metrics (eg, filter_sort_column=nb_visits_percent_of_total), the percent
     * columns are computed before the Truncate filter runs and Truncate sums the per row
     * quotients into its summary row. The sum of the rounded quotients can drift far from the
     * actual percentage (small values round to 0 and disappear from the sum entirely), so
     * replace it with the quotient of the summed metric values.
     */
    public static function recomputeSummaryRows(DataTable $table): void
    {
        $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];

        $metrics = [];
        foreach ($extraProcessedMetrics as $metric) {
            if ($metric instanceof self) {
                $metrics[] = $metric;
            }
        }

        if (empty($metrics)) {
            return;
        }

        $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
        if ($summaryRow) {
            foreach ($metrics as $metric) {
                if (!$summaryRow->hasColumn($metric->getName())) {
                    continue;
                }

                $value = $metric->compute($summaryRow);
                if ($value !== false) {
                    $summaryRow->setColumn($metric->getName(), $value);
                }
            }
        }

        foreach ($table->getRows() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                self::recomputeSummaryRows($subtable);
            }
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
