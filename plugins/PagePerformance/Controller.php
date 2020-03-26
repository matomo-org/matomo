<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Controller as PluginController;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution as EvolutionViz;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePageLoadTime;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeLatency;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeTransfer;
use Piwik\View;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

class Controller extends PluginController
{
    public function indexPagePerformance()
    {
        $view = new View('@PagePerformance/getPagePerformancePopover');

        $dataTable = $this->getEvolutionTable();

        $view->graph = $this->getRowEvolutionGraph($dataTable);

        return $view->render();
    }

    protected function getEvolutionTable()
    {
        $apiMethod = Common::getRequestVar('apiMethod');
        $period    = Common::getRequestVar('period');

        $params = [
            'method'    => $apiMethod,
            'period'    => $period,
            'label'     => Common::getRequestVar('label', ''),
            'idSite'    => $this->idSite,
            'segment'   => Common::getRequestVar('segment', ''),
            'date'      => 'range' === $period ? $this->strDate : EvolutionViz::getDateRangeAndLastN($period, $this->strDate)[0],
            'format'    => 'original',
            'serialize' => '0',
        ];

        /** @var DataTable $dataTable */
        $dataTable = Request::processRequest($apiMethod, $params, []);
        $dataTable->deleteColumn('label');

        return $dataTable;
    }

    /**
     * @return string|void
     * @throws \Exception
     */
    public function getRowEvolutionGraph($dataTable=null)
    {
        $this->checkSitePermission();

        $apiMethod = Common::getRequestVar('apiMethod');

        if (empty($dataTable)) {
            $dataTable = $this->getEvolutionTable();
        }

        // set up the view data table
        $view = ViewDataTableFactory::build(
            Evolution::ID, $apiMethod, 'PagePerformance.getRowEvolutionGraph', $forceDefault = true);
        $view->setDataTable($dataTable);

        $view->config->columns_to_display = [
            (new AverageTimeLatency())->getName(),
            (new AverageTimeTransfer())->getName(),
            (new AverageTimeDomProcessing())->getName(),
            (new AverageTimeDomCompletion())->getName(),
            (new AverageTimeOnLoad())->getName(),
            (new AveragePageLoadTime())->getName(),
        ];

        $view->requestConfig->request_parameters_to_modify['label'] = '';
        $view->config->show_goals = false;
        $view->config->show_search = false;
        $view->config->show_all_views_icons = false;
        $view->config->show_related_reports  = false;
        $view->config->show_series_picker    = false;
        $view->config->show_footer_message   = false;

        return $this->renderView($view);
    }

    public function getEvolutionGraph()
    {
        $this->checkSitePermission();

        $columns = Common::getRequestVar('columns', false);
        if (false !== $columns) {
            $columns = Piwik::getArrayFromApiParameter($columns);
        }

        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'PagePerformance.get');

        if (!empty($columns)) {
            $view->config->columns_to_display = $columns;
        } elseif (empty($view->config->columns_to_display)) {
            $view->config->columns_to_display = array('avg_page_load_time');
        }

        $report = ReportsProvider::factory('PagePerformance', 'get');
        $view->config->selectable_columns = $report->getAllMetrics();

        $numberFormatter = new Formatter\Html();
        $metrics = $report->getMetrics();
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

        $view->config->documentation = Piwik::translate('General_EvolutionOverPeriod');

        return $this->renderView($view);
    }
}
