<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\Columns\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\CoreHome\Columns\Metrics\EvolutionMetric;
use Piwik\Site;

/**
 * Ecommerce evolution metric adapter. This is a special processed metric for MultiSites API methods. It will
 * only be calculated for sites that have ecommerce enabled. The site is determined by the label
 * of each row.
 */
class EcommerceOnlyEvolutionMetric extends EvolutionMetric
{
    private $isRevenueEvolution;

    public function __construct($wrapped, DataTable $pastData, $evolutionMetricName = false, $quotientPrecision = 0)
    {
        parent::__construct($wrapped, $pastData, $evolutionMetricName, $quotientPrecision);

        $this->isRevenueEvolution = $this->getName() == 'revenue_evolution';
    }

    public function compute(Row $row)
    {
        $columnName = $this->getWrappedName();
        $currentValue = $this->getMetric($row, $columnName);

        // if the site this is for doesn't support ecommerce & this is for the revenue_evolution column,
        // we don't add the new column
        if ($currentValue === false || !$this->isRevenueEvolution) {
            $idSite = $row->getMetadata('idsite');
            if (!$idSite || !Site::isEcommerceEnabledFor($idSite)) {
                $row->deleteColumn($columnName);

                return false;
            }
        }

        return parent::compute($row);
    }
}