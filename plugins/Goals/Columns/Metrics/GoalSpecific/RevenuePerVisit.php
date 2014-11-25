<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific;

use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecificProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * Revenue per visit for a specific goal. Calculated as:
 *
 *     goal's revenue / (nb_visits or goal's nb_conversions depending on what is present in data)
 *
 * Goal revenue & nb_conversion are calculated by the Goals archiver.
 */
class RevenuePerVisit extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_revenue_per_visit';
    }

    public function getTranslatedName()
    {
        return $this->getGoalName() . ' ' . Piwik::translate('General_ColumnValuePerVisit');
    }

    public function getDocumentation()
    {
        if ($this->idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            return Piwik::translate('Goals_ColumnAverageOrderRevenueDocumentation', $this->getGoalNameForDocs());
        } else {
            return Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik::translate('Goals_EcommerceAndGoalsMenu'));
        }
    }

    public function getDependentMetrics()
    {
        return array('goals', 'nb_visits');
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();

        $goalMetrics = $this->getGoalMetrics($row);

        $nbVisits = $this->getMetric($row, 'nb_visits');
        $conversions = $this->getMetric($goalMetrics, 'nb_conversions', $mappingFromNameToIdGoal);

        $goalRevenue = (float) $this->getMetric($goalMetrics, 'revenue', $mappingFromNameToIdGoal);

        return Piwik::getQuotientSafe($goalRevenue, $nbVisits == 0 ? $conversions : $nbVisits, GoalManager::REVENUE_PRECISION);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyMoney($value, $this->idSite);
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTableFactory::getSiteIdFromMetadata($table);
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }
}