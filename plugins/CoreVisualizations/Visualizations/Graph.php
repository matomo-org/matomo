<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\CoreVisualizations\Metrics\Formatter\Numeric;
use Piwik\Piwik;
use Piwik\Plugin\Visualization;

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

        $this->requestConfig->request_parameters_to_modify['format_metrics'] = 1;

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
            $isVisible = in_array($rowLabel, $this->config->rows_to_display);
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

        $this->config->selectable_rows = array_values($this->selectableRows);

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

        // set default selectable columns, if none specified
        $selectableColumns = $this->config->selectable_columns;
        if (false === $selectableColumns) {
            $selectableColumns = array('nb_visits', 'nb_actions', 'nb_uniq_visitors', 'nb_users');

            if ($this->config->show_goals) {
                $goalMetrics       = array('nb_conversions', 'revenue');
                $selectableColumns = array_merge($selectableColumns, $goalMetrics);
            }
        }

        $transformed = array();
        foreach ($selectableColumns as $column) {
            $transformed[] = array(
                'column'      => $column,
                'translation' => @$this->config->translations[$column],
                'displayed'   => in_array($column, $this->config->columns_to_display)
            );
        }

        $this->config->selectable_columns = $transformed;
    }
}
