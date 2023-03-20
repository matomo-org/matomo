<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable\AllColumns;

class Events extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Metrics.getDefaultMetricDocumentationTranslations' => 'addMetricDocumentationTranslations',
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Metrics.getDefaultMetricSemanticTypes' => 'addMetricSemanticTypes',
            'ViewDataTable.configure'   => 'configureViewDataTable',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Actions.getCustomActionDimensionFieldsAndJoins' => 'provideActionDimensionFields'
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $translations = array_merge($translations, $this->getMetricTranslations());
    }

    public function addMetricDocumentationTranslations(&$translations)
    {
        $translations = array_merge($translations, $this->getMetricDocumentation());
    }

    public function addMetricSemanticTypes(array &$types): void
    {
        $metricTypes = array(
            'nb_events'            => Dimension::TYPE_NUMBER,
            'sum_event_value'      => Dimension::TYPE_NUMBER,
            'min_event_value'      => Dimension::TYPE_NUMBER,
            'max_event_value'      => Dimension::TYPE_NUMBER,
            'nb_events_with_value' => Dimension::TYPE_NUMBER,
        );
        $types = array_merge($types, $metricTypes);
    }

    public function getMetricDocumentation()
    {
        $documentation = array(
            'nb_events'            => 'Events_TotalEventsDocumentation',
            'sum_event_value'      => 'Events_TotalValueDocumentation',
            'min_event_value'      => 'Events_MinValueDocumentation',
            'max_event_value'      => 'Events_MaxValueDocumentation',
            'avg_event_value'      => 'Events_AvgValueDocumentation',
            'nb_events_with_value' => 'Events_EventsWithValueDocumentation',
        );
        $documentation = array_map(array('\\Piwik\\Piwik', 'translate'), $documentation);
        return $documentation;
    }

    public function getMetricTranslations()
    {
        $metrics = array(
            'nb_events'            => 'Events_Events',
            'sum_event_value'      => 'Events_EventValue',
            'min_event_value'      => 'Events_MinValue',
            'max_event_value'      => 'Events_MaxValue',
            'avg_event_value'      => 'Events_AvgValue',
            'nb_events_with_value' => 'Events_EventsWithValue',
        );
        $metrics = array_map(array('\\Piwik\\Piwik', 'translate'), $metrics);
        return $metrics;
    }

    public $metadataDimensions = array(
        'eventCategory' => array('Events_EventCategory', 'log_link_visit_action.idaction_event_category'),
        'eventAction'   => array('Events_EventAction', 'log_link_visit_action.idaction_event_action'),
        'eventName'     => array('Events_EventName', 'log_link_visit_action.idaction_name'),
    );

    public function getDimensionLabel($dimension)
    {
        return Piwik::translate($this->metadataDimensions[$dimension][0]);
    }
    /**
     * @return array
     */
    public static function getLabelTranslations()
    {
        return array(
            'getCategory' => array('Events_EventCategories', 'Events_EventCategory'),
            'getAction'   => array('Events_EventActions', 'Events_EventAction'),
            'getName'     => array('Events_EventNames', 'Events_EventName'),
        );
    }

    /**
     * Given getCategory, returns "Event Categories"
     *
     * @param $apiMethod
     * @return string
     */
    public function getReportTitleTranslation($apiMethod)
    {
        return $this->getTranslation($apiMethod, $index = 0);
    }

    /**
     * Given getCategory, returns "Event Category"
     *
     * @param $apiMethod
     * @return string
     */
    public function getColumnTranslation($apiMethod)
    {
        return $this->getTranslation($apiMethod, $index = 1);
    }

    protected function getTranslation($apiMethod, $index)
    {
        $labels = $this->getLabelTranslations();
        foreach ($labels as $action => $translations) {
            // Events.getActionFromCategoryId returns translation for Events.getAction
            if (strpos($apiMethod, $action) === 0) {
                $columnLabel = $translations[$index];
                return Piwik::translate($columnLabel);
            }
        }
        throw new \Exception("Translation not found for report $apiMethod");
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        if ($view->requestConfig->getApiModuleToRequest() != 'Events') {
            return;
        }

        // eg. 'Events.getCategory'
        $apiMethod = $view->requestConfig->getApiMethodToRequest();

        $secondaryDimension = $this->getSecondaryDimensionFromRequest();
        $view->config->subtable_controller_action = API::getInstance()->getActionToLoadSubtables($apiMethod, $secondaryDimension);

        $pivotBy = Common::getRequestVar('pivotBy', false);
        if (empty($pivotBy)) {
            $view->config->columns_to_display = array('label', 'nb_events', 'sum_event_value');
        }

        $view->config->show_flatten_table = true;
        $view->requestConfig->filter_sort_column = 'nb_events';

        if ($view->isViewDataTableId(AllColumns::ID)) {
            $view->config->filters[] = function (DataTable $table) use ($view) {
                $columsToDisplay = array('label');

                $columns = $table->getColumns();
                if (in_array('nb_visits', $columns)) {
                    $columsToDisplay[] = 'nb_visits';
                }

                if (in_array('nb_uniq_visitors', $columns)) {
                    $columsToDisplay[] = 'nb_uniq_visitors';
                }

                $view->config->columns_to_display = array_merge($columsToDisplay, array('nb_events', 'sum_event_value', 'avg_event_value', 'min_event_value', 'max_event_value'));

                if (!in_array($view->requestConfig->filter_sort_column, $view->config->columns_to_display)) {
                    $view->requestConfig->filter_sort_column = 'nb_events';
                }
            };
            $view->config->show_pivot_by_subtable = false;
        }

        $labelTranslation = $this->getColumnTranslation($apiMethod);
        $view->config->addTranslation('label', $labelTranslation);
        $view->config->addTranslations($this->getMetricTranslations());
        $this->addRelatedReports($view, $secondaryDimension);
        $this->addTooltipEventValue($view);

        $subtableReport = ReportsProvider::factory('Events', $view->config->subtable_controller_action);
        $view->config->pivot_by_dimension = $subtableReport->getDimension()->getId();
        $view->config->pivot_by_column = 'nb_events';
    }

    private function addRelatedReports($view, $secondaryDimension)
    {
        if (empty($secondaryDimension)) {
            // eg. Row Evolution
            return;
        }

        $view->config->show_related_reports = true;

        $apiMethod = $view->requestConfig->getApiMethodToRequest();
        $secondaryDimensions = API::getInstance()->getSecondaryDimensions($apiMethod);

        if (empty($secondaryDimensions)) {
            return;
        }

        $secondaryDimensionTranslation = $this->getDimensionLabel($secondaryDimension);
        $view->config->related_reports_title =
            Piwik::translate('Events_SecondaryDimension', $secondaryDimensionTranslation)
            . "<br/>"
            . Piwik::translate('Events_SwitchToSecondaryDimension', '');

        foreach($secondaryDimensions as $dimension) {
            if ($dimension == $secondaryDimension) {
                // don't show as related report the currently selected dimension
                continue;
            }

            $dimensionTranslation = $this->getDimensionLabel($dimension);
            $view->config->addRelatedReport(
                $view->requestConfig->apiMethodToRequestDataTable,
                $dimensionTranslation,
                array('secondaryDimension' => $dimension)
            );
        }

    }

    private function addTooltipEventValue(ViewDataTable $view)
    {
        // Creates the tooltip message for Event Value column
        $tooltipCallback = function ($hits, $min, $max, $avg) {
            if (!$hits) {
                return false;
            }

            $avg = $avg ?: 0;

            $msgEventMinMax = Piwik::translate("Events_EventValueTooltip", array($hits, "<br />", $min, $max));
            $msgEventAvg = Piwik::translate("Events_AvgEventValue", $avg);
            return $msgEventMinMax . "<br/>" . $msgEventAvg;
        };

        // Add tooltip metadata column to the DataTable
        $view->config->filters[] = array('ColumnCallbackAddMetadata',
            array(
                array(
                    'nb_events',
                    'min_event_value',
                    'max_event_value',
                    'avg_event_value'
                ),
                'sum_event_value_tooltip',
                $tooltipCallback
            )
        );
    }

    /**
     * @return mixed
     */
    public function getSecondaryDimensionFromRequest()
    {
        return Common::getRequestVar('secondaryDimension', false, 'string');
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Events/stylesheets/datatable.less";
    }

    public function provideActionDimensionFields(&$fields, &$joins)
    {
        $fields[] = 'log_action_event_category.type AS eventType';
        $fields[] = 'log_action_event_category.name AS eventCategory';
        $fields[] = 'log_action_event_action.name as eventAction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_event_action
					ON  log_link_visit_action.idaction_event_action = log_action_event_action.idaction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_event_category
					ON  log_link_visit_action.idaction_event_category = log_action_event_category.idaction';
    }
}
