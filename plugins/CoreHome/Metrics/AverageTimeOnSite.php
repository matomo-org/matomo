<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
 */
class AverageTimeOnSite extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_time_on_site';
    }

    public function compute(Row $row)
    {
        $sumVisitLength = $this->getColumn($row, 'sum_visit_length');
        $nbVisits = $this->getColumn($row, 'nb_visits');

        return Piwik::getQuotientSafe($sumVisitLength, $nbVisits, $precision = 0);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnAvgTimeOnSite');
    }

    public function getDependenctMetrics()
    {
        return array('sum_visit_length', 'nb_visits');
    }
}