<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Events\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
 */
class AverageEventValue extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_event_value';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('Events_AvgValueDocumentation');
    }

    public function compute(Row $row)
    {
        $sumEventValue = $this->getColumn($row, 'sum_event_value');
        $eventsWithValue = $this->getColumn($row, 'nb_events_with_value');

        return Piwik::getQuotientSafe($sumEventValue, $eventsWithValue, $precision = 2); // TODO: used to use shouldSkipRows = true
    }

    public function getDependenctMetrics()
    {
        return array('sum_event_value', 'nb_events_with_value');
    }
}