<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics\GoalSpecific;

use Piwik\DataTable\Row;
use Piwik\Metrics;
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
        return self::getName(); // TODO???
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
}