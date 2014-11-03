<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\VisitFrequency\Metrics;

use Exception;
use Piwik\DataTable\Row;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
 */
class BounceRateReturning extends ProcessedMetric
{
    public function getName()
    {
        return 'bounce_rate_returning';
    }

    public function format($value)
    {
        return ($value * 100) . '%';
    }

    public function compute(Row $row)
    {
        // empty (metric is not computed, it is copied from segmented report)
    }
}