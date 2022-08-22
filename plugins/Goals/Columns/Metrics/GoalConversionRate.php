<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Goals;
use Piwik\Tracker\GoalManager;

/**
 * The conversion rate for a specific goal. Calculated as:
 *
 *     goal's nb_conversions / nb_visits
 *
 * The goal's nb_conversions is calculated by the Goal archiver and nb_visits
 * by the core archiving process.
 */
class GoalConversionRate extends GoalSpecificProcessedMetric
{

    public function getName()
    {
        return Goals::makeGoalColumn($this->idGoal, 'conversion_rate');
    }

    public function getTranslatedName()
    {
        return Piwik::translate('Goals_ConversionRate', $this->getGoalName());
    }

    public function getDocumentation()
    {
        return Piwik::translate('Goals_ColumnConversionRateDocumentation', $this->getGoalNameForDocs());
    }

    public function getDependentMetrics()
    {
        return array('nb_visits', Goals::makeGoalColumn($this->idGoal, 'nb_conversions'),  Goals::makeGoalColumn($this->idGoal, 'nb_visits_converted'));
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function compute(Row $row)
    {
        $nbVisits = $this->getMetric($row, 'nb_visits');
        $conversions = $this->getMetric($row, Goals::makeGoalColumn($this->idGoal, 'nb_visits_converted'));

        return Piwik::getQuotientSafe($conversions, $nbVisits, GoalManager::REVENUE_PRECISION + 2);
    }
}