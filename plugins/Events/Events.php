<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

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
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $translations = array_merge($translations, $this->getMetricTranslations());
    }

    public function getMetricDocumentation()
    {
        return array(
            'nb_events'            => 'Events_TotalEventsDocumentation',
            'sum_event_value'      => 'Events_TotalValueDocumentation',
            'min_event_value'      => 'Events_MinValueDocumentation',
            'max_event_value'      => 'Events_MaxValueDocumentation',
            'nb_events_with_value' => 'Events_EventsWithValueDocumentation',
        );
    }

    protected function getMetricTranslations()
    {
        return array(
            'nb_events'            => 'Events_TotalEvents',
            'sum_event_value'      => 'Events_TotalValue',
            'min_event_value'      => 'Events_MinValue',
            'max_event_value'      => 'Events_MaxValue',
            'nb_events_with_value' => 'Events_EventsWithValue',
        );
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

        // Translate
        $callback = array('\\Piwik\\Piwik', 'translate');
        $metrics = array_map($callback, $metrics);
        $documentation = array_map($callback, $documentation);

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
     * Given Events.getCategory, returns the translations to use
     *
     * @param $apiReport
     * @throws \Exception
     * @return array
     */
    protected function getLabelTranslation($apiReport)
    {
        $labels = $this->getLabelTranslations();
        foreach($labels as $action => $translations) {
            $action = 'Events.' . $action;
            if($apiReport == $action) {
                return $translations;
            }
        }
        throw new \Exception("Translation not found for report $apiReport");
    }

    /**
     * @return array
     */
    protected function getLabelTranslations()
    {
        return array(
            'getCategory' => array('Events_EventCategories', 'Events_EventCategory'),
            'getAction'   => array('Events_EventActions', 'Events_EventAction'),
            'getName'     => array('Events_EventNames', 'Events_EventName'),
        );
    }

}
