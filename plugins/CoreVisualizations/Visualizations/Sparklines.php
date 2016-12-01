<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugin\ViewDataTable;
use Piwik\Url;
use Piwik\View;

/**
 * Reads the requested DataTable from the API and prepares data for the Sparklines view. It can display any amount
 * of sparklines. Within a reporting page sparklines are shown in 2 columns, in a dashboard or when exported as a widget
 * the sparklines are shown in one column.
 *
 * The sparklines view currently only supports requesting columns from the same API (the API method of the defining
 * report) via {Sparklines\Config::addSparklineMetric($columns = array('nb_visits', 'nb_unique_visitors'))}.
 *
 * Example:
 * $view->config->addSparklineMetric('nb_visits'); // if an array of metrics given, they will be displayed comma separated
 * $view->config->addTranslation('nb_visits', 'Visits');
 * Results in: [sparkline image] X visits
 * Data is fetched from the configured $view->requestConfig->apiMethodToRequestDataTable.
 *
 * In case you want to add any custom sparklines from any other API method you can call
 * {@link Sparklines\Config::addSparkline()}.
 *
 * Example:
 * $sparklineUrlParams = array('columns' => array('nb_visits));
 * $evolution = array('currentValue' => 5, 'pastValue' => 10, 'tooltip' => 'Foo bar');
 * $view->config->addSparkline($sparklineUrlParams, $value = 5, $description = 'Visits', $evolution);
 *
 * @property Sparklines\Config $config
 */
class Sparklines extends ViewDataTable
{
    const ID = 'sparklines';

    public static function getDefaultConfig()
    {
        return new Sparklines\Config();
    }

    /**
     * @see ViewDataTable::main()
     * @return mixed
     */
    public function render()
    {
        $view = new View('@CoreVisualizations/_dataTableViz_sparklines.twig');

        $columnsList = array();
        if ($this->config->hasSparklineMetrics()) {
            foreach ($this->config->getSparklineMetrics() as $cols) {
                $columns = $cols['columns'];
                if (!is_array($columns)) {
                    $columns = array($columns);
                }

                $columnsList = array_merge($columns, $columnsList);
            }
        }

        $view->allMetricsDocumentation = Metrics::getDefaultMetricsDocumentation();

        $this->requestConfig->request_parameters_to_modify['columns'] = $columnsList;
        $this->requestConfig->request_parameters_to_modify['format_metrics'] = '1';

        if (!empty($this->requestConfig->apiMethodToRequestDataTable)) {
            $this->fetchConfiguredSparklines();
        }

        $view->sparklines = $this->config->getSortedSparklines();
        $view->isWidget = Common::getRequestVar('widget', 0, 'int');
        $view->titleAttributes = $this->config->title_attributes;
        $view->footerMessage = $this->config->show_footer_message;
        $view->areSparklinesLinkable = $this->config->areSparklinesLinkable();

        $view->title = '';
        if ($this->config->show_title) {
            $view->title = $this->config->title;
        }

        return $view->render();
    }

    private function fetchConfiguredSparklines()
    {
        $data = $this->loadDataTableFromAPI();

        $this->applyFilters($data);

        if (!$this->config->hasSparklineMetrics()) {
            foreach ($data->getColumns() as $column) {
                $this->config->addSparklineMetric($column);
            }
        }

        $translations = $this->config->translations;

        $firstRow = $data->getFirstRow();

        foreach ($this->config->getSparklineMetrics() as $sparklineMetric) {
            $column = $sparklineMetric['columns'];
            $order  = $sparklineMetric['order'];

            if ($column === 'label') {
                continue;
            }

            if (empty($column)) {
                $this->config->addPlaceholder($order);
                continue;
            }

            if (!is_array($column)) {
                $column = array($column);
            }

            $values = array();
            $descriptions = array();

            foreach ($column as $col) {
                $value = $firstRow->getColumn($col);

                if ($value === false) {
                    $value = 0;
                }

                $values[] = $value;
                $descriptions[] = isset($translations[$col]) ? $translations[$col] : $col;
            }

            $sparklineUrlParams = array(
                'columns' => $column,
                'module'  => $this->requestConfig->getApiModuleToRequest(),
                'action'  => $this->requestConfig->getApiMethodToRequest()
            );

            $this->config->addSparkline($sparklineUrlParams, $values, $descriptions, null, $order);
        }
    }

    private function applyFilters(DataTable\DataTableInterface $table)
    {
        foreach ($this->config->getPriorityFilters() as $filter) {
            $table->filter($filter[0], $filter[1]);
        }

        // queue other filters so they can be applied later if queued filters are disabled
        foreach ($this->config->getPresentationFilters() as $filter) {
            $table->queueFilter($filter[0], $filter[1]);
        }

        $table->applyQueuedFilters();
    }
}
