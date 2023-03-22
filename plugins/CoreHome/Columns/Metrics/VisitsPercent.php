<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * Percent of visits in the whole table. Calculated as:
 *
 *     nb_visits / sum(all nb_visits in table)
 *
 * nb_visits is calculated by core archiving process.
 */
class VisitsPercent extends ProcessedMetric
{
    private $cachedTotalVisits = null;
    private $forceTotalVisits = null;

    /**
     * Constructor.
     *
     * @param int|null $totalVisits The forced value of total visits to use.
     */
    public function __construct($totalVisits = null)
    {
        $this->forceTotalVisits = $totalVisits;
    }

    public function getName()
    {
        return 'nb_visits_percentage';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnPercentageVisits');
    }

    public function compute(Row $row)
    {
        $visits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($visits, $this->cachedTotalVisits, $precision = 2);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function getDependentMetrics()
    {
        return array('nb_visits');
    }

    public function beforeCompute($report, DataTable $table)
    {
        if ($this->forceTotalVisits === null) {
            $this->cachedTotalVisits = array_sum($this->getMetricValues($table, 'nb_visits'));
        } else {
            $this->cachedTotalVisits = $this->forceTotalVisits;
        }

        return true; // always compute
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }
}