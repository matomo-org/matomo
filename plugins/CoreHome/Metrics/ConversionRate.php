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
class ConversionRate extends ProcessedMetric
{
    public function getName()
    {
        return 'conversion_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnConversionRate');
    }

    public function getDependenctMetrics()
    {
        return array('nb_visits_converted', 'nb_visits');
    }

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function compute(Row $row)
    {
        $nbVisitsConverted = $this->getMetric($row, 'nb_visits_converted');
        $nbVisits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($nbVisitsConverted, $nbVisits, $precision = 4);
    }
}