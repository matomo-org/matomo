<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\API;

use Piwik\Metrics;

class Glossary
{
    protected $metadata = array();

    public function __construct(API $api)
    {
        $this->api = $api;
    }

    public function reportsGlossary($idSite)
    {
        $metadata = $this->api->getReportMetadata($idSite);

        $reports = array();
        foreach ($metadata as $report) {
            if (isset($report['documentation'])) {
                $docReport = array(
                    'name' => sprintf("%s (%s)", $report['name'], $report['category']),
                    'documentation' => $report['documentation']
                );

                if (isset($report['onlineGuideUrl'])) {
                    $docReport['onlineGuideUrl'] = $report['onlineGuideUrl'];
                }

                $reports[] = $docReport;
            }
        }

        usort($reports, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $reports;
    }

    public function metricsGlossary($idSite)
    {
        $metadata = $this->api->getReportMetadata($idSite);

        $metrics = array();
        foreach ($metadata as $report) {
            if (!isset($report['metricsDocumentation'])) {
                continue;
            }

            foreach ($report['metricsDocumentation'] as $metricId => $metricDocumentation) {

                $metricKey = $metricId;

                if(empty($report['metrics'][$metricId])
                    && empty($report['processedMetrics'][$metricId])) {
                    continue;
                }

                $metricName = isset($report['metrics'][$metricId]) ? $report['metrics'][$metricId] : $report['processedMetrics'][$metricId];


                // Already one metric with same name, but different documentation...
                if (isset($metrics[$metricKey])
                    && $metrics[$metricKey]['documentation'] !== $metricDocumentation) {

                    // Don't show nb_hits in glossary since it duplicates others, eg. nb_downloads,
                    if($metricKey == 'nb_hits') {
                        continue;
                    }

                    $metricName = sprintf("%s (%s)", $metricName, $report['category']);
                    $metricKey = $metricName;

                    if (isset($metrics[$metricKey]) && $metrics[$metricKey]['documentation'] !== $metricDocumentation) {
                        throw new \Exception(sprintf("Metric %s has two different documentations: \n(1) %s \n(2) %s",
                                $metricKey,
                                $metrics[$metricKey]['documentation'],
                                $metricDocumentation)
                        );
                    }
                } else {

                    if (!isset($report['metrics'][$metricId])
                        && !isset($report['processedMetrics'][$metricId])
                    ) {
                        // $metricId metric name not found in  $report['dimension'] report
                        // it will be set in another one
                        continue;
                    }

                }

                $metrics[$metricKey] = array(
                    'name' => $metricName,
                    'id' => $metricId,
                    'documentation' => $metricDocumentation
                );
            }
        }


        $metricsTranslations = Metrics::getDefaultMetricTranslations();
        foreach (Metrics::getDefaultMetricsDocumentation() as $metric => $translation) {
            if (!isset($metrics[$metric]) && isset($metricsTranslations[$metric])) {
                $metrics[$metric] = array(
                    'name' => $metricsTranslations[$metric],
                    'id' => $metric,
                    'documentation' => $translation
                );
            }
        }

        usort($metrics, function ($a, $b) {
            $key = ($a['name'] === $b['name'] ? 'id' : 'name');

            return strcmp($a[$key], $b[$key]);
        });
        return $metrics;
    }
}
