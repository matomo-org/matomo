<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics\GoalSpecific;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Metrics\GoalSpecificProcessedMetric;

/**
 * Revenue for a specific goal.
 */
class Revenue extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return $this->getColumnPrefix() . '_revenue';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('%s ' . Piwik::translate('General_ColumnRevenue'), $this->getGoalName());
    }

    public function getDocumentation()
    {
        return Piwik::translate('Goals_ColumnRevenueDocumentation', $this->getGoalNameForDocs());
    }

    public function getDependentMetrics()
    {
        return array('goals');
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();

        $goalMetrics = $this->getGoalMetrics($row);
        return (float) $this->getMetric($goalMetrics, 'revenue', $mappingFromNameToIdGoal);
    }

    public function format($value)
    {
        return MetricsFormatter::getPrettyMoney(sprintf("%.2f", $value), $this->idSite, $isHtml = false);
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTable::getSiteIdFromMetadata($table);
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }
}