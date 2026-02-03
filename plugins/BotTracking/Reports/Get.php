<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\BotTracking\Columns\Metrics\ClickThroughRate;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends Report
{
    protected function init(): void
    {
        parent::init();
        $this->categoryId       = 'General_AIAssistants';
        $this->subcategoryId    = 'BotTracking_AIBotsOverview';
        $this->name             = Piwik::translate('BotTracking_ReportTitleBotsOverview');
        $this->documentation    = '';
        $this->metrics          = Metrics::getReportMetricColumns();
        $this->processedMetrics = [
            new ClickThroughRate(),
        ];
        $this->order            = 10;

        if (\Piwik\Request::fromRequest()->getStringParameter('period', '') !== 'day') {
            $this->metrics = array_filter($this->metrics, function ($metric) {
                return !in_array($metric, [Metrics::METRIC_AI_ASSISTANTS_UNIQUE_DOCUMENT_URLS, Metrics::METRIC_AI_ASSISTANTS_UNIQUE_PAGE_URLS]);
            });
        }
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('BotTracking_ReportTitleBotsOverTime')
                ->forceViewDataTable(Evolution::ID)
                ->setAction('getEvolutionGraph')
                ->setOrder(1)
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('BotTracking_ReportTitleBotsOverview')
                ->forceViewDataTable(Sparklines::ID)
                ->setOrder(2)
        );
    }

    public function configureView(ViewDataTable $view): void
    {
        if (!$view->isViewDataTableId(Sparklines::ID)) {
            return;
        }

        /** @var Sparklines $view */
        $view->config->title = Piwik::translate('BotTracking_ReportTitleBotsOverview');
        $view->config->addTranslations(Metrics::getMetricTranslations());
        $view->config->metrics_documentation = Metrics::getMetricDocumentation();

        $order = 0;
        foreach (Metrics::getSparklineMetricOrder() as $metric) {
            if (
                \Piwik\Request::fromRequest()->getStringParameter('period', '') !== 'day'
                && in_array($metric, [Metrics::METRIC_AI_ASSISTANTS_UNIQUE_DOCUMENT_URLS, Metrics::METRIC_AI_ASSISTANTS_UNIQUE_PAGE_URLS])
            ) {
                continue;
            }
            $view->config->addSparklineMetric($metric, $order++);
        }

        $segment = Request::getRawSegmentFromRequest();
        if (!empty($segment)) {
            $view->config->show_footer_message = Piwik::translate('BotTracking_SegmentNotSupported');
        }
    }
}
