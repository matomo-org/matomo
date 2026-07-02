<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Columns\Dimension;

/**
 * The average amount of time spent on a page. Calculated as:
 *
 *     sum_time_spent / nb_hits_with_time_spent
 *
 * `nb_hits_with_time_spent` is preferred over `nb_hits` so that rows without measurable time-on-page
 * (e.g. the visit's last pageview with no follow-up event and no heartbeat) do not drag the average
 * down toward zero. Archives produced before the accurate-time-on-page metric was introduced
 * (Matomo 5.13.0) report `nb_hits` only; in that case we fall back to it to keep historical numbers
 * comparable.
 */
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
        $hitsWithTime = $this->getMetric($row, 'nb_hits_with_time_spent');

        if ($hitsWithTime <= 0) {
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
        return array('sum_time_spent', 'nb_hits_with_time_spent', 'nb_hits');
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_DURATION_S;
    }
}
