<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Columns\Dimension;

class AverageTimeOnPage extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_time_on_page';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnAverageTimeOnPage');
    }

    public function compute(Row $row)
    {
        $sumTimeSpent = $this->getMetric($row, 'sum_time_spent');
        $hitsWithTime = $row->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS_WITH_TIME_SPENT);

        if ($hitsWithTime === false || $hitsWithTime <= 0) {
            $hitsWithTime = $this->getMetric($row, 'nb_hits');
        }

        return Piwik::getQuotientSafe($sumTimeSpent, $hitsWithTime, $precision = 0);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyTimeFromSeconds($value, $timeAsSentence = false);
    }

    public function getDependentMetrics()
    {
        return array('sum_time_spent', 'nb_hits');
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_DURATION_S;
    }
}
