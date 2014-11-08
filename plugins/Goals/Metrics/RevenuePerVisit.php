<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * TODO
 */
class RevenuePerVisit extends ProcessedMetric
{
    public function getName()
    {
        return 'revenue_per_visit';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnValuePerVisit');
    }

    public function getDependenctMetrics()
    {
        return array('revenue', 'nb_visits', 'nb_conversions','goals');
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();
        $goals = $this->getMetric($row, 'goals');

        $revenue = 0;
        foreach ($goals as $goalId => $goalMetrics) {
            if ($goalId == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                continue;
            }
            if ($goalId >= GoalManager::IDGOAL_ORDER
                || $goalId == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER
            ) {
                $revenue += (int) $this->getMetric($goalMetrics, 'revenue', $mappingFromNameToIdGoal);
            }
        }

        if ($revenue == 0) {
            $revenue = (int) $this->getMetric($row, 'revenue');
        }

        $nbVisits    = (int) $this->getMetric($row, 'nb_visits');
        $conversions = (int) $this->getMetric($row, 'nb_conversions');

        // If no visit for this metric, but some conversions, we still want to display some kind of "revenue per visit"
        // even though it will actually be in this edge case "Revenue per conversion"
        return Piwik::getQuotientSafe($revenue, $nbVisits == 0 ? $conversions : $nbVisits, GoalManager::REVENUE_PRECISION);
    }
}