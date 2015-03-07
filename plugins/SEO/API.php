<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
 * Alexa Rank, age of the Domain name and count of DMOZ entries.
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

        return $this->toDataTable($metrics);
    }

    /**
     * @param Metric[] $metrics
     * @return DataTable
     */
    private function toDataTable(array $metrics)
    {
        $translated = array();

        foreach ($metrics as $metric) {
            if (!$metric instanceof Metric) {
                continue;
            }

            $label = Piwik::translate($metric->getName());
            $translated[$label] = array(
                'id'           => $metric->getId(),
                'rank'         => $metric->getValue(),
                'logo'         => $metric->getLogo(),
                'logo_link'    => $metric->getLogoLink(),
                'logo_tooltip' => Piwik::translate($metric->getLogoTooltip()),
                'rank_suffix'  => Piwik::translate($metric->getValueSuffix()),
            );
        }

        return DataTable::makeFromIndexedArray($translated);
    }
}
