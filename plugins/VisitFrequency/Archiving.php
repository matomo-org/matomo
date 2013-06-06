<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

class Piwik_VisitFrequency_Archiving
{
    // OMG THIS IS SO WRONG!
    // use segment instead
    public function archiveDay($archiveProcessing)
    {
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

    public function archivePeriod($archiveProcessing)
    {
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
}