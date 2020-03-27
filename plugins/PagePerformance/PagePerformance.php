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

/**
 */
class PagePerformance extends \Piwik\Plugin
{
    protected $availableForMethods = [
        'getPageUrls',
        'getEntryPageUrls',
        'getExitPageUrls',
        'getPageUrlsFollowingSiteSearch',
        'getPageTitles',
        'getPageTitlesFollowingSiteSearch',
    ];

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $events = array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
            'Actions.Archiving.addActionMetrics'     => 'addActionMetrics',
            'ViewDataTable.configure'                => 'configureViewDataTable',
            'Metrics.getDefaultMetricTranslations'   => 'addMetricTranslations',
        );

        foreach ($this->availableForMethods as $method) {
            $events["API.Actions.$method.end"] = 'enrichApi';
        }

        return $events;
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        //$pages[] = "PagePerformance_Actions.Transitions_Transitions";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        //$stylesheets[] = 'plugins/PagePerformance/stylesheets/styles.less';
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

    public function enrichApi(DataTable\DataTableInterface $dataTable, $params)
    {
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
        if ('Actions' === $module && in_array($method, $this->availableForMethods)) {
            $view->config->columns_to_display[] = 'avg_page_load_time';
        }
    }

    public function addActionMetrics(&$metricsConfig)
    {
        Metrics::attachActionMetrics($metricsConfig);
    }
}
