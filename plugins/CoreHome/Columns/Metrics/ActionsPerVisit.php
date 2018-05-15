<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * The average number of actions per visit. Calculated as:
 *
 *     nb_actions / nb_visits
 *
 * nb_actions & nb_visits are calculated during archiving.
 */
class ActionsPerVisit extends ProcessedMetric
{
    public function getName()
    {
        return 'nb_actions_per_visit';
    }

    public function compute(Row $row)
    {
        $actions = $this->getMetric($row, 'nb_actions');
        $visits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($actions, $visits, $precision = 1);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnActionsPerVisit');
    }

    public function getDependentMetrics()
    {
        return array('nb_actions', 'nb_visits');
    }
}