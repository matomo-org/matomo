<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\SEO\Metric\Aggregator;
use Piwik\Plugins\SEO\Metric\Metric;
use Piwik\Plugins\SEO\Metric\ProviderCache;
use Piwik\Url;

/**
 * @see plugins/Referrers/functions.php
 * @method static API getInstance()
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

/**
 * The SEO API lets you access a list of SEO metrics for the specified URL: Google PageRank, Google/Bing indexed pages
 * and age of the Domain name.
 *
 * @method static API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns SEO statistics for a URL.
     *
     * @param string $url URL to request SEO stats for
     * @return DataTable
     */
    public function getRank($url)
    {
        Piwik::checkUserHasSomeViewAccess();

        $metricProvider = new ProviderCache(new Aggregator());
        $domain = Url::getHostFromUrl($url);
        $metrics = $metricProvider->getMetrics($domain);

        $dataTable = $this->toDataTable($metrics);
        $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, [
            'id'           => 'skip',
            'rank'         => 'skip',
            'logo'         => 'skip',
            'logo_link'    => 'skip',
            'logo_tooltip' => 'skip',
            'rank_suffix'  => 'skip',
        ]);
        $dataTable->disableFilter('Limit');

        return $dataTable;
    }

    /**
     * @param Metric[] $metrics
     * @return DataTable
     */
    private function toDataTable(array $metrics)
    {
        $translated = [];

        foreach ($metrics as $metric) {
            if (!$metric instanceof Metric) {
                continue;
            }

            $label = Piwik::translate($metric->getName());
            $translated[$label] = [
                'id'           => $metric->getId(),
                'rank'         => $metric->getValue(),
                'logo'         => $metric->getLogo(),
                'logo_link'    => $metric->getLogoLink(),
                'logo_tooltip' => Piwik::translate($metric->getLogoTooltip()),
                'rank_suffix'  => Piwik::translate($metric->getValueSuffix()),
            ];
        }

        return DataTable::makeFromIndexedArray($translated);
    }
}
