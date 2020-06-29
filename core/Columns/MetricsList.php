<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;

/**
 * Manages the global list of metrics that can be used in reports.
 *
 * Metrics are added automatically by dimensions as well as through the {@hook Metric.addMetrics} and
 * {@hook Metric.addComputedMetrics} and filtered through the {@hook Metric.filterMetrics} event.
 * Observers for this event should call the {@link addMetric()} method to add metrics or use any of the other
 * methods to remove metrics.
 *
 * @api since Piwik 3.2.0
 */
class MetricsList
{
    /**
     * List of metrics
     *
     * @var Metric[]
     */
    private $metrics = array();

    private $metricsByNameCache = array();

    /**
     * @param Metric $metric
     */
    public function addMetric(Metric $metric)
    {
        $this->metrics[] = $metric;
        $this->metricsByNameCache = array();
    }

    /**
     * Get all available metrics.
     *
     * @return Metric[]
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * Removes one or more metrics from the metrics list.
     *
     * @param string $metricCategory The metric category id. Can be a translation token eg 'General_Visits'
     *                                 see {@link Metric::getCategory()}.
     * @param string|false $metricName The name of the metric to remove eg 'nb_visits'.
     *                                 If not supplied, all metrics within that category will be removed.
     */
    public function remove($metricCategory, $metricName = false)
    {
        foreach ($this->metrics as $index => $metric) {
            if ($metric->getCategoryId() === $metricCategory) {
                if (!$metricName || $metric->getName() === $metricName) {
                    unset($this->metrics[$index]);
                    $this->metricsByNameCache = array();
                }
            }
        }
    }

    /**
     * @param string $metricName
     * @return Metric|ArchivedMetric|null
     */
    public function getMetric($metricName)
    {
        if (empty($this->metricsByNameCache)) {
            // this method might be called quite often... eg when having heaps of goals... need to cache it
            foreach ($this->metrics as $index => $metric) {
                $this->metricsByNameCache[$metric->getName()] = $metric;
            }
        }

        if (!empty($this->metricsByNameCache[$metricName])) {
            return $this->metricsByNameCache[$metricName];
        }

        return null;
    }

    /**
     * Get all metrics defined in the Piwik platform.
     * @ignore
     * @return static
     */
    public static function get()
    {
        $cache = Cache::getTransientCache();
        $cacheKey = CacheId::siteAware('MetricsList');

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $list = new static;

        /**
         * Triggered to add new metrics that cannot be picked up automatically by the platform.
         * This is useful if the plugin allows a user to create metrics dynamically. For example
         * CustomDimensions or CustomVariables.
         *
         * **Example**
         *
         *     public function addMetric(&$list)
         *     {
         *         $list->addMetric(new MyCustomMetric());
         *     }
         *
         * @param MetricsList $list An instance of the MetricsList. You can add metrics to the list this way.
         */
        Piwik::postEvent('Metric.addMetrics', array($list));

        $dimensions = Dimension::getAllDimensions();
        foreach ($dimensions as $dimension) {
            $factory = new DimensionMetricFactory($dimension);
            $dimension->configureMetrics($list, $factory);
        }

        $computedFactory = new ComputedMetricFactory($list);

        /**
         * Triggered to add new metrics that cannot be picked up automatically by the platform.
         * This is useful if the plugin allows a user to create metrics dynamically. For example
         * CustomDimensions or CustomVariables.
         *
         * **Example**
         *
         *     public function addMetric(&$list)
         *     {
         *         $list->addMetric(new MyCustomMetric());
         *     }
         *
         * @param MetricsList $list An instance of the MetricsList. You can add metrics to the list this way.
         */
        Piwik::postEvent('Metric.addComputedMetrics', array($list, $computedFactory));

        /**
         * Triggered to filter metrics.
         *
         * **Example**
         *
         *     public function removeMetrics(Piwik\Columns\MetricsList $list)
         *     {
         *         $list->remove($category='General_Visits'); // remove all metrics having this category
         *     }
         *
         * @param MetricsList $list An instance of the MetricsList. You can change the list of metrics this way.
         */
        Piwik::postEvent('Metric.filterMetrics', array($list));

        $availableMetrics = array();
        foreach ($list->getMetrics() as $metric) {
            $availableMetrics[] = $metric->getName();
        }

        foreach ($list->metrics as $index => $metric) {
            if ($metric instanceof ProcessedMetric) {
                $depMetrics = $metric->getDependentMetrics();
                if (is_array($depMetrics)) {
                    foreach ($depMetrics as $depMetric) {
                        if (!in_array($depMetric, $availableMetrics, $strict = true)) {
                            unset($list->metrics[$index]); // not resolvable metric
                        }
                    }
                }
            }
        }

        $cache->save($cacheKey, $list);

        return $list;
    }

}
