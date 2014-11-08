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
// Bounce rate = single page visits on this page / visits started on this page
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

    public function compute(Row $row)
    {
        $entryBounceCount = $this->getMetric($row, 'entry_bounce_count');
        $entryVisits = $this->getMetric($row, 'entry_nb_visits');

        return Piwik::getQuotientSafe($entryBounceCount, $entryVisits, $precision = 2);
    }

    public function format($value)
    {
        return ($value * 100) . '%'; // TODO: how does this affect the float/locale bug?
    }

    public function getDependenctMetrics()
    {
        return array('entry_bounce_count', 'entry_nb_visits');
    }
}