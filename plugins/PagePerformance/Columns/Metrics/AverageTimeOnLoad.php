<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\PagePerformance\Columns\Metrics;

use Piwik\Piwik;

/**
 * The average amount of time browser needs to execute javascript waiting for window.load event. Calculated as
 *
 *     sum_time_on_load / nb_hits_with_time_on_load
 *
 * The above metrics are calculated during archiving. This metric is calculated before
 * serving a report.
 */
class AverageTimeOnLoad extends AveragePerformanceMetric
{
    const ID = 'time_on_load';

    public function getTranslatedName()
    {
        return Piwik::translate('PagePerformance_ColumnAverageTimeOnLoad');
    }

    public function getDocumentation()
    {
        return Piwik::translate('PagePerformance_ColumnAverageTimeOnLoadDocumentation');
    }
}