<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Controller as PluginController;
use Piwik\Plugin\ReportsProvider;

class Controller extends PluginController
{
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
