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

/**
 *
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('VisitTime_PluginDescription'),
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
            'Goals.getReportsWithGoalMetrics'  => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
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
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_WidgetLocalTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerLocalTime',
            'dimension'         => Piwik_Translate('VisitTime_ColumnLocalTime'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetLocalTimeDocumentation', array('<b>', '</b>')),
            'constantRowsCount' => true,
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_WidgetServerTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerServerTime',
            'dimension'         => Piwik_Translate('VisitTime_ColumnServerTime'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetServerTimeDocumentation', array('<b>', '</b>')),
            'constantRowsCount' => true,
            'order'             => 15,
        );

        $reports[] = array(
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_VisitsByDayOfWeek'),
            'module'            => 'VisitTime',
            'action'            => 'getByDayOfWeek',
            'dimension'         => Piwik_Translate('VisitTime_DayOfWeek'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetByDayOfWeekDocumentation'),
            'constantRowsCount' => true,
            'order'             => 25,
        );
    }

    function addWidgets()
    {
        Piwik_AddWidget('VisitsSummary_VisitsSummary', 'VisitTime_WidgetLocalTime', 'VisitTime', 'getVisitInformationPerLocalTime');
        Piwik_AddWidget('VisitsSummary_VisitsSummary', 'VisitTime_WidgetServerTime', 'VisitTime', 'getVisitInformationPerServerTime');
        Piwik_AddWidget('VisitsSummary_VisitsSummary', 'VisitTime_VisitsByDayOfWeek', 'VisitTime', 'getByDayOfWeek');
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'VisitTime_SubmenuTimes', array('module' => 'VisitTime', 'action' => 'index'));
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getReportsWithGoalMetrics($notification)
    {
        $dimensions =& $notification->getNotificationObject();
        $dimensions[] = array('category' => Piwik_Translate('VisitTime_ColumnServerTime'),
                              'name'     => Piwik_Translate('VisitTime_ColumnServerTime'),
                              'module'   => 'VisitTime',
                              'action'   => 'getVisitInformationPerServerTime',
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
        $acceptedValues = "0, 1, 2, 3, ..., 20, 21, 22, 23";
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => Piwik_Translate('VisitTime_ColumnServerTime'),
            'segment'        => 'visitServerHour',
            'sqlSegment'     => 'HOUR(log_visit.visit_last_action_time)',
            'acceptedValues' => $acceptedValues
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => Piwik_Translate('VisitTime_ColumnLocalTime'),
            'segment'        => 'visitLocalHour',
            'sqlSegment'     => 'HOUR(log_visit.visitor_localtime)',
            'acceptedValues' => $acceptedValues
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    function archivePeriod($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $dataTableToSum = array(
            'VisitTime_localTime',
            'VisitTime_serverTime',
        );
        $archiveProcessing->archiveDataTable($dataTableToSum);
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    public function archiveDay($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $this->archiveDayAggregateVisits($archiveProcessing);
        $this->archiveDayAggregateGoals($archiveProcessing);
        $this->archiveDayRecordInDatabase($archiveProcessing);
    }

    protected function archiveDayAggregateVisits($archiveProcessing)
    {
        $labelSQL = "HOUR(log_visit.visitor_localtime)";
        $this->interestByLocalTime = $archiveProcessing->getArrayInterestForLabel($labelSQL);

        $labelSQL = "HOUR(log_visit.visit_last_action_time)";
        $this->interestByServerTime = $archiveProcessing->getArrayInterestForLabel($labelSQL);
    }

    protected function convertServerTimeToLocalTimezone($interestByServerTime, $archiveProcessing)
    {
        $date = Piwik_Date::factory($archiveProcessing->getStartDatetimeUTC())->toString();
        $timezone = $archiveProcessing->site->getTimezone();
        $visitsByHourTz = array();
        foreach ($interestByServerTime as $hour => $stats) {
            $datetime = $date . ' ' . $hour . ':00:00';
            $hourInTz = (int)Piwik_Date::factory($datetime, $timezone)->toString('H');
            $visitsByHourTz[$hourInTz] = $stats;
        }
        return $visitsByHourTz;
    }

    protected function archiveDayAggregateGoals($archiveProcessing)
    {
        $query = $archiveProcessing->queryConversionsByDimension("HOUR(log_conversion.server_time)");

        if ($query === false) return;

        while ($row = $query->fetch()) {
            if (!isset($this->interestByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow($row['idgoal']);
            $archiveProcessing->updateGoalStats($row, $this->interestByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
        }
        $goalByServerTime = $this->convertServerTimeToLocalTimezone($this->interestByServerTime, $archiveProcessing);
        $archiveProcessing->enrichConversionsByLabelArray($this->interestByServerTime);
    }

    protected function archiveDayRecordInDatabase($archiveProcessing)
    {
        $tableLocalTime = $archiveProcessing->getDataTableFromArray($this->interestByLocalTime);
        $this->makeSureAllHoursAreSet($tableLocalTime, $archiveProcessing);
        $archiveProcessing->insertBlobRecord('VisitTime_localTime', $tableLocalTime->getSerialized());
        destroy($tableLocalTime);

        $this->interestByServerTime = $this->convertServerTimeToLocalTimezone($this->interestByServerTime, $archiveProcessing);
        $tableServerTime = $archiveProcessing->getDataTableFromArray($this->interestByServerTime);
        $this->makeSureAllHoursAreSet($tableServerTime, $archiveProcessing);
        $archiveProcessing->insertBlobRecord('VisitTime_serverTime', $tableServerTime->getSerialized());
        destroy($tableServerTime);
    }

    private function makeSureAllHoursAreSet($table, $archiveProcessing)
    {
        for ($i = 0; $i <= 23; $i++) {
            if ($table->getRowFromLabel($i) === false) {
                $row = $archiveProcessing->getNewInterestRowLabeled($i);
                $table->addRow($row);
            }
        }
    }
}

