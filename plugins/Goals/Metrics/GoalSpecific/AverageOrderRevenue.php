<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics\GoalSpecific;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Metrics\GoalSpecificProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * The average order revenue for a specific goal. Calculated as:
 *
 *     goals' revenue / goal's nb_conversions
 */
class AverageOrderRevenue extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_avg_order_revenue';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_AverageOrderValue');
    }

    public function getDocumentation()
    {
        return Piwik::translate('Goals_ColumnAverageOrderRevenueDocumentation', $this->getGoalNameForDocs());
    }

    public function getDependentMetrics()
    {
        return array('goals');
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();

        $goalMetrics = $this->getGoalMetrics($row);

        $goalRevenue = $this->getMetric($goalMetrics, 'revenue', $mappingFromNameToIdGoal);
        $conversions = $this->getMetric($goalMetrics, 'nb_conversions', $mappingFromNameToIdGoal);

        return Piwik::getQuotientSafe($goalRevenue, $conversions, GoalManager::REVENUE_PRECISION);
    }

    public function format($value)
    {
        return MetricsFormatter::getPrettyMoney(sprintf("%.1f", $value), $this->idSite, $isHtml = false);
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTable::getSiteIdFromMetadata($table);
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }
}