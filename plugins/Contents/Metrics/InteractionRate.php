<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
// Content interaction rate = interactions / impressions
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

    public function compute(Row $row)
    {
        $interactions = $this->getMetric($row, 'nb_interactions');
        $impressions = $this->getMetric($row, 'nb_impressions');

        return Piwik::getQuotientSafe($interactions, $impressions, $precision = 4);
    }

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function getDependenctMetrics()
    {
        return array('nb_interactions', 'nb_impressions');
    }
}