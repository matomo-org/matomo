<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Actions\Columns\Metrics;

use Piwik\Piwik;

/**
 * The average amount of time the network needs to start serving a page. Calculated as
 *
 *     sum_time_latency / nb_hits_with_time_latency
 *
 * The above metrics are calculated during archiving. This metric is calculated before
 * serving a report.
 */
class AverageTimeLatency extends AveragePerformanceMetric
{
    const ID = 'time_latency';

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnAverageTimeLatency');
    }

    public function getDocumentation()
    {
        return Piwik::translate('General_ColumnAverageTimeLatencyDocumentation');
    }
}