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
class BounceRate extends ProcessedMetric
{
    public function getName()
    {
        return 'bounce_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnBounceRate');
    }

    public function getDependenctMetrics()
    {
        return array('bounce_count', 'nb_visits');
    }

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function compute(Row $row)
    {
        $bounceCount = $this->getMetric($row, 'bounce_count');
        $visits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($bounceCount, $visits, $precision = 4);
    }
}