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
 * The average amount of time the server needs to start serving a page. Calculated as
 *
 *     sum_time_server / nb_hits_with_time_server
 *
 * The above metrics are calculated during archiving. This metric is calculated before
 * serving a report.
 */
class AverageTimeServer extends AveragePerformanceMetric
{
    const ID = 'time_server';

    public function getTranslatedName()
    {
        return Piwik::translate('PagePerformance_ColumnAverageTimeServer');
    }

    public function getDocumentation()
    {
        return Piwik::translate('PagePerformance_ColumnAverageTimeServerDocumentation');
    }
}