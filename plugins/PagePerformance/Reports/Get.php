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
        $this->categoryId = 'General_Actions';
        $this->subcategoryId = 'PagePerformance_Performance';

        $this->name = Piwik::translate('PagePerformance_Overview');
        $this->documentation = '';
        $this->processedMetrics = [
            // none
        ];
        $this->metrics = Metrics::getAllPagePerformanceMetrics();
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $config = $factory->createWidget();
        $config->forceViewDataTable(StackedBarEvolution::ID);
        $config->setAction('getEvolutionGraph');
        $config->setOrder(5);
        $config->setName('General_EvolutionOverPeriod');
        $widgetsList->addWidgetConfig($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(Sparklines::ID);
        $config->setName('');
        $config->setIsNotWidgetizable();
        $config->setOrder(15);
        $widgetsList->addWidgetConfig($config);
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)
            && $view instanceof Sparklines
        ) {
            $this->addSparklineColumns($view);

            $numberFormatter = new Formatter\Html();
            $metrics = $this->getMetrics();
            $view->config->filters[] = function (DataTable $table) use ($numberFormatter, $metrics) {
                $firstRow = $table->getFirstRow();
                if ($firstRow) {
                    foreach ($metrics as $metric => $name) {
                        $metricValue = $firstRow->getColumn($metric);
                        if (false !== $metricValue) {
                            $firstRow->setColumn($metric, $numberFormatter->getPrettyTimeFromSeconds($metricValue / 1000));
                        }
                    }
                }
            };

            $view->config->columns_to_display = array_keys(Metrics::getAllPagePerformanceMetrics());
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