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
class RevenuePerVisit extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_revenue_per_visit';
    }

    public function getTranslatedName()
    {
        return self::getName(); // TODO???
    }

    public function getDependenctMetrics()
    {
        return array('goals', 'nb_visits');
    }

    public function compute(Row $row)
    {
        $goalMetrics = $this->getColumn($row, 'goals');

        $nbVisits = $this->getColumn($row, 'nb_visits');
        $conversions = $this->getColumn($goalMetrics, 'nb_conversions');

        $goalRevenue = (float) $this->getColumn($goalMetrics, 'revenue');

        return Piwik::getQuotientSafe($goalRevenue, $nbVisits == 0 ? $conversions : $nbVisits, GoalManager::REVENUE_PRECISION);
    }
}