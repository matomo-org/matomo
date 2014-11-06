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
 * TODO
 */
class ConversionRate extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_conversion_rate';
    }

    public function getTranslatedName()
    {
        return self::getName(); // TODO???
    }

    public function getDependenctMetrics()
    {
        return array('goals');
    }

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function compute(Row $row)
    {
        $goalMetrics = $this->getColumn($row, 'goals');

        $nbVisits = $this->getColumn($row, 'nb_visits');
        $conversions = $this->getColumn($goalMetrics, 'nb_conversions');

        return Piwik::getQuotientSafe($conversions, $nbVisits, GoalManager::REVENUE_PRECISION);
    }
}