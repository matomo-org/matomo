<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Actions\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
// % Exit = Number of visits that finished on this page / visits on this page
 */
class ExitRate extends ProcessedMetric
{
    public function getName()
    {
        return 'exit_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnExitRate');
    }

    public function compute(Row $row)
    {
        $exitVisits = $this->getColumn($row, 'exit_nb_visits');
        $visits = $this->getColumn($row, 'nb_visits');

        return Piwik::getQuotientSafe($exitVisits, $visits, $precision = 0);
    }

    public function getDependenctMetrics()
    {
        return array('exit_nb_visits', 'nb_visits');
    }
}