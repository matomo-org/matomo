<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance;

use Piwik\Columns\Dimension;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePageLoadTime;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeServer;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeTransfer;
use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\TimeServer;
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
            new AverageTimeNetwork(),
            new AverageTimeServer(),
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
            new AverageTimeNetwork(),
            new AverageTimeServer(),
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

    public static function getMetricSemanticTypes()
    {
        $types = [];
        foreach (self::getAllPagePerformanceMetrics() as $metric) {
            $types[$metric->getName()] = Dimension::TYPE_DURATION_S;
        }
        return $types;
    }

    public static function attachActionMetrics(&$metricsConfig)
    {
        $table = 'log_link_visit_action';

        /**
         * @var ActionDimension[] $performanceDimensions
         */
        $performanceDimensions = [
            new TimeNetwork(),
            new TimeServer(),
            new TimeTransfer(),
            new TimeDomProcessing(),
            new TimeDomCompletion(),
            new TimeOnLoad()
        ];
        foreach($performanceDimensions as $dimension) {
            $id = $dimension->getColumnName();
            $column = $table . '.' . $id;
            $metricsConfig['sum_'.$id] = [
                'aggregation' => 'sum',
                'query' => "sum(
                    case when " . $column . " is null
                        then 0
                        else " . $column . "
                    end
                ) / 1000"
            ];
            $metricsConfig['nb_hits_with_'.$id] = [
                'aggregation' => 'sum',
                'query' => "sum(
                    case when " . $column . " is null
                        then 0
                        else 1
                    end
                )"
            ];
            $metricsConfig['min_'.$id] = [
                'aggregation' => 'min',
                'query' => "min(" . $column . ") / 1000"
            ];
            $metricsConfig['max_'.$id] = [
                'aggregation' => 'max',
                'query' => "max(" . $column . ") / 1000"
            ];
        }

        return $metricsConfig;
    }

}
