<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance;

use Piwik\DataTable;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 */
class PagePerformance extends \Piwik\Plugin
{
    public static $availableForMethods = [
        'getPageUrls',
        'getEntryPageUrls',
        'getExitPageUrls',
        'getPageUrlsFollowingSiteSearch',
        'getPageTitles',
        'getEntryPageTitles',
        'getExitPageTitles',
        'getPageTitlesFollowingSiteSearch',
    ];

    public function isTrackerPlugin()
    {
        return true;
    }

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Actions.Archiving.addActionMetrics'     => 'addActionMetrics',
            'ScheduledReports.processReports'        => 'processReports',
            'ViewDataTable.configure'                => 'configureViewDataTable',
            'Metrics.getDefaultMetricTranslations'   => 'addMetricTranslations',
            'Metrics.getDefaultMetricSemanticTypes' => 'addMetricSemanticTypes',
            'Metrics.isLowerValueBetter'             => 'isLowerValueBetter',
            'API.Request.dispatch.end'               => 'enrichApi'
        ];
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/PagePerformance/javascripts/PagePerformance.js';
        $jsFiles[] = 'plugins/PagePerformance/javascripts/rowaction.js';
        $jsFiles[] = 'plugins/PagePerformance/javascripts/jqplotStackedBarEvolutionGraph.js';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'PagePerformance_RowActionTitle';
        $translationKeys[] = 'PagePerformance_RowActionDescription';
        $translationKeys[] = 'PagePerformance_PagePerformanceTitle';
        $translationKeys[] = 'General_Total';
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics      = Metrics::getMetricTranslations();
        $translations = array_merge($translations, $metrics);
    }

    public function addMetricSemanticTypes(array &$types): void
    {
        $metricTypes = Metrics::getMetricSemanticTypes();
        $types = array_merge($types, $metricTypes);
    }

    public function isLowerValueBetter(&$isLowerBetter, $metric)
    {
        if (array_key_exists($metric, Metrics::getAllPagePerformanceMetrics())) {
            $isLowerBetter = true;
        }
    }

    public function enrichApi($dataTable, $params)
    {
        if ('Actions' !== $params['module'] || !$dataTable instanceof DataTable\DataTableInterface) {
            return;
        }

        // remove additional metrics for action reports that don't have data
        if (!in_array($params['action'], self::$availableForMethods)) {
            $dataTable->deleteColumns([
                'sum_time_network',
                'nb_hits_with_time_network',
                'min_time_network',
                'max_time_network',
                'sum_time_server',
                'nb_hits_with_time_server',
                'min_time_server',
                'max_time_server',
                'sum_time_transfer',
                'nb_hits_with_time_transfer',
                'min_time_transfer',
                'max_time_transfer',
                'sum_time_dom_processing',
                'nb_hits_with_time_dom_processing',
                'min_time_dom_processing',
                'max_time_dom_processing',
                'sum_time_dom_completion',
                'nb_hits_with_time_dom_completion',
                'min_time_dom_completion',
                'max_time_dom_completion',
                'sum_time_on_load',
                'nb_hits_with_time_on_load',
                'min_time_on_load',
                'max_time_on_load',
            ], true);
            return;
        }

        $dataTable->filter(function (DataTable $dataTable) {
            $extraProcessedMetrics = $dataTable->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);

            if (empty($extraProcessedMetrics)) {
                $extraProcessedMetrics = array();
            }

            foreach (Metrics::getAllPagePerformanceMetrics() as $pagePerformanceMetric) {
                $extraProcessedMetrics[] = $pagePerformanceMetric;
            }

            $dataTable->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
        });
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        $module = $view->requestConfig->getApiModuleToRequest();
        $method = $view->requestConfig->getApiMethodToRequest();
        if ('Actions' === $module && in_array($method, self::$availableForMethods) && $view instanceof HtmlTable) {
            $view->config->columns_to_display[] = 'avg_page_load_time';
        }
    }

    public function addActionMetrics(&$metricsConfig)
    {
        Metrics::attachActionMetrics($metricsConfig);
    }

    public function processReports(&$processedReports, $reportType, $outputType, $report)
    {
        foreach ($processedReports as &$processedReport) {
            $metadata = &$processedReport['metadata'];

            // Ensure average page load time is displayed in the evolution chart
            if ($metadata['module'] == 'PagePerformance') {
                $metadata['imageGraphUrl'] .= '&columns=avg_page_load_time';
                $metadata['imageGraphEvolutionUrl'] .= '&columns=avg_page_load_time';
            }
        }
    }
}
