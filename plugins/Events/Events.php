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
            'nb_events'         => 'Events_TotalEventsDocumentation',
            'sum_event_value'   => 'Events_TotalValueDocumentation',
            'min_event_value'   => 'Events_MinValueDocumentation',
            'max_event_value'   => 'Events_MaxValueDocumentation',
        );
    }

    protected function getMetricTranslations()
    {
        return array(
            'nb_events'       => 'Events_TotalEvents',
            'sum_event_value' => 'Events_TotalValue',
            'min_event_value' => 'Events_MinValue',
            'max_event_value' => 'Events_MaxValue',
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

        $reportsMetadata = array(
            array('Events_EventCategories', 'Events_EventCategory', 'getCategory'),
            array('Events_EventActions', 'Events_EventAction', 'getAction'),
            array('Events_EventNames', 'Events_EventName', 'getName'),
        );

        foreach($reportsMetadata as $order => $reportMeta) {
            $reports[] = array(
                'category'              => Piwik::translate('Events_Events'),
                'name'                  => Piwik::translate($reportMeta[0]),
                'module'                => 'Events',
                'action'                => $reportMeta[2],
                'dimension'             => Piwik::translate($reportMeta[1]),
                'metrics'               => $metrics,
                'metricsDocumentation'  => $documentation,
                'processedMetrics'      => false,
                'actionToLoadSubTables' => API::getInstance()->getSubtableAction($reportMeta[2]),
                'order'                 => $order
            );

        }
    }
}
