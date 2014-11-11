<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics\GoalSpecific;

use Piwik\DataTable\Row;
use Piwik\Plugins\Goals\Metrics\GoalSpecificProcessedMetric;

/**
 * The conversions for a specific goal. Returns the conversions for a single goal which
 * is then treated as a new column.
 */
class Conversions extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_nb_conversions';
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
        $goalMetrics = $this->getGoalMetrics($row);
        return (int) $this->getMetric($goalMetrics, 'nb_conversions');
    }
}