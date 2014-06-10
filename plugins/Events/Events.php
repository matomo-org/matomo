<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

/**
 */
class Events extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'API.getSegmentDimensionMetadata'       => 'getSegmentsMetadata',
            'Metrics.getDefaultMetricTranslations'  => 'addMetricTranslations',
            'API.getReportMetadata'                 => 'getReportMetadata',
            'ViewDataTable.configure'               => 'configureViewDataTable',

        );
    }

    public function addMetricTranslations(&$translations)
    {
        $translations = array_merge($translations, $this->getMetricTranslations());
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
            'nb_events'            => 'Events_TotalEvents',
            'sum_event_value'      => 'Events_TotalValue',
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
    static public function getLabelTranslations()
    {
        return array(
            'getCategory' => array('Events_EventCategories', 'Events_EventCategory'),
            'getAction'   => array('Events_EventActions', 'Events_EventAction'),
            'getName'     => array('Events_EventNames', 'Events_EventName'),
        );
    }

    public function getSegmentsMetadata(&$segments)
    {
        $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

        foreach($this->metadataDimensions as $dimension => $metadata) {
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'Events_Events',
                'name'       => $metadata[0],
                'segment'    => $dimension,
                'sqlSegment' => $metadata[1],
                'sqlFilter'  => $sqlFilter,
            );
        }
        $segments[] = array(
            'type'           => 'metric',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => 'Events_TotalEvents',
            'segment'        => 'events',
            'sqlSegment'     => 'log_visit.visit_total_events',
            'acceptedValues' => 'To select all visits who triggered an Event, use: &segment=events>0',
        );
//        $segments[] = array(
//            'type'           => 'metric',
//            'category'       => 'Events_Events',
//            'name'           => 'Events_EventValue',
//            'segment'        => 'eventValue',
//            'sqlSegment'     => 'log_link_visit_action.custom_float',
//            'sqlFilter'      => '\\Piwik\\Plugins\\Events\\Events::getSegmentEventValue'
//        );
    }
//
//    public static function getSegmentEventValue($valueToMatch, $sqlField, $matchType, $segmentName)
//    {
//        $andActionisNotEvent = \Piwik\Plugins\Actions\Archiver::getWhereClauseActionIsNotEvent();
//        $andActionisEvent = str_replace("IS NULL", "IS NOT NULL", $andActionisNotEvent);
//
//        return array(
//            'extraWhere' => $andActionisEvent,
//            'bind' => $valueToMatch
//        );
//    }

    public function getReportMetadata(&$reports)
    {
        $metrics = $this->getMetricTranslations();
        $documentation = $this->getMetricDocumentation();
        $labelTranslations = $this->getLabelTranslations();

        $order = 0;
        foreach($labelTranslations as $action => $translations) {
            $secondaryDimension = $this->getSecondaryDimensionFromRequest();
            $actionToLoadSubtables = API::getInstance()->getActionToLoadSubtables($action, $secondaryDimension);
            $reports[] = array(
                'category'              => Piwik::translate('Events_Events'),
                'name'                  => Piwik::translate($translations[0]),
                'module'                => 'Events',
                'action'                => $action,
                'dimension'             => Piwik::translate($translations[1]),
                'metrics'               => $metrics,
                'metricsDocumentation'  => $documentation,
                'processedMetrics'      => false,
                'actionToLoadSubTables' => $actionToLoadSubtables,
                'order'                 => $order++
            );

        }
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
        if($view->requestConfig->getApiModuleToRequest() != 'Events') {
            return;
        }

        // eg. 'Events.getCategory'
        $apiMethod = $view->requestConfig->getApiMethodToRequest();

        $secondaryDimension = $this->getSecondaryDimensionFromRequest();
        $view->config->subtable_controller_action = API::getInstance()->getActionToLoadSubtables($apiMethod, $secondaryDimension);
        $view->config->columns_to_display = array('label', 'nb_events', 'sum_event_value');
        $view->config->show_flatten_table = true;
        $view->config->show_table_all_columns = false;
        $view->requestConfig->filter_sort_column = 'nb_events';

        $labelTranslation = $this->getColumnTranslation($apiMethod);
        $view->config->addTranslation('label', $labelTranslation);
        $view->config->addTranslations($this->getMetricTranslations());
        $this->addRelatedReports($view, $secondaryDimension);
        $this->addTooltipEventValue($view);
    }

    protected function addRelatedReports($view, $secondaryDimension)
    {
        if(empty($secondaryDimension)) {
            // eg. Row Evolution
            return;
        }
        $view->config->show_related_reports = true;

        $apiMethod = $view->requestConfig->getApiMethodToRequest();
        $secondaryDimensions = API::getInstance()->getSecondaryDimensions($apiMethod);

        if(empty($secondaryDimensions)) {
            return;
        }

        $secondaryDimensionTranslation = $this->getDimensionLabel($secondaryDimension);
        $view->config->related_reports_title =
            Piwik::translate('Events_SecondaryDimension', $secondaryDimensionTranslation)
            . "<br/>"
            . Piwik::translate('Events_SwitchToSecondaryDimension', '');

        foreach($secondaryDimensions as $dimension) {
            if($dimension == $secondaryDimension) {
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

    protected function addTooltipEventValue($view)
    {
        // Creates the tooltip message for Event Value column
        $tooltipCallback = function ($hits, $min, $max, $avg) {
            if (!$hits) {
                return false;
            }
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
    protected function getSecondaryDimensionFromRequest()
    {
        return Common::getRequestVar('secondaryDimension', false, 'string');
    }
}
