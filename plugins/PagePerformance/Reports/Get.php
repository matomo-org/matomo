<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\Reports;

use Piwik\EventDispatcher;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\PagePerformance\Metrics;
use Piwik\Plugins\PagePerformance\Visualizations\JqplotGraph\StackedBarEvolution;
use Piwik\Plugins\PagePerformance\Visualizations\PerformanceColumns;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();

        $this->dimension = null;
        $this->categoryId = 'General_Actions';
        $this->subcategoryId = 'PagePerformance_Performance';
        $this->order = 5;

        $this->name = Piwik::translate('PagePerformance_Overview');
        $this->documentation = Piwik::translate('PagePerformance_OverviewDocumentation');
        $this->onlineGuideUrl = 'https://matomo.org/faq/how-to/how-do-i-see-page-performance-reports/';
        $this->processedMetrics = Metrics::getAllPagePerformanceMetrics();
        $this->metrics = Metrics::getAllPagePerformanceMetrics();
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $config = $factory->createWidget();
        $config->forceViewDataTable(StackedBarEvolution::ID);
        $config->setAction('getEvolutionGraph');
        $config->setOrder(1);
        $config->setName('PagePerformance_EvolutionOverPeriod');
        $widgetsList->addWidgetConfig($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(Sparklines::ID);
        $config->setName('');
        $config->setIsNotWidgetizable();
        $config->setOrder(2);
        $widgetsList->addWidgetConfig($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(PerformanceColumns::ID);
        $config->setModule('Actions');
        $config->setAction('getPageUrls');
        $config->setName('Actions_PageUrls');
        $config->setOrder(3);
        // set an additional parameter so we can use that in the report to check if we are in a report on the performance page
        $config->addParameters(['performance' => 1]);
        $config->setIsNotWidgetizable();
        $config->setIsWide();
        $widgetsList->addWidgetConfig($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(PerformanceColumns::ID);
        $config->setModule('Actions');
        $config->setAction('getPageTitles');
        $config->setName('Actions_SubmenuPageTitles');
        $config->setOrder(4);
        // set an additional parameter so we can use that in the report to check if we are in a report on the performance page
        $config->addParameters(['performance' => 1]);
        $config->setIsWide();
        $config->setIsNotWidgetizable();
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
            $this->configureFooterMessage($view);
        }
    }

    private function addSparklineColumns(Sparklines $view)
    {
        $count = 0;
        foreach ($this->getMetrics() as $metric => $translation) {
            $view->config->addSparklineMetric([$metric], $count++);
        }
    }

    private function configureFooterMessage(ViewDataTable $view)
    {
        $out = '';
        EventDispatcher::getInstance()->postEvent('Template.afterPagePerformanceReport', array(&$out));
        $view->config->show_footer_message = $out;
    }
}