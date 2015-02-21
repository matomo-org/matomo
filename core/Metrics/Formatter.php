<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Metrics;

use Piwik\DataTable;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * Contains methods to format metric values. Passed to the {@link \Piwik\Plugin\Metric::format()}
 * method when formatting Metrics.
 *
 * @api
 */
abstract class Formatter
{
    const PROCESSED_METRICS_FORMATTED_FLAG = 'processed_metrics_formatted';

    /**
     * Returns a prettified, rounded number.
     *
     * @param int|float $value
     * @param int $precision
     * @return int|float|string
     */
    public abstract function getPrettyNumber($value, $precision = 0);

    /**
     * Returns a pretty formatted time value.
     *
     * @param int|float $numberOfSeconds The seconds value to format.
     * @param bool $displayTimeAsSentence If true, the formatter might return the number w/ words
     *                                    and numbers. The formatter may ignore this value as well,
     *                                    depending on the context.
     * @param bool $round If true, the number of seconds is rounded, otherwise the result will contain
     *                    a floating point.
     * @return int|float|string
     */
    public abstract function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = false, $round = false);

    /**
     * Returns the prettified byte size value.
     *
     * @param int $size
     * @param string|null $unit A specific unit to format as, or null to use the biggest one that
     *                          results in a value greater than 1. Supported units include:
     *                          `'B', 'K', 'M', 'G', 'T'`
     * @param int $precision
     * @return int|float|string
     */
    public abstract function getPrettySizeFromBytes($size, $unit = null, $precision = 1);

    /**
     * Returns a pretty formatted money value using a site's currency.
     *
     * @param int|float $value
     * @param int $idSite
     * @return int|float|string
     */
    public abstract function getPrettyMoney($value, $idSite);

    /**
     * Returns a pretty formatted percent value from a quotient value.
     *
     * @param float $value
     * @return int|float|string
     */
    public abstract function getPrettyPercentFromQuotient($value);

    /**
     * Formats all metrics, including processed metrics, for a DataTable. Metrics to format
     * are found through report metadata and DataTable metadata.
     *
     * @param DataTable $dataTable The table to format metrics for.
     * @param Report|null $report The report the table belongs to.
     * @param string[]|null $metricsToFormat Whitelist of names of metrics to format.
     */
    public function formatMetrics(DataTable $dataTable, Report $report = null, $metricsToFormat = null)
    {
        $metrics = $this->getMetricsToFormat($dataTable, $report);
        if (empty($metrics)
            || $dataTable->getMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG)
        ) {
            return;
        }

        $dataTable->setMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG, true);

        if ($metricsToFormat !== null) {
            $metricMatchRegex = $this->makeRegexToMatchMetrics($metricsToFormat);
            $metrics = array_filter($metrics, function (ProcessedMetric $metric) use ($metricMatchRegex) {
                return preg_match($metricMatchRegex, $metric->getName());
            });
        }

        foreach ($metrics as $name => $metric) {
            if (!$metric->beforeFormat($report, $dataTable)) {
                continue;
            }

            foreach ($dataTable->getRows() as $row) {
                $columnValue = $row->getColumn($name);
                if ($columnValue !== false) {
                    $row->setColumn($name, $metric->format($columnValue, $this));
                }

                $subtable = $row->getSubtable();
                if (!empty($subtable)) {
                    $this->formatMetrics($subtable, $report, $metricsToFormat);
                }
            }
        }
    }

    private function makeRegexToMatchMetrics($metricsToFormat)
    {
        $metricsRegexParts = array();
        foreach ($metricsToFormat as $metricFilter) {
            if ($metricFilter[0] == '/') {
                $metricsRegexParts[] = '(?:' . substr($metricFilter, 1, strlen($metricFilter) - 2) . ')';
            } else {
                $metricsRegexParts[] = preg_quote($metricFilter);
            }
        }
        return '/^' . implode('|', $metricsRegexParts) . '$/';
    }

    /**
     * @param DataTable $dataTable
     * @param Report $report
     * @return Metric[]
     */
    private function getMetricsToFormat(DataTable $dataTable, Report $report = null)
    {
        return Report::getMetricsForTable($dataTable, $report, $baseType = 'Piwik\\Plugin\\Metric');
    }
}