<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Actions\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * The bounce rate for individual pages. Calculated as:
 *
 *     entry_bounce_count (single page visits on this page) / entry_nb_visits (all visits that started on this page)
 *
 * entry_bounce_count & entry_nb_visits are calculated by the Actions archiver.
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

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function getDependentMetrics()
    {
        return array('entry_bounce_count', 'entry_nb_visits');
    }
}