<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorInterest
 */

/**
 *
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('VisitorInterest_PluginDescription'),
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
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_WidgetLengths'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerVisitDuration',
            'dimension'         => Piwik_Translate('VisitorInterest_ColumnVisitDuration'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetLengthsDocumentation')
                . '<br />' . Piwik_Translate('General_ChangeTagCloudView'),
            'order'             => 15
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_WidgetPages'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerPage',
            'dimension'         => Piwik_Translate('VisitorInterest_ColumnPagesPerVisit'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetPagesDocumentation')
                . '<br />' . Piwik_Translate('General_ChangeTagCloudView'),
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_visitsByVisitCount'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByVisitCount',
            'dimension'         => Piwik_Translate('VisitorInterest_visitsByVisitCount'),
            'metrics'           => array(
                'nb_visits',
                'nb_visits_percentage' => Piwik_Translate('General_ColumnPercentageVisits')
            ),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetVisitsByNumDocumentation')
                . '<br />' . Piwik_Translate('General_ChangeTagCloudView'),
            'order'             => 25
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_VisitsByDaysSinceLast'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByDaysSinceLast',
            'dimension'         => Piwik_Translate('VisitorInterest_VisitsByDaysSinceLast'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetVisitsByDaysSinceLastDocumentation'),
            'order'             => 30
        );
    }

    function addWidgets()
    {
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetLengths', 'VisitorInterest', 'getNumberOfVisitsPerVisitDuration');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetPages', 'VisitorInterest', 'getNumberOfVisitsPerPage');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_visitsByVisitCount', 'VisitorInterest', 'getNumberOfVisitsByVisitCount');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetVisitsByDaysSinceLast', 'VisitorInterest', 'getNumberOfVisitsByDaysSinceLast');
    }

    function addMenu()
    {
        Piwik_RenameMenuEntry('General_Visitors', 'VisitFrequency_SubmenuFrequency',
            'General_Visitors', 'VisitorInterest_Engagement');
    }

    function postLoad()
    {
        Piwik_AddAction('template_headerVisitsFrequency', array('Piwik_VisitorInterest', 'headerVisitsFrequency'));
        Piwik_AddAction('template_footerVisitsFrequency', array('Piwik_VisitorInterest', 'footerVisitsFrequency'));
    }

    // third element is unit (s for seconds, default is munutes)
    protected static $timeGap = array(
        array(0, 10, 's'),
        array(11, 30, 's'),
        array(31, 60, 's'),
        array(1, 2),
        array(2, 4),
        array(4, 7),
        array(7, 10),
        array(10, 15),
        array(15, 30),
        array(30)
    );

    protected static $pageGap = array(
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 7),
        array(8, 10),
        array(11, 14),
        array(15, 20),
        array(20)
    );

    /**
     * The set of ranges used when calculating the 'visitors who visited at least N times' report.
     */
    protected static $visitNumberGap = array(
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 6),
        array(7, 7),
        array(8, 8),
        array(9, 14),
        array(15, 25),
        array(26, 50),
        array(51, 100),
        array(101, 200),
        array(200)
    );

    /**
     * The set of ranges used when calculating the 'days since last visit' report.
     */
    protected static $daysSinceLastVisitGap = array(
        array(0, 0),
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 6),
        array(7, 7),
        array(8, 14),
        array(15, 30),
        array(31, 60),
        array(61, 120),
        array(121, 364),
        array(364)
    );

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    function archivePeriod($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $dataTableToSum = array(
            'VisitorInterest_timeGap',
            'VisitorInterest_pageGap',
            'VisitorInterest_visitsByVisitCount',
            'VisitorInterest_daysSinceLastVisit'
        );
        $archiveProcessing->archiveDataTable($dataTableToSum);
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    public function archiveDay($notification)
    {
        $this->archiveProcessing = $notification->getNotificationObject();

        if (!$this->archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        // these prefixes are prepended to the 'SELECT as' parts of each SELECT expression. detecting
        // these prefixes allows us to get all the data in one query.
        $timeGapPrefix = 'tg';
        $pageGapPrefix = 'pg';
        $visitsByVisitNumPrefix = 'vbvn';
        $daysSinceLastVisitPrefix = 'dslv';

        // extra condition for the SQL SELECT that makes sure only returning visits are counted
        // when creating the 'days since last visit' report. the SELECT expression below it
        // is used to count all new visits.
        $daysSinceLastExtraCondition = 'and log_visit.visitor_returning = 1';
        $selectAs = $daysSinceLastVisitPrefix . 'General_NewVisits';
        $newVisitCountSelect = "sum(case when log_visit.visitor_returning = 0 then 1 else 0 end) as `$selectAs`";

        // create the select expressions to use
        $timeGapSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visit_total_time', self::getSecondsGap(), 'log_visit', $timeGapPrefix);
        $pageGapSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visit_total_actions', self::$pageGap, 'log_visit', $pageGapPrefix);
        $visitsByVisitNumSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visitor_count_visits', self::$visitNumberGap, 'log_visit', $visitsByVisitNumPrefix);

        $daysSinceLastVisitSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visitor_days_since_last', self::$daysSinceLastVisitGap, 'log_visit', $daysSinceLastVisitPrefix,
            $daysSinceLastExtraCondition);
        array_unshift($daysSinceLastVisitSelects, $newVisitCountSelect);

        $selects = array_merge(
            $timeGapSelects, $pageGapSelects, $visitsByVisitNumSelects, $daysSinceLastVisitSelects);

        // select data for every report
        $row = $this->archiveProcessing->queryVisitsSimple(implode(',', $selects));

        // archive visits by total time report
        $recordName = 'VisitorInterest_timeGap';
        $this->archiveRangeStats($recordName, $row, Piwik_Archive::INDEX_NB_VISITS, $timeGapPrefix);

        // archive visits by total actions report
        $recordName = 'VisitorInterest_pageGap';
        $this->archiveRangeStats($recordName, $row, Piwik_Archive::INDEX_NB_VISITS, $pageGapPrefix);

        // archive visits by visit number report
        $recordName = 'VisitorInterest_visitsByVisitCount';
        $this->archiveRangeStats($recordName, $row, Piwik_Archive::INDEX_NB_VISITS, $visitsByVisitNumPrefix);

        // archive days since last visit report
        $recordName = 'VisitorInterest_daysSinceLastVisit';
        $this->archiveRangeStats($recordName, $row, Piwik_Archive::INDEX_NB_VISITS, $daysSinceLastVisitPrefix);
    }

    /**
     * Transforms and returns the set of ranges used to calculate the 'visits by total time'
     * report from ranges in minutes to equivalent ranges in seconds.
     */
    protected static function getSecondsGap()
    {
        $secondsGap = array();
        foreach (self::$timeGap as $gap) {
            if (count($gap) == 3 && $gap[2] == 's') // if the units are already in seconds, just assign them
            {
                $secondsGap[] = array($gap[0], $gap[1]);
            } else if (count($gap) == 2) {
                $secondsGap[] = array($gap[0] * 60, $gap[1] * 60);
            } else {
                $secondsGap[] = array($gap[0] * 60);
            }
        }
        return $secondsGap;
    }

    /**
     * Creates and archives a DataTable from some (or all) elements of a supplied database
     * row.
     *
     * @param string $recordName The record name to use when inserting the new archive.
     * @param array $row The database row to use.
     * @param string $selectAsPrefix The string to look for as the prefix of SELECT as
     *                               expressions. Elements in $row that have a SELECT as
     *                               with this string as a prefix are used in creating
     *                               the DataTable.'
     */
    protected function archiveRangeStats($recordName, $row, $index, $selectAsPrefix)
    {
        // create the DataTable from parts of the result row
        $dataTable = $this->archiveProcessing->getSimpleDataTableFromRow($row, $index, $selectAsPrefix);

        // insert the data table as a blob archive
        $this->archiveProcessing->insertBlobRecord($recordName, $dataTable->getSerialized());
        destroy($dataTable);
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    static public function headerVisitsFrequency($notification)
    {
        $out =& $notification->getNotificationObject();
        $out = '<div id="leftcolumn">';
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    static public function footerVisitsFrequency($notification)
    {
        $out =& $notification->getNotificationObject();
        $out = '</div>
			<div id="rightcolumn">
			';
        $out .= Piwik_FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
        $out .= '</div>';
    }
}

