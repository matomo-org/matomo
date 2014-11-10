<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\Metrics;

use Piwik\DataTable\Row;
use Piwik\Plugins\CoreHome\Metrics\EvolutionMetric;
use Piwik\Site;

/**
 * Revenue evolution. This is a special processed metric for MultiSites API methods. It will
 * only be calculated for sites that have ecommerce enabled. The site is determined by the label
 * of each row.
 */
class RevenueEvolution extends EvolutionMetric
{
    public function compute(Row $row)
    {
        $columnName = $this->getWrappedName();
        $currentValue = $this->getMetric($row, $columnName);

        // if the site this is for doesn't support ecommerce & this is for the revenue_evolution column,
        // we don't add the new column
        if ($currentValue === false
            && !Site::isEcommerceEnabledFor($row->getColumn('label'))
        ) {
            return false;
        }

        return parent::compute($row);
    }
}