<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific;

use Piwik\Columns\Dimension;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecificProcessedMetric;
use Piwik\Plugins\Goals\Goals;

/**
 * The conversions for a specific goal. Returns the conversions for a single goal which
 * is then treated as a new column.
 */
class Conversions extends GoalSpecificProcessedMetric
{
    public function getName()
    {
        return Goals::makeGoalColumn($this->idGoal, 'nb_conversions', false);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('Goals_Conversions', $this->getGoalNameForDocs());
    }

    public function getDependentMetrics()
    {
        return array('goals');
    }

    public function compute(Row $row)
    {
        $mappingFromNameToIdGoal = Metrics::getMappingFromNameToIdGoal();

        $goalMetrics = $this->getGoalMetrics($row);
        return (int) $this->getMetric($goalMetrics, 'nb_conversions', $mappingFromNameToIdGoal);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_NUMBER;
    }
}