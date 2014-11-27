<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\VisitFrequency\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * Processed metric for VisitFrequency.get API method which just copies VisitsSummary.get
 * metrics as differently named metrics.
 *
 * This metric must be supplied in order to ensure correct formatting for processed
 * metrics that are copied from VisitsSummary.get.
 */
class ReturningMetric extends ProcessedMetric
{
    private static $translations = array(
        'avg_time_on_site_returning' => 'VisitFrequency_ColumnAverageVisitDurationForReturningVisitors',
        'nb_actions_per_visit_returning' => 'VisitFrequency_ColumnAvgActionsPerReturningVisit',
        'bounce_rate_returning' => 'VisitFrequency_ColumnBounceRateForReturningVisits',
    );

    /**
     * @var ProcessedMetric
     */
    private $wrapped;

    public function __construct(ProcessedMetric $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function getName()
    {
        return $this->wrapped->getName() . '_returning';
    }

    public function getTranslatedName()
    {
        return Piwik::translate(self::$translations[$this->getName()]);
    }

    public function format($value, Formatter $formatter)
    {
        return $this->wrapped->format($value, $formatter);
    }

    public function compute(Row $row)
    {
        return 0; // (metric is not computed, it is copied from segmented report)
    }

    public function getDependentMetrics()
    {
        return array();
    }
}