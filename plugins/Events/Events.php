<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\WidgetsList;

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
            'Menu.Reporting.addItems'               => 'addMenus',
            'WidgetsList.addWidgets'                => 'addWidgets',
        );
    }

    public function addWidgets()
    {
        foreach(self::getLabelTranslations() as $apiMethod => $labels) {
            WidgetsList::add('Events_Events', $labels[0], 'Events', $apiMethod);
        }
    }

    public function addMenus()
    {
        MenuMain::getInstance()->add('General_Actions', 'Events_Events', array('module' => 'Events', 'action' => 'index'), true, 20);
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

    protected function getMetricTranslations()
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

    public function getSegmentsMetadata(&$segments)
    {
        $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Events_Events',
            'name'       => 'Events_EventCategory',
            'segment'    => 'eventCategory',
            'sqlSegment' => 'log_link_visit_action.idaction_event_category',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Events_Events',
            'name'       => 'Events_EventAction',
            'segment'    => 'eventAction',
            'sqlSegment' => 'log_link_visit_action.idaction_event_action',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'Events_Events',
            'name'       => 'Events_EventName',
            'segment'    => 'eventName',
            'sqlSegment' => 'log_link_visit_action.idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
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
            $reports[] = array(
                'category'              => Piwik::translate('Events_Events'),
                'name'                  => Piwik::translate($translations[0]),
                'module'                => 'Events',
                'action'                => $action,
                'dimension'             => Piwik::translate($translations[1]),
                'metrics'               => $metrics,
                'metricsDocumentation'  => $documentation,
                'processedMetrics'      => false,
                'actionToLoadSubTables' => API::getInstance()->getSubtableAction($action),
                'order'                 => $order++
            );

        }
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
    protected function getColumnTranslation($apiMethod)
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
        // eg. 'Events.getCategory'
        $apiMethod = $view->requestConfig->getApiMethodToRequest();

        if($view->requestConfig->getApiModuleToRequest() != 'Events') {
            // this is not an Events apiMethod
            return;
        }

        $view->config->addTranslation('label', $this->getColumnTranslation($apiMethod));
        $view->config->addTranslations($this->getMetricTranslations());
        $view->config->columns_to_display = array('label', 'nb_events', 'sum_event_value');
        $view->config->subtable_controller_action  = API::getInstance()->getSubtableAction($apiMethod);

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
                                             array('nb_events', 'min_event_value', 'max_event_value', 'avg_event_value'),
                                             'sum_event_value_tooltip',
                                             $tooltipCallback
                                         )
        );

        $view->config->custom_parameters = array('flat' => 0);
    }

}
