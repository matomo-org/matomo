<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;
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
    public const ID = 'graph';

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
            [$conversionsColumn, $revenueColumn] = $this->getGoalMetricColumns();
            $this->config->translations[$conversionsColumn] = Piwik::translate('Goals_ColumnConversions');
            $this->config->translations[$revenueColumn] = Piwik::translate('General_TotalRevenue');
        }
    }

    public function beforeLoadDataTable()
    {
        $idGoal = $this->getSpecificGoalId();
        if (false !== $idGoal) {
            // For a specific goal, the aggregated nb_conversions / revenue columns sum conversions
            // across all goals. Trigger the goal-column processing (as the goals table does) so the
            // per-goal goal_<idGoal>_nb_conversions / goal_<idGoal>_revenue columns are available,
            // and chart those instead of the aggregated ones.
            $this->requestConfig->request_parameters_to_modify['filter_update_columns_when_show_all_goals'] = $idGoal;
            $this->requestConfig->request_parameters_to_modify['filter_show_goal_columns_process_goals'] = $idGoal;

            $this->config->columns_to_display = $this->replaceAggregatedGoalColumns($this->config->columns_to_display, $idGoal);
        }

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
    public function determineWhichRowsAreSelectable(): void
    {
        if ($this->config->row_picker_match_rows_by === false) {
            return;
        }

        // collect all selectable rows
        $self = $this;

        $this->dataTable->filter(function (DataTable $dataTable) use ($self) {

            $identifier = $self->config->row_picker_match_rows_by;

            foreach ($dataTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');
                $rowIdentifier = $row->hasColumn($identifier) ? $row->getColumn($identifier) : $row->getMetadata($identifier);

                if (false === $rowLabel || false === $rowIdentifier) {
                    continue;
                }

                $rowIdentifier = (string) $rowIdentifier; // ensure we always have the same type

                // build config
                if (!isset($self->selectableRows[$rowIdentifier])) {
                    $self->selectableRows[$rowIdentifier] = [
                        'label'     => $rowLabel,
                        'matcher'   => $rowIdentifier,
                        'displayed' => $self->isRowVisible($rowLabel, $rowIdentifier),
                    ];
                }
            }
        });
    }

    public function isRowVisible($rowLabel, $rowIdentifier): bool
    {
        if (false !== $this->config->row_picker_match_rows_by) {
            return is_array($this->config->rows_to_display) &&
                (in_array($rowLabel, $this->config->rows_to_display) || in_array($rowIdentifier, $this->config->rows_to_display));
        }

        return true;
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

    protected function addTranslations(): void
    {
        if ($this->config->add_total_row) {
            $totalTranslation = Piwik::translate('General_Total');
            $this->selectableRows['total'] = [
                'label'     => $totalTranslation,
                'matcher'   => 'total',
                'displayed' => $this->isRowVisible($totalTranslation, 'total'),
            ];
        }

        if ($this->config->show_goals) {
            [$conversionsColumn, $revenueColumn] = $this->getGoalMetricColumns();
            $this->config->addTranslations([
                $conversionsColumn => Piwik::translate('Goals_ColumnConversions'),
                $revenueColumn     => Piwik::translate('General_TotalRevenue'),
            ]);
        }

        $transformed = [];
        foreach ($this->config->selectable_columns as $column) {
            $transformed[] = [
                'column'      => $column,
                'translation' => @$this->config->translations[$column],
                'displayed'   => in_array($column, $this->config->columns_to_display),
            ];
        }
        $this->config->selectable_columns = $transformed;
    }

    protected function generateSelectableColumns()
    {
        $defaultColumns = $this->getDefaultColumnsToDisplay();
        if ($this->config->show_goals) {
            $defaultColumns = array_merge($defaultColumns, $this->getGoalMetricColumns());
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
            $extraProcessedMetricNames = array_map(function (Metric $m) {
                return $m->getName();
            }, $extraProcessedMetrics);
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
            'nb_users',
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

    /**
     * Returns the goal conversion and revenue columns the graph should offer and chart.
     *
     * When a specific goal is selected these are the per-goal columns
     * (goal_<idGoal>_nb_conversions / goal_<idGoal>_revenue), otherwise the aggregated
     * nb_conversions / revenue columns that sum every goal.
     *
     * @return string[] [$conversionsColumn, $revenueColumn]
     */
    protected function getGoalMetricColumns(): array
    {
        $idGoal = $this->getSpecificGoalId();

        if (false === $idGoal) {
            return ['nb_conversions', 'revenue'];
        }

        return [
            $this->makeGoalColumn($idGoal, 'nb_conversions'),
            $this->makeGoalColumn($idGoal, 'revenue'),
        ];
    }

    /**
     * Replaces the aggregated goal columns in the given list with the per-goal columns
     * for the selected goal, leaving all other columns untouched.
     */
    private function replaceAggregatedGoalColumns(array $columns, $idGoal): array
    {
        $map = [
            'nb_conversions' => $this->makeGoalColumn($idGoal, 'nb_conversions'),
            'revenue'        => $this->makeGoalColumn($idGoal, 'revenue'),
        ];

        foreach ($columns as $key => $column) {
            if (isset($map[$column])) {
                $columns[$key] = $map[$column];
            }
        }

        return $columns;
    }

    private function makeGoalColumn($idGoal, string $column): string
    {
        return 'goal_' . $idGoal . '_' . $column;
    }

    /**
     * Returns the id of the currently selected goal when the graph should chart that goal's
     * specific conversion/revenue columns instead of the aggregated ones, or false otherwise.
     *
     * Returns false when goals are not shown, when no specific goal is selected (eg, the goals
     * overview or the full goals table), or when the report exposes its goal metrics through a
     * different set of columns (eg, Actions page/entry page reports).
     *
     * @return int|string|false
     */
    protected function getSpecificGoalId()
    {
        if (!$this->config->show_goals) {
            return false;
        }

        $idGoal = Common::getRequestVar('idGoal', '', 'string');

        // A specific site goal (positive id) or the ecommerce order goal, both of which expose
        // goal_<idGoal>_nb_conversions / goal_<idGoal>_revenue columns. The goals overview, the full
        // goals table and the abandoned cart goal are intentionally excluded.
        $isSpecificGoal = (is_numeric($idGoal) && (int) $idGoal > 0)
            || $idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;

        if (!$isSpecificGoal) {
            return false;
        }

        // Only the "normal" per-goal columns (goal_<idGoal>_nb_conversions / _revenue) are handled
        // here. Actions page/entry reports expose goal metrics through different columns and are
        // left untouched.
        $requestMethod = $this->requestConfig->getApiModuleToRequest() . '.' . $this->requestConfig->getApiMethodToRequest();
        if (AddColumnsProcessedMetricsGoal::getProcessOnlyIdGoalToUseForReport($idGoal, $requestMethod) !== $idGoal) {
            return false;
        }

        // Normalise numeric goal ids to int so the built column names are canonical
        // (eg, "goal_1_nb_conversions"), matching how goal columns are keyed elsewhere.
        return is_numeric($idGoal) ? (int) $idGoal : $idGoal;
    }
}
