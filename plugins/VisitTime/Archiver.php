<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

class Piwik_VisitTime_Archiver extends Piwik_PluginsArchiver
{
    public function archiveDay()
    {
        $this->aggregateByLocalTime();
        $this->aggregateByServerTime();
    }

    protected function aggregateByServerTime()
    {
        $metricsByServerTime = $this->getProcessor()->getMetricsForLabel("HOUR(log_visit.visit_last_action_time)");
        $query = $this->getProcessor()->queryConversionsByDimension("HOUR(log_conversion.server_time)");

        if ($query === false) return;

        while ($row = $query->fetch()) {
            if (!isset($metricsByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
                $metricsByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $this->getProcessor()->makeEmptyGoalRow($row['idgoal']);
            }
            $this->getProcessor()->sumGoalMetrics($row, $metricsByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
        }
        $this->getProcessor()->enrichConversionsByLabelArray($metricsByServerTime);

        $metricsByServerTime = $this->convertServerTimeToLocalTimezone($metricsByServerTime);
        $tableServerTime = $this->getProcessor()->getDataTableFromArray($metricsByServerTime);
        $this->makeSureAllHoursAreSet($tableServerTime);
        $this->getProcessor()->insertBlobRecord('VisitTime_serverTime', $tableServerTime->getSerialized());
    }

    protected function aggregateByLocalTime()
    {
        $metricsByLocalTime = $this->getProcessor()->getMetricsForLabel("HOUR(log_visit.visitor_localtime)");
        $tableLocalTime = $this->getProcessor()->getDataTableFromArray($metricsByLocalTime);
        $this->makeSureAllHoursAreSet($tableLocalTime);
        $this->getProcessor()->insertBlobRecord('VisitTime_localTime', $tableLocalTime->getSerialized());
    }

    protected function convertServerTimeToLocalTimezone($metricsByServerTime)
    {
        $date = Piwik_Date::factory($this->getProcessor()->getStartDatetimeUTC())->toString();
        $timezone = $this->getProcessor()->site->getTimezone();
        $visitsByHourTz = array();
        foreach ($metricsByServerTime as $hour => $stats) {
            $datetime = $date . ' ' . $hour . ':00:00';
            $hourInTz = (int)Piwik_Date::factory($datetime, $timezone)->toString('H');
            $visitsByHourTz[$hourInTz] = $stats;
        }
        return $visitsByHourTz;
    }


    private function makeSureAllHoursAreSet($table)
    {
        for ($i = 0; $i <= 23; $i++) {
            if ($table->getRowFromLabel($i) === false) {
                $row = $this->getProcessor()->makeEmptyRowLabeled($i);
                $table->addRow($row);
            }
        }
    }

    public function archivePeriod()
    {
        $dataTableToSum = array(
            'VisitTime_localTime',
            'VisitTime_serverTime',
        );
        $this->getProcessor()->archiveDataTable($dataTableToSum);
    }
}