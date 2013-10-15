<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\ViewDataTable;

use Piwik\DataTable\Row;
use Piwik\DataTable;
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

    public function getDefaultConfig()
    {
        return new Graph\Config();
    }

    public function getDefaultRequestConfig()
    {
        $config = parent::getDefaultRequestConfig();
        $config->addPropertiesThatShouldBeAvailableClientSide(array('columns'));

        return $config;
    }

    public function configureVisualization()
    {
        if ($this->config->show_goals) {
            $this->config->translations['nb_conversions'] = Piwik::translate('Goals_ColumnConversions');
            $this->config->translations['revenue'] = Piwik::translate('General_TotalRevenue');
        }
    }

    /**
     * Defaults the selectable_columns property if it has not been set and then transforms
     * it into something the SeriesPicker JavaScript class can use.
     */
    public function afterAllFilteresAreApplied()
    {
        $this->selectable_rows = array_values($this->selectableRows);

        $selectableColumns = $this->config->selectable_columns;

        // set default selectable columns, if none specified
        if ($selectableColumns === false) {
            $selectableColumns = array('nb_visits', 'nb_actions');

            if (in_array('nb_uniq_visitors', $this->dataTable->getColumns())) {
                $selectableColumns[] = 'nb_uniq_visitors';
            }
        }

        if ($this->config->show_goals) {
            $goalMetrics = array('nb_conversions', 'revenue');
            $selectableColumns = array_merge($selectableColumns, $goalMetrics);
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

    /**
     * Determines what rows are selectable and stores them in the selectable_rows property in
     * a format the SeriesPicker JavaScript class can use.
     */
    public function beforeLoadDataTable()
    {
        // TODO: this should not be required here. filter_limit should not be a view property, instead HtmlTable should use 'limit' or something,
        //       and manually set request_parameters_to_modify['filter_limit'] based on that. (same for filter_offset).
        $this->requestConfig->request_parameters_to_modify['filter_limit'] = false;

        if ($this->config->max_graph_elements) {
            $this->requestConfig->request_parameters_to_modify['filter_truncate'] = $this->config->max_graph_elements - 1;
        }

        if ($this->config->row_picker_match_rows_by === false) {
            return;
        }
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        // collect all selectable rows
        $self = $this;
        $properties = $this->config;

        $this->dataTable->filter(function ($dataTable) use ($self, $properties) {
            foreach ($dataTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');
                if ($rowLabel === false) {
                    continue;
                }

                // determine whether row is visible
                $isVisible = true;
                if ($properties->row_picker_match_rows_by == 'label') {
                    $isVisible = in_array($rowLabel, $properties->rows_to_display);
                }

                // build config
                if (!isset($self->selectableRows[$rowLabel])) {
                    $self->selectableRows[$rowLabel] = array(
                        'label'     => $rowLabel,
                        'matcher'   => $rowLabel,
                        'displayed' => $isVisible
                    );
                }
            }
        });
    }
}