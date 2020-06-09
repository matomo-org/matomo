<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\Reports;

use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\PagePerformance\Metrics;
use Piwik\Plugins\PagePerformance\Visualizations\JqplotGraph\StackedBarEvolution;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();

        $this->dimension = null;
        $this->categoryId = 'General_Visitors';
        $this->subcategoryId = 'General_Overview';
        $this->order = 5;

        $this->name = Piwik::translate('PagePerformance_Overview');
        $this->documentation = '';
        $this->onlineGuideUrl = 'https://matomo.org/docs/page-performance/';
        $this->processedMetrics = Metrics::getAllPagePerformanceMetrics();
        $this->metrics = Metrics::getAllPagePerformanceMetrics();
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $config = $factory->createWidget();
        $config->forceViewDataTable(StackedBarEvolution::ID);
        $config->setAction('getEvolutionGraph');
        $config->setOrder(20);
        $config->setName('PagePerformance_EvolutionOverPeriod');
        $widgetsList->addWidgetConfig($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(Sparklines::ID);
        $config->setName('');
        $config->setIsNotWidgetizable();
        $config->setOrder(21);
        $widgetsList->addWidgetConfig($config);
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)
            && $view instanceof Sparklines
        ) {
            $this->addSparklineColumns($view);

            $view->config->columns_to_display = array_keys(Metrics::getAllPagePerformanceMetrics());
            $view->config->setNotLinkableWithAnyEvolutionGraph();
        }
    }

    private function addSparklineColumns(Sparklines $view)
    {
        $count = 0;
        foreach ($this->getMetrics() as $metric => $translation) {
            $view->config->addSparklineMetric([$metric], $count++);
        }
    }
}