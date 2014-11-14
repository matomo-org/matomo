<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * The average number of seconds spent on the site per visit. Calculated as:
 *
 *     sum_visit_length / nb_visits
 *
 * sum_visit_length & nb_visits are calculated during archiving.
 *
 * @api
 */
class AverageTimeOnSite extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_time_on_site';
    }

    public function compute(Row $row)
    {
        $sumVisitLength = $this->getMetric($row, 'sum_visit_length');
        $nbVisits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($sumVisitLength, $nbVisits, $precision = 0);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyTimeFromSeconds($value);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnAvgTimeOnSite');
    }

    public function getDependentMetrics()
    {
        return array('sum_visit_length', 'nb_visits');
    }
}