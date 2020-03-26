<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance;

use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePageLoadTime;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeLatency;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeTransfer;
use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeLatency;
use Piwik\Plugins\PagePerformance\Columns\TimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\TimeTransfer;

class Metrics
{
    /**
     * @return \Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePerformanceMetric[]
     */
    public static function getPagePerformanceMetrics()
    {
        $metrics = [
            new AverageTimeLatency(),
            new AverageTimeTransfer(),
            new AverageTimeDomProcessing(),
            new AverageTimeDomCompletion(),
            new AverageTimeOnLoad(),
        ];

        $mappedMetrics = [];

        foreach ($metrics as $metric) {
            $mappedMetrics[$metric->getName()] = $metric;
        }

        return $mappedMetrics;
    }

    /**
     * @return \Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePerformanceMetric[]
     */
    public static function getAllPagePerformanceMetrics()
    {
        $metrics = [
            new AverageTimeLatency(),
            new AverageTimeTransfer(),
            new AverageTimeDomProcessing(),
            new AverageTimeDomCompletion(),
            new AverageTimeOnLoad(),
            new AveragePageLoadTime()
        ];

        $mappedMetrics = [];

        foreach ($metrics as $metric) {
            $mappedMetrics[$metric->getName()] = $metric;
        }

        return $mappedMetrics;
    }

    public static function getMetricTranslations()
    {
        $translations = array();
        foreach (self::getAllPagePerformanceMetrics() as $metric) {
            $translations[$metric->getName()] = $metric->getTranslatedName();
        }

        return $translations;
    }

    public static function attachActionMetrics(&$metricsConfig)
    {
        /**
         * @var ActionDimension[] $performanceDimensions
         */
        $performanceDimensions = [
            new TimeLatency(),
            new TimeTransfer(),
            new TimeDomProcessing(),
            new TimeDomCompletion(),
            new TimeOnLoad()
        ];
        foreach($performanceDimensions as $dimension) {
            $id = $dimension->getColumnName();
            $metricsConfig['sum_'.$id] = [
                'aggregation' => 'sum',
                'query' => "sum(
                    case when " . $id . " is null
                        then 0
                        else " . $id . "
                    end
                ) / 1000"
            ];
            $metricsConfig['nb_hits_with_'.$id] = [
                'aggregation' => 'sum',
                'query' => "sum(
                    case when " . $id . " is null
                        then 0
                        else 1
                    end
                )"
            ];
            $metricsConfig['min_'.$id] = [
                'aggregation' => 'min',
                'query' => "min(" . $id . ") / 1000"
            ];
            $metricsConfig['max_'.$id] = [
                'aggregation' => 'max',
                'query' => "max(" . $id . ") / 1000"
            ];
        }

        return $metricsConfig;
    }

}
