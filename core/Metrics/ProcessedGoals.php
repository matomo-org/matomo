<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Metrics;

use Piwik\Metrics;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Tracker\GoalManager;

class ProcessedGoals extends Base
{

    public function getRevenuePerVisit(Row $row)
    {
        $goals = $this->getColumn($row, Metrics::INDEX_GOALS);

        $revenue = 0;
        foreach ($goals as $goalId => $goalMetrics) {
            if ($goalId == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                continue;
            }
            if ($goalId >= GoalManager::IDGOAL_ORDER
                || $goalId == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER
            ) {
                $revenue += (int) $this->getColumn($goalMetrics, Metrics::INDEX_GOAL_REVENUE, Metrics::$mappingFromIdToNameGoal);
            }
        }

        if ($revenue == 0) {
            $revenue = (int) $this->getColumn($row, Metrics::INDEX_REVENUE);
        }

        $nbVisits    = $this->getNumVisits($row);
        $conversions = (int) $this->getColumn($row, Metrics::INDEX_NB_CONVERSIONS);

        // If no visit for this metric, but some conversions, we still want to display some kind of "revenue per visit"
        // even though it will actually be in this edge case "Revenue per conversion"
        $revenuePerVisit = $this->invalidDivision;

        if ($nbVisits > 0
            || $conversions > 0
        ) {
            $revenuePerVisit = round($revenue / ($nbVisits == 0 ? $conversions : $nbVisits), GoalManager::REVENUE_PRECISION);
        }

        return $revenuePerVisit;
    }

    public function getConversionRate(Row $row, $goalMetrics)
    {
        $nbVisits = $this->getNumVisits($row);

        if ($nbVisits == 0) {
            $value = $this->invalidDivision;
        } else {
            $conversions = $this->getNbConversions($goalMetrics);
            $value = round(100 * $conversions / $nbVisits, GoalManager::REVENUE_PRECISION);
        }

        if (empty($value)) {
            return '0%';
        }

        return $value . "%";
    }

    public function getNbConversions($goalMetrics)
    {
        return (int) $this->getColumn($goalMetrics,
                                      Metrics::INDEX_GOAL_NB_CONVERSIONS,
                                      Metrics::$mappingFromIdToNameGoal);
    }

    public function getRevenue($goalMetrics)
    {
        return (float) $this->getColumn($goalMetrics,
                                        Metrics::INDEX_GOAL_REVENUE,
                                        Metrics::$mappingFromIdToNameGoal);
    }

    public function getRevenuePerVisitForGoal(Row $row, $goalMetrics)
    {
        $nbVisits = $this->getNumVisits($row);

        $div = $nbVisits;
        if ($nbVisits == 0) {
            $div = $this->getNbConversions($goalMetrics);
        }

        $goalRevenue = $this->getRevenue($goalMetrics);

        return round($goalRevenue / $div, GoalManager::REVENUE_PRECISION);
    }

    public function getAvgOrderRevenue($goalMetrics)
    {
        $goalRevenue = $this->getRevenue($goalMetrics);
        $conversions = $this->getNbConversions($goalMetrics);

        return $goalRevenue / $conversions;
    }

    public function getItems($goalMetrics)
    {
        $items = $this->getColumn($goalMetrics,
                                  Metrics::INDEX_GOAL_ECOMMERCE_ITEMS,
                                  Metrics::$mappingFromIdToNameGoal);

        if (empty($items)) {
            return 0;
        }

        return $items;
    }

}