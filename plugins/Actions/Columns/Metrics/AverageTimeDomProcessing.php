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
 * The average amount of time the browser spends until user can start interacting with the page. Calculated as
 *
 *     sum_time_dom_processing / nb_hits_with_time_dom_processing
 *
 * The above metrics are calculated during archiving. This metric is calculated before
 * serving a report.
 */
class AverageTimeDomProcessing extends AveragePerformanceMetric
{
    const ID = 'time_dom_processing';

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnAverageTimeDomProcessing');
    }

    public function getDocumentation()
    {
        return Piwik::translate('General_ColumnAverageTimeDomProcessingDocumentation');
    }
}