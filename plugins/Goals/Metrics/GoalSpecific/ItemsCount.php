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

    public function getDependenctMetrics()
    {
        return array('goals');
    }

    public function compute(Row $row)
    {
        $goalMetrics = $this->getColumn($row, 'goals');
        return (int) $this->getCOlumn($goalMetrics, 'items');
    }
}