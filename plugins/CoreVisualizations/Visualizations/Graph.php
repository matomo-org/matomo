<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Plugin\Metric;
use Piwik\Plugins\AbTesting\Columns\Metrics\ProcessedMetric;
use Piwik\Plugins\CoreVisualizations\Metrics\Formatter\Numeric;
use Piwik\Piwik;
use Piwik\Plugin\Visualization;
use Piwik\SettingsPiwik;

/**
 * This is an abstract visualization that should be the base of any 'graph' visualization.
 * This class defines certain visualization properties that are specific to all graph types.
 * Derived visualizations can decide for themselves whether they should support individual
 * properties.
 *
 * @property Graph\Config $config
 */
abstract class Graph extends Visualization
{
    const ID = 'graph';

    public $selectableRows = array();

    public static function getDefaultConfig()
    {
        return new Graph\Config();
    }

    public static function getDefaultRequestConfig()
    {
        $config = parent::getDefaultRequestConfig();
        $config->addPropertiesThatShouldBeAvailableClientSide(array('columns'));

        return $config;
    }

    public function beforeRender()
    {
        if ($this->config->show_goals) {
            $this->config->translations['nb_conversions'] = Piwik::translate('Goals_ColumnConversions');
            $this->config->translations['revenue'] = Piwik::translate('General_TotalRevenue');
        }
    }

    public function beforeLoadDataTable()
    {
        // TODO: this should not be required here. filter_limit should not be a view property, instead HtmlTable should use 'limit' or something,
        //       and manually set request_parameters_to_modify['filter_limit'] based on that. (same for filter_offset).
        $this->requestConfig->request_parameters_to_modify['filter_limit'] = false;

        if ($this->config->max_graph_elements) {
            $this->requestConfig->request_parameters_to_modify['filter_truncate'] = $this->config->max_graph_elements - 1;
        }

        // Only default to formatting metrics if the request hasn't already been set to not format metrics
        if (!isset($this->requestConfig->request_parameters_to_modify['format_metrics'])) {
            $this->requestConfig->request_parameters_to_modify['format_metrics'] = 1;
        }

        // if addTotalRow was called in GenerateGraphHTML, add a row containing totals of
        // different metrics
        if ($this->config->add_total_row) {
            $this->requestConfig->request_parameters_to_modify['totals'] = 1;
            $this->requestConfig->request_parameters_to_modify['keep_totals_row'] = 1;
            $this->requestConfig->request_parameters_to_modify['keep_totals_row_label'] = Piwik::translate('General_Total');
        }

        if (!empty($this->config->columns_to_display)) {
            $metrics = $this->removeUnavailableMetrics($this->config->columns_to_display);
            if (empty($metrics)) {
                if (!empty($this->config->selectable_columns)) {
                    $this->config->columns_to_display = array(reset($this->config->selectable_columns));
                } else {
                    $this->config->columns_to_display = array('nb_visit');
                }
                $this->requestConfig->request_parameters_to_modify['columns'] = 'nb_visits';
                $this->requestConfig->request_parameters_to_modify['columns_to_display'] = 'nb_visits';
            }
        }

        $this->metricsFormatter = new Numeric();
    }

    /**
     * Determines what rows are selectable and stores them in the selectable_rows property in
     * a format the SeriesPicker JavaScript class can use.
     */
    public function determineWhichRowsAreSelectable()
    {
        if ($this->config->row_picker_match_rows_by === false) {
            return;
        }

        // collect all selectable rows
        $self = $this;

        $this->dataTable->filter(function ($dataTable) use ($self) {
            /** @var DataTable $dataTable */

            foreach ($dataTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');

                if (false === $rowLabel) {
                    continue;
                }

                // build config
                if (!isset($self->selectableRows[$rowLabel])) {
                    $self->selectableRows[$rowLabel] = array(
                        'label'     => $rowLabel,
                        'matcher'   => $rowLabel,
                        'displayed' => $self->isRowVisible($rowLabel)
                    );
                }
            }
        });
    }

    public function isRowVisible($rowLabel)
    {
        $isVisible = true;
        if ('label' == $this->config->row_picker_match_rows_by) {
            $isVisible = in_array($rowLabel, $this->config->rows_to_display === false ? [] : $this->config->rows_to_display);
        }

        return $isVisible;
    }

