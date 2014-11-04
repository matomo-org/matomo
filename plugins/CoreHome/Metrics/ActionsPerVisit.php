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
        return Piwik::getQuotientSafe($row->getColumn('nb_actions'), $row->getColumn('nb_visits'), $precision = 1);
    }
}