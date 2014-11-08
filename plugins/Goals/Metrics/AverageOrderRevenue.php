<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * TODO
 */
class AverageOrderRevenue extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_order_revenue';
    }

    public function compute(Row $row)
    {
        $revenue = $this->getMetric($row, 'revenue');
        $conversions = $this->getMetric($row, 'nb_conversions');

        return Piwik::getQuotientSafe($revenue, $conversions, $precision = 2);
    }

    public function getTranslatedName()
    {
        return Piwik::translate(''); // TODO: no translated name?
    }

    public function getDependenctMetrics()
    {
        return array('revenue', 'nb_conversions');
    }
}