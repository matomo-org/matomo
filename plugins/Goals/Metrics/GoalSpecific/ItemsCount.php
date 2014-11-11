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
use Piwik\Plugins\Goals\Metrics\GoalSpecificProcessedMetric;

/**
 * The number of ecommerce order items for conversions of a goal. Returns the 'items'
 * goal specific metric.
 */
class ItemsCount extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_items';
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
        return (int) $this->getMetric($goalMetrics, 'items', $mappingFromNameToIdGoal);
    }
}