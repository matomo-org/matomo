<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Piwik;
use Piwik\Request;

class Controller extends \Piwik\Plugin\Controller
{
    public function getEvolutionGraph(): ?string
    {
        $this->checkSitePermission();

        $columns = [];

        $columnsFromRequest = Request::fromRequest()->getParameter('columns', '');
        if (!empty($columnsFromRequest) && (is_array($columnsFromRequest) || is_string($columnsFromRequest))) {
            $columns = Piwik::getArrayFromApiParameter($columnsFromRequest);
        }

        $documentation = Piwik::translate('BotTracking_BotsOverTimeReportDocumentation') . '<br /><br />';
        $translations  = Metrics::getMetricTranslations();
        $docs          = Metrics::getMetricDocumentation();
        foreach (Metrics::getSparklineMetricOrder() as $metric) {
            if (empty($translations[$metric]) || empty($docs[$metric])) {
                continue;
            }

            $documentation .= sprintf('<b>%s:</b> %s<br />', $translations[$metric], $docs[$metric]);
        }

        $metrics = Metrics::getSparklineMetricOrder();

        if (Request::fromRequest()->getStringParameter('period', '') !== 'day') {
            $metrics = array_filter($metrics, function ($metric) {
                return !in_array($metric, [Metrics::METRIC_AI_ASSISTANTS_UNIQUE_DOCUMENT_URLS, Metrics::METRIC_AI_ASSISTANTS_UNIQUE_PAGE_URLS]);
            });
        }

        $view = $this->getLastUnitGraphAcrossPlugins(
            $this->pluginName,
            __FUNCTION__,
            $columns,
            $metrics,
            $documentation,
            'BotTracking.get'
        );

        if (empty($view->config->columns_to_display)) {
            $view->config->columns_to_display = [Metrics::METRIC_AI_ASSISTANTS_REQUESTS];
        }

        return $this->renderView($view);
    }
}
