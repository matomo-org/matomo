<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitorInterest\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * TODO
 */
class VisitsPercent extends ProcessedMetric
{
    private $cachedTotalVisits = null;
    private $forceTotalVisits = null;

    /**
     * TODO
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

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function getDependenctMetrics()
    {
        return array('nb_visits');
    }

    public function beforeCompute($report, DataTable $table)
    {
        if ($this->forceTotalVisits === null) {
            $columnName = 'nb_visits';

            $firstRow = $table->getFirstRow();
            if (!empty($firstRow)
                && $firstRow->getColumn($columnName) === false
            ) {
                $columnName = Metrics::INDEX_NB_VISITS;
            }

            $this->cachedTotalVisits = array_sum($table->getColumn($columnName));
        } else {
            $this->cachedTotalVisits = $this->forceTotalVisits;
        }

        return true; // always compute
    }
}