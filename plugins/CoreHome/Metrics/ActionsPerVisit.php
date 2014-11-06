<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Translate;

/**
 * TODO
 */
class ActionsPerVisit extends ProcessedMetric
{
    public function getName()
    {
        return 'nb_actions_per_visit';
    }

    public function compute(Row $row)
    {
        $actions = $this->getColumn($row, 'nb_actions');
        $visits = $this->getColumn($row, 'nb_visits');

        return Piwik::getQuotientSafe($actions, $visits, $precision = 2);
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnActionsPerVisit');
    }

    public function getDependenctMetrics()
    {
        return array('nb_actions', 'nb_visits');
    }
}