<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Cache;

/**
 * Caches another provider.
 */
class ProviderCache implements MetricsProvider
{
    /**
     * @var MetricsProvider
     */
    private $provider;

    /**
     * @var Cache\Lazy
     */
    private $cache;

    public function __construct(MetricsProvider $provider)
    {
        $this->provider = $provider;
        $this->cache = Cache::getLazyCache();
    }

    public function getMetrics($domain)
    {
        $cacheId = 'SEO_getRank_' . md5($domain);

        $metrics = $this->cache->fetch($cacheId);

        if (! is_array($metrics)) {
            $metrics = $this->provider->getMetrics($domain);

            $this->cache->save($cacheId, $metrics, 60 * 60 * 6);
        }

        return $metrics;
    }
}
