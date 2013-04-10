<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitFrequency
 */

/**
 *
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('VisitFrequency_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
        return $info;
    }

    function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenu',
            'API.getReportMetadata'            => 'getReportMetadata',
        );
        return $hooks;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();
        $reports[] = array(
            'category'         => Piwik_Translate('General_Visitors'),
            'name'             => Piwik_Translate('VisitFrequency_ColumnReturningVisits'),
            'module'           => 'VisitFrequency',
            'action'           => 'get',
            'metrics'          => array(
                'nb_visits_returning'            => Piwik_Translate('VisitFrequency_ColumnReturningVisits'),
                'nb_actions_returning'           => Piwik_Translate('VisitFrequency_ColumnActionsByReturningVisits'),
                'avg_time_on_site_returning'     => Piwik_Translate('VisitFrequency_ColumnAverageVisitDurationForReturningVisitors'),
                'bounce_rate_returning'          => Piwik_Translate('VisitFrequency_ColumnBounceRateForReturningVisits'),
                'nb_actions_per_visit_returning' => Piwik_Translate('VisitFrequency_ColumnAvgActionsPerReturningVisit'),
                'nb_uniq_visitors_returning'     => Piwik_Translate('VisitFrequency_ColumnUniqueReturningVisitors'),
// Not displayed
//    			'nb_visits_converted_returning',
//    			'sum_visit_length_returning',
//    			'max_actions_returning',
//    			'bounce_count_returning',
            ),
            'processedMetrics' => false,
            'order'            => 40
        );
    }

    function addWidgets()
    {
        Piwik_AddWidget('General_Visitors', 'VisitFrequency_WidgetOverview', 'VisitFrequency', 'getSparklines');
        Piwik_AddWidget('General_Visitors', 'VisitFrequency_WidgetGraphReturning', 'VisitFrequency', 'getEvolutionGraph', array('columns' => array('nb_visits_returning')));
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'VisitFrequency_SubmenuFrequency', array('module' => 'VisitFrequency', 'action' => 'index'));
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    function archivePeriod($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $numericToSum = array(
            'nb_visits_returning',
            'nb_actions_returning',
            'sum_visit_length_returning',
            'bounce_count_returning',
            'nb_visits_converted_returning',
        );
        $archiveProcessing->archiveNumericValuesSum($numericToSum);
        $archiveProcessing->archiveNumericValuesMax('max_actions_returning');
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    function archiveDay($notification)
    {
        /* @var $archiveProcessing Piwik_ArchiveProcessing */
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $select = "count(distinct log_visit.idvisitor) as nb_uniq_visitors_returning,
				count(*) as nb_visits_returning,
				sum(log_visit.visit_total_actions) as nb_actions_returning,
				max(log_visit.visit_total_actions) as max_actions_returning,
				sum(log_visit.visit_total_time) as sum_visit_length_returning,
				sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as bounce_count_returning,
				sum(case log_visit.visit_goal_converted when 1 then 1 else 0 end) as nb_visits_converted_returning";

        $from = "log_visit";

        $where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
		 		AND log_visit.idsite = ?
		 		AND log_visit.visitor_returning >= 1";

        $bind = array($archiveProcessing->getStartDatetimeUTC(),
                      $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite);

        $query = $archiveProcessing->getSegment()->getSelectQuery($select, $from, $where, $bind);

        $row = $archiveProcessing->db->fetchRow($query['sql'], $query['bind']);

        if ($row === false || $row === null) {
            $row['nb_visits_returning'] = 0;
            $row['nb_actions_returning'] = 0;
            $row['max_actions_returning'] = 0;
            $row['sum_visit_length_returning'] = 0;
            $row['bounce_count_returning'] = 0;
            $row['nb_visits_converted_returning'] = 0;
        }

        foreach ($row as $name => $value) {
            $archiveProcessing->insertNumericRecord($name, $value);
        }
    }
}

