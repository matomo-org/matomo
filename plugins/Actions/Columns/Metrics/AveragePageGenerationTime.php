<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Actions\Columns\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * The average amount of time it takes to generate a page. Calculated as
 *
 *     sum_time_generation / nb_hits_with_time_generation
 *
 * The above metrics are calculated during archiving. This metric is calculated before
 * serving a report.
 */
class AveragePageGenerationTime extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_time_generation';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnAverageGenerationTime');
    }

    public function getDependentMetrics()
    {
        return array('sum_time_generation', 'nb_hits_with_time_generation');
    }

    public function getTemporaryMetrics()
    {
        return array('sum_time_generation');
    }

    public function compute(Row $row)
    {
        $sumGenerationTime = $this->getMetric($row, 'sum_time_generation');
        $hitsWithTimeGeneration = $this->getMetric($row, 'nb_hits_with_time_generation');

        return Piwik::getQuotientSafe($sumGenerationTime, $hitsWithTimeGeneration, $precision = 3);
    }

    public function format($value, Formatter $formatter)
    {
        if ($formatter instanceof Formatter\Html
            && !$value
        ) {
            return '-';
        } else {
            return $formatter->getPrettyTimeFromSeconds($value, $displayAsSentence = true);
        }
    }

    public function beforeCompute($report, DataTable $table)
    {
        $hasTimeGeneration = array_sum($this->getMetricValues($table, 'sum_time_generation')) > 0;

        if (!$hasTimeGeneration
            && $table->getRowsCount() != 0
            && !$this->hasAverageTimeGeneration($table)
        ) {
            // No generation time: remove it from the API output and add it to empty_columns metadata, so that
            // the columns can also be removed from the view
            $table->filter('ColumnDelete', array(array(
                Metrics::INDEX_PAGE_SUM_TIME_GENERATION,
                Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION,
                Metrics::INDEX_PAGE_MIN_TIME_GENERATION,
                Metrics::INDEX_PAGE_MAX_TIME_GENERATION,
                'sum_time_generation',
                'nb_hits_with_time_generation',
                'min_time_generation',
                'max_time_generation'
            )));

            if ($table instanceof DataTable) {
                $emptyColumns = $table->getMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME);
                if (!is_array($emptyColumns)) {
                    $emptyColumns = array();
                }
                $emptyColumns[] = 'sum_time_generation';
                $emptyColumns[] = 'avg_time_generation';
                $emptyColumns[] = 'min_time_generation';
                $emptyColumns[] = 'max_time_generation';
                $table->setMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME, $emptyColumns);
            }
        }

        return $hasTimeGeneration;
    }

    private function hasAverageTimeGeneration(DataTable $table)
    {
        return $table->getFirstRow()->getColumn('avg_time_generation') !== false;
    }
}