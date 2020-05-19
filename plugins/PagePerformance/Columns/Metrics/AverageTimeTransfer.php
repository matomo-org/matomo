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
 * The average amount of time it takes to transfer a page. Calculated as
 *
 *     sum_time_transfer / nb_hits_with_time_transfer
 *
 * The above metrics are calculated during archiving. This metric is calculated before
 * serving a report.
 */
class AverageTimeTransfer extends AveragePerformanceMetric
{
    const ID = 'time_transfer';

    public function getTranslatedName()
    {
        return Piwik::translate('PagePerformance_ColumnAverageTimeTransfer');
    }

    public function getDocumentation()
    {
        return Piwik::translate('PagePerformance_ColumnAverageTimeTransferDocumentation');
    }
}