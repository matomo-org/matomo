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