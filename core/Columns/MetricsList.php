<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Piwik\Cache;
use Piwik\Piwik;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;

class MetricsList
{
    /**
     * List of metrics
     *
     * @var Metric[]
     */
    private $metrics = array();

    /**
     * @param Metric $metric
     */
    public function addMetric(Metric $metric)
    {
        $this->metrics[] = $metric;
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
            if ($metric->getCategory() === $metricCategory) {
                if (!$metricName || $metric->getName() === $metricName) {
                    unset($this->metrics[$index]);
                }
            }
        }
    }

    public function getMetric($metricName)
    {
        foreach ($this->metrics as $index => $metric) {
            if ($metric->getName() === $metricName) {
                return $metric;
            }
        }
    }

    /**
     * Get all metrics defined in the Piwik platform.
     * @ignore
     * @return static
     */
    public static function get()
    {
        $cache = Cache::getTransientCache();
        $cacheKey = 'MetricsList';

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

        // TODO implement a sort based on category and then alpabetically!
        // usort($instances, array($this, 'sort'));

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

        // todo maybe remove this as it might be still ok to include them in the list if some plugin handles it
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
