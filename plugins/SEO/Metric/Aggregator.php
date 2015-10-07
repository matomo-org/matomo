<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter;
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
        $metrics = array();

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

        $providers = array(
            $container->get('Piwik\Plugins\SEO\Metric\Google'),
            $container->get('Piwik\Plugins\SEO\Metric\Bing'),
            $container->get('Piwik\Plugins\SEO\Metric\Alexa'),
            $container->get('Piwik\Plugins\SEO\Metric\DomainAge'),
            $container->get('Piwik\Plugins\SEO\Metric\Dmoz'),
        );

        /**
         * Use this event to register new SEO metrics providers.
         *
         * @param array $providers Contains an array of Piwik\Plugins\SEO\Metric\MetricsProvider instances.
         */
        Piwik::postEvent('SEO.getMetricsProviders', array(&$providers));

        return $providers;
    }
}
