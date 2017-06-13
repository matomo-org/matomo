<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Cache as PiwikCache;
use Piwik\CacheId;

class MetricsProvider
{
    /**
     * Get an instance of a specific metric.
     * @param  string $module
     * @param  string $action
     * @return null|\Piwik\Plugin\Metric
     * @api
     */
    public static function factory($metricName)
    {
        $listMetrics = self::getMapOfNameToMetric();

        if (!array_key_exists($metricName, $listMetrics)) {
            return null;
        }

        return $listMetrics[$metricName];
    }

    private static function getMapOfNameToMetric()
    {
        $metrics = new static();
        $metrics = $metrics->getAllMetrics();

        $mapNameToMetric = array();
        foreach ($metrics as $metric) {
            $mapNameToMetric[$metric->getName()] = $metric;
        }

        return $mapNameToMetric;
    }

    /**
     * Returns a list of all available metrics.
     * @return Plugin\Metric[]
     */
    public function getAllMetrics()
    {
        $metrics = $this->getAllMetricClasses();
        $cacheId = CacheId::languageAware('Metrics' . md5(implode('', $metrics)));
        $cache   = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $instances = array();

            /**
             * Triggered to add new metrics that cannot be picked up automatically by the platform.
             * This is useful if the plugin allows a user to create metrics dynamically. For example
             * CustomDimensions or CustomVariables.
             *
             * **Example**
             *
             *     public function addMetric(&$metrics)
             *     {
             *         $metrics[] = new MyCustomMetric();
             *     }
             *
             * @param Plugin\Metric[] $metrics An array of metrics
             */
            Piwik::postEvent('Metric.addMetrics', array(&$instances));

            foreach (Dimension::getAllDimensions() as $dimension) {
                foreach ($dimension->getMetrics() as $metric) {
                    $instances[] = $metric;
                }
            }

            foreach ($metrics as $metric) {
                // TODO we cannot pick up most of them automatically because they have constructor parameters!
               //  $instances[] = new $metric();
            }

            /**
             * Triggered to filter / restrict metrics.
             *
             * **Example**
             *
             *     public function filterMetrics(&$metrics)
             *     {
             *         foreach ($metrics as $index => $metric) {
             *              if ($metric->getCategory() === 'Actions') {}
             *                  unset($metrics[$index]); // remove all metrics belonging to Actions category
             *              }
             *         }
             *     }
             *
             * @param Plugin\Metric[] $metrics An array of metrics
             */
            Piwik::postEvent('Metric.addMetrics', array(&$instances));

            // TODO implement a sort based on category and then alpabetically!
            // usort($instances, array($this, 'sort'));

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }


    /**
     * Returns class names of all Metric metadata classes.
     *
     * @return string[]
     */
    public function getAllMetricClasses()
    {
        return Plugin\Manager::getInstance()->findMultipleComponents('Columns', '\\Piwik\\Plugin\\Metric');
    }

}