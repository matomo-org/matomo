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

/**
 * TODO
 *         // Average time on page = total time on page / number visits on that page
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
        $sumTimeSpent = $this->getColumn($row, 'sum_time_spent');
        $visits = $this->getColumn($row, 'nb_visits');

        return Piwik::getQuotientSafe($sumTimeSpent, $visits, $precision = 0);
    }

    public function getDependenctMetrics()
    {
        return array('sum_time_spent', 'nb_visits');
    }
}