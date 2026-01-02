<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API;

use Piwik\Metrics;

class Glossary
{
    /**
     * @var API
     */
    private $api;

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
                    'documentation' => $report['documentation'],
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

        $metrics = [];

        // Add default values
        $metricsTranslations = Metrics::getDefaultMetricTranslations();
        foreach (Metrics::getDefaultMetricsDocumentation() as $metric => $translation) {
            // Don't show nb_hits in glossary since it duplicates others, eg. nb_downloads,
            if ($metric == 'nb_hits') {
                continue;
            }

            $metrics[$metric] = [
                'name'          => $metricsTranslations[$metric],
                'id'            => $metric,
                'documentation' => [
                    'default'        => $translation,
                    'reportSpecific' => [],
                ],
            ];
        }


        foreach ($metadata as $report) {
            if (!isset($report['metricsDocumentation'])) {
                continue;
            }

            foreach ($report['metricsDocumentation'] as $metricId => $metricDocumentation) {
                $metricKey = $metricId;

                if (
                    empty($report['metrics'][$metricId])
                    && empty($report['processedMetrics'][$metricId])
                ) {
                    continue;
                }

                $metricName = $report['metrics'][$metricId] ?? $report['processedMetrics'][$metricId];


                // Already one metric with same name, but different documentation...
                if (
                    isset($metrics[$metricKey])
                    && $metrics[$metricKey]['documentation']['default'] !== $metricDocumentation
                ) {
                    // Don't show nb_hits in glossary since it duplicates others, eg. nb_downloads,
                    if ($metricKey == 'nb_hits') {
                        continue;
                    }

                    $metrics[$metricKey]['documentation']['reportSpecific'][$report['uniqueId']] = $metricDocumentation;
                } elseif (!isset($metrics[$metricKey])) {
                    $metrics[$metricKey] = [
                        'name'          => $metricName,
                        'id'            => $metricId,
                        'documentation' => [
                            'default'        => null,
                            'reportSpecific' => [
                                $report['uniqueId'] => $metricDocumentation,
                            ],
                        ],
                    ];
                }
            }
        }

        foreach ($metrics as &$metric) {
            if (empty($metric['documentation']['default'])) {
                $valueCount = array_count_values($metric['documentation']['reportSpecific']);

                // in case we do not have a default set, but more than the half of all reports are using the same documentation, we assume that as default.
                if (array_sum($valueCount) - reset($valueCount) < reset($valueCount)) {
                    $metric['documentation']['default'] = key($valueCount);

                    foreach ($metric['documentation']['reportSpecific'] as $uniqueId => $documentation) {
                        if ($documentation === $metric['documentation']['default']) {
                            unset($metric['documentation']['reportSpecific'][$uniqueId]);
                        }
                    }
                }
            }
            if (empty($metric['documentation']['reportSpecific'])) {
                $metric['documentation']['reportSpecific'] = null;
            }
        }

        // convert back to structure with only one metric documentation
        // discarding all metrics that don't have a default documentation
        // @todo remove with Matomo 6 and provide report specific documentation where available
        foreach ($metrics as $metricId => &$metric) {
            if (empty($metric['documentation']['default'])) {
                unset($metrics[$metricId]);
            } else {
                $metric['documentation'] = $metric['documentation']['default'];
            }
        }

        usort($metrics, function ($a, $b) {
            $key = ($a['name'] === $b['name'] ? 'id' : 'name');

            return strcmp($a[$key], $b[$key]);
        });

        return $metrics;
    }
}
