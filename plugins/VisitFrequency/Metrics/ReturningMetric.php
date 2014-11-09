<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\VisitFrequency\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
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

    public function format($value)
    {
        return $this->wrapped->format($value);
    }

    public function compute(Row $row)
    {
        return 0; // (metric is not computed, it is copied from segmented report)
    }

    public function getDependenctMetrics()
    {
        return array();
    }
}