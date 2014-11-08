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
 * TODO
 */
class Revenue extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_revenue';
    }

    public function getTranslatedName()
    {
        return self::getName(); // TODO???
    }

    public function getDependenctMetrics()
    {
        return array('goals');
    }

    public function compute(Row $row)
    {
        $goalMetrics = $this->getMetric($row, 'goals');
        return (float) $this->getMetric($goalMetrics, 'revenue');
    }
}