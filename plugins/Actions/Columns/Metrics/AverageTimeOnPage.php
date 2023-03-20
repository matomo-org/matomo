<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 *     sum_time_spent / nb_visits
 *
 * sum_time_spent and nb_visits are calculated by Archiver classes.
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
        $visits = $this->getMetric($row, 'nb_hits');

        return Piwik::getQuotientSafe($sumTimeSpent, $visits, $precision = 0);
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
