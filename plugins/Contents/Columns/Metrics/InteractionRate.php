<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * The content interaction rate. Calculated as:
 *
 *     nb_interactions / nb_impressions
 *
 * nb_interactions & nb_impressions are calculated by the Contents archiver.
 */
class InteractionRate extends ProcessedMetric
{
    public function getName()
    {
        return 'interaction_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('Contents_InteractionRate');
    }

    public function getDocumentation()
    {
        return Piwik::translate('Contents_InteractionRateMetricDocumentation');
    }

    public function compute(Row $row)
    {
        $interactions = $this->getMetric($row, 'nb_interactions');
        $impressions = $this->getMetric($row, 'nb_impressions');

        return Piwik::getQuotientSafe($interactions, $impressions, $precision = 4);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function getDependentMetrics()
    {
        return array('nb_interactions', 'nb_impressions');
    }
}