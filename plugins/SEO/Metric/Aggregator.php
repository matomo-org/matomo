<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;

/**
 * Aggregates metrics from several providers.
 */
class Aggregator implements MetricsProvider
{
    /**
     * @var MetricsProvider[]
     */
    private $providers;

    public function __construct()
    {
        $this->providers = $this->getProviders();
    }

    public function getMetrics($domain)
    {
        $metrics = [];

        foreach ($this->providers as $provider) {
            $metrics = array_merge($metrics, $provider->getMetrics($domain));
        }

        return $metrics;
    }

    /**
     * @return MetricsProvider[]
     */
    private function getProviders()
    {
        $container = StaticContainer::getContainer();

        $providers = [
            $container->get('Piwik\Plugins\SEO\Metric\Google'),
            $container->get('Piwik\Plugins\SEO\Metric\Bing'),
            $container->get('Piwik\Plugins\SEO\Metric\DomainAge'),
        ];

        /**
         * Use this event to register new SEO metrics providers.
         *
         * @param array $providers Contains an array of Piwik\Plugins\SEO\Metric\MetricsProvider instances.
         */
        Piwik::postEvent('SEO.getMetricsProviders', [&$providers]);

        return $providers;
    }
}