    /**
     * Defaults the selectable_columns property if it has not been set and then transforms
     * it into something the SeriesPicker JavaScript class can use.
     */
    public function afterAllFiltersAreApplied()
    {
        $this->determineWhichRowsAreSelectable();

        // set default selectable columns, if none specified
        $selectableColumns = $this->config->selectable_columns;
        if (false === $selectableColumns) {
            $this->generateSelectableColumns();
        }

        $this->ensureValidColumnsToDisplay();

        $this->addTranslations();

        $this->config->selectable_rows = array_values($this->selectableRows);

    }

    protected function addTranslations()
    {
        if ($this->config->add_total_row) {
            $totalTranslation = Piwik::translate('General_Total');
            $this->config->selectable_rows[] = array(
                'label'     => $totalTranslation,
                'matcher'   => $totalTranslation,
                'displayed' => $this->isRowVisible($totalTranslation)
            );
        }

        if ($this->config->show_goals) {
            $this->config->addTranslations(array(
                'nb_conversions' => Piwik::translate('Goals_ColumnConversions'),
                'revenue'        => Piwik::translate('General_TotalRevenue')
            ));
        }

        $transformed = array();
        foreach ($this->config->selectable_columns as $column) {
            $transformed[] = array(
                'column'      => $column,
                'translation' => @$this->config->translations[$column],
                'displayed'   => in_array($column, $this->config->columns_to_display)
            );
        }
        $this->config->selectable_columns = $transformed;
    }

    protected function generateSelectableColumns()
    {
        $defaultColumns = $this->getDefaultColumnsToDisplay();
        if ($this->config->show_goals) {
            $goalMetrics       = array('nb_conversions', 'revenue');
            $defaultColumns    = array_merge($defaultColumns, $goalMetrics);
        }

        // Use the subset of default columns that are actually present in the datatable
        $allColumns = $this->getDataTable()->getColumns();
        $selectableColumns = array_intersect($defaultColumns, $allColumns);

        // If there are no default columns, just strip out the 'label' column and use all the others
        if (empty($selectableColumns)) {
            $selectableColumns = $this->removeLabelFromArray($allColumns);
        }

        $this->config->selectable_columns = $selectableColumns;
    }

    private function removeLabelFromArray($theArray)
    {
        if (!empty($theArray) && is_array($theArray)) {
            $key = array_search('label', $theArray);
            if ($key !== false) {
                unset($theArray[$key]);
                $theArray = array_values($theArray);
            }
        }

        return $theArray;
    }

    protected function ensureValidColumnsToDisplay()
    {
        $columnsToDisplay = $this->config->columns_to_display;

        // Remove 'label' from columns to display if present
        $columnsToDisplay = $this->removeLabelFromArray($columnsToDisplay);

        // Strip out any columns_to_display that are not in the dataset
        $allColumns = [];
        if ($this->report) {
            $allColumns = $this->report->getAllMetrics();
        }
        $allColumns = array_merge($allColumns, $this->getDataTable()->getColumns());

        $dataTable = $this->getDataTable();
        if ($dataTable instanceof DataTable\Map) {
            $dataTable = $dataTable->getFirstRow();
        }

        /** @var ProcessedMetric[] $extraProcessedMetrics */
        $extraProcessedMetrics = $dataTable->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
        if (!empty($extraProcessedMetrics)) {
            $extraProcessedMetricNames = array_map(function (Metric $m) { return $m->getName(); }, $extraProcessedMetrics);
            $allColumns = array_merge($allColumns, $extraProcessedMetricNames);
        }

        $allColumns = array_unique($allColumns);

        // If the datatable has no data, use the default columns (there must be data for evolution graphs or else nothing displays)
        if (empty($allColumns)) {
            $allColumns = $this->getDefaultColumnsToDisplay();
        }

        $this->config->columns_to_display = $this->removeUnavailableMetrics(array_intersect($columnsToDisplay, $allColumns));
    }

    private function getDefaultColumnsToDisplay()
    {
        return array(
            'nb_visits',
            'nb_actions',
            'nb_uniq_visitors',
            'nb_users'
        );
    }

    private function removeUnavailableMetrics($metrics)
    {
        $currentPeriod = Common::getRequestVar('period', false);

        if (!SettingsPiwik::isUniqueVisitorsEnabled($currentPeriod)) {
            $metrics = array_diff($metrics, ['nb_uniq_visitors', 'nb_users']);
        }

        return $metrics;
    }
}
