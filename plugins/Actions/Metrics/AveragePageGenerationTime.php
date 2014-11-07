<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Actions\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\Actions\Archiver;

/**
 * TODO
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

    public function getDependenctMetrics()
    {
        return array('sum_time_generation', 'nb_hits_with_time_generation');
    }

    public function compute(Row $row)
    {
        $sumGenerationTime = $this->getColumn($row, Archiver::METRIC_SUM_TIME_RECORD_NAME);
        $hitsWithTimeGeneration = $this->getColumn($row, Archiver::METRIC_HITS_TIMED_RECORD_NAME);

        return Piwik::getQuotientSafe($sumGenerationTime, $hitsWithTimeGeneration, $precision = 3);
    }
}